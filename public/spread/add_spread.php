<?php
/* Copyright (C) 2023 EVARISK <technique@evarisk.com>
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
 *  \file       public/spread/add_spread.php
 *  \ingroup    saturne
 */

if (!defined('NOTOKENRENEWAL')) {
    define('NOTOKENRENEWAL', 1);
}
if (!defined('NOREQUIREMENU')) {
    define('NOREQUIREMENU', 1);
}
if (!defined('NOREQUIREHTML')) {
    define('NOREQUIREHTML', 1);
}
if (!defined('NOLOGIN')) { // This means this output page does not require to be logged
    define('NOLOGIN', 1);
}
if (!defined('NOCSRFCHECK')) { // We accept to go on this page from external website
    define('NOCSRFCHECK', 1);
}
if (!defined('NOIPCHECK')) { // Do not check IP defined into conf $dolibarr_main_restrict_ip
    define('NOIPCHECK', 1);
}
if (!defined('NOBROWSERNOTIF')) {
    define('NOBROWSERNOTIF', 1);
}

// Load Saturne environment
if (file_exists('../../../saturne/saturne.main.inc.php')) {
    require_once __DIR__ . '/../../../saturne/saturne.main.inc.php';
} elseif (file_exists('../../../saturne.main.inc.php')) {
    require_once __DIR__ . '/../../../../saturne/saturne.main.inc.php';
} else {
    die('Include of saturne main fails');
}

// Get module parameters
$moduleName   = GETPOST('module_name', 'alpha');
$objectType   = GETPOST('object_type', 'alpha');
$documentType = GETPOST('document_type', 'alpha');

$moduleNameLowerCase = strtolower($moduleName);

// Libraries
if (isModEnabled('societe')) {
    require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
    require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
}

require_once DOL_DOCUMENT_ROOT . '/ecm/class/ecmfiles.class.php';

require_once DOL_DOCUMENT_ROOT . '/custom/saturne/class/saturnesignature.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/saturne/class/saturnemail.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/doliletter/class/doliletterattendancesheet.class.php';
// Global variables definitions
global $conf, $db, $hookmanager, $langs, $user;

if (!isset($_SESSION['dol_login'])) {
    $user->loadDefaultValues();
} else {
    $user->fetch('', $_SESSION['dol_login'], '', 1);
    $user->getrights();
}

// Load translation files required by the page
saturne_load_langs(['doliletter@doliletter']);

// Get parameters
$id                 = GETPOST('id', 'int');
$ref                = GETPOST('ref', 'alpha');
$action             = GETPOST('action', 'aZ09');
$contextpage        = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : $objectType . 'signature'; // To manage different context of search
$cancel             = GETPOST('cancel', 'aZ09');
$backtopage         = GETPOST('backtopage', 'alpha');
$attendantTableMode = (GETPOSTISSET('attendant_table_mode') ? GETPOST('attendant_table_mode', 'alpha') : 'advanced');
$subaction          = GETPOST('subaction', 'alpha');

// Initialize technical objects
$className       = ucfirst($objectType);
$signatory       = new SaturneSignature($db, $moduleNameLowerCase, $objectType);
$saturneMail     = new SaturneMail($db, $moduleNameLowerCase, $objectType);
$usertmp         = new User($db);
$attendanceSheet = new DoliletterAttendanceSheet($db, $moduleNameLowerCase);
$form            = new Form($db);
$ecmFiles        = new EcmFiles($db);
if (isModEnabled('societe')) {
    $thirdparty = new Societe($db);
    $contact    = new Contact($db);
}

$objectsMetadata    = saturne_get_objects_metadata();

$attendanceSheet->fetch(0, '', ' AND object_type = ' . "'" . $objectType  . "'" . ' AND fk_object = ' . $id);

