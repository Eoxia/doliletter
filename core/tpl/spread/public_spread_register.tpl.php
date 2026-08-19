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
 * \file    core/tpl/spread/public_spread_register.tpl.php
 * \ingroup doliletter
 * \brief   Free registration form of the spread public page: a visitor joins the spread with their
 *          identity only, no login and no Dolibarr user needed.
 *          Expects: $langs.
 */
?>
<style>
.public-register { background: #fff; border: 1px solid #e5e5e5; border-radius: 8px; padding: 20px; margin: 20px 0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.public-register__title { display: flex; align-items: center; gap: 8px; margin: 0 0 8px; font-size: 16px; font-weight: 600; color: #333; }
.public-register__title i { color: #3b82f6; }
.public-register__intro { margin: 0 0 16px; font-size: 14px; line-height: 1.5; color: #666; }
.public-register__grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.public-register__field label { display: block; margin-bottom: 6px; font-size: 12px; font-weight: 500; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
.public-register__field input { width: 100%; padding: 10px 12px; font-family: inherit; font-size: 14px; background: #fff; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; }
.public-register__field input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.public-register__actions { margin-top: 16px; text-align: right; }

@media (max-width: 768px) {
    .public-register__grid { grid-template-columns: 1fr; }
    .public-register__actions .wpeo-button { width: 100%; justify-content: center; }
}
</style>

<div class="public-register">
    <h3 class="public-register__title">
        <i class="fas fa-user-plus"></i>
        <?php echo $langs->trans('SpreadPublicRegister'); ?>
    </h3>
    <p class="public-register__intro"><?php echo $langs->trans('SpreadPublicRegisterInfo'); ?></p>
      <?php if (!empty($isPreventionPlan) && !empty($ppCertifications) && is_array($ppCertifications)) {
          $tmpCertSignatoryId = '';
          // If Saturne is doing an AJAX refresh, it posts JSON containing the subdir
          if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
              $inputData = json_decode(file_get_contents('php://input'), true);
              if (is_array($inputData) && !empty($inputData['objectSubdir']) && preg_match('/\/certifications\/(tmp_[^\/]+)\//', $inputData['objectSubdir'], $matches)) {
                  $tmpCertSignatoryId = $matches[1];
              }
          }
          if (empty($tmpCertSignatoryId)) {
              $tmpCertSignatoryId = 'tmp_' . uniqid();
          }
          
          echo '<input type="hidden" id="public-register-tmp-id" value="' . dol_escape_htmltag($tmpCertSignatoryId) . '">';
        echo '<input type="hidden" id="public-register-not-concerned" value="{}">';
        
        if (!isset($ppCertificationStates)) {
            $ppCertificationStates = [];
        }
        $ppCertificationStates[$tmpCertSignatoryId] = doliletter_spread_get_certification_states(
            $ppCertifications,
            $certificationOptions ?? [],
            $ppCertBaseDir ?? '',
            $tmpCertSignatoryId,
            []
        );
        
        print '<div class="pp-signatory-media-row" style="margin-bottom: 20px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">';
        print '<div class="pp-signatory-media-row__label" style="font-size: 13px; font-weight: 600; color: #475569; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;"><i class="fas fa-id-badge"></i> ' . ($langs->transnoentitiesnoconv('SpreadRequestedElements') ?: 'Éléments demandés') . '</div>';
        
        $certSignatoryId = $tmpCertSignatoryId;
        require __DIR__ . '/preventionplan_signatory_certs.tpl.php';
        
        print '</div>';
    } ?>
    <div class="public-register__grid">
        <?php if ($confExtFirstnameVisible) { ?>
        <div class="public-register__field">
            <label for="public-register-firstname"><?php echo $langs->trans('Firstname') . ($confExtFirstnameMandatory ? ' *' : ''); ?></label>
            <input type="text" id="public-register-firstname" autocomplete="given-name" <?php echo $confExtFirstnameMandatory ? 'required' : ''; ?>>
        </div>
        <?php } ?>
        <?php if ($confExtLastnameVisible) { ?>
        <div class="public-register__field">
            <label for="public-register-lastname"><?php echo $langs->trans('Lastname') . ($confExtLastnameMandatory ? ' *' : ''); ?></label>
            <input type="text" id="public-register-lastname" autocomplete="family-name" <?php echo $confExtLastnameMandatory ? 'required' : ''; ?>>
        </div>
        <?php } ?>
        <?php if ($confExtEmailVisible) { ?>
        <div class="public-register__field">
            <label for="public-register-email"><?php echo $langs->trans('Email') . ($confExtEmailMandatory ? ' *' : ''); ?></label>
            <input type="email" id="public-register-email" autocomplete="email" <?php echo $confExtEmailMandatory ? 'required' : ''; ?>>
        </div>
        <?php } ?>
        <?php if ($confExtPhoneVisible) { ?>
        <div class="public-register__field">
            <label for="public-register-phone"><?php echo $langs->trans('Phone') . ($confExtPhoneMandatory ? ' *' : ''); ?></label>
            <input type="tel" id="public-register-phone" autocomplete="tel" <?php echo $confExtPhoneMandatory ? 'required' : ''; ?>>
        </div>
        <?php } ?>
    </div>
    <div class="public-register__actions">
        <button type="button" class="wpeo-button button-blue public-register-btn">
            <i class="fas fa-check"></i>
            <?php echo $langs->trans('SpreadPublicRegisterSubmit'); ?>
        </button>
    </div>
</div>
