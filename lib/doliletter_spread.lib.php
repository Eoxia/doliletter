<?php
/* Copyright (C) 2025 EVARISK <technique@evarisk.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    lib/doliletter_spread.lib.php
 * \ingroup doliletter
 * \brief   Library files with common functions for the spread public page
 */

require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

/**
 * Element type carried by a signatory registered from the public page.
 *
 * Such a signatory has no Dolibarr user behind it: identity is fully held by the
 * firstname / lastname / email / phone columns of the signature table.
 */
const DOLILETTER_SPREAD_EXTERNAL_ELEMENT_TYPE = 'external';

/**
 * Sub folder of a certification holding the documents (PDF...) uploaded for it, next to its photos.
 *
 * Kept apart so that the files list of the media block does not show the photos a second time.
 */
const DOLILETTER_SPREAD_CERT_DOCUMENTS_DIR = 'documents';

/**
 * Pattern of a phone number entered on the spread, without delimiters so the page script builds the
 * same RegExp: an optional leading "+", then 8 to 15 digits (E.164 caps a number at 15) that the
 * usual separators (space, dot, dash, brackets) may split. No letter goes through.
 */
const DOLILETTER_SPREAD_PHONE_PATTERN = '^\+?(?:[ .\-()]*[0-9]){8,15}[ .\-()]*$';

/**
 * PDF model of the attendance sheet generated at each signature and downloaded from the public page.
 *
 * Named here rather than read from the default model of the Diffusion tab, which may be the ODT one:
 * the file offered for download must be the PDF one.
 */
const DOLILETTER_SPREAD_SIGNIN_SHEET_PDF_MODEL = 'signinsheetdocument_FE_Doliletter';

/**
 * What the people of a spread get of a document of the diffused object: nothing, a file to download, or
 * the document shown in full on the page, to read before signing.
 *
 * The public page serves the files of the object that have a share key, and shows in full the ones
 * flagged with the "favorite" extra field of Saturne: a mode is that pair, set from the Diffusion tab.
 */
const DOLILETTER_SPREAD_DOCUMENT_HIDDEN   = 'hidden';
const DOLILETTER_SPREAD_DOCUMENT_DOWNLOAD = 'download';
const DOLILETTER_SPREAD_DOCUMENT_READ     = 'read';

/**
 * Create the attendance sheet backing a spread the first time someone is added to it.
 *
 * @param  DoliletterAttendanceSheet $attendanceSheet Attendance sheet, already fetched (may be empty)
 * @param  array                     $objectsMetadata Saturne objects metadata
 * @param  string                    $objectType      Type of the spread source object
 * @param  int                       $objectId        ID of the spread source object
 * @param  User                      $user            User doing the creation
 * @return int                                        < 0 if KO, ID of the attendance sheet if OK
 */
function doliletter_spread_ensure_attendance_sheet(DoliletterAttendanceSheet $attendanceSheet, array $objectsMetadata, string $objectType, int $objectId, User $user): int
{
    global $conf;

    if ($attendanceSheet->id > 0) {
        return $attendanceSheet->id;
    }

    // The metadata of the diffused object is missing when its module does not contribute it on
    // the calling page : creating the sheet from it would be a fatal error.
    if (!isset($objectsMetadata[$objectType]['object'])) {
        $attendanceSheet->error = 'ErrorSpreadObjectTypeUnknown';
        return -1;
    }

    $objectsMetadata[$objectType]['object']->fetch($objectId);

    // A visitor registering from the public page has no session, so $user->id is empty and
    // createCommon() would write null into fk_user_creat, which the column refuses. The sheet is
    // then attributed to the owner of the diffused object, the one who asked for the spread.
    $sheetAuthorId = ($user->id > 0) ? (int) $user->id : (int) $objectsMetadata[$objectType]['object']->fk_user_creat;

    $attendanceSheet->ref           = $objectsMetadata[$objectType]['object']->ref;
    $attendanceSheet->status        = $attendanceSheet::STATUS_VALIDATED;
    $attendanceSheet->fk_object     = $objectId;
    $attendanceSheet->object_type   = $objectType;
    $attendanceSheet->entity        = $conf->entity;
    $attendanceSheet->fk_user_creat = $sheetAuthorId;

    // createCommon() overwrites fk_user_creat with $user->id whenever it is not > 0: an object
    // with no owner would bring back the very null the column rejects
    if ($sheetAuthorId <= 0) {
        $attendanceSheet->error = 'ErrorSpreadNoAuthorForAttendanceSheet';

        return -1;
    }

    return $attendanceSheet->create($user);
}

