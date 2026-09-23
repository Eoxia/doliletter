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

// Load DoliLetter environment
if (file_exists('../doliletter.main.inc.php')) {
    require_once __DIR__ . '/../doliletter.main.inc.php';
} elseif (file_exists('../../doliletter.main.inc.php')) {
    require_once __DIR__ . '/../../doliletter.main.inc.php';
} else {
    die('Include of doliletter main fails');
}

global $conf, $db, $langs, $user;

// Libraries
require_once __DIR__ . '/../lib/doliletter.lib.php';
require_once __DIR__ . '/../lib/doliletter_linked_object.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

// Translations
// Loaded before saturne_get_objects_metadata(), which pulls the lang file of every spreadable
// object : the first file loaded wins, and the spread wording of the shared template is ours.
saturne_load_langs(['admin', 'doliletter@doliletter']);

// Parameters
$backtopage = GETPOST('backtopage', 'alpha');
$action = GETPOST('action', 'alpha');

// Access control
if (!$user->admin) accessforbidden();

/*
 * Actions
 */

// Spreadable objects actions
if (in_array($action, ['toggle_link', 'toggle_all_links', 'clean_unused_links'])) {
    $db->begin();

    if ($action == 'toggle_link') {
        $objectType = GETPOST('objecttype', 'aZ09');
        $value      = GETPOSTINT('value');

        $spreadableObjects = doliletter_get_spreadable_objects();
        if (isset($spreadableObjects[$objectType])) {
            $constName = DOLILETTER_SPREAD_LINK_CONST_PREFIX . strtoupper($objectType);
            dolibarr_set_const($db, $constName, $value, 'integer', 0, '', $conf->entity);
        }
    } elseif ($action == 'toggle_all_links') {
        $value = GETPOSTINT('value');

        foreach (array_keys(doliletter_get_spreadable_objects()) as $objectType) {
            $constName = DOLILETTER_SPREAD_LINK_CONST_PREFIX . strtoupper($objectType);
            dolibarr_set_const($db, $constName, $value, 'integer', 0, '', $conf->entity);
        }
    }

    // clean_unused_links has no branch of its own : realigning tabs and hooks on the constants is its whole job.
    $report = doliletter_sync_spread_objects();

    if ($report['errors'] > 0) {
        $db->rollback();
        setEventMessages($langs->trans('LinkedObjectSyncError'), [], 'errors');
    } else {
        $db->commit();
        setEventMessage($langs->trans('LinkedObjectSyncDone', $report['tabs'], $report['hooks']));
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

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

// saturne_header loads saturne.min.js, which carries the confirmation of the spread toggles
saturne_header(0, '', $langs->trans($page_name), $help_url);

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

// Spreadable elements, driven by the DOLILETTER_SPREAD_LINK_* constants
$linkableObjects            = doliletter_get_spreadable_objects();
$enabledObjectTypes         = doliletter_get_enabled_spread_object_types();
$linkedObjectExtraFieldName = DOLILETTER_SPREAD_LINK_USAGE_KEY;
$linkedObjectUsage          = doliletter_get_spread_usage();

require_once __DIR__ . '/../../saturne/core/tpl/admin/object/linked_object_view.tpl.php';

print '<hr>';


// Page end
print dol_get_fiche_end();
llxFooter();
$db->close();
