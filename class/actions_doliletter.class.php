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
            }
        }

        return 0;
    }
}
