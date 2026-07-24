<?php
/* Copyright (C) 2025 EVARISK <technique@evarisk.com>
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
 * \file        class/doliletterspreadsignature.class.php
 * \ingroup     doliletter
 * \brief       Signature of a spread, able to carry the answers given by the signatory
 */

require_once DOL_DOCUMENT_ROOT . '/custom/saturne/class/saturnesignature.class.php';

/**
 * Class for DoliletterSpreadSignature
 */
class DoliletterSpreadSignature extends SaturneSignature
{
    /**
     * Constructor
     *
     * @param DoliDB $db                  Database handler
     * @param string $moduleNameLowerCase Module name
     * @param string $objectType          Object element type
     */
    public function __construct(DoliDB $db, string $moduleNameLowerCase = 'saturne', string $objectType = 'saturne_signature')
    {
        parent::__construct($db, $moduleNameLowerCase, $objectType);

        // SaturneObject drops every field flagged as disabled, `json` included. The spread keeps the
        // answers of a signatory there (documents they are not concerned by), so it is restored here.
        $this->fields['json'] = ['type' => 'text', 'label' => 'JSON', 'enabled' => 1, 'position' => 210, 'notnull' => 0, 'visible' => 0];
    }
}
