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

require_once DOL_DOCUMENT_ROOT . '/custom/saturne/class/saturnesignature.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/saturne/class/saturnemail.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/doliletter/class/doliletterattendancesheet.class.php';
// Global variables definitions
global $conf, $db, $hookmanager, $langs;

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
$form        = new Form($db);
if (isModEnabled('societe')) {
    $thirdparty = new Societe($db);
    $contact    = new Contact($db);
}

$attendanceSheet->fetch(0, '', ' AND object_type = ' . "'" . $objectType  . "'" . ' AND fk_object = ' . $id);

if ($action == 'add_spread_user') {
    if ($attendanceSheet->id <= 0 || $attendanceSheet->id == null) {

        $attendanceSheet->ref           = $object->ref;
        $attendanceSheet->status        = $attendanceSheet::STATUS_DRAFT;
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
        $user->fetch(GETPOSTINT('user_id'));

        $signatory->element_id   = GETPOSTINT('user_id');
        $signatory->element_type = 'user';

        $signatory->firstname = $user->firstname;
        $signatory->lastname  = $user->lastname;

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
            $signatory->update($user);
        }
    }
    $action = '';
}

if ($action == 'save_private_note') {
    if ($attendanceSheet->id > 0) {
        $data = json_decode(file_get_contents('php://input'), true);
        $note = $data['note_private'] ?? '';

        $attendanceSheet->note_private = $note;
        $attendanceSheet->update($user);
    }
    $action = '';
}


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