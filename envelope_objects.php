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
 *   	\file       envelope_card.php
 *		\ingroup    envelope
 *		\brief      Page to create/edit/view envelope
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
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
require_once './class/envelope.class.php';
require_once './core/modules/doliletter/mod_envelope_standard.php';
require_once './lib/doliletter_envelope.lib.php';
require_once './lib/doliletter_function.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT. '/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
require_once DOL_DOCUMENT_ROOT. '/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT. '/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT. '/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT. '/contrat/class/contrat.class.php';
require_once DOL_DOCUMENT_ROOT. '/product/class/product.class.php';

global $db, $conf, $langs, $user, $hookmanager;

// Load translation files required by the page
$langs->loadLangs(array("doliletter@doliletter", "other"));

// Get parameters
$id          = GETPOST('id', 'int');
$action      = GETPOST('action', 'aZ09');
$massaction  = GETPOST('massaction', 'alpha'); // The bulk action (combo box choice into lists)
$confirm     = GETPOST('confirm', 'alpha');
$cancel      = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ?GETPOST('contextpage', 'aZ') : 'riskcard'; // To manage different context of search
$backtopage  = GETPOST('backtopage', 'alpha');

// Initialize technical objects
$object         = new Envelope($db);
$contracttemp   = new Contrat($db);
$invoicetemp    = new Facture($db);
$commandetemp   = new Commande($db);
$projecttemp    = new Project($db);
$tickettemp     = new Ticket($db);
$producttemp    = new Product($db);
$propaltemp     = new Propal($db);
$refEnvelopeMod = new $conf->global->DOLILETTER_ENVELOPE_ADDON();
$extrafields    = new ExtraFields($db);

$object->fetch($id);

$hookmanager->initHooks(array('lettercard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha')) {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

$permissiontoread = $user->rights->doliletter->envelope->read;
$permissiontoadd = $user->rights->doliletter->envelope->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->rights->doliletter->envelope->delete || ($permissiontoadd && isset($object->status));
$permissionnote = $user->rights->doliletter->envelope->write; // Used by the include of actions_setnotes.inc.php
$permissiondellink = $user->rights->envelope->letter->write; // Used by the include of actions_dellink.inc.php
$upload_dir = $conf->doliletter->multidir_output[$conf->entity];
$thirdparty = new Societe($db);
$thirdparty->fetch($object->fk_soc);
$usertemp = new User($db);

/*
 * Actions
 */



$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if ($action == 'remove_file') {
	if (!empty($upload_dir)) {
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$langs->load("other");
		$filetodelete = GETPOST('file', 'alpha');
		$file = $upload_dir.'/'.$filetodelete;
		$ret = dol_delete_file($file, 0, 0, 0, $object);
		if ($ret) setEventMessages($langs->trans("FileWasRemoved", $filetodelete), null, 'mesgs');
		else setEventMessages($langs->trans("ErrorFailToDeleteFile", $filetodelete), null, 'errors');

		// Make a redirect to avoid to keep the remove_file into the url that create side effects
		$urltoredirect = $_SERVER['REQUEST_URI'];
		$urltoredirect = preg_replace('/#builddoc$/', '', $urltoredirect);
		$urltoredirect = preg_replace('/action=remove_file&?/', '', $urltoredirect);

		header('Location: '.$urltoredirect);
		exit;
	}
	else {
		setEventMessages('BugFoundVarUploaddirnotDefined', null, 'errors');
	}
}

if (empty($reshook)) {

	$error = 0;

	$backurlforlist = dol_buildpath('/doliletter/envelope_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/doliletter/envelope_card.php', 1).'?id='.($id > 0 ? $id : '__ID__');
			}
		}
	}

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';


	// Actions to send emails
	$triggersendname = 'DOLILETTER_ENVELOPE_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_ENVELOPE_TO';
	$trackid = 'envelope'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}


if ($action == 'addLink') {
	$element_types  = GETPOST('element_types');
	$element_id  = GETPOST('element_id');
	if (!is_array($element_id)) {
		$element_id = array($element_id);
	}
	//echo '<pre>'; print_r( $element_types );print_r($element_id); echo '</pre>'; exit;
	foreach ($element_id as $ids) {
		$object->add_object_linked($element_types, $ids);
	}
}