/**
 * Path of the PDF attendance sheet of a spread, below the documents directory of DoliLetter.
 *
 * The PDF model writes one file per attendance sheet, named after its ref, and overwrites it at each
 * generation: it is also the file listed in the Diffusion tab.
 *
 * @param  DoliletterAttendanceSheet $attendanceSheet Attendance sheet of the spread
 * @return string                                     Relative path, as document.php expects it
 */
function doliletter_spread_get_signin_sheet_file(DoliletterAttendanceSheet $attendanceSheet): string
{
    $sheetRef = dol_sanitizeFileName($attendanceSheet->ref);

    return 'signinsheet/' . $sheetRef . '/' . $sheetRef . '.pdf';
}

/**
 * Generate the PDF attendance sheet of a spread again, so it carries every signature given so far.
 *
 * The sheet was only produced by hand from the Diffusion tab: the copy found there missed whoever had
 * signed since. A failure is logged and returned, it must not undo the signature it follows.
 *
 * @param  DoliletterAttendanceSheet $attendanceSheet Attendance sheet of the spread, fetched
 * @param  User                      $user            User generating, anonymous on the public page
 * @param  Translate                 $outputLangs     Language of the document
 * @return int                                        1 if OK, <= 0 if KO
 */
function doliletter_spread_generate_signin_sheet(DoliletterAttendanceSheet $attendanceSheet, User $user, Translate $outputLangs): int
{
    global $db;

    if ($attendanceSheet->id <= 0) {
        return -1;
    }

    dol_include_once('/doliletter/class/doliletterdocuments/signinsheetdocument.class.php');

    $document   = new SigninSheetDocument($db);
    $moreParams = ['object' => $attendanceSheet, 'user' => $user, 'zone' => 'private', 'specimen' => 0];

    $result = $document->generateDocument(DOLILETTER_SPREAD_SIGNIN_SHEET_PDF_MODEL, $outputLangs, 0, 0, 0, $moreParams);
    if ($result <= 0) {
        dol_syslog('doliletter_spread: attendance sheet ' . $attendanceSheet->ref . ' not generated - ' . $document->error, LOG_ERR);
    }

    return $result;
}

/**
 * Font Awesome icon of a file offered for download on the public page.
 *
 * Was declared at the bottom of public_spread_view.tpl.php, inside a block: the function only existed
 * once that line was reached, after the files list calling it, which then died on an undefined function.
 *
 * @param  string $extension Extension of the file, lowercase
 * @return string            Classes of the icon
 */
function doliletter_spread_get_file_icon(string $extension): string
{
    $icons = [
        'pdf'  => 'fas fa-file-pdf text-danger',
        'doc'  => 'fas fa-file-word text-primary',
        'docx' => 'fas fa-file-word text-primary',
        'xls'  => 'fas fa-file-excel text-success',
        'xlsx' => 'fas fa-file-excel text-success',
        'ppt'  => 'fas fa-file-powerpoint text-warning',
        'pptx' => 'fas fa-file-powerpoint text-warning',
        'txt'  => 'fas fa-file-alt text-secondary',
        'jpg'  => 'fas fa-file-image text-info',
        'jpeg' => 'fas fa-file-image text-info',
        'png'  => 'fas fa-file-image text-info',
        'gif'  => 'fas fa-file-image text-info',
        'zip'  => 'fas fa-file-archive text-dark',
        'rar'  => 'fas fa-file-archive text-dark',
        '7z'   => 'fas fa-file-archive text-dark',
    ];

    return $icons[$extension] ?? 'fas fa-file text-muted';
}