if ($action == 'add_spread_user') {
    if ($attendanceSheet->id <= 0 || $attendanceSheet->id == null) {

        $objectsMetadata[$objectType]['object']->fetch($id);

        $attendanceSheet->ref           = $objectsMetadata[$objectType]['object']->ref;
        $attendanceSheet->status        = $attendanceSheet::STATUS_VALIDATED;
        $attendanceSheet->fk_object     = $id;
        $attendanceSheet->object_type   = $objectType;
        $attendanceSheet->entity        = $conf->entity;
        $attendanceSheet->fk_user_creat = $user->id;

        $result = $attendanceSheet->create($user);
        if ($result < 0) {
            setEventMessages($attendanceSheet->error, $attendanceSheet->errors, 'errors');
            exit;
        }
    }

    $tmpSignatory = new SaturneSignature($db, $moduleNameLowerCase, $attendanceSheet->element);
    $tmpSignatory->element_id     = 0;
    $tmpSignatory->element_type   = 'user';
    $tmpSignatory->role           = '';
    $tmpSignatory->object_type    = $attendanceSheet->element;
    $tmpSignatory->fk_object      = $attendanceSheet->id;
    $tmpSignatory->module_name    = $moduleNameLowerCase;
    $tmpSignatory->status         = $tmpSignatory::STATUS_PENDING_SIGNATURE;

    $result = $tmpSignatory->create($user);
    if ($result < 0) {
        setEventMessages($signatory->error, $signatory->errors, 'errors');
        echo '<pre>'; print_r($signatory->db->lasterror()); echo '</pre>'; exit;
        exit;
    }
    $action = '';
}

if ($action == 'remove_spread_user') {
    $signatory_id = GETPOSTINT('signatory_id');

    $signatory->fetch($signatory_id);
    if ($signatory->id > 0) {
        $result = $signatory->delete($user);
    }
    $action = '';
}

if ($action == 'update_spread_user') {
    $signatory_id = GETPOSTINT('signatory_id');
    $signatory->fetch($signatory_id);
    if ($signatory->id > 0) {
        $tmpUser = new User($db);
        $tmpUser->fetch(GETPOSTINT('user_id'));
        $attendanceSheet->context = [
            'user' => $tmpUser->firstname . ' ' . $tmpUser->lastname,
            'old_user' => $signatory->element_id ? $signatory->firstname . ' ' . $signatory->lastname : ''
        ];
        $attendanceSheet->call_trigger('SPREAD_ADD_USER', $user);

        $signatory->element_id   = GETPOSTINT('user_id');
        $signatory->element_type = 'user';

        $signatory->firstname = $tmpUser->firstname;
        $signatory->lastname  = $tmpUser->lastname;

        $signatory->update($user);
    }
    $action = '';
}

if ($action == 'validate_signature') {
    $signatory_id = GETPOSTINT('signatory_id');
    $signatory->fetch($signatory_id);
    if ($signatory->id > 0) {
        $data      = json_decode(file_get_contents('php://input'), true);
        $signature = $data['signature'] ?? '';

        if (!empty($signature)) {
            $signatory->signature      = $signature;
            $signatory->status         = $signatory::STATUS_SIGNED;
            $signatory->signature_date = dol_now();
            $signatory->signature_url  = generate_random_id();
            $signatory->update($user);
        }
    }
    $action = '';
}

if ($action == 'save_public_note') {
    if ($attendanceSheet->id > 0) {
        $data = json_decode(file_get_contents('php://input'), true);
        $note = $data['note_public'] ?? '';

        $attendanceSheet->note_public = $note;
        $attendanceSheet->update($user);
    }
    $action = '';
}

