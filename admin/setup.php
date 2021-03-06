<?php
/* Copyright (C) 2021 EOXIA <dev@eoxia.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    doliletter/admin/setup.php
 * \ingroup doliletter
 * \brief   doliLetter setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res && file_exists("../../../../main.inc.php")) $res = @include "../../../../main.inc.php";
if (!$res) die("Include of main fails");

global $db, $langs, $user;

// Libraries
require_once '../lib/doliletter.lib.php';

// Translations
$langs->loadLangs(array("admin", "doliletter@doliletter"));

// Parameters
$backtopage = GETPOST('backtopage', 'alpha');

// Access control
if (!$user->admin) accessforbidden();

/*
 * View
 */

$page_name = "DoliLetterSetup";
$help_url  = '';

llxHeader('', $langs->trans($page_name), $help_url);

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_doliletter@doliletter');
// Configuration header
$head = doliletterAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $langs->trans($page_name), -1, "doliletter@doliletter");

print load_fiche_titre('<i class="fas fa-exclamation-circle"></i> ' . $langs->trans('PublicInterfaceConfig'), '', '');

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Name") . '</td>';
print '<td>' . $langs->trans("Description") . '</td>';
print '<td class="center">' . $langs->trans("Status") . '</td>';
print '</tr>';

print '<tr class="oddeven"><td>';
print $langs->trans('PublicInterface');
print "</td><td>";
print $langs->trans('EnablePublicInterface');
print '</td>';

print '<td class="center">';
print ajax_constantonoff('DOLILETTER_SIGNATURE_ENABLE_PUBLIC_INTERFACE');
print '</td>';
print '</tr>';

print '<tr class="oddeven"><td>';
print $langs->trans('PublicInterfaceDocuments');
print "</td><td>";
print $langs->trans('ShowDocumentsOnPublicInterface');
print '</td>';

print '<td class="center">';
print ajax_constantonoff('DOLILETTER_SHOW_DOCUMENTS_ON_PUBLIC_INTERFACE');
print '</td>';
print '</tr>';

print '<tr class="oddeven"><td>';
print $langs->trans('DeleteSignatureAfterReception');
print "</td><td>";
print $langs->trans('DeleteSignatureAfterReceptionText');
print '</td>';

print '<td class="center">';
print ajax_constantonoff('DOLILETTER_DELETE_PUBLIC_DOWNLOAD_LINKS_AFTER_SIGNATURE');
print '</td>';
print '</tr>';


print '</table>';
print '<hr>';


// Page end
print dol_get_fiche_end();
llxFooter();
$db->close();
