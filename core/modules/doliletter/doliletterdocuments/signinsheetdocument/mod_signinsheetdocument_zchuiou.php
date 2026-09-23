<?php
/* Copyright (C) 2026 DoliLetter
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
 *	\file       core/modules/doliletter/doliletterdocuments/signinsheetdocument/mod_signinsheetdocument_chuiou.php
 * \ingroup     doliletter
 *	\brief      File with class to manage standard numbering for attendance sheets
 */

require_once DOL_DOCUMENT_ROOT . '/custom/saturne/core/modules/saturne/modules_saturne.php';

/**
 * 	Class to manage standard numbering for attendance sheets
 */
class mod_signinsheetdocument_zchuiou extends ModeleNumRefSaturne
{
	/**
	 * @var int Sort order
	 */
	public int $position = 10;

	/**
	 * @var string Module name
	 */
	public $module = 'doliletter';

	/**
	 * @var string Element type of object
	 */
	public $element = 'signinsheetdocument';

	/**
	 * @var string document prefix
	 */
	public string $prefix = 'FE';

	/**
	 * @var string document suffix
	 */
	public string $suffix = '0000';

	/**
	 * @var string model name
	 */
	public string $name = 'chuiou';

	/**
	 * @var string description
	 */
	public string $description = 'Modèle de numérotation standard';

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * Return description of module
	 *
	 * @return string Description text
	 */
	public function info(): string
	{
		global $langs;
		return $langs->trans('Renvoie une numérotation au format FEyymm-nnnn');
	}

	/**
	 * Return an example of numbering
	 *
	 * @return string Example
	 */
	public function getExample(): string
	{
		return $this->prefix . dol_print_date(dol_now(), '%y%m') . '-0001';
	}

	/**
	 * Return next free value
	 *
	 * @param  object $object Object we need next value for
	 * @return string         Value if OK, <0 if KO
	 */
	public function getNextValue(object $object): string
	{
		global $db, $conf;

		$prefix = $this->prefix;

		$posindice = dol_strlen($prefix) + 6;
		$sql = "SELECT MAX(CAST(SUBSTRING(ref FROM ".$posindice.") AS SIGNED)) as max";
		$sql .= " FROM ".MAIN_DB_PREFIX.$object->table_element;
		$sql .= " WHERE ref LIKE '".$db->escape($prefix)."____-%'";
		if (isset($object->ismultientitymanaged) && $object->ismultientitymanaged == 1) {
			$sql .= " AND entity = ".$conf->entity;
		}

		$resql = $db->query($sql);
		if ($resql) {
			$obj = $db->fetch_object($resql);
			if ($obj) {
				$max = intval($obj->max);
			} else {
				$max = 0;
			}
		} else {
			return '-1';
		}

		$date = dol_now();
		if (isset($object->date)) {
			$date = $object->date;
		} elseif (isset($object->date_creation)) {
			$date = $object->date_creation;
		}
		
		$yymm = dol_print_date($date, '%y%m');
		$num = sprintf("%04d", $max + 1);

		$ref = $prefix . $yymm . "-" . $num;

		return $ref;
	}
}