/**
 * Directory a document added from the documents tab of a spread is saved in: the one of the attached
 * files tab of the diffused object, so that the document is found there too.
 *
 * An object of a Saturne module keeps them under <module>/<element>/<ref>, as saturne_document.php does,
 * a Dolibarr object where its Documents tab does. The few objects the core gives no directory (contact,
 * lot...) get one below DoliLetter: the public page serves the files by their share key anyway.
 *
 * @param  CommonObject $object Diffused object, fetched
 * @return string               Absolute directory, without trailing slash
 */
function doliletter_spread_get_document_dir(CommonObject $object): string
{
    global $conf;

    $entity = (int) ($object->entity ?: $conf->entity);
    if ($object instanceof SaturneObject && !empty($conf->{$object->module}->multidir_output[$entity])) {
        $documentDir = $conf->{$object->module}->multidir_output[$entity] . '/' . $object->element . '/' . get_exdir(0, 0, 0, 1, $object);
    } else {
        $documentDir = (string) getMultidirOutput($object, '', 1);
    }

    if (dol_strlen($documentDir) == 0 || strpos($documentDir, 'error-') === 0) {
        $documentDir = $conf->doliletter->multidir_output[$entity] . '/spread/' . $object->element . '/' . (int) $object->id;
    }

    return rtrim($documentDir, '/');
}

/**
 * Files and links of the diffused object, with what the people of the spread get of each one.
 *
 * They are the ones the public page picks from: the files indexed on the object and the links recorded
 * with its spread type. The documents generated from the object card are added: they stay indexed on
 * their Saturne document, hence hidden, until they are diffused.
 *
 * @param  CommonObject $object       Diffused object, fetched
 * @param  string       $tableElement Table of the object, as the files are indexed on it
 * @param  string       $objectType   Spread type of the object, as the public page reads its links
 * @return array<int, array{type: string, id: int, name: string, date: int, generated: bool, mode: string, url: string}> Newest first
 */
function doliletter_spread_get_documents(CommonObject $object, string $tableElement, string $objectType): array
{
    global $db;

    require_once DOL_DOCUMENT_ROOT . '/ecm/class/ecmfiles.class.php';
    require_once DOL_DOCUMENT_ROOT . '/core/class/link.class.php';

    $documents = [];

    $sql  = 'SELECT f.rowid FROM ' . MAIN_DB_PREFIX . 'ecm_files as f';
    $sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . "saturne_object_documents as d ON f.src_object_type = 'saturne_object_documents' AND d.rowid = f.src_object_id";
    $sql .= ' WHERE f.entity IN (' . getEntity('ecmfiles') . ')';
    $sql .= " AND ((f.src_object_type = '" . $db->escape($tableElement) . "' AND f.src_object_id = " . ((int) $object->id) . ')';
    $sql .= " OR (d.parent_type = '" . $db->escape($object->element) . "' AND d.parent_id = " . ((int) $object->id) . '))';

    $resql = $db->query($sql);
    while ($resql && ($obj = $db->fetch_object($resql))) {
        $ecmFile = new EcmFiles($db);
        if ($ecmFile->fetch($obj->rowid) <= 0) {
            continue;
        }

        $mode = DOLILETTER_SPREAD_DOCUMENT_HIDDEN;
        if ($ecmFile->src_object_type == $tableElement && dol_strlen((string) $ecmFile->share) > 0) {
            $mode = empty($ecmFile->array_options['options_favorite']) ? DOLILETTER_SPREAD_DOCUMENT_DOWNLOAD : DOLILETTER_SPREAD_DOCUMENT_READ;
        }

        $documents[] = [
            'type'      => 'file',
            'id'        => (int) $ecmFile->id,
            'name'      => $ecmFile->filename,
            'date'      => (int) $ecmFile->date_c,
            'generated' => ($ecmFile->gen_or_uploaded == 'generated'),
            'mode'      => $mode,
            'url'       => ($mode != DOLILETTER_SPREAD_DOCUMENT_HIDDEN) ? DOL_URL_ROOT . '/document.php?hashp=' . urlencode($ecmFile->share) : '',
        ];
    }

    $links = [];
    $link  = new Link($db);
    $link->fetchAll($links, $objectType, $object->id);
    foreach ($links as $linkedItem) {
        $linkedItem->fetch_optionals();

        // A link is always offered on the public page, it can only be shown in full or not
        $documents[] = [
            'type'      => 'link',
            'id'        => (int) $linkedItem->id,
            'name'      => dol_strlen($linkedItem->label) ? $linkedItem->label : $linkedItem->url,
            'date'      => (int) $linkedItem->datea,
            'generated' => false,
            'mode'      => empty($linkedItem->array_options['options_favorite']) ? DOLILETTER_SPREAD_DOCUMENT_DOWNLOAD : DOLILETTER_SPREAD_DOCUMENT_READ,
            'url'       => $linkedItem->url,
        ];
    }

    usort($documents, fn ($a, $b) => $b['date'] <=> $a['date']);

    return $documents;
}

