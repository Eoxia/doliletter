<?php
/* Copyright (C) 2022-2025 EVARISK <technique@evarisk.com>
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
 * \file    view/control/control_list.php
 * \ingroup digiquali
 * \brief   List page for control
 */

// Load DigiQuali environment
if (file_exists('../doliletter.main.inc.php')) {
    require_once __DIR__ . '/../doliletter.main.inc.php';
} elseif (file_exists('../../doliletter.main.inc.php')) {
    require_once __DIR__ . '/../../doliletter.main.inc.php';
} else {
    die('Include of doliletter main fails');
}

// Load Dolibarr libraries
if (isModEnabled('categorie')) {
    require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
}

// Global variables definitions
global $conf, $db, $hookmanager, $langs, $user;

// Load translation files required by the page
saturne_load_langs();

// Get parameters
$action     = GETPOSTISSET('action') ? GETPOST('action', 'aZ09') : 'view'; // The action 'add', 'create', 'edit', 'update', 'view', ...
$massaction = GETPOST('massaction', 'alpha');                                        // The bulk action (combo box choice into lists)
$fromType   = GETPOST('fromtype', 'alpha');                                          // Element type
$fromId     = GETPOSTINT('fromid');                                                        // Element id

// Get list parameters
$toselect                                   = [];
[$confirm, $contextpage, $optioncss, $mode] = ['', '', '', ''];
$listParameters                             = saturne_load_list_parameters(basename(dirname(__FILE__)));
foreach ($listParameters as $listParameterKey => $listParameter) {
    $$listParameterKey = $listParameter;
}

// Get pagination parameters
[$limit, $page, $offset] = [0, 0, 0];
[$sortfield, $sortorder] = ['', ''];
$paginationParameters    = saturne_load_pagination_parameters();
foreach ($paginationParameters as $paginationParameterKey => $paginationParameter) {
    $$paginationParameterKey = $paginationParameter;
}

// Initialize technical objects
include_once DOL_DOCUMENT_ROOT . '/custom/saturne/class/saturnedocuments/signinsheetdocument.class.php';
include_once DOL_DOCUMENT_ROOT . '/custom/saturne/class/saturneattendancesheet.class.php';
$document = new SigninSheetDocument($db);
$object   = new SaturneAttendanceSheet($db, 'doliletter');
$extrafields = new ExtraFields($db);
if (isModEnabled('categorie')) {
    $categorie = new Categorie($db);
}

$upload_dir = $conf->doliletter->multidir_output[isset($conf->entity) ? $conf->entity : 1];

// Initialize view objects
$form = new Form($db);

$hookmanager->initHooks([$contextpage]); // Note that conf->hooks_modules contains array

$object->fetch(0, '', ' AND object_type = ' . "'" . $fromType  . "'" . ' AND fk_object = ' . $fromId);

// Extra fields
require_once DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_array_fields.tpl.php';

$arrayfields    = dol_sort_array($arrayfields, 'position');

$objectsMetadata    = saturne_get_objects_metadata();

// // Permissions
// $permissiontoread   = $user->hasRight($object->module, $object->element, 'read');
// $permissiontoadd    = $user->hasRight($object->module, $object->element, 'write');
$permissiontoadd        = 1;
$permissiontodelete     = 1;
// $permissiontodelete = $user->hasRight($object->module, $object->element, 'delete');

// Security check
// saturne_check_access($permissiontoread, $object);

/*
 * Actions
 */

$parameters = ['arrayfields' => &$arrayfields];
$resHook    = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($resHook < 0) {
    setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($resHook)) {

    require_once __DIR__ . '/../../saturne/core/tpl/documents/documents_action.tpl.php';

}

/*
 * View
 */

if ($mode == 'pwa') {
    $conf->dol_hide_topmenu  = 1;
    $conf->dol_hide_leftmenu = 1;
}

$title = $langs->trans(ucfirst($object->element) . 'List');
saturne_header(0,'', $title, $helpUrl ?? '', '', 0, 0, [], [], '', 'mod-' . $object->module . '-' . $object->element . ' page-list bodyforlist');

if (!empty($fromType)) {
    $objectsMetadata[$fromType]['object']->fetch($fromId);
    saturne_get_fiche_head($objectsMetadata[$fromType]['object'], 'spread', '');
    $linkBack = '<a href="' . dol_buildpath($fromType . '/list.php?restore_lastsearch_values=1', 1) . '">' . $langs->trans('BackToList') . '</a>';
    saturne_banner_tab($objectsMetadata[$fromType]['object'], 'fromtype=' . $fromType . '&fromid', $linkBack, 1, 'rowid', ($fromType == 'productlot' ? 'batch' : 'ref'));

    $moreUrlParameters = '&fromtype=' . $fromType . '&fromid=' . $fromId . '&mode=' . $mode;
}

print '<div class="fichecenter">';

$backtocard = dol_buildpath('/custom/' . $moduleNameLowerCase . '/view/' . $object->element . '/' . $object->element . '_card.php?id=' . $id, 1);

$parameters = ['backtocard' => $backtocard];
$reshook    = $hookmanager->executeHooks('saturneAttendantsBackToCard', $parameters, $object); // Note that $action and $object may have been modified by some hooks
if ($reshook > 0) {
    $backtocard = $hookmanager->resPrint;
}

print '</div>';

// Add link to public interface
$publicUrl = dol_buildpath('/saturne/public/spread/add_spread.php', 1) . '?id=' . $fromId . '&object_type=' . $fromType;
print '<div class="tabsAction">';
print '<a class="butAction" href="' . $publicUrl . '" target="_blank">' . $langs->trans('PublicInterface') . '</a>';
print '</div>';

print '<div class="spread-table-container">';

$modulePart = 'doliletter:SigninSheet';
$objref     = dol_sanitizeFileName($object->ref);
$dirFiles   = 'signinsheet' . '/' . $objref;
$fileDir    = $upload_dir . '/' . $dirFiles;
// Protocole (http ou https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

// Nom de domaine + port (si différent de 80/443)
$host = $_SERVER['HTTP_HOST'];

// URI + query string
$requestUri = $_SERVER['REQUEST_URI'];

// URL complète
$urlSource = $protocol . $host . $requestUri;

print saturne_show_documents($modulePart, $dirFiles, $fileDir, $urlSource, 1, 1, '', 1, 0, 0, 0, 0, '', '', $langs->defaultlang, 0, $object, 0, 'remove_file', !empty($object->id));

print '</div>';

// End of page
llxFooter();
$db->close();