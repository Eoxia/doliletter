<?php
/* Copyright (C) 2021	Noe Sellam	<noe.sellam@epitech.eu>
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
 * \file    lib/betterform.lib.php
 * \ingroup doliletter
 * \brief   unified form function
 */


function fetchAllAny($objecttype, $sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND') {
	global $conf, $db;

	$objecttype = is_string($objecttype) ?: get_class($objecttype);
	$type = new $objecttype($db);
	$records = array();
	$sql = 'SELECT ';
	$sql .= $type->getFieldList();
	$sql .= ' FROM '.MAIN_DB_PREFIX.$type->table_element;
	if (isset($type->ismultientitymanaged) && $type->ismultientitymanaged == 1) $sql .= ' WHERE entity IN ('.getEntity($type->table_element).')';
	else $sql .= ' WHERE 1 = 1';

	// Manage filter
	$sqlwhere = array();
	if (count($filter) > 0) {
		foreach ($filter as $key => $value) {
			if ($key == 'rowid') {
				$sqlwhere[] = $key.'='.$value;
			} elseif (in_array($type->fields[$key]['type'], array('date', 'datetime', 'timestamp'))) {
				$sqlwhere[] = $key.' = \''.$type->db->idate($value).'\'';
			} elseif ($key == 'customsql') {
				$sqlwhere[] = $value;
			} elseif (strpos($value, '%') === false) {
				$sqlwhere[] = $key.' IN ('.$type->db->sanitize($type->db->escape($value)).')';
			} else {
				$sqlwhere[] = $key.' LIKE \'%'.$type->db->escape($value).'%\'';
			}
		}
	}
	if (count($sqlwhere) > 0) {
		$sql .= ' AND ('.implode(' '.$filtermode.' ', $sqlwhere).')';
	}

	if (!empty($sortfield)) {
		$sql .= $type->db->order($sortfield, $sortorder);
	}
	if (!empty($limit)) {
		$sql .= ' '.$type->db->plimit($limit, $offset);
	}

	$resql = $type->db->query($sql);

	if ($resql) {
		$num = $type->db->num_rows($resql);
		$i = 0;
		while ($i < ($limit ? min($limit, $num) : $num))
		{
			$obj = $type->db->fetch_object($resql);

			$record = new Facture($db);
			$record->setVarsFromFetchObj($obj);

			$records[$record->id] = $record;

			$i++;
		}
		$type->db->free($resql);

		return $records;
	} else {
		$type->errors[] = 'Error '.$type->db->lasterror();
		dol_syslog(__METHOD__.' '.join(',', $type->errors), LOG_ERR);

		return -1;
	}
}

/**
 * Prints form to select objects of a given type
 *
 * @param $objecttype object of the type to select from
 * @param string $sortorder Sort Order
 * @param string $sortfield Sort field
 * @param int $limit limit
 * @param int $offset Offset
 * @param array $filter Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
 * @param string $filtermode Filter mode (AND or OR)
 * @param string $htmlname html form name
 * @param string $htmlid html form id
 * @param array $notid array of int for ids of element not to print (eg. all but thoses ids to be printed
 * @param boolean $multiplechoices wether to allow multiple choices or not
 * @return int                 int <0 if KO, else returns string containing form
 */

function selectForm($objecttype, $htmlname = 'form[]', $htmlid ='', $notid = array(), $sortorder = '', $sortfield = '', $limit = 0, $offset = 0, $filter = array(), $filtermode = 'AND', $multiplechoices = true) {
	//$error = 0;
	$str = '';
	if ($notid === null){
		$notid = array();
	}
	//print '<div> debug  ';
	if ($htmlid == '' && preg_match( '/\[]/', $htmlname))
		$htmlid == substr($htmlname, 0, -2);
	$records =fetchAllAny($objecttype);

	$str .=('<select class="minwidth200" data-select2-id="'.$htmlname.'" name="' . $htmlname . '">');
	foreach ($records as $line) {
		if (!in_array($line->id, $notid)) {
			$str .= '<option data-select2-id="'.$line->id.$line->ref.'" value="' . $line->id . '">' . $line->ref . '</option>';
		}
	}
	$str .= '</select>';
	include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
	$str .= ajax_combobox($htmlname);
	return $str;
}