/**
 * Set what the people of a spread get of one document of the diffused object.
 *
 * To be served, a file must be indexed on the object and have a share key: a generated document is
 * moved onto the object for that, as Digirisk does for the documents of its mobile forms. Hiding a file
 * removes its share key, so its public link stops working.
 *
 * @param  CommonObject $object       Diffused object, fetched
 * @param  string       $tableElement Table of the object, as the files are indexed on it
 * @param  string       $objectType   Spread type of the object, as the public page reads its links
 * @param  string       $type         'file' or 'link'
 * @param  int          $id           ID of the file in the index, or of the link
 * @param  string       $mode         One of the DOLILETTER_SPREAD_DOCUMENT_* modes
 * @param  User         $user         User doing the change
 * @return int                        Negative if KO, positive if OK
 */
function doliletter_spread_set_document_mode(CommonObject $object, string $tableElement, string $objectType, string $type, int $id, string $mode, User $user): int
{
    global $db;

    // Only a document of the object: any other ID would let the tab publish a file of another record
    $isDocumentOfObject = false;
    foreach (doliletter_spread_get_documents($object, $tableElement, $objectType) as $document) {
        $isDocumentOfObject = $isDocumentOfObject || ($document['type'] == $type && $document['id'] == $id);
    }
    if (!$isDocumentOfObject || !in_array($mode, [DOLILETTER_SPREAD_DOCUMENT_HIDDEN, DOLILETTER_SPREAD_DOCUMENT_DOWNLOAD, DOLILETTER_SPREAD_DOCUMENT_READ], true)) {
        return -1;
    }

    if ($type == 'link') {
        $link = new Link($db);
        if ($mode == DOLILETTER_SPREAD_DOCUMENT_HIDDEN || $link->fetch($id) <= 0) {
            return -1;
        }

        $link->fetch_optionals();
        $link->array_options['options_favorite'] = ($mode == DOLILETTER_SPREAD_DOCUMENT_READ) ? 1 : 0;

        return $link->insertExtraFields();
    }

    $ecmFile = new EcmFiles($db);
    if ($ecmFile->fetch($id) <= 0) {
        return -1;
    }

    if ($mode == DOLILETTER_SPREAD_DOCUMENT_HIDDEN) {
        $ecmFile->share = '';
    } else {
        $ecmFile->src_object_type = $tableElement;
        $ecmFile->src_object_id   = $object->id;
        if (dol_strlen((string) $ecmFile->share) == 0) {
            require_once DOL_DOCUMENT_ROOT . '/core/lib/security2.lib.php';
            $ecmFile->share = getRandomPassword(true);
        }
    }
    // update() saves the extra fields as well
    $ecmFile->array_options['options_favorite'] = ($mode == DOLILETTER_SPREAD_DOCUMENT_READ) ? 1 : 0;

    return $ecmFile->update($user);
}

