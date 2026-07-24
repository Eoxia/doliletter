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
 * \file    core/tpl/spread/preventionplan_public_info.tpl.php
 * \ingroup doliletter
 * \brief   Read-only public display of a prevention plan (risks / protections / required certifications).
 *          Certification photos are uploaded per signatory (Saturne media block) in the signatories list.
 *          Expects: $langs, $ppRisks, $ppProtections, $ppProtectionMap, $ppCertifications, $certificationOptions.
 */
?>
<style>
.pp-public-block { background: #fff; border: 1px solid #e5e5e5; border-radius: 8px; padding: 16px; margin: 16px 0; }
.pp-public-block__title { display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 600; color: #333; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #eee; }
.pp-public-block__title i { color: #3b82f6; }
.pp-public-grid { display: flex; flex-wrap: wrap; gap: 12px; }
.pp-public-item { display: flex; align-items: center; gap: 10px; min-width: 220px; padding: 8px 10px; border: 1px solid #e5e5e5; border-radius: 8px; }
.pp-public-item img { width: 44px; height: 44px; object-fit: contain; flex: 0 0 auto; }
.pp-public-item__name { font-size: 13px; font-weight: 600; color: #333; }
.pp-public-item__comment { font-size: 12px; color: #666; }
.pp-public-badge { display: inline-block; margin-top: 4px; padding: 2px 8px; font-size: 11px; font-weight: 600; color: #fff; background: #ef4444; border-radius: 10px; }
.pp-signatory-media-row { margin: 6px 0 12px; padding: 10px 12px; border: 1px dashed #d1d5db; border-radius: 6px; background: #fafafa; }
.pp-signatory-media-row__label { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #666; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
.pp-signatory-media-row__label i { color: #3b82f6; }
.pp-cert-uploads { display: flex; flex-direction: column; gap: 10px; margin-top: 8px; }
.pp-cert-upload { padding: 8px 10px; border: 1px solid #e5e5e5; border-radius: 6px; background: #fff; }
.pp-cert-upload__label { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; font-size: 13px; font-weight: 600; color: #333; margin-bottom: 6px; }
.pp-cert-upload__label i { color: #3b82f6; }
.pp-cert-upload__row { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
.pp-cert-upload__row .linked-medias { margin: 0; }
.pp-cert-upload__row .photo { border-radius: 6px; }
.pp-public-badge--muted { background: #6b7280; }
.pp-cert-upload .pp-cert-badge-not-concerned { display: none; }
.pp-cert-upload--not-concerned .pp-cert-badge-mandatory { display: none; }
.pp-cert-upload--not-concerned .pp-cert-badge-not-concerned { display: inline-block; }
.pp-cert-not-concerned-btn { display: inline-flex; align-items: center; gap: 6px; margin-left: auto; padding: 4px 10px; font-size: 12px; font-weight: 600; color: #4b5563; background: #fff; border: 1px solid #d1d5db; border-radius: 14px; cursor: pointer; }
.pp-cert-not-concerned-btn:hover { border-color: #9ca3af; background: #f9fafb; }
.pp-cert-not-concerned-btn--active { color: #fff; background: #6b7280; border-color: #6b7280; }
.pp-cert-upload--not-concerned { background: #f9fafb; }
.pp-cert-upload--not-concerned .pp-cert-upload__label { color: #6b7280; }
.pp-cert-upload--not-concerned .pp-cert-upload__row { display: none; }
.pp-mandatory-pending { display: flex; align-items: flex-start; gap: 8px; margin: 12px 0; padding: 12px 14px; font-size: 13px; color: #92400e; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; }
.pp-mandatory-pending i { margin-top: 2px; }
.pp-mandatory-pending__list { margin: 4px 0 0; padding-left: 18px; font-weight: 600; }
.pp-single-person { margin-top: 16px; }
.pp-inline-signature { margin: 12px 0; padding: 14px; background: #fff; border: 1px solid #e5e5e5; border-radius: 8px; }
.pp-inline-signature__title { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; color: #333; margin-bottom: 10px; }
.pp-inline-signature__title i { color: #3b82f6; }
.pp-inline-signature .signature-element { position: relative; display: block; }
.pp-inline-canvas { width: 100%; height: 200px; touch-action: none; }
.pp-inline-signature__actions { margin-top: 10px; text-align: right; }
.pp-signed-confirm { display: flex; align-items: center; gap: 8px; margin: 12px 0; padding: 14px; font-size: 14px; font-weight: 600; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; }
.pp-invalid-link { display: flex; align-items: center; gap: 8px; margin: 16px 0; padding: 14px; font-size: 14px; font-weight: 600; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; }
</style>

<div class="pp-public-info">
    <?php if (!empty($ppRisks)) { ?>
    <div class="pp-public-block">
        <div class="pp-public-block__title"><i class="fas fa-exclamation-triangle"></i> <?php echo $langs->trans('MobilePPRisks'); ?></div>
        <div class="pp-public-grid">
            <?php foreach ($ppRisks as $ppRiskItem) { ?>
            <div class="pp-public-item">
                <?php if (!empty($ppRiskItem['thumb'])) { ?><img src="<?php echo $ppRiskItem['thumb']; ?>" alt=""><?php } ?>
                <div>
                    <div class="pp-public-item__name"><?php echo dol_escape_htmltag(($ppRiskItem['name'] != -1) ? $ppRiskItem['name'] : ''); ?></div>
                    <?php if (dol_strlen($ppRiskItem['comment'])) { ?><div class="pp-public-item__comment"><?php echo dol_escape_htmltag($ppRiskItem['comment']); ?></div><?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <?php if (!empty($ppProtections)) { ?>
    <div class="pp-public-block">
        <div class="pp-public-block__title"><i class="fas fa-hard-hat"></i> <?php echo $langs->trans('MobilePPProtections'); ?></div>
        <div class="pp-public-grid">
            <?php foreach ($ppProtections as $ppProtectionItem) {
                $protectionCategory = $ppProtectionMap[$ppProtectionItem['position']] ?? null;
                if (empty($protectionCategory)) {
                    continue;
                }
                $protectionThumb = DOL_URL_ROOT . '/custom/digiriskdolibarr/img/' . $protectionCategory['name_thumbnail'];
            ?>
            <div class="pp-public-item">
                <img src="<?php echo $protectionThumb; ?>" alt="">
                <div>
                    <div class="pp-public-item__name"><?php echo dol_escape_htmltag($protectionCategory['name']); ?></div>
                    <?php if (!empty($ppProtectionItem['comment'])) { ?><div class="pp-public-item__comment"><?php echo dol_escape_htmltag($ppProtectionItem['comment']); ?></div><?php } ?>
                    <?php if (!empty($ppProtectionItem['mandatory'])) { ?><span class="pp-public-badge"><?php echo $langs->trans('MobilePPMandatory'); ?></span><?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <?php if (!empty($ppCertifications)) { ?>
    <div class="pp-public-block">
        <div class="pp-public-block__title"><i class="fas fa-id-badge"></i> <?php echo $langs->trans('MobilePPCertifications'); ?></div>
        <div class="pp-public-grid">
            <?php foreach ($ppCertifications as $ppCertItem) {
                $certLabel = $certificationOptions[$ppCertItem['code']] ?? $ppCertItem['code'];
            ?>
            <div class="pp-public-item">
                <div>
                    <div class="pp-public-item__name"><?php echo dol_escape_htmltag($certLabel); ?></div>
                    <?php if (!empty($ppCertItem['mandatory'])) { ?><span class="pp-public-badge"><?php echo $langs->trans('MobilePPMandatory'); ?></span><?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>
