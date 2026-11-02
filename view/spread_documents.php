<?php
/* Copyright (C) 2026 EVARISK <technique@evarisk.com>
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
 * \file    view/spread_documents.php
 * \ingroup doliletter
 * \brief   Tab of a diffused object listing its documents and what the people of the spread get of
 *          each one (hidden, to download, to read in full), with the upload of a new one.
 */

// Load DoliLetter environment
if (file_exists('../doliletter.main.inc.php')) {
    require_once __DIR__ . '/../doliletter.main.inc.php';
} elseif (file_exists('../../doliletter.main.inc.php')) {
    require_once __DIR__ . '/../../doliletter.main.inc.php';
} else {
    die('Include of doliletter main fails');
}

// Global variables definitions
global $conf, $db, $hookmanager, $langs, $user;

// Load translation files required by the page
saturne_load_langs(['doliletter@doliletter']);

// Get parameters
$action   = GETPOST('action', 'aZ09');
$fromType = GETPOST('fromtype', 'alpha'); // Element type
$fromId   = GETPOSTINT('fromid');         // Element id

// Initialize technical objects
dol_include_once('/doliletter/class/doliletterattendancesheet.class.php');
dol_include_once('/doliletter/lib/doliletter_spread.lib.php');

$object = new DoliletterAttendanceSheet($db, 'doliletter');

$hookmanager->initHooks(['spreaddocuments']); // Note that conf->hooks_modules contains array

$objectsMetadata = saturne_get_objects_metadata();

// Diffused object, whose documents are offered to the people of the spread
$spreadObject = null;
if (!empty($fromType) && isset($objectsMetadata[$fromType]['object']) && $objectsMetadata[$fromType]['object']->fetch($fromId) > 0) {
    $spreadObject = $objectsMetadata[$fromType]['object'];
}
$spreadTableElement = $objectsMetadata[$fromType]['table_element'] ?? '';

// The attendance sheet only gives the notes of the banner: it does not exist before the first person
$object->fetch(0, '', " AND object_type = '" . $db->escape($fromType) . "' AND fk_object = " . $fromId);

// Permissions
$permissiontoread            = $user->hasRight('doliletter', 'read') || $user->hasRight('doliletter', 'lire');
$permissiontomanagedocuments = $user->hasRight('doliletter', 'spread', 'write');

// Security check
saturne_check_access($permissiontoread && is_object($spreadObject));

/*
 * Actions
 */

$parameters = [];
$resHook    = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($resHook < 0) {
    setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($resHook)) {
    $backToDocuments = $_SERVER['PHP_SELF'] . '?fromid=' . $fromId . '&fromtype=' . urlencode($fromType);

    // Documents added from the tab are shown in full on the public page, see doliletter_spread_add_documents()
    if ($action == 'add_spread_document' && $permissiontomanagedocuments && getDolGlobalString('MAIN_UPLOAD_DOC')) {
        doliletter_spread_add_documents($spreadObject, $spreadTableElement, $fromType, 'spread_document', $user);

        header('Location: ' . $backToDocuments);
        exit;
    }

    if ($action == 'set_spread_document_mode' && $permissiontomanagedocuments) {
        $result = doliletter_spread_set_document_mode($spreadObject, $spreadTableElement, $fromType, GETPOST('document_type', 'aZ09'), GETPOSTINT('document_id'), GETPOST('document_mode', 'aZ09'), $user);
        if ($result > 0) {
            setEventMessages($langs->trans('SpreadDocumentModeSaved'), null);
        } else {
            setEventMessages($langs->trans('ErrorSpreadDocumentMode'), null, 'errors');
        }

        header('Location: ' . $backToDocuments);
        exit;
    }
}

/*
 * View
 */

$title = $langs->trans('SpreadDocuments');
saturne_header(0, '', $title, '', '', 0, 0, [], [], '', 'mod-doliletter-spreaddocuments');

$spreadTab = 'spreaddocuments';
require __DIR__ . '/../core/tpl/spread/spread_banner.tpl.php';

$spreadDocuments = doliletter_spread_get_documents($spreadObject, $spreadTableElement, $fromType);
require __DIR__ . '/../core/tpl/spread/spread_documents.tpl.php';

// End of page
llxFooter();
$db->close();
