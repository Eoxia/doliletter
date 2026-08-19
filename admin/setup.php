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
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

// Translations
$langs->loadLangs(array("admin", "doliletter@doliletter"));

// Parameters
$backtopage = GETPOST('backtopage', 'alpha');
$action = GETPOST('action', 'alpha');

// Access control
if (!$user->admin) accessforbidden();

/*
 * Actions
 */

if ($action == 'save') {

    if (!empty($_POST['email_template'])) {
        dolibarr_set_const($db, 'DOLILETTER_EMAIL_TEMPLATE_SPREAD', $_POST['email_template'], 'chaine', 0, '', $conf->entity);
    }

    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    header('Location: ' . $_SERVER["PHP_SELF"]);
    exit;
}

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
$head = doliletter_admin_prepare_head();
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

print '<tr class="oddeven"><td>';
print $langs->trans('ShowSpreadSignature');
print "</td><td>";
print $langs->trans('ShowSpreadSignatureDescription');
print '</td>';

print '<td class="center">';
print ajax_constantonoff('DOLILETTER_SPREAD_SHOW_SIGNATURE');
print '</td>';
print '</tr>';


// Initialize default configuration for external fields if not set
$defaultExtFields = [
    'DOLILETTER_SPREAD_EXT_FIELD_FIRSTNAME_VISIBLE' => '1',
    'DOLILETTER_SPREAD_EXT_FIELD_LASTNAME_VISIBLE' => '1',
    'DOLILETTER_SPREAD_EXT_FIELD_EMAIL_VISIBLE' => '1',
    'DOLILETTER_SPREAD_EXT_FIELD_PHONE_VISIBLE' => '1'
];
foreach ($defaultExtFields as $k => $v) {
    if (getDolGlobalString($k) === '') {
        dolibarr_set_const($db, $k, $v, 'chaine', 0, '', $conf->entity);
        $conf->global->$k = $v;
    }
}

// Ext fields configuration
print '<tr class="oddeven"><td>';
print $langs->trans('ConfigSpreadExtFields');
print '</td><td colspan="2">';
print $langs->trans('ConfigSpreadExtFieldsDesc');

print '<table class="noborder centpercent" style="margin-top: 10px;">';
print '<tr class="liste_titre">';
print '<td>Champ</td>';
print '<td class="center">' . $langs->trans('ConfigSpreadExtVisible') . '</td>';
print '<td class="center">' . $langs->trans('ConfigSpreadExtMandatory') . '</td>';
print '</tr>';

$extFields = [
    'FIRSTNAME' => 'ConfigSpreadExtFieldFirstname',
    'LASTNAME' => 'ConfigSpreadExtFieldLastname',
    'EMAIL' => 'ConfigSpreadExtFieldEmail',
    'PHONE' => 'ConfigSpreadExtFieldPhone'
];

foreach ($extFields as $key => $labelKey) {
    print '<tr class="oddeven">';
    print '<td>' . $langs->trans($labelKey) . '</td>';
    print '<td class="center ext-visible-cell">';
    print ajax_constantonoff('DOLILETTER_SPREAD_EXT_FIELD_' . $key . '_VISIBLE', array(), null, 0, 0, 0, 2, 0, 1);
    print '</td>';
    print '<td class="center ext-mandatory-cell">';
    print ajax_constantonoff('DOLILETTER_SPREAD_EXT_FIELD_' . $key . '_MANDATORY', array(), null, 0, 0, 0, 2, 0, 1);
    print '</td>';
    print '</tr>';
}

print '</table>';

print '
<script type="text/javascript">
$(document).ready(function() {
    // If mandatory is turned on, force visible to ON
    $(".ext-mandatory-cell .switch").on("click", function() {
        var $mandatorySwitch = $(this);
        setTimeout(function() {
            var isMandatory = $mandatorySwitch.find("input[type=checkbox]").is(":checked");
            if (isMandatory) {
                var $visibleSwitch = $mandatorySwitch.closest("tr").find(".ext-visible-cell .switch");
                var isVisible = $visibleSwitch.find("input[type=checkbox]").is(":checked");
                if (!isVisible) {
                    $visibleSwitch.click();
                }
            }
        }, 300);
    });
});
</script>
';

print '</td></tr>';

print '</table>';

require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/saturne/class/saturnemail.class.php';

$form        = new Form($db);
$saturneMail = new SaturneMail($db);
$result      = $saturneMail->fetchAll('', '', 0, 0, ['customsql' => 't.type_template = "doliletter_spread"']);

$options = [];
foreach ($result as $item) {
    $options[$item->id] = $item->label;
}


print load_fiche_titre('<i class="fas fa-exclamation-circle"></i> ' . $langs->trans('EmailConfig'), '', '');

print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
print '<input type="hidden" name="action" value="save">';
print '<input type="hidden" name="token" value="'.newToken().'">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Name") . '</td>';
print '<td>' . $langs->trans("Description") . '</td>';
print '<td class="center">' . $langs->trans("Value") . '</td>';
print '</tr>';

// Email Subject Configuration
print '<tr class="oddeven"><td>';
print $langs->trans('ConfigEmailSpread');
print "</td><td>";
print $langs->trans('ConfigEmailSpreadDescription');
print '</td>';
print '<td class="center">';
print $form->selectarray('email_template', $options, getDolGlobalInt('DOLILETTER_EMAIL_TEMPLATE_SPREAD'), 1);
print '</td>';
print '</tr>';

print '</table>';

print '<div class="tabsAction">';
print '<input type="submit" class="button" name="save" value="' . $langs->trans("Save") . '">';
print '</div>';
print '</form>';

print load_fiche_titre('<i class="fas fa-exclamation-circle"></i> ' . $langs->trans('SpreadConfig'), '', '');

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Name") . '</td>';
print '<td>' . $langs->trans("Description") . '</td>';
print '<td class="center">' . $langs->trans("Status") . '</td>';
print '</tr>';

print '<tr class="oddeven"><td>';
print $langs->trans('ConfigSpreadQuickSign');
print "</td><td>";
print $langs->trans('ConfigSpreadQuickSignDescription');
print '</td>';

print '<td class="center">';
print ajax_constantonoff('DOLILETTER_SPREAD_QUICK_SIGN');
print '</td>';
print '</tr>';

print '<tr class="oddeven"><td>';
print $langs->trans('ConfigSpreadPublicRegister');
print "</td><td>";
print $langs->trans('ConfigSpreadPublicRegisterDescription');
print '</td>';

print '<td class="center">';
print ajax_constantonoff('DOLILETTER_SPREAD_PUBLIC_REGISTER');
print '</td>';
print '</tr>';

print '</table>';

print '<hr>';


// Page end
print dol_get_fiche_end();
llxFooter();
$db->close();