/**
 * Add documents to the spread from its documents tab: saved with the attached files of the diffused
 * object, then shown in full on the public page, which is what a document is added there for.
 *
 * @param  CommonObject $object       Diffused object, fetched
 * @param  string       $tableElement Table of the object, as the files are indexed on it
 * @param  string       $objectType   Spread type of the object
 * @param  string       $inputName    Name of the file input of the form
 * @param  User         $user         User adding the documents
 * @return int                        Negative if KO (dol_add_file_process() tells why), else the number of documents added
 */
function doliletter_spread_add_documents(CommonObject $object, string $tableElement, string $objectType, string $inputName, User $user): int
{
    $knownFiles = array_filter(doliletter_spread_get_documents($object, $tableElement, $objectType), fn ($document) => $document['type'] == 'file');
    $knownIds   = array_column($knownFiles, 'id');

    // Indexed on the object (third argument): the upload itself reports its errors and its success
    if (dol_add_file_process(doliletter_spread_get_document_dir($object), 0, 1, $inputName, '', null, '', 1, $object) <= 0) {
        return -1;
    }

    $added = 0;
    foreach (doliletter_spread_get_documents($object, $tableElement, $objectType) as $document) {
        if ($document['type'] == 'file' && !in_array($document['id'], $knownIds, true)) {
            if (doliletter_spread_set_document_mode($object, $tableElement, $objectType, 'file', $document['id'], DOLILETTER_SPREAD_DOCUMENT_READ, $user) < 0) {
                return -1;
            }
            $added++;
        }
    }

    return $added;
}

/**
 * Readable reason why an object refused to be saved.
 *
 * The public registration used to answer a bare "Error": the caller could neither tell the visitor
 * what to correct nor report anything useful. Dolibarr puts the database message in ->errors and
 * the business one in ->error, either of which may be a translation key.
 *
 * @param  CommonObject $object Object that failed
 * @param  Translate    $langs  Translation handler
 * @return string               Reason, or a generic label when the object said nothing
 */
function doliletter_spread_get_object_error(CommonObject $object, Translate $langs): string
{
    $reasons = [];

    if (dol_strlen($object->error)) {
        $reasons[] = $langs->transnoentities($object->error);
    }
    foreach ((array) $object->errors as $objectError) {
        if (dol_strlen($objectError)) {
            $reasons[] = $langs->transnoentities($objectError);
        }
    }

    $reasons = array_unique($reasons);

    dol_syslog('doliletter_spread: ' . get_class($object) . ' KO - ' . implode(' | ', $reasons), LOG_ERR);

    return !empty($reasons) ? implode(' - ', $reasons) : $langs->transnoentities('ErrorSpreadUnknown');
}

/**
 * Situated error message for the public page.
 *
 * A bare "Error" on a page nobody can debug from is a dead end: the visitor cannot say what they
 * were doing and the support cannot tell where it broke. The message carries a stable code, the
 * subject, the script and line it comes from, and the raw reason, so that a screenshot is enough.
 *
 * @param  string    $code    Stable code of the failure point, quoted in support requests
 * @param  string    $subject Translation key naming what the visitor was doing
 * @param  string    $reason  Raw reason, already readable
 * @param  string    $file    __FILE__ of the failure point
 * @param  int       $line    __LINE__ of the failure point
 * @param  Translate $langs   Translation handler
 * @return string             Message to show to the visitor
 */
function doliletter_spread_public_error(string $code, string $subject, string $reason, string $file, int $line, Translate $langs): string
{
    if (!dol_strlen($reason)) {
        $reason = $langs->transnoentities('ErrorSpreadUnknown');
    }

    $location = basename($file) . ':' . $line;

    dol_syslog('doliletter_spread: [' . $code . '] ' . $location . ' - ' . $reason, LOG_ERR);

    return $langs->transnoentities('ErrorSpreadContext', $code, $langs->transnoentities($subject), $location, $reason);
}

