<?php
/* Copyright (C) 2021 EOXIA <dev@eoxia.com>
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
 *       \file       public/signature/add_signature.php
 *       \ingroup    doliletter
 *       \brief      Public page to add signature
 */

if ( ! defined('NOREQUIREUSER'))  define('NOREQUIREUSER', '1');
if ( ! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1');
if ( ! defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1');
if ( ! defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1');
if ( ! defined('NOLOGIN'))        define("NOLOGIN", 1);           // This means this output page does not require to be logged.
if ( ! defined('NOCSRFCHECK'))    define("NOCSRFCHECK", 1);       // We accept to go on this page from external web site.
if ( ! defined('NOIPCHECK'))		define('NOIPCHECK', '1');      // Do not check IP defined into conf $dolibarr_main_restrict_ip
if ( ! defined('NOBROWSERNOTIF')) define('NOBROWSERNOTIF', '1');
// Entity of the envelope, carried by the link: a visitor has no session to take it from. Defined before main.inc.php, which
// reads it, as the public pages of Dolibarr do: GETPOST() is not loaded yet
if (!defined('DOLENTITY')) {
	define('DOLENTITY', !empty($_GET['entity']) ? (int) $_GET['entity'] : (!empty($_POST['entity']) ? (int) $_POST['entity'] : 1));
}

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if ( ! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if ( ! $res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) $res          = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
if ( ! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
// Try main.inc.php using relative path
if ( ! $res && file_exists("../../main.inc.php")) $res       = @include "../../main.inc.php";
if ( ! $res && file_exists("../../../main.inc.php")) $res    = @include "../../../main.inc.php";
if ( ! $res && file_exists("../../../../main.inc.php")) $res = @include "../../../../main.inc.php";
if ( ! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmdirectory.class.php';
require_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmfiles.class.php';

require_once '../../class/envelope.class.php';
require_once '../../lib/doliletter_function.lib.php';

global $conf, $langs, $user, $db;
// Load translation files required by the page
// saturne@saturne holds SignaturePublicInterfaceForbidden, shown untranslated without it
$langs->loadLangs(array("doliletter@doliletter", "saturne@saturne", "other", "errors"));

// Get parameters
$track_id = GETPOST('track_id', 'alpha');
$action   = GETPOST('action', 'aZ09');
$url      = dirname($_SERVER['PHP_SELF']) . '/signature_success.php';
$source   = GETPOST('source', 'aZ09');
// The links the Signature tab sent by email carried no type: envelope, the only one this page handles
$type     = GETPOST('type', 'aZ09') ?: 'envelope';

// Initialize technical objects
$user = new User($db);

switch ($type) {
	case 'envelope':
		$object         = new Envelope($db);
		$signatory      = new EnvelopeSignature($db);
		break;
}

if (dol_strlen($track_id) > 0) {
	$signatory->fetch('', '', " AND signature_url = '" . $db->escape($track_id) . "'");
}
// A link sent before it carried the entity opens the page in the first entity, where the signatory of an envelope of another
// entity is not found: back to the link, in the entity of its signatory
if (empty($signatory->id) && dol_strlen($track_id) > 0 && !GETPOSTISSET('entity')) {
	$resql = $db->query('SELECT entity FROM ' . MAIN_DB_PREFIX . $signatory->table_element . " WHERE signature_url = '" . $db->escape($track_id) . "'");
	$obj   = $resql ? $db->fetch_object($resql) : null;
	if (is_object($obj) && (int) $obj->entity != (int) $conf->entity) {
		header('Location: ' . dol_buildpath('/doliletter/public/signature/add_signature.php', 1) . '?track_id=' . urlencode($track_id) . '&type=' . urlencode($type) . '&entity=' . ((int) $obj->entity));
		exit;
	}
}
// Unknown link, or opened in a session of another entity: the session wins over the entity of the link
if (empty($signatory->id) || $object->fetch($signatory->fk_object) <= 0) {
	print $langs->trans('ErrorRecordNotFound');
	exit;
}

// Before the actions: with the interface switched off, signing through the action stayed possible
if (empty($conf->global->DOLILETTER_SIGNATURE_ENABLE_PUBLIC_INTERFACE)) {
	print $langs->trans('SignaturePublicInterfaceForbidden');
	exit;
}

/*
 * Actions
 */

$parameters = array();
$reshook    = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

// Action to add record
if ($action == 'addSignature') {
	$request_body = file_get_contents('php://input');
	$client_ip = $_SERVER['REMOTE_ADDR'];

	// The signatory of the link and its envelope, loaded above: the signatoryID the script sends is not trusted, it let a
	// visitor sign for any signatory
	$signatory->signature      = $request_body;
	$signatory->signature_date = dol_now();
	$signatory->ip = $client_ip;

	if ( ! $error) {
		$result = $signatory->update($user, false);
		if ($result > 0) {
			$signatory->setSigned($user, false);
			$object->setStatusCommon($user, 5, 1); // No trigger code to give: without notrigger, CommonObject calls an empty one
			$object->call_trigger('ENVELOPE_RECIPIENT_SIGN', $user);
			if (method_exists($object, 'generateDocument'))
			{
				// The public page has no display option of the card: details, description and reference are shown
				$result = $object->generateDocument('deimos', $langs, 0, 0, 0);

				if ($result < 0) {
					dol_print_error($db, $object->error, $object->errors);
					exit();
				}
			}
			// The option is absent from llx_const until it is switched on once in the setup page
			if (getDolGlobalInt('DOLILETTER_SHOW_DOCUMENTS_ON_PUBLIC_INTERFACE')) {
				// Folder and index of the entity of the envelope: the first entity ones held another envelope of the same ref
				$receiptFile = $object->getLastDocumentIndex('acknowledgementreceipt');
				if ($receiptFile !== null) {
					require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
					$receiptFile->share = getRandomPassword(true);
					$receiptFile->update($user);
				}
			}
			// Creation signature OK
			exit;
		} else {
			// Creation signature KO
			if ( ! empty($signatory->errors)) setEventMessages(null, $signatory->errors, 'errors');
			else setEventMessages($signatory->error, null, 'errors');
		}
	}
}

/*
 * View
 */

$form = new Form($db);

$element = $signatory->fetchSignatory($signatory->role, $signatory->fk_object, $type);
$element = array_shift($element);
// The success page finds the envelope from the link, as this page: by its id, it showed the documents of any envelope
$url = dol_buildpath('/doliletter/public/signature/signature_success.php', 1) . '?document_name=' .  $object->ref . '-' . $object->label . '&track_id=' . urlencode($track_id) . '&type=' . $type . '&entity=' . ((int) ($object->entity ?: $conf->entity));

if (dol_strlen($element->signature)) {
	header("Location: ".$url);
	exit;
}

// Header printed after the redirection above: once sent, the Location header could no longer go out
$morejs  = array("/doliletter/js/signature-pad.min.js", "/doliletter/js/doliletter.js.php");
$morecss = array("/doliletter/css/doliletter.css");

llxHeaderSignature($langs->trans("Signature"), "", 0, 0, $morejs, $morecss);

// Any other value than "private" makes the signature script go to the success page
$zone = 'public';
?>
<div class="digirisk-signature-container">
	<input hidden id="redirectURL" value="<?php echo $url ?>">
	<div class="wpeo-gridlayout grid-2">
		<div class="informations">
			<?php if (getDolGlobalInt('DOLILETTER_SHOW_DOCUMENTS_ON_PUBLIC_INTERFACE')) : ?>
				<div class="wpeo-gridlayout grid-2 file-generation">
					<?php if ($type == 'envelope') : ?>
						<?php // A draft only has its specimen: there is no document to share yet
						$envelope_file = $object->getLastDocumentIndex();
						?>
						<?php if (is_object($envelope_file) && dol_strlen($envelope_file->share)) : ?>
							<strong class="grid-align-middle"><?php echo $langs->trans("LinkedDocument"); ?></strong>

						<?php endif; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<br>
			<div class="wpeo-table table-flex table-2">
				<div class="table-row">
					<div class="table-cell"><?php echo $langs->trans("CompleteName"); ?></div>
					<div class="table-cell table-end"><?php echo $signatory->firstname . ' ' . $signatory->lastname; ?></div>
				</div>
				<div class="table-row">
					<div class="table-cell"><?php echo $langs->trans("DocumentName"); ?></div>
					<div class="table-cell table-end"><?php echo $object->ref . ' ' . $object->label; ?></div>
				</div>
			</div>
		</div>
		<div class="signature signatures-container">
			<input hidden class="role" value="E_RECEIVER">
			<div class="wpeo-gridlayout grid-2">
				<strong class="grid-align-middle"><?php echo $langs->trans("Signature"); ?></strong>
				<?php
				$modal_id = 'contact-' . $object->fk_contact;
				?>
				<div class="wpeo-button button-primary button-square-40 button-radius-2 grid-align-right wpeo-modal-event modal-signature-open modal-open" value="<?php echo $element->id ?>">
					<span><i class="fas fa-pen-nib"></i> <?php echo $langs->trans('Sign'); ?></span>
				</div>
			</div>
			<br>
			<div class="signature-element">
				<?php require  "../../core/tpl/doliletter_signature_view.tpl.php"; ?>
			</div>
		</div>
	</div>
<?php

llxFooter('', 'public');
$db->close();