if ($action == 'send_email') {
    $signatory_id = GETPOSTINT('signatory_id');

    $signatory->fetch($signatory_id);
    if ($signatory->id > 0) {
        require_once DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php';

        $objectsMetadata[$objectType]['object']->fetch($id);

        $tmpUser = new User($db);
        $tmpUser->fetch($signatory->element_id);

        $from = $conf->global->MAIN_MAIL_EMAIL_FROM;

        // Make substitution in email content
        $substitutionarray                       = getCommonSubstitutionArray($langs, 0, null, $objectsMetadata[$objectType]['object']);
        $substitutionarray['__OBJECT_ELEMENT__'] = dol_strtolower($langs->transnoentities(ucfirst($objectsMetadata[$objectType]['object']->element)));
        $substitutionarray['__REF__']            = $objectsMetadata[$objectType]['object']->ref;
        $signatoryLink = dol_buildpath('/custom/saturne/public/signature/add_signature.php', 3) . '?track_id=' . $signatory->signature_url . '&entity=1&module_name=doliletter&object_type=doliletterattendancesheet';
        $substitutionarray['__SATURNE_SIGNATORY_URL__'] = '<a href=' . $signatoryLink . ' target="_blank">' . $langs->transnoentities('SignatureEmailURL') . '</a>';
        complete_substitutions_array($substitutionarray, $langs, $objectsMetadata[$objectType]['object'], $parameters);

        $result  = $saturneMail->fetch(getDolGlobalInt('SATURNE_EMAIL_TEMPLATE_SPREAD'));
        $subject = $result > 0 ? $saturneMail->topic : $langs->transnoentities('EmailSpreadTopic');
        $message = $result > 0 ? $saturneMail->content : $langs->transnoentities('EmailSpreadContent');
        $sendto  = $tmpUser->email;

        $subject = make_substitutions($subject, $substitutionarray);
        $message = make_substitutions($message, $substitutionarray);

        // Create form object
        // Send mail (substitutionarray must be done just before this)
        $mailfile = new CMailFile($subject, $sendto, $from, $message, [], [], [], '', '', 0, -1, '', '', '', '', 'mail');
        if ($mailfile->error) {
            setEventMessages($mailfile->error, $mailfile->errors, 'errors');
        } elseif (!empty($conf->global->MAIN_MAIL_SMTPS_ID) || $conf->global->SATURNE_USE_ALL_EMAIL_MODE > 0) {
            $result = $mailfile->sendfile();
            if ($result) {
                $signatory->last_email_sent_date = dol_now();
                $signatory->update($user, true);
                $signatory->setPending($user, false);
                echo '<input type="hidden" id="success" value="' . $langs->transnoentities('SendEmailAt', dol_escape_htmltag($sendto)) . '">';
                exit;
            } else {
                echo '<input type="hidden" id="error" value="' . $langs->transnoentities('ErrorFailedToSendMail', dol_escape_htmltag($from), dol_escape_htmltag($sendto)) . '">';
                exit;
            }
        }
    }
}

$ecmFiles->fetchAll('', '', 0, 0, 't.share:isnot:null');

$linkedFiles = [];
if (is_array($ecmFiles->lines) && !empty($ecmFiles->lines)) {
    $linkedFiles = array_filter($ecmFiles->lines, function ($ecmFilesLine) use ($objectType, $id, $objectsMetadata) {

        if ($objectType == 'project') {
            $objectType = 'projet';
        } elseif ($objectType == 'project_task') {
            $objectType = 'projet_task';
        }

        $objectType = $objectsMetadata[$objectType]['table_element'];

        return $ecmFilesLine->src_object_type == $objectType && $ecmFilesLine->src_object_id == $id;
    });
}

$objectsMetadata[$objectType]['object']->fetch($id);
$objectRef   = $objectsMetadata[$objectType]['object']->ref;
$objectLabel = $objectsMetadata[$objectType]['object']->title ?? '';

/*
 * View
 */

$title  = $langs->trans('Signature');
$moreJS = ['/saturne/js/includes/signature-pad.min.js'];

$conf->dol_hide_topmenu  = 1;
$conf->dol_hide_leftmenu = 1;

saturne_header(0,'', $title, '', '', 0, 0, $moreJS, [], '', 'page-public-card');

$signatories = $signatory->fetchSignatory('', $attendanceSheet->id ?? 0, $attendanceSheet->element);
if ($signatories <= 0) {
    $signatories = [];
} elseif (is_array($signatories)) {
    $signatories = current($signatories);
}

require_once __DIR__ . '/../../core/tpl/spread/public_spread_view.tpl.php';

llxFooter('', 'public');
$db->close();