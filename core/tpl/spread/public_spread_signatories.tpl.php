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
 * \file    core/tpl/spread/public_spread_signatories.tpl.php
 * \ingroup doliletter
 * \brief   Liste en lecture seule des personnes de la diffusion.
 *          Une personne diffusee ne voyait que sa propre ligne : elle ne pouvait pas savoir qui
 *          d'autre avait pris connaissance du document, alors que c'est le sujet de la diffusion.
 *          Expects: $db, $langs, $signatories, $tmpUser.
 */

global $db, $langs;

$signedCount = 0;
foreach ($signatories as $signatoryItem) {
    if (!empty($signatoryItem->signature)) {
        $signedCount++;
    }
}
?>
<div class="spread-signatories">
    <div class="spread-signatories__header">
        <div class="spread-signatories__title">
            <i class="fas fa-users"></i> <?php echo $langs->trans('SpreadSignatoriesTitle'); ?>
        </div>
        <span class="spread-signatories__count"><?php echo $langs->trans('SpreadSignatoriesCount', $signedCount, count($signatories)); ?></span>
    </div>

    <?php if (empty($signatories)) { ?>
    <div class="spread-signatories__empty"><?php echo $langs->trans('SpreadNoSignatoryYet'); ?></div>
    <?php } else { ?>
    <ul class="spread-signatories__list">
        <?php foreach ($signatories as $signatoryItem) {
            $signatoryName = doliletter_spread_get_signatory_name($signatoryItem);
            if (!dol_strlen($signatoryName) && !empty($signatoryItem->element_id)) {
                $tmpUser->fetch($signatoryItem->element_id);
                $signatoryName = $tmpUser->getFullName($langs);
            }
            $hasSigned = !empty($signatoryItem->signature);
        ?>
        <li class="spread-signatories__item<?php echo $hasSigned ? ' spread-signatories__item--signed' : ''; ?>">
            <i class="fas <?php echo $hasSigned ? 'fa-check-circle' : 'fa-hourglass-half'; ?>"></i>
            <span class="spread-signatories__name"><?php echo dol_escape_htmltag(dol_strlen($signatoryName) ? $signatoryName : $langs->trans('Unknown')); ?></span>
            <span class="spread-signatories__status">
                <?php echo $hasSigned ? dol_print_date($signatoryItem->signature_date, '%d/%m/%Y %H:%M') : $langs->trans('SpreadSignatoryPending'); ?>
            </span>
        </li>
        <?php } ?>
    </ul>
    <?php } ?>
</div>