/**
 * Make the images of a Saturne media block reachable by an anonymous visitor.
 *
 * The block serves its gallery through document.php, which requires a session: on the public page
 * the visitor received the login page instead of the photo they had just uploaded. The Saturne
 * image wrapper takes exactly the same parameters and is the one already used for the risk photos
 * of this page. Only the anonymous rendering goes through it, a logged-in user keeps document.php
 * and its access control.
 *
 * @param  string $mediaBlockHtml HTML returned by saturne_render_media_block()
 * @return string                 Same block, its image URLs served publicly
 */
function doliletter_spread_public_media_block(string $mediaBlockHtml): string
{
    // Les URL apparaissent en clair dans le src et echappees dans le data-json de la galerie :
    // couper avant le nom de script couvre les deux formes
    return str_replace('document.php?modulepart=', 'custom/saturne/utils/viewimage.php?modulepart=', $mediaBlockHtml);
}

/**
 * Full name of a signatory registered from the public page.
 *
 * @param  SaturneSignature $signatory Signatory to name
 * @return string                      Firstname + lastname, empty when both are unset
 */
function doliletter_spread_get_signatory_name(SaturneSignature $signatory): string
{
    return trim($signatory->firstname . ' ' . $signatory->lastname);
}

/**
 * Whether a signatory designates someone: a Dolibarr user, or at least one identity field.
 *
 * @param  SaturneSignature $signatory Signatory to check
 * @return bool                        False for a signature nobody was assigned to
 */
function doliletter_spread_signatory_has_identity(SaturneSignature $signatory): bool
{
    return $signatory->element_id > 0 || dol_strlen(trim($signatory->firstname . $signatory->lastname . $signatory->email . $signatory->phone)) > 0;
}

/**
 * Visible and mandatory state of the identity fields of an external signatory, from the module setup.
 *
 * A field nobody configured yet is shown and mandatory: with every field optional, a person could
 * register with a phone number only and nobody knew who signed. The setup switches save 0 when turned
 * off, so a field the admin made optional stays optional.
 *
 * @return array<string, array{visible: bool, mandatory: bool}> Keyed by FIRSTNAME, LASTNAME, EMAIL and PHONE
 */
function doliletter_spread_get_ext_fields_config(): array
{
    $extFieldsConfig = [];
    foreach (['FIRSTNAME', 'LASTNAME', 'EMAIL', 'PHONE'] as $extField) {
        $visibleConst   = 'DOLILETTER_SPREAD_EXT_FIELD_' . $extField . '_VISIBLE';
        $mandatoryConst = 'DOLILETTER_SPREAD_EXT_FIELD_' . $extField . '_MANDATORY';

        $isVisible   = getDolGlobalInt($visibleConst) > 0 || getDolGlobalString($visibleConst) === '';
        $isMandatory = getDolGlobalInt($mandatoryConst) > 0 || ($isVisible && getDolGlobalString($mandatoryConst) === '');

        $extFieldsConfig[$extField] = ['visible' => $isVisible || $isMandatory, 'mandatory' => $isMandatory];
    }

    return $extFieldsConfig;
}

/**
 * Whether a phone number entered on the spread is valid.
 *
 * @param  string $phone Phone number as typed
 * @return bool          True when empty (the mandatory check is the configuration's business) or matching DOLILETTER_SPREAD_PHONE_PATTERN
 */
function doliletter_spread_is_valid_phone(string $phone): bool
{
    $phone = trim($phone);

    return $phone === '' || preg_match('/' . DOLILETTER_SPREAD_PHONE_PATTERN . '/', $phone) === 1;
}

/**
 * Decode the JSON payload carried by a signatory.
 *
 * The Saturne signature table already owns a free `json` column, so the answers given by a
 * signatory on the public page are stored there rather than in a dedicated table. Reading and
 * writing it requires a DoliletterSpreadSignature: SaturneObject drops that disabled field.
 *
 * @param  SaturneSignature $signatory Signatory to read
 * @return array                       Decoded payload, empty when unset or malformed
 */
function doliletter_spread_get_signatory_data(SaturneSignature $signatory): array
{
    if (empty($signatory->json)) {
        return [];
    }

    $data = json_decode($signatory->json, true);

    return is_array($data) ? $data : [];
}

