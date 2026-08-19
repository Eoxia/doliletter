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
 *	\file       core/modules/doliletter/doliletterdocuments/signinsheetdocument/mod_signinsheetdocument_kikela.php
 * \ingroup     doliletter
 *	\brief      File with class to manage kikela numbering for attendance sheets
 */

require_once DOL_DOCUMENT_ROOT . '/custom/saturne/core/modules/saturne/modules_saturne.php';

/**
 * 	Class to manage kikela numbering for attendance sheets
 */
class mod_signinsheetdocument_kikela extends CustomModeleNumRefSaturne
{
	/**
	 * @var int Sort order
	 */
	public int $position = 20;

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
	public string $name = 'kikela';

	/**
	 * @var string description
	 */
	public string $description = 'Modèle de numérotation avec masque personnalisable';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->setCustomValue('doliletter', 'signinsheet');
	}

	/**
	 * Return an example of numbering
	 *
	 * @return string Example
	 */
	public function getExample(): string
	{
		global $langs, $mysoc;
		require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';

		$mask = getDolGlobalString('DOLILETTER_SIGNINSHEETDOCUMENT_KIKELA_ADDON');
		if (!$mask) {
			return $langs->trans('NotConfigured');
		}

		$numExample = get_next_value($this->db, $mask, 'doliletter_attendance_sheet', 'ref', '', $mysoc, dol_now(), 'next', false);
		if (!$numExample) {
			$numExample = $langs->trans('NotConfigured');
		}
		return $numExample;
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
		require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';

		$mask = getDolGlobalString('DOLILETTER_SIGNINSHEETDOCUMENT_KIKELA_ADDON');
		if (!$mask) {
			return 'NotConfigured';
		}

		$date = !empty($object->date_creation) ? $object->date_creation : dol_now();
		
		// Ensure we query the correct table since SaturneDocuments doesn't define table_element
		$table = !empty($object->table_element) ? $object->table_element : 'doliletter_attendance_sheet';

		$numFinal = get_next_value($db, $mask, $table, 'ref', '', $object, $date, 'next', false, null, $conf->entity);

		return $numFinal;
	}
}
