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
        <?php if (!isset($showCount) || $showCount) { ?>
        <span class="spread-signatories__count"><?php echo $langs->trans('SpreadSignatoriesCount', $signedCount, count($signatories)); ?></span>
        <?php } ?>
    </div>

    <?php if (empty($signatories)) { ?>
    <div class="spread-signatories__empty"><?php echo $langs->trans('SpreadNoSignatoryYet'); ?></div>
    <?php } elseif (!isset($showName) || $showName || !empty($showContact) || !empty($showDocs)) { ?>
    <ul class="spread-signatories__list">
        <?php foreach ($signatories as $signatoryItem) {
            $hasSigned = !empty($signatoryItem->signature);
            // Hide pending signatories if they shouldn't be listed. Actually, the original template shows them.
            // We keep showing them.

            $signatoryName  = '';
            $signatoryEmail = $signatoryItem->email ?? '';
            $signatoryPhone = $signatoryItem->phone ?? '';

            if (!empty($signatoryItem->element_id)) {
                $tmpUser->fetch($signatoryItem->element_id);
                if (empty($signatoryEmail) && !empty($tmpUser->email)) {
                    $signatoryEmail = $tmpUser->email;
                }
                if (empty($signatoryPhone)) {
                    if (!empty($tmpUser->user_mobile)) {
                        $signatoryPhone = $tmpUser->user_mobile;
                    } elseif (!empty($tmpUser->office_phone)) {
                        $signatoryPhone = $tmpUser->office_phone;
                    }
                }
            }

            if (!isset($showName) || $showName) {
                $signatoryName = doliletter_spread_get_signatory_name($signatoryItem);
                if (!dol_strlen($signatoryName) && !empty($signatoryItem->element_id)) {
                    $signatoryName = $tmpUser->getFullName($langs);
                }
            } else {
                $signatoryName = $langs->trans('Signatory');
            }
        ?>
        <li class="spread-signatories__item<?php echo $hasSigned ? ' spread-signatories__item--signed' : ''; ?>" style="display: block;">
            <div style="display: flex; align-items: center; width: 100%;">
                <i class="fas <?php echo $hasSigned ? 'fa-check-circle' : 'fa-hourglass-half'; ?>" style="margin-right: 10px;"></i>
                <span class="spread-signatories__name" style="flex: 1 1 auto;"><?php echo dol_escape_htmltag(dol_strlen($signatoryName) ? $signatoryName : $langs->trans('Unknown')); ?></span>
                <span class="spread-signatories__status">
                    <?php echo $hasSigned ? dol_print_date($signatoryItem->signature_date, '%d/%m/%Y %H:%M') : $langs->trans('SpreadSignatoryPending'); ?>
                </span>
            </div>
            
            <?php if (!empty($showContact) && (!empty($signatoryEmail) || !empty($signatoryPhone))) { ?>
            <div style="margin-left: 24px; margin-top: 6px; font-size: 13px; color: #6b7280; display: flex; flex-wrap: wrap; gap: 15px;">
                <?php if (!empty($signatoryEmail)) { ?>
                <span style="white-space: nowrap; display: inline-flex; align-items: center; gap: 5px;"><i class="fas fa-envelope"></i> <?php echo dol_escape_htmltag($signatoryEmail); ?></span>
                <?php } ?>
                <?php if (!empty($signatoryPhone)) { ?>
                <span style="white-space: nowrap; display: inline-flex; align-items: center; gap: 5px;"><i class="fas fa-phone" style="transform: scaleX(-1);"></i> <?php echo dol_escape_htmltag($signatoryPhone); ?></span>
                <?php } ?>
            </div>
            <?php } ?>
        </li>
        <?php } ?>
    </ul>
    <?php } ?>
</div>
