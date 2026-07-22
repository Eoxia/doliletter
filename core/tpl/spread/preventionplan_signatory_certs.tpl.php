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
 *          Expects: $langs, $objectRef, $certSignatoryId, $ppCertifications, $certificationOptions.
 */

if (!empty($ppCertifications)) { ?>
<div class="pp-cert-uploads">
    <?php foreach ($ppCertifications as $ppCertItem) {
        $certCode  = $ppCertItem['code'];
        $certLabel = $certificationOptions[$certCode] ?? $certCode;
        $certSubDir = 'preventionplan/' . dol_sanitizeFileName($objectRef) . '/certifications/' . ((int) $certSignatoryId) . '/' . dol_sanitizeFileName($certCode);
    ?>
    <div class="pp-cert-upload">
        <div class="pp-cert-upload__label">
            <i class="fas fa-id-badge"></i> <?php echo dol_escape_htmltag($certLabel); ?>
            <?php if (!empty($ppCertItem['mandatory'])) { ?><span class="pp-public-badge"><?php echo $langs->trans('MobilePPMandatory'); ?></span><?php } ?>
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