/**
 * Certification codes a signatory declared they are not concerned by.
 *
 * @param  SaturneSignature $signatory Signatory to read
 * @return string[]                    Certification codes
 */
function doliletter_spread_get_not_concerned_certifications(SaturneSignature $signatory): array
{
    $data = doliletter_spread_get_signatory_data($signatory);

    return (!empty($data['cert_not_concerned']) && is_array($data['cert_not_concerned'])) ? $data['cert_not_concerned'] : [];
}

/**
 * Flag (or unflag) a certification as "not concerned" for a signatory.
 *
 * The signatory must be a DoliletterSpreadSignature, otherwise the `json` column is not saved.
 *
 * @param  SaturneSignature $signatory     Signatory answering
 * @param  string           $certCode      Certification code
 * @param  bool             $notConcerned  True to declare the signatory is not concerned
 * @param  User             $user          User doing the update
 * @return int                             < 0 if KO, > 0 if OK
 */
function doliletter_spread_set_not_concerned_certification(SaturneSignature $signatory, string $certCode, bool $notConcerned, User $user): int
{
    $data          = doliletter_spread_get_signatory_data($signatory);
    $notConcerneds = doliletter_spread_get_not_concerned_certifications($signatory);

    if ($notConcerned) {
        if (!in_array($certCode, $notConcerneds, true)) {
            $notConcerneds[] = $certCode;
        }
    } else {
        $notConcerneds = array_values(array_diff($notConcerneds, [$certCode]));
    }

    $data['cert_not_concerned'] = $notConcerneds;
    $signatory->json            = json_encode($data);

    return $signatory->update($user, 1);
}

/**
 * Danger categories a signatory declared having taken note of.
 *
 * @param  SaturneSignature $signatory Signatory to read
 * @return int[]                       Danger category positions
 */
function doliletter_spread_get_acknowledged_risks(SaturneSignature $signatory): array
{
    $data = doliletter_spread_get_signatory_data($signatory);

    return (!empty($data['acknowledged_risks']) && is_array($data['acknowledged_risks'])) ? array_map('intval', $data['acknowledged_risks']) : [];
}

/**
 * Record that a signatory has taken note of one risk, with its protections and its photos.
 *
 * The signatory must be a DoliletterSpreadSignature, otherwise the `json` column is not saved.
 *
 * @param  SaturneSignature $signatory    Signatory answering
 * @param  int              $riskCategory Danger category position of the risk
 * @param  User             $user         User doing the update
 * @return int                            < 0 if KO, > 0 if OK
 */
function doliletter_spread_acknowledge_risk(SaturneSignature $signatory, int $riskCategory, User $user): int
{
    $data         = doliletter_spread_get_signatory_data($signatory);
    $acknowledged = doliletter_spread_get_acknowledged_risks($signatory);

    if (!in_array($riskCategory, $acknowledged, true)) {
        $acknowledged[] = $riskCategory;
    }

    $data['acknowledged_risks'] = $acknowledged;
    $signatory->json            = json_encode($data);

    return $signatory->update($user, 1);
}

/**
 * Risks the signatory still has to take note of before being allowed to sign.
 *
 * @param  array $risks        Risks of the plan, each holding a 'category' and a 'name'
 * @param  int[] $acknowledged Danger category positions already acknowledged
 * @return array               Risks left, same shape as the input
 */
function doliletter_spread_get_pending_risks(array $risks, array $acknowledged): array
{
    $pending = [];
    foreach ($risks as $risk) {
        if (!in_array((int) $risk['category'], $acknowledged, true)) {
            $pending[] = $risk;
        }
    }

    return $pending;
}

/**
 * Directory holding the photos uploaded for one certification of one signatory.
 *
 * Must stay aligned with the sub directory given to saturne_render_media_block().
 *
 * @param  string $certBaseDir Certifications directory of the prevention plan
 * @param  int    $signatoryId ID of the signatory
 * @param  string $certCode    Certification code
 * @return string              Absolute directory path
 */
function doliletter_spread_get_certification_dir(string $certBaseDir, $signatoryId, string $certCode): string
{
    return $certBaseDir . '/' . $signatoryId . '/' . dol_sanitizeFileName($certCode);
}

