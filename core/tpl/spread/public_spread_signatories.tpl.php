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

            $signatoryName  = '';
            $signatoryEmail = $signatoryItem->email ?? '';
            $signatoryPhone = $signatoryItem->phone ?? '';
            $isDolUser = false;

            if (!empty($signatoryItem->element_id)) {
                $tmpUser->fetch($signatoryItem->element_id);
                $isDolUser = true;
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

            $userPicto = '';
            if (!isset($showName) || $showName) {
                $signatoryName = doliletter_spread_get_signatory_name($signatoryItem);
                
                if ($isDolUser) {
                    $userPicto = $tmpUser->getNomUrl(-2, 'nolink');
                } else {
                    $userPicto = '<i class="fas fa-user-circle opacitymedium" style="margin-right: 8px; font-size: 1.2em; vertical-align: middle;"></i>';
                }

                if (!dol_strlen($signatoryName) && $isDolUser) {
                    $signatoryName = dol_escape_htmltag($tmpUser->getFullName($langs));
                } else {
                    $signatoryName = dol_escape_htmltag($signatoryName);
                }
            } else {
                $signatoryName = $langs->trans('Signatory');
                $userPicto = '<i class="fas fa-user-circle opacitymedium" style="margin-right: 8px; font-size: 1.2em; vertical-align: middle;"></i>';
            }
        ?>
        <li class="spread-signatories__item<?php echo $hasSigned ? ' spread-signatories__item--signed' : ''; ?>" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; padding: 10px 0;">
            
            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 15px; flex: 1 1 auto;">
                
                <span class="spread-signatories__name" style="font-weight: 500; color: #4b5563; min-width: 150px; display: flex; align-items: center;">
                    <span style="margin-right: 8px; display: inline-flex; align-items: center; justify-content: center;"><?php echo $userPicto; ?></span>
                    <?php echo dol_strlen($signatoryName) ? $signatoryName : $langs->trans('Unknown'); ?>
                </span>
                
                <?php if (!empty($showContact) && (!empty($signatoryEmail) || !empty($signatoryPhone))) { ?>
                    <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 15px; font-size: 13px; color: #6b7280;">
                        <?php if (!empty($signatoryEmail)) { ?>
                        <span style="white-space: nowrap; display: inline-flex; align-items: center; gap: 5px;">
                            <i class="fas fa-envelope opacitymedium"></i> <?php echo dol_escape_htmltag($signatoryEmail); ?>
                        </span>
                        <?php } ?>
                        <?php if (!empty($signatoryPhone)) { ?>
                        <span style="white-space: nowrap; display: inline-flex; align-items: center; gap: 5px;">
                            <i class="fas fa-phone opacitymedium" style="transform: scaleX(-1);"></i> <?php echo dol_escape_htmltag($signatoryPhone); ?>
                        </span>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
            
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px; white-space: nowrap; font-size: 13px; color: #6b7280;">
                <span class="spread-signatories__status" style="margin-right: 5px;">
                    <?php echo $hasSigned ? dol_print_date($signatoryItem->signature_date, '%d/%m/%Y %H:%M') : $langs->trans('SpreadSignatoryPending'); ?>
                </span>
                <i class="fas <?php echo $hasSigned ? 'fa-check-circle' : 'fa-hourglass-half'; ?>" style="color: <?php echo $hasSigned ? '#10b981' : '#9ca3af'; ?>; min-width: 16px; text-align: center; font-size: 1.1em;"></i>
            </div>
            
        </li>
        <?php } ?>
    </ul>
    <?php } ?>
</div>
