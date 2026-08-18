<?php
/* Copyright (C) 2025 EVARISK <technique@evarisk.com>
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
 * \file    class/actions_doliletter.class.php
 * \ingroup doliletter
 * \brief   Doliletter hook overload
 */
/**
 * Class ActionsDoliletter
 */
class ActionsDoliletter
{
    /**
     * @var DoliDB Database handler
     */
    public DoliDB $db;

    /**
     * @var string Error code (or message)
     */
    public string $error = '';

    /**
     * @var array Errors.
     */
    public array $errors = [];

    /**
     * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
     */
    public array $results = [];

    /**
     * @var string|null String displayed by executeHook() immediately after return
     */
    public ?string $resprints;

    /**
     * Constructor
     *
     *  @param DoliDB $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        $this->db = $db;
    }

    /**
     * Overloading the addHtmlHeader function : replacing the parent's function with the one below
     *
     * @param  array $parameters Hook metadata (context, etc...)
     * @return int               0 < on error, 0 on success, 1 to replace standard code
     */
    public function addHtmlHeader(array $parameters): int
    {

        return 0; // or return 1 to replace standard code
    }

    /**
     * Overloading the printUserListWhere function : replacing the parent's function with the one below
     *
     * @param  array $parameters Hook metadata (context, etc...)
     * @return int               0 < on error, 0 on success, 1 to replace standard code
     */
    public function printUserListWhere(array $parameters): int
    {

        return 0; // or return 1 to replace standard code
    }

    /**
     * Overloading the saturnePrintFieldListLoopObject function : replacing the parent's function with the one below
     *
     * @param  array $parameters Hook metadata (context, etc...)
     * @param  object $object    Current object
     * @return int               0 < on error, 0 on success, 1 to replace standard code
     * @throws Exception
     */
    public function saturnePrintFieldListLoopObject(array $parameters, object $object): int
    {
        global $conf, $langs;

        if (preg_match('/spreadlist/', $parameters['context'])) {
            if ($parameters['key'] == 'number_of_users') {

                require_once DOL_DOCUMENT_ROOT . '/custom/saturne/class/saturnesignature.class.php';
                $signatory = new SaturneSignature($this->db, 'doliletter', 'spread');

                $signatories = $signatory->fetchSignatory('', $object->id, $object->element);
                if ($signatories <= 0) {
                    $signatories = [];
                } elseif (is_array($signatories)) {
                    $signatories = current($signatories);
                }
                $signatories = array_filter($signatories, function ($signatory) {
                    return $signatory->element_id != 0;
                });
                print count($signatories);
            } elseif ($parameters['key'] == 'label') {

                require_once DOL_DOCUMENT_ROOT . '/custom/saturne/lib/saturne.lib.php';

                $objectsMetadata    = saturne_get_objects_metadata();

                $objectsMetadata[$object->object_type]['object']->fetch($object->id);
                $objectLabel = $objectsMetadata[$object->object_type]['object']->{$objectsMetadata[$object->object_type]['label_field']} ?? '';
                print $objectLabel;

            } elseif ($parameters['key'] == 'object_type') {
                require_once DOL_DOCUMENT_ROOT . '/custom/saturne/lib/saturne.lib.php';

                $objectsMetadata    = saturne_get_objects_metadata();

                $this->results['object_type'] = $langs->trans($objectsMetadata[$object->object_type]['langs']);
                return 1;
            } elseif ($parameters['key'] == 'public_url') {
                $url = DOL_URL_ROOT . '/custom/doliletter/public/spread/add_spread.php?id=' . $object->fk_object . '&object_type=' . $object->object_type;
                print '<a href="' . $url . '" target="_blank">' . $url . '</a>';
            }
        }

        return 0;
    }

    /**
     * Overloading the completeTabsHead function : replacing the parent's function with the one below
     *
     * @param  array $parameters Hook metadata (context, etc...)
     * @return int               0 < on error, 0 on success, 1 to replace standard code
     */
    function completeTabsHead(array $parameters, $object) : int
    {
        global $db;

        require_once DOL_DOCUMENT_ROOT . '/custom/doliletter/class/doliletterattendancesheet.class.php';
        require_once DOL_DOCUMENT_ROOT . '/custom/saturne/class/saturnesignature.class.php';

        $attendanceSheet = new DoliletterAttendanceSheet($db, 'doliletter');
        $signatory       = new SaturneSignature($db, 'doliletter', 'spread');

        if (strpos($parameters['context'], 'main') !== false) {
            if (!empty($parameters['head'])) {
                foreach ($parameters['head'] as $headKey => $headTab) {
                    if (is_array($headTab) && count($headTab) > 0) {
                        if (isset($headTab[2]) && $headTab[2] == 'spread' && strpos($headTab[1], 'badge') === false) {

                            $attendanceSheet->fetch(0, '', ' AND object_type = ' . "'" . $object->element . "'" . ' AND fk_object = ' . $object->id);

                            if (empty($attendanceSheet->id)) {
                                continue;
                            }

                            $signatories = $signatory->fetchSignatory('', $attendanceSheet->id ?? 0, $attendanceSheet->element);
                            if ($signatories <= 0) {
                                $signatories = [];
                            } elseif (is_array($signatories)) {
                                $signatories = current($signatories);
                            }
                            $totalNumberOfSignatories = count(array_filter($signatories, function ($signatory) {
                                return $signatory->element_id != 0;
                            }));
                            $totalNumberOfSignedSignatories = count(array_filter($signatories, function ($signatory) {
                                return !empty($signatory->signature);
                            }));

                            $parameters['head'][$headKey][1] .= '<span class="badge marginleftonlyshort">' . $totalNumberOfSignedSignatories . ' / ' . $totalNumberOfSignatories . '</span>';
                        }
                    }
                }
            }
        }

        return 0; // or return 1 to replace standard code
    }

    /**
	 *  Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param $parameters
	 * @return int
	 */
	public function emailElementlist($parameters)
    {
        global $user, $langs;

		$value = [];

		if (isModEnabled('doliletter') && strpos($parameters['context'], 'emailtemplates') !== false) {
			if ($user->hasRight('doliletter', 'spread', 'write')) {
				$value['doliletter_spread'] = '<i class="fas fa-check-circle" style="color: #63ACC9;"></i>  ' . dol_escape_htmltag($langs->trans('Spread'));
			}
		}

        $this->results = $value;
        return 0;
    }

    /**
	 *  Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param $parameters
	 * @return int
	 */
	public function redirectAfterConnection($parameters)
	{
		global $conf;

		$value = '';

		if (strpos($parameters['context'], 'mainloginpage') !== false) {	    // do something only for the context 'somecontext1' or 'somecontext2'
			if (!empty($_COOKIE['doliletter_login_backtopage'])) {
                $value = dol_buildpath('/custom/doliletter/public/spread/add_spread_login.php', 1);
            }
		}

		if (true) {
			$this->resprints = $value;
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
    }

    /**
     * Overloading the saturneAdminDocumentData function : replacing the parent's function with the one below
     *
     * @param  array $parameters Hook metadata (context, etc...)
     * @return int               0 < on error, 0 on success, 1 to replace standard code
     */
    public function saturneAdminDocumentData(array $parameters): int
    {
        if (strpos($parameters['context'], 'doliletteradmindocuments') !== false) {
            $types = [
                'SigninSheetDocument' => [
                    'documentType' => 'signinsheet',
                    'className'    => 'signinsheetdocument',
                    'picto'        => 'fontawesome_fa-file-signature_fas_#63ACC9'
                ]
            ];
            $this->results = array_merge((array)$this->results, $types);
        }

        return 0;
    }
}
