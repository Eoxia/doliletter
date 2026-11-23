<?php
/* Copyright (C) 2021 SuperAdmin
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
 * \file    doliletter/lib/doliletter.lib.php
 * \ingroup doliletter
 * \brief   Library files with common functions for doliletter
 */

/**
 * Register the PDF attendance sheet, the standard model of DoliLetter, on an instance updated without
 * reactivating the module: only the activation registers the document models and sets their default, so
 * the model stayed disabled in the Documents setup and was missing from the models of the Diffusion tab.
 *
 * Done once: nothing happens as soon as the default model constants are set, so a model the user
 * disabled or replaced since is left as it is.
 *
 * @return void
 */
function doliletter_register_signin_sheet_model()
{
	global $conf, $db;

	$model    = 'signinsheetdocument_FE_Doliletter';
	$defaults = [
		// Models of the attendance sheet in the Documents setup tab
		'signinsheetdocument' => ['const' => 'DOLILETTER_SIGNINSHEETDOCUMENT_DEFAULT_MODEL', 'label' => ''],
		// Models offered in the Diffusion tab of an object
		'signinsheet'         => ['const' => 'DOLILETTER_SIGNINSHEET_DEFAULT_MODEL', 'label' => 'SigninSheetDocument'],
	];

	foreach ($defaults as $type => $default) {
		if (getDolGlobalString($default['const']) !== '') {
			continue;
		}

		require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

		$sql   = 'SELECT rowid FROM ' . MAIN_DB_PREFIX . "document_model WHERE nom = '" . $db->escape($model) . "' AND type = '" . $db->escape($type) . "' AND entity = " . ((int) $conf->entity);
		$resql = $db->query($sql);
		if (!$resql) {
			continue;
		}
		if ($db->num_rows($resql) == 0 && addDocumentModel($model, $type, $default['label'], '') <= 0) {
			continue;
		}

		dolibarr_set_const($db, $default['const'], $model, 'chaine', 0, '', $conf->entity);
	}
}

/**
 * Prepare admin pages header
 *
 * @return array
 */
function doliletter_admin_prepare_head()
{
	global $langs, $conf;

	$langs->load("doliletter@doliletter");

	// The Documents setup page lists the models right after this header
	doliletter_register_signin_sheet_model();

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/doliletter/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	/*
	$head[$h][0] = dol_buildpath("/doliletter/admin/myobject_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$head[$h][2] = 'myobject_extrafields';
	$h++;
	*/

	$head[$h][0] = dol_buildpath("/doliletter/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	$head[$h][0] = dol_buildpath('saturne/admin/documents.php?module_name=Doliletter', 1);
    $head[$h][1] = $conf->browser->layout == 'classic' ? '<i class="fas fa-file-alt pictofixedwidth"></i>' . $langs->trans('YourDocuments') : '<i class="fas fa-file-alt"></i>';
    $head[$h][2] = 'documents';
    $h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@doliletter:/doliletter/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@doliletter:/doliletter/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'doliletter');

	return $head;
}