/**
 *      Return a string to show the box with list of available documents for object.
 *      This also set the property $this->numoffiles
 *
 * @param      string				$modulepart         Module the files are related to ('propal', 'facture', 'facture_fourn', 'mymodule', 'mymodule:nameofsubmodule', 'mymodule_temp', ...)
 * @param      string				$modulesubdir       Existing (so sanitized) sub-directory to scan (Example: '0/1/10', 'FA/DD/MM/YY/9999'). Use '' if file is not into subdir of module.
 * @param      string				$filedir            Directory to scan
 * @param      string				$urlsource          Url of origin page (for return)
 * @param      int|string[]        $genallowed         Generation is allowed (1/0 or array list of templates)
 * @param      int					$delallowed         Remove is allowed (1/0)
 * @param      string				$modelselected      Model to preselect by default
 * @param      int					$allowgenifempty	Allow generation even if list of template ($genallowed) is empty (show however a warning)
 * @param		int					$noform				Do not output html form tags
 * @param		string				$param				More param on http links
 * @param		string				$title				Title to show on top of form. Example: '' (Default to "Documents") or 'none'
 * @param		string				$buttonlabel		Label on submit button
 * @param		string				$morepicto			Add more HTML content into cell with picto
 * @param      Object              $object             Object when method is called from an object card.
 * @param		int					$hideifempty		Hide section of generated files if there is no file
 * @param      string              $removeaction       (optional) The action to remove a file
 * @param      bool                 $active             (optional) To show gen button disabled
 * @param      string              $tooltiptext       (optional) Tooltip text when gen button disabled
 * @return		string              					Output string with HTML array of documents (might be empty string)
 */