/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formother = new FormOther($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("Envelope");
$help_url = '';
llxHeader('', $title, $help_url);

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();

	$head = envelopePrepareHead($object);
	print dol_get_fiche_head($head, 'objects', $langs->trans("Envelope"), -1, "doliletter@doliletter");

	$formconfirm = '';


	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) {
		$formconfirm .= $hookmanager->resPrint;
	} elseif ($reshook > 0) {
		$formconfirm = $hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/doliletter/envelope_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	$morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">' . "\n";

	print dol_get_fiche_end();


	$titre = $langs->trans("CardProduct" . $product->type);
	$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $product, $action); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	if ($reshook < 0) {
		setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
	}

	$linkback = '<a href="' . DOL_URL_ROOT . '/product/list.php?restore_lastsearch_values=1">' . $langs->trans("BackToList") . '</a>';

	$shownav = 1;
	if ($user->socid && !in_array('product', explode(',', $conf->global->MAIN_MODULES_FOR_EXTERNAL))) {
		$shownav = 0;
	}


	print '<div class="fichecenter">';

	print '<div class="underbanner clearboth"></div>';
	print '<table class="border tableforfield" width="100%">';

	//$nboflines = show_stats_for_company($product, $socid);

	print "</table>";

	print '</div>';
	print '<div style="clear:both"></div>';

	print dol_get_fiche_end();



	//to do: trans file for titles
	$object->fetchObjectLinked(null, '', null, 'doliletter_envelope');

	// Contracts
	print '<p>';
	if (is_countable($object->linkedObjectsIds['contract'])) {
		$nbcontract = count($object->linkedObjectsIds['contract']);
	}else {
		$nbcontract = 0;
	}
	print '<div class="titre inline-block"> Contrats <span class="opacitymedium colorblack paddingleft">'.$nbcontract.'</span></div><br>';
	print '<table class="border tableforfield" width="100%">';
	if (!empty($object->linkedObjectsIds['contract'])) {
		foreach ($object->linkedObjectsIds['contract'] as $contractid) {
			$contracttemp->fetch($contractid);
			print $contracttemp->getNomUrl(1);
			print '<br>';
		}
	}
	print '<br></table>';

	print '<div>';
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="addLink">';
	print '<input type="hidden" name="element_types" value="contract">';

	print selectForm( $contracttemp, 'element_id[]', 'element_id', $object->linkedObjectsIds['contract']);

	print '<input type="submit" class="button" name="addLink" value="' . dol_escape_htmltag($langs->trans("Create")) . '">';
	print '</div>';
	print '</form>';

	// Factures
	print '</p><p>';
	if (is_countable($object->linkedObjectsIds['facture'])) {
		$nbfactures = count($object->linkedObjectsIds['facture']);
	} else {
		$nbfactures = 0;
	}
	print '<div class="titre inline-block">Factures<span class="opacitymedium colorblack paddingleft">' . $nbfactures . '</span></div><br>';
	print '<table class="border tableforfield" width="100%">';
	if (!empty($object->linkedObjectsIds['facture'])) {
		foreach ($object->linkedObjectsIds['facture'] as $invoiceid) {
			$invoicetemp->fetch($invoiceid);
			print $invoicetemp->getNomUrl(1);
			print '<br>';
		}
	}

	print '<br></table>';
	print '<div>';
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="addLink">';
	print '<input type="hidden" name="element_types" value="facture">';

	print selectForm( $invoicetemp, 'element_id[]', 'element_id', $object->linkedObjectsIds['facture']);

	print '<input type="submit" class="button" name="addLink" value="' . dol_escape_htmltag($langs->trans("Create")) . '">';
	print '</div>';
	print '</form>';


	// Commandes
	print '</p><p>';
	if (is_countable($object->linkedObjectsIds['commande'])) {
		$nbcommandes = count($object->linkedObjectsIds['commande']);
	} else {
		$nbcommandes = 0;
	}
	print '<div class="titre inline-block">Commandes<span class="opacitymedium colorblack paddingleft">' . $nbcommandes . '</span></div><br>';
	print '<table class="border tableforfield" width="100%">';
	if (!empty($object->linkedObjectsIds['commande'])) {
		foreach ($object->linkedObjectsIds['commande'] as $commandeid) {
			$commandetemp->fetch($commandeid);
			print $commandetemp->getNomUrl(1);
			print '<br>';
		}
	}
	print '<br></table>';
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="addLink">';
	print '<input type="hidden" name="element_types" value="order">';

	print selectForm( $commandetemp, 'element_id[]', 'element_id', $object->linkedObjectsIds['order']);

	print '<input type="submit" class="button" name="addLink" value="' . dol_escape_htmltag($langs->trans("Create")) . '">';
	print '</div>';
	print '</form>';

	// Product
	print '</p><p>';
	if (is_countable($object->linkedObjectsIds['product'])) {
		$nbproducts = count($object->linkedObjectsIds['product']);
	} else {
		$nbproducts = 0;
	}
	print '<div class="titre inline-block">Produit<span class="opacitymedium colorblack paddingleft">' . $nbproducts . '</span></div><br>';
	print '<table class="border tableforfield" width="100%">';
	if (!empty($object->linkedObjectsIds['product'])) {
		foreach ($object->linkedObjectsIds['product'] as $productid) {
			$producttemp->fetch($productid);
			print $producttemp->getNomUrl(1);
			print '<br>';
		}
	}
	print '<br></table>';
	print '<div>';
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="addLink">';
	print '<input type="hidden" name="element_types" value="product">';

	print selectForm( $producttemp, 'element_id[]', 'element_id', $object->linkedObjectsIds['product']);

	print '<input type="submit" class="button" name="addLink" value="' . dol_escape_htmltag($langs->trans("Create")) . '">';
	print '</div>';
	print '</form>';


	// Propositions commerciales
	print '</p><p>';
	if (is_countable($object->linkedObjectsIds['propal'])) {
		$nbpropal = count($object->linkedObjectsIds['propal']);
	}else {
		$nbpropal = 0;
	}
	print '<div class="titre inline-block">Propositions commerciales<span class="opacitymedium colorblack paddingleft">'.$nbpropal.'</span></div><br>';
	print '<table class="border tableforfield" width="100%">';
	if (!empty($object->linkedObjectsIds['propal'])) {
		foreach ($object->linkedObjectsIds['propal'] as $propalid) {
			$propaltemp->fetch($propalid);
			print $propaltemp->getNomUrl(1);
			print '<br>';
		}
	}
	print '<br></table>';
