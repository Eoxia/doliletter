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
 * \file    core/tpl/spread/preventionplan_signatory_certs.tpl.php
 * \ingroup doliletter
 * \brief   One Saturne media block per required certification (document) for a given signatory.
 *          A mandatory certification can be waived by declaring the signatory is not concerned by it.
 *          Expects: $langs, $objectRef, $certSignatoryId, $ppCertifications, $certificationOptions, $ppCertificationStates.
 */

if (!empty($ppCertifications)) { ?>
<div class="pp-cert-uploads">
    <?php foreach ($ppCertificationStates[$certSignatoryId] ?? [] as $certState) {
        $certCode     = $certState['code'];
        $certSubDir   = 'preventionplan/' . dol_sanitizeFileName($objectRef) . '/certifications/' . ((int) $certSignatoryId) . '/' . dol_sanitizeFileName($certCode);
        $notConcerned = !empty($certState['not_concerned']);
    ?>
    <div class="pp-cert-upload<?php echo $notConcerned ? ' pp-cert-upload--not-concerned' : ''; ?>" data-cert-code="<?php echo dol_escape_htmltag($certCode); ?>" data-cert-signatory-id="<?php echo (int) $certSignatoryId; ?>">
        <div class="pp-cert-upload__label">
            <i class="fas fa-id-badge"></i> <span class="pp-cert-upload__name"><?php echo dol_escape_htmltag($certState['label']); ?></span>
            <?php if (!empty($certState['mandatory'])) { ?>
            <span class="pp-public-badge pp-cert-badge-mandatory"><?php echo $langs->trans('MobilePPMandatory'); ?></span>
            <span class="pp-public-badge pp-public-badge--muted pp-cert-badge-not-concerned"><?php echo $langs->trans('SpreadNotConcerned'); ?></span>
            <?php } ?>
            <?php if (!empty($certState['mandatory'])) { ?>
            <button type="button" class="pp-cert-not-concerned-btn<?php echo $notConcerned ? ' pp-cert-not-concerned-btn--active' : ''; ?>">
                <i class="fas <?php echo $notConcerned ? 'fa-undo' : 'fa-ban'; ?>"></i>
                <span><?php echo $notConcerned ? $langs->trans('SpreadIAmConcerned') : $langs->trans('SpreadIAmNotConcerned'); ?></span>
            </button>
            <?php } ?>
        </div>
        <div class="pp-cert-upload__row">
        <?php
        // Saturne media block only (upload buttons + photo editor + gallery), as documented in
        // saturne/admin/media.php. The page implements the action=uploadPhoto contract.
        echo saturne_render_media_block('digiriskdolibarr', $certSubDir, 'cert-' . ((int) $certSignatoryId) . '-' . $certCode, '', ['show_photo' => true, 'show_audio' => false]);
        ?>
        </div>
    </div>
    <?php } ?>
</div>
<?php }