function dolilettershowdocuments($modulepart, $modulesubdir, $filedir, $urlsource, $genallowed, $delallowed = 0, $modelselected = '', $allowgenifempty = 1, $noform = 0, $param = '', $title = '', $buttonlabel = '', $morepicto = '', $object = null, $hideifempty = 0, $removeaction = 'remove_file', $active = true, $tooltiptext = '')
{

	global $db, $langs, $conf, $hookmanager, $form;

	if ( ! is_object($form)) $form = new Form($db);

	include_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

	// Add entity in $param if not already exists
	if ( ! preg_match('/entity\=[0-9]+/', $param)) {
		$param .= ($param ? '&' : '') . 'entity=' . ( ! empty($object->entity) ? $object->entity : $conf->entity);
	}

	$hookmanager->initHooks(array('formfile'));

	// Get list of files
	$file_list = null;
	if ( ! empty($filedir)) {
		$file_list = dol_dir_list($filedir, 'files', 0, '(\.odt|\.zip|\.pdf)', '', 'date', SORT_DESC, 1);
	}

	if ($hideifempty && empty($file_list)) return '';

	$out         = '';
	$forname     = 'builddoc';
	$headershown = 0;
	$showempty   = 0;

	$out .= "\n" . '<!-- Start show_document -->' . "\n";

	$titletoshow                       = $langs->trans("Documents");
	if ( ! empty($title)) $titletoshow = ($title == 'none' ? '' : $title);

	// Show table
	if ($genallowed) {
		$submodulepart = $modulepart;
		// modulepart = 'nameofmodule' or 'nameofmodule:NameOfObject'
		$tmp = explode(':', $modulepart);
		if ( ! empty($tmp[1])) {
			$modulepart    = $tmp[0];
			$submodulepart = $tmp[1];
		}

		// For normalized external modules.
		$file = dol_buildpath('/' . $modulepart . '/core/modules/' . $modulepart . '/modules_' . strtolower($submodulepart) . '.php', 0);

		include_once $file;

		$class = 'ModelePDF' . $submodulepart;

		if (class_exists($class)) {
			if (preg_match('/specimen/', $param)) {
				$type      = strtolower($class) . 'specimen';
				include_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
				$modellist = getListOfModels($db, $type, 0);
			} else {
				$modellist = call_user_func($class . '::liste_modeles', $db, 100);
			}
		} else {
			dol_print_error($db, "Bad value for modulepart '" . $modulepart . "' in showdocuments");
			return -1;
		}

		// Set headershown to avoid to have table opened a second time later
		$headershown = 1;

		if (empty($buttonlabel)) $buttonlabel = $langs->trans('Generate');

		if ($conf->browser->layout == 'phone') $urlsource .= '#' . $forname . '_form'; // So we switch to form after a generation
		if (empty($noform)) $out                          .= '<form action="' . $urlsource . (empty($conf->global->MAIN_JUMP_TAG) ? '' : '#builddoc') . '" id="' . $forname . '_form" method="post">';
		$out                                              .= '<input type="hidden" name="action" value="builddoc">';
		$out                                              .= '<input type="hidden" name="token" value="' . newToken() . '">';

		$out .= load_fiche_titre($titletoshow, '', '');
		$out .= '<div class="div-table-responsive-no-min">';
		$out .= '<table class="liste formdoc noborder centpercent">';

		$out .= '<tr class="liste_titre">';

		$addcolumforpicto = ($delallowed || $morepicto);
		$colspan          = (3 + ($addcolumforpicto ? 1 : 0)); $colspanmore = 0;

		$out .= '<th colspan="' . $colspan . '" class="formdoc liste_titre maxwidthonsmartphone center">';

		// Model
		if ( ! empty($modellist)) {
			asort($modellist);
			$out      .= '<span class="hideonsmartphone">' . $langs->trans('Model') . ' </span>';
			$modellist = array_filter($modellist, 'remove_index');
			if (is_array($modellist) && count($modellist) == 1) {    // If there is only one element
				$arraykeys                = array_keys($modellist);
				$arrayvalues              = preg_replace('/template_/', '', array_values($modellist)[0]);
				$modellist[$arraykeys[0]] = $arrayvalues;
				$modelselected            = $arraykeys[0];
			}
			$morecss                                        = 'maxwidth200';
			if ($conf->browser->layout == 'phone') $morecss = 'maxwidth100';
			$out                                           .= $form::selectarray('model', $modellist, $modelselected, $showempty, 0, 0, '', 0, 0, 0, '', $morecss);

			if ($conf->use_javascript_ajax) {
				$out .= ajax_combobox('model');
			}
		} else {
			$out .= '<div class="float">' . $langs->trans("Files") . '</div>';
		}

		// Button
		if ($active) {
			$genbutton  = '<input class="button buttongen" id="' . $forname . '_generatebutton" name="' . $forname . '_generatebutton"';
			$genbutton .= ' type="submit" value="' . $buttonlabel . '"';
		} else {
			$genbutton  = '<input class="button buttongen disabled" name="' . $forname . '_generatebutton" style="cursor: not-allowed"';
			$genbutton .= '  value="' . $buttonlabel . '"';
		}

		if ( ! $allowgenifempty && ! is_array($modellist) && empty($modellist)) $genbutton .= ' disabled';
		$genbutton                                                                         .= '>';
		if ($allowgenifempty && ! is_array($modellist) && empty($modellist) && empty($conf->dol_no_mouse_hover) && $modulepart != 'unpaid') {
			$langs->load("errors");
			$genbutton .= ' ' . img_warning($langs->transnoentitiesnoconv("WarningNoDocumentModelActivated"));
		}
		if ( ! $allowgenifempty && ! is_array($modellist) && empty($modellist) && empty($conf->dol_no_mouse_hover) && $modulepart != 'unpaid') $genbutton = '';
		if (empty($modellist) && ! $showempty && $modulepart != 'unpaid') $genbutton                                                                      = '';
		$out                                                                                                                                             .= $genbutton;
		if ( ! $active) {
			$htmltooltip  = '';
			$htmltooltip .= $tooltiptext;

			$out .= '<span class="center">';
			$out .= $form->textwithpicto($langs->trans('Help'), $htmltooltip, 1, 0);
			$out .= '</span>';
		}

		$out .= '</th>';

		if ( ! empty($hookmanager->hooks['formfile'])) {
			foreach ($hookmanager->hooks['formfile'] as $module) {
				if (method_exists($module, 'formBuilddocLineOptions')) {
					$colspanmore++;
					$out .= '<th></th>';
				}
			}
		}
		$out .= '</tr>';

		// Execute hooks
		$parameters = array('colspan' => ($colspan + $colspanmore), 'socid' => (isset($GLOBALS['socid']) ? $GLOBALS['socid'] : ''), 'id' => (isset($GLOBALS['id']) ? $GLOBALS['id'] : ''), 'modulepart' => $modulepart);
		if (is_object($hookmanager)) {
			$hookmanager->executeHooks('formBuilddocOptions', $parameters, $GLOBALS['object']);
			$out    .= $hookmanager->resPrint;
		}
	}

	// Get list of files
	if ( ! empty($filedir)) {
		$link_list = array();
		$addcolumforpicto = ($delallowed || $morepicto);
		$colspan          = (3 + ($addcolumforpicto ? 1 : 0)); $colspanmore = 0;
		if (is_object($object) && $object->id > 0) {
			require_once DOL_DOCUMENT_ROOT . '/core/class/link.class.php';
			$link      = new Link($db);
			$sortfield = $sortorder = null;
			$link->fetchAll($link_list, $object->element, $object->id, $sortfield, $sortorder);
		}

		$out .= '<!-- html.formfile::showdocuments -->' . "\n";

		// Show title of array if not already shown
		if (( ! empty($file_list) || ! empty($link_list) || preg_match('/^massfilesarea/', $modulepart))
			&& ! $headershown) {
			$headershown = 1;
			$out        .= '<div class="titre">' . $titletoshow . '</div>' . "\n";
			$out        .= '<div class="div-table-responsive-no-min">';
			$out        .= '<table class="noborder centpercent" id="' . $modulepart . '_table">' . "\n";
		}

		// Loop on each file found
		if (is_array($file_list)) {
			foreach ($file_list as $file) {
				// Define relative path for download link (depends on module)
				$relativepath                    = $file["name"]; // Cas general
				if ($modulesubdir) $relativepath = $modulesubdir . "/" . $file["name"]; // Cas propal, facture...

				$out .= '<tr class="oddeven">';

				$documenturl                                                      = DOL_URL_ROOT . '/document.php';
				if (isset($conf->global->DOL_URL_ROOT_DOCUMENT_PHP)) $documenturl = $conf->global->DOL_URL_ROOT_DOCUMENT_PHP; // To use another wrapper

				// Show file name with link to download
				$out .= '<td class="minwidth200">';
				$out .= '<a class="documentdownload paddingright" href="' . $documenturl . '?modulepart=' . $modulepart . '&amp;file=' . urlencode($relativepath) . ($param ? '&' . $param : '') . '"';

				$mime                                  = dol_mimetype($relativepath, '', 0);
				if (preg_match('/text/', $mime)) $out .= ' target="_blank"';
				$out                                  .= '>';
				$out                                  .= img_mime($file["name"], $langs->trans("File") . ': ' . $file["name"]);
				$out                                  .= dol_trunc($file["name"], 150);
				$out                                  .= '</a>' . "\n";
				$out                                  .= '</td>';

				// Show file size
				$size = ( ! empty($file['size']) ? $file['size'] : dol_filesize($filedir . "/" . $file["name"]));
				$out .= '<td class="nowrap right">' . dol_print_size($size, 1, 1) . '</td>';

				// Show file date
				$date = ( ! empty($file['date']) ? $file['date'] : dol_filemtime($filedir . "/" . $file["name"]));
				$out .= '<td class="nowrap right">' . dol_print_date($date, 'dayhour', 'tzuser') . '</td>';

				if ($delallowed || $morepicto) {
					$out .= '<td class="right nowraponall">';
					if ($delallowed) {
						$tmpurlsource = preg_replace('/#[a-zA-Z0-9_]*$/', '', $urlsource);
						$out         .= '<a href="' . $tmpurlsource . ((strpos($tmpurlsource, '?') === false) ? '?' : '&amp;') . 'action=' . $removeaction . '&amp;file=' . urlencode($relativepath);
						$out         .= ($param ? '&amp;' . $param : '');
						$out         .= '">' . img_picto($langs->trans("Delete"), 'delete') . '</a>';
					}
					if ($morepicto) {
						$morepicto = preg_replace('/__FILENAMEURLENCODED__/', urlencode($relativepath), $morepicto);
						$out      .= $morepicto;
					}
					$out .= '</td>';
				}

				if (is_object($hookmanager)) {
					$parameters = array('colspan' => ($colspan + $colspanmore), 'socid' => (isset($GLOBALS['socid']) ? $GLOBALS['socid'] : ''), 'id' => (isset($GLOBALS['id']) ? $GLOBALS['id'] : ''), 'modulepart' => $modulepart, 'relativepath' => $relativepath);
					$res        = $hookmanager->executeHooks('formBuilddocLineOptions', $parameters, $file);
					if (empty($res)) {
						$out .= $hookmanager->resPrint; // Complete line
						$out .= '</tr>';
					} else {
						$out = $hookmanager->resPrint; // Replace all $out
					}
				}
			}
		}
		// Loop on each link found
		//      if (is_array($link_list))
		//      {
		//          $colspan = 2;
		//
		//          foreach ($link_list as $file)
		//          {
		//              $out .= '<tr class="oddeven">';
		//              $out .= '<td colspan="'.$colspan.'" class="maxwidhtonsmartphone">';
		//              $out .= '<a data-ajax="false" href="'.$file->url.'" target="_blank">';
		//              $out .= $file->label;
		//              $out .= '</a>';
		//              $out .= '</td>';
		//              $out .= '<td class="right">';
		//              $out .= dol_print_date($file->datea, 'dayhour');
		//              $out .= '</td>';
		//              if ($delallowed || $printer || $morepicto) $out .= '<td></td>';
		//              $out .= '</tr>'."\n";
		//          }
		//      }

		if (count($file_list) == 0 && count($link_list) == 0 && $headershown) {
			$out .= '<tr><td colspan="' . (3 + ($addcolumforpicto ? 1 : 0)) . '" class="opacitymedium">' . $langs->trans("None") . '</td></tr>' . "\n";
		}
	}

	if ($headershown) {
		// Affiche pied du tableau
		$out .= "</table>\n";
		$out .= "</div>\n";
		if ($genallowed) {
			if (empty($noform)) $out .= '</form>' . "\n";
		}
	}
	$out .= '<!-- End show_document -->' . "\n";

	return $out;
}
