<?php
/* Copyright (C) 2026 EVARISK <technique@evarisk.com>
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
 * \file    core/tpl/spread/public_spread_signatory_details.tpl.php
 * \ingroup doliletter
 * \brief   Answers of one person of the spread, folded under their line of the list: the documents
 *          provided for each requested element (name + link), the elements declared not concerned and
 *          the risks taken note of. Only included for a logged user allowed to read the object: the
 *          links go through document.php, which checks that right again.
 *          Expects: $conf, $langs, $signatoryItem, $signatoryEmail, $signatoryPhone, $showContact,
 *                   $ppCertificationStates, $ppCertSubDir, $ppRisks, $ppTexts.
 */

$detailsCertStates = $ppCertificationStates[$signatoryItem->id] ?? [];
$detailsFileCount  = array_sum(array_map(function (array $certState) {
    return count($certState['files']);
}, $detailsCertStates));
$detailsShowContact = empty($showContact) && (!empty($signatoryEmail) || !empty($signatoryPhone));
$detailsRisksRead   = count(array_intersect(array_column($ppRisks, 'category'), doliletter_spread_get_acknowledged_risks($signatoryItem)));
?>
<details class="spread-signatory-details">
    <summary class="spread-signatory-details__toggle">
        <i class="fas fa-folder-open"></i> <?php echo $langs->trans('SpreadSignatoryDetails'); ?>
        <?php if ($detailsFileCount > 0) { ?>
        <span class="spread-signatory-details__count"><?php echo $detailsFileCount; ?></span>
        <?php } ?>
        <i class="fas fa-chevron-down spread-signatory-details__chevron"></i>
    </summary>
    <div class="spread-signatory-details__body">
        <?php if ($detailsShowContact) { ?>
        <div class="spread-signatory-details__line">
            <?php if (!empty($signatoryEmail)) { ?>
            <span><i class="fas fa-envelope"></i> <?php echo dol_escape_htmltag($signatoryEmail); ?></span>
            <?php } ?>
            <?php if (!empty($signatoryPhone)) { ?>
            <span><i class="fas fa-phone"></i> <?php echo dol_escape_htmltag($signatoryPhone); ?></span>
            <?php } ?>
        </div>
        <?php } ?>

        <?php if (!empty($ppRisks)) { ?>
        <div class="spread-signatory-details__line">
            <span><i class="fas fa-check-square"></i> <?php echo $langs->trans('SpreadSignatoryBlocksRead', $langs->transnoentities($ppTexts['blocks']), $detailsRisksRead, count($ppRisks)); ?></span>
        </div>
        <?php } ?>

        <?php foreach ($detailsCertStates as $certState) {
            if (!empty($certState['not_concerned'])) {
                $certStateKey = 'not-concerned';
                $certStateLabel = $langs->trans('SpreadNotConcerned');
            } elseif (!empty($certState['has_file'])) {
                $certStateKey = 'provided';
                $certStateLabel = $langs->trans('SpreadCertProvided');
            } else {
                $certStateKey = !empty($certState['mandatory']) ? 'missing' : 'none';
                $certStateLabel = $langs->trans('SpreadCertNotProvided');
            } ?>
        <div class="spread-signatory-details__cert">
            <div class="spread-signatory-details__cert-label">
                <i class="fas fa-id-badge"></i>
                <span><?php echo dol_escape_htmltag($certState['label']); ?></span>
                <span class="spread-signatory-details__state spread-signatory-details__state--<?php echo $certStateKey; ?>"><?php echo $certStateLabel; ?></span>
            </div>
            <?php if (!empty($certState['files'])) { ?>
            <ul class="spread-signatory-details__files">
                <?php foreach ($certState['files'] as $certFile) {
                    $certFileUrl = DOL_URL_ROOT . '/document.php?modulepart=digiriskdolibarr&entity=' . ((int) $conf->entity) . '&attachment=0&file=' . urlencode($ppCertSubDir . '/' . ((int) $signatoryItem->id) . '/' . dol_sanitizeFileName($certState['code']) . '/' . $certFile['path']); ?>
                <li>
                    <a href="<?php echo dol_escape_htmltag($certFileUrl); ?>" target="_blank" rel="noopener" title="<?php echo dol_escape_htmltag($certFile['name']); ?>">
                        <i class="fas <?php echo $certFile['is_image'] ? 'fa-file-image' : 'fa-file-alt'; ?>"></i>
                        <span><?php echo dol_escape_htmltag($certFile['name']); ?></span>
                    </a>
                </li>
                <?php } ?>
            </ul>
            <?php } ?>
        </div>
        <?php } ?>

        <?php if (!$detailsShowContact && empty($ppRisks) && empty($detailsCertStates)) { ?>
        <div class="spread-signatory-details__line"><?php echo $langs->trans('SpreadSignatoryNoDetails'); ?></div>
        <?php } ?>
    </div>
</details>