/**
 * Files uploaded for one certification of one signatory: the photos, then the documents (PDF...) kept in
 * their own sub folder.
 *
 * @param  string $certDir Directory of the certification, from doliletter_spread_get_certification_dir()
 * @return array           One entry per file: name, path (relative to $certDir), is_image
 */
function doliletter_spread_get_certification_files(string $certDir): array
{
    $certFiles = [];
    foreach (['', DOLILETTER_SPREAD_CERT_DOCUMENTS_DIR . '/'] as $certFilesSubDir) {
        if (!dol_is_dir($certDir . '/' . $certFilesSubDir)) {
            continue;
        }

        foreach (dol_dir_list($certDir . '/' . $certFilesSubDir, 'files', 0, '', '(\.meta|_preview.*\.png)$', 'name') as $certFile) {
            $certFiles[] = [
                'name'     => $certFile['name'],
                'path'     => $certFilesSubDir . $certFile['name'],
                'is_image' => str_starts_with(dol_mimetype($certFile['name']), 'image/'),
            ];
        }
    }

    return $certFiles;
}

/**
 * Build the state of every certification required by a prevention plan, for one signatory.
 *
 * @param  array    $certifications       Certifications required by the prevention plan
 * @param  array    $certificationOptions Certification code => label
 * @param  string   $certBaseDir          Certifications directory of the prevention plan
 * @param  int      $signatoryId          ID of the signatory
 * @param  string[] $notConcernedCodes    Codes the signatory declared they are not concerned by
 * @return array                          One entry per certification: code, label, mandatory, has_file, files, not_concerned
 */
function doliletter_spread_get_certification_states(array $certifications, array $certificationOptions, string $certBaseDir, $signatoryId, array $notConcernedCodes): array
{
    $states = [];

    foreach ($certifications as $certification) {
        $certCode = $certification['code'];
        // A certification received as a document (PDF...) is provided as well as one photographed
        $certFiles = doliletter_spread_get_certification_files(doliletter_spread_get_certification_dir($certBaseDir, $signatoryId, $certCode));

        $states[] = [
            'code'          => $certCode,
            'label'         => $certificationOptions[$certCode] ?? $certCode,
            'mandatory'     => !empty($certification['mandatory']),
            'has_file'      => !empty($certFiles),
            'files'         => $certFiles,
            'not_concerned' => in_array($certCode, $notConcernedCodes, true),
        ];
    }

    return $states;
}

/**
 * Mandatory certifications still waiting for an answer: no photo uploaded and no "not concerned" declaration.
 *
 * @param  array $certificationStates States built by doliletter_spread_get_certification_states()
 * @return array                      Pending certifications, same structure as the given states
 */
function doliletter_spread_get_pending_certifications(array $certificationStates): array
{
    return array_values(array_filter($certificationStates, function (array $certificationState) {
        return $certificationState['mandatory'] && empty($certificationState['has_file']) && empty($certificationState['not_concerned']);
    }));
}

/**
 * State of each step of the signature workflow shown at the top of the spread public page.
 *
 * A step is only done once the ones before it are: risks read while nobody is identified yet do not
 * make the reading done, the person has yet to be known. The page script applies the same rule when it
 * refreshes the steps (spreadSteps.getStates()).
 *
 * @param  array<string, bool> $done Whether each step meets its own condition, in order: who, read, sign
 * @return array<string, string>     Same keys: 'done', 'current' for the first step not done, 'todo' after it
 */
function doliletter_spread_get_step_states(array $done): array
{
    $states       = [];
    $previousDone = true;
    $hasCurrent   = false;
    foreach ($done as $step => $isDone) {
        $isDone = $previousDone && $isDone;
        if ($isDone) {
            $states[$step] = 'done';
        } elseif (!$hasCurrent) {
            $states[$step] = 'current';
            $hasCurrent    = true;
        } else {
            $states[$step] = 'todo';
        }
        $previousDone = $isDone;
    }

    return $states;
}