print '<div>';
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="addLink">';
	print '<input type="hidden" name="element_types" value="propal">';

	print selectForm( $propaltemp, 'element_id[]', 'element_id', $object->linkedObjectsIds['propal']);

	print '<input type="submit" class="button" name="addLink" value="' . dol_escape_htmltag($langs->trans("Create")) . '">';
	print '</div>';
	print '</form>';
}	// Tickets
print '</p><p>';
if (is_countable($object->linkedObjectsIds['ticket'])) {
	$nbtickets = count($object->linkedObjectsIds['ticket']);
}else {
	$nbtickets = 0;
}
print '<div class="titre inline-block">Tickets<span class="opacitymedium colorblack paddingleft">'.$nbtickets.'</span></div><br>';
print '<table class="border tableforfield" width="100%">';
if (!empty($object->linkedObjectsIds['ticket'])) {
	foreach ($object->linkedObjectsIds['ticket'] as $ticketid) {
		$tickettemp->fetch($ticketid);
		print $tickettemp->getNomUrl(1);
		print '<br>';
	}
}
print '<br></table>';
print '<div>';
print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="addLink">';
print '<input type="hidden" name="element_types" value="ticket">';

print ajax_combobox('parent');
print selectForm( $tickettemp, 'element_id[]', 'element_id', $object->linkedObjectsIds['ticket']);

print '<input type="submit" class="button" name="addLink" value="' . dol_escape_htmltag($langs->trans("Create")) . '">';
print '</div>';
print '</form>';



print '</p>';

// End of page
llxFooter();
$db->close();
