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

    $objectsMetadata[$objectType]['object']->fetch($objectId);

    $attendanceSheet->ref           = $objectsMetadata[$objectType]['object']->ref;
    $attendanceSheet->status        = $attendanceSheet::STATUS_VALIDATED;
    $attendanceSheet->fk_object     = $objectId;
    $attendanceSheet->object_type   = $objectType;
    $attendanceSheet->entity        = $conf->entity;
    $attendanceSheet->fk_user_creat = $user->id;

    return $attendanceSheet->create($user);
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
 * Directory holding the photos uploaded for one certification of one signatory.
 *
 * Must stay aligned with the sub directory given to saturne_render_media_block().
 *
 * @param  string $certBaseDir Certifications directory of the prevention plan
 * @param  int    $signatoryId ID of the signatory
 * @param  string $certCode    Certification code
 * @return string              Absolute directory path
 */
function doliletter_spread_get_certification_dir(string $certBaseDir, int $signatoryId, string $certCode): string
{
    return $certBaseDir . '/' . $signatoryId . '/' . dol_sanitizeFileName($certCode);
}

/**
 * Build the state of every certification required by a prevention plan, for one signatory.
 *
 * @param  array    $certifications       Certifications required by the prevention plan
 * @param  array    $certificationOptions Certification code => label
 * @param  string   $certBaseDir          Certifications directory of the prevention plan
 * @param  int      $signatoryId          ID of the signatory
 * @param  string[] $notConcernedCodes    Codes the signatory declared they are not concerned by
 * @return array                          One entry per certification: code, label, mandatory, has_file, not_concerned
 */
function doliletter_spread_get_certification_states(array $certifications, array $certificationOptions, string $certBaseDir, int $signatoryId, array $notConcernedCodes): array
{
    $states = [];

    foreach ($certifications as $certification) {
        $certCode  = $certification['code'];
        $certDir   = doliletter_spread_get_certification_dir($certBaseDir, $signatoryId, $certCode);
        $certFiles = dol_is_dir($certDir) ? dol_dir_list($certDir, 'files', 0, '', '(\.meta|_preview.*\.png)$') : [];

        $states[] = [
            'code'          => $certCode,
            'label'         => $certificationOptions[$certCode] ?? $certCode,
            'mandatory'     => !empty($certification['mandatory']),
            'has_file'      => !empty($certFiles),
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
