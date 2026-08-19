<?php

// Spread config: external fields
$confExtFirstnameMandatory = getDolGlobalInt('DOLILETTER_SPREAD_EXT_FIELD_FIRSTNAME_MANDATORY');
$confExtFirstnameVisible = getDolGlobalInt('DOLILETTER_SPREAD_EXT_FIELD_FIRSTNAME_VISIBLE') || $confExtFirstnameMandatory || (getDolGlobalString('DOLILETTER_SPREAD_EXT_FIELD_FIRSTNAME_VISIBLE') === '');

$confExtLastnameMandatory = getDolGlobalInt('DOLILETTER_SPREAD_EXT_FIELD_LASTNAME_MANDATORY');
$confExtLastnameVisible = getDolGlobalInt('DOLILETTER_SPREAD_EXT_FIELD_LASTNAME_VISIBLE') || $confExtLastnameMandatory || (getDolGlobalString('DOLILETTER_SPREAD_EXT_FIELD_LASTNAME_VISIBLE') === '');

$confExtEmailMandatory = getDolGlobalInt('DOLILETTER_SPREAD_EXT_FIELD_EMAIL_MANDATORY');
$confExtEmailVisible = getDolGlobalInt('DOLILETTER_SPREAD_EXT_FIELD_EMAIL_VISIBLE') || $confExtEmailMandatory || (getDolGlobalString('DOLILETTER_SPREAD_EXT_FIELD_EMAIL_VISIBLE') === '');

$confExtPhoneMandatory = getDolGlobalInt('DOLILETTER_SPREAD_EXT_FIELD_PHONE_MANDATORY');
$confExtPhoneVisible = getDolGlobalInt('DOLILETTER_SPREAD_EXT_FIELD_PHONE_VISIBLE') || $confExtPhoneMandatory || (getDolGlobalString('DOLILETTER_SPREAD_EXT_FIELD_PHONE_VISIBLE') === '');

/* Copyright (C) 2021-2024 EVARISK <technique@evarisk.com>
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
 * \file    core/tpl/signature/public_signature_view.tpl.php
 * \ingroup saturne
 * \brief   Template page for public signature view
 */

/**
 * The following vars must be defined :
 * Global     : $conf, $langs
 * Parameters : $objectType
 * Objects    : $object, $$signatories
 * Variable   : $moduleNameLowerCase, $moreParams
 */

// Initialize Form object for user selection
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
$tmpUser = new User($db);
$form    = new Form($db);

// Logo de l'entreprise : la page arrive par un lien brut, sans rien qui dise de qui elle vient.
// Le wrapper public de Saturne sert l'image sans session, contrairement a viewimage.php natif.
$spreadLogoFile = '';
if (!empty($mysoc->logo_squarred_small)) {
    $spreadLogoFile = 'logos/thumbs/' . $mysoc->logo_squarred_small;
} elseif (!empty($mysoc->logo_squarred)) {
    $spreadLogoFile = 'logos/thumbs/' . $mysoc->logo_squarred;
} elseif (!empty($mysoc->logo_small)) {
    $spreadLogoFile = 'logos/thumbs/' . $mysoc->logo_small;
} elseif (!empty($mysoc->logo)) {
    $spreadLogoFile = 'logos/' . $mysoc->logo;
}
$spreadLogoUrl = dol_strlen($spreadLogoFile)
    ? DOL_URL_ROOT . '/custom/saturne/utils/viewimage.php?modulepart=mycompany&entity=' . $conf->entity . '&file=' . urlencode($spreadLogoFile)
    : '';

// Retours vers l'application, reserves aux personnes connectees : un visiteur anonyme n'aurait
// qu'un ecran de connexion au bout du lien
$spreadCardUrl   = '';
$spreadMobileUrl = '';
if ($isLogged && !empty($objectsMetadata[$objectType]['create_url'])) {
    // If it's a prevention plan and it's locked, don't allow editing
    $isLocked = (!empty($isPreventionPlan) && isset($ppObject) && $ppObject->status == PreventionPlan::STATUS_LOCKED);
    
    if (!$isLocked) {
        $spreadCardUrl = dol_buildpath(str_replace('?action=create', '', $objectsMetadata[$objectType]['create_url']), 1) . '?id=' . $id;

        if (!empty($isPreventionPlan)) {
            $spreadMobileUrl = dol_buildpath('/custom/digiriskdolibarr/view/preventionplan/preventionplan_mobile_create.php', 1) . '?id=' . $id;
        }
    }
}
?>

<style>
body {
    background: #f5f5f5;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.public-card__container {
    max-width: 900px;
    margin: 20px auto;
    padding: 0;
    background: transparent;
}

/* Saturne pose width:100% + padding:2em sur ce conteneur, sans box-sizing et avec une
   specificite superieure a la regle ci-dessus : sur mobile il deborde et rogne tout ce qu'il
   contient (photos des risques, champs du formulaire d'inscription) */
.page-public-card .public-card__container {
    box-sizing: border-box;
    overflow-x: hidden;
}

.public-card__header {
    background: transparent;
    margin-bottom: 16px;
    overflow: visible;
}

.public-card__content {
    padding: 0;
}

.object-title-section {
    padding: 20px 0px;
    background: transparent;
    border-bottom: 2px solid #e5e5e5;
    font-size: 18px;
    font-weight: 500;
    color: #333;
    border-radius: 8px 8px 0 0;
}

.public-note-section {
    background: transparent;
    border-radius: 8px;
}

.public-note-header h3 {
    margin: 0 0 12px 0;
    color: #333;
    font-size: 14px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.public-note-textarea {
    width: 100%;
    min-height: 60px;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    resize: vertical;
    font-family: inherit;
    font-size: 14px;
    box-sizing: border-box;
    background: white;
}

.public-note-textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.public-note-actions {
    margin-top: 12px;
    text-align: right;
}

.user-list-container {
    background: transparent;
}

.user-signature-item {
    background: transparent;
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    margin-bottom: 8px;
    padding: 16px;
    transition: all 0.2s ease;
}

.user-signature-item:hover {
    border-color: #d1d5db;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.user-signature-item.signature-validated {
    border-color: #10b981;
    border-width: 2px;
}

.user-signature-item.signature-not-validated {
    border-color: #9ca3af;
    border-width: 2px;
}

.form-element label {
    display: block;
    margin-bottom: 6px;
    font-size: 12px;
    font-weight: 500;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.input-with-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-status {
    flex: 1;
    min-width: 250px;
}

.user-status select,
.user-status .select2-container {
    width: 100% !important;
}

.signature-status {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 4px;
    min-width: 120px;
    width: 120px;
    text-align: center;
    justify-content: center;
}

.signature-status span:last-child {
    font-size: 11px;
    color: #666;
    line-height: 1.2;
    white-space: nowrap;
}

.signature-status i {
    font-size: 14px;
}

.signature-status .badge {
    margin-right: 2px;
}

.linked-files-section {
    background: transparent;
    margin: 16px 0;
}

.linked-files-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.file-box {
    display: flex;
    align-items: center;
    flex-direction: column;
    padding: 12px;
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    background: transparent;
    gap: 12px;
    transition: all 0.2s ease;
    width: 100%;
}

.file-box:hover {
    border-color: #d1d5db;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.file-icon {
    font-size: 20px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #666;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e5e5e5;
}

.file-content {
    display: flex;
    align-items: center;
    gap: 8px;
}

.file-actions-container {
    display: flex;
    align-items: center;
    gap: 8px;
}

.file-content .wpeo-button i {
    color: white !important;
}

.file-name {
    font-weight: 400;
    margin-bottom: 6px;
    font-size: 13px;
    color: #333;
    word-break: break-word;
    line-height: 1.3;
    text-align: center;
    width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 200px;
}

.modal-spread {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}

.modal-spread-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    border: 1px solid #e5e5e5;
}

.modal-spread-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e5e5e5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: transparent;
}

.modal-spread-header h3 {
    margin: 0;
    color: #333;
    font-size: 16px;
    font-weight: 500;
}

.close-modal-spread {
    cursor: pointer;
    font-size: 24px;
    font-weight: bold;
    color: #666;
    transition: color 0.2s ease;
    line-height: 1;
    padding: 4px;
    border-radius: 2px;
}

.close-modal-spread:hover {
    color: #ef4444;
}

.modal-spread-body {
    padding: 20px;
    background: white;
}

.signature-element {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
}

.canvas-signature {
    border: 2px dashed #d1d5db;
    border-radius: 6px;
    cursor: crosshair;
    background: white;
}

.signature-erase {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #ef4444 !important;
    border-color: #ef4444 !important;
    padding: 6px !important;
    border-radius: 4px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.signature-erase:active {
    transform: scale(0.95);
}

.signature-erase i,
.remove-user-btn i {
    color: white !important;
}

.modal-spread-footer {
    padding: 16px 20px;
    border-top: 1px solid #e5e5e5;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    background: transparent;
}

.modal-spread-footer .wpeo-button {
    font-size: 14px;
    padding: 8px 16px;
}

@media (max-width: 768px) {
    .public-card__container {
        margin: 10px;
        overflow-x: hidden;
    }

    .user-signature-item {
        padding: 8px;
    }

    .input-with-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 4px;
    }

    .user-status {
        flex: 1;
        min-width: 140px;
        max-width: calc(100% - 140px);
        order: 1;
    }

    .user-status select,
    .user-status .select2-container {
        width: 100% !important;
    }

    .input-with-actions .wpeo-button {
        order: 2;
        flex-shrink: 0;
        flex: 0 0 auto;
        min-width: 35px;
        padding: 8px !important;
        font-size: 12px;
    }

    .signature-status {
        order: 3;
        width: 100%;
        margin-top: 8px;
        display: flex !important;
        flex-direction: row !important;
        justify-content: flex-start !important;
        align-items: center !important;
        gap: 8px !important;
        min-width: auto !important;
        text-align: left !important;
    }
    
    .linked-files-grid {
        grid-template-columns: 1fr;
    }

    .file-box {
        flex-direction: row;
        width: 100%;
        max-width: 100%;
        justify-content: flex-start;
        align-items: center;
        gap: 12px;
        padding: 16px;
        box-sizing: border-box;
        overflow: hidden;
    }

    .file-name {
        flex: 1;
        margin-bottom: 0;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: calc(100% - 120px);
        margin-left: 0;
        margin-right: 0;
        order: 2;
        min-width: 0;
    }

    .file-content {
        flex-shrink: 0;
        order: 3;
        width: 40px;
    }

    .file-actions-container {
        display: contents;
    }

    .file-icon {
        flex-shrink: 0;
        order: 1;
        width: 40px;
        height: 40px;
    }
}

.login-message {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-top: 20px;
}

.login-message p {
    margin: 0;
    color: #6c757d;
    font-size: 16px;
}

.external-signatory {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.external-signatory__name {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.external-signatory__contact {
    font-size: 12px;
    color: #666;
}

.quick-sign {
    background: white;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.quick-sign h3 {
    margin: 0 0 12px 0;
    color: #333;
    font-size: 16px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

.quick-sign p {
    margin: 0 0 16px 0;
    color: #666;
    font-size: 14px;
    line-height: 1.5;
}

.quick-sign-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.quick-sign-email-group {
    display: flex;
    gap: 8px;
    align-items: flex-end;
}

.quick-sign-email-field {
    flex: 1;
}

.quick-sign-email-field label {
    display: block;
    margin-bottom: 6px;
    font-size: 12px;
    font-weight: 500;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.quick-sign-email-field input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-family: inherit;
    font-size: 14px;
    box-sizing: border-box;
    background: white;
}

.quick-sign-email-field input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.quick-sign-send-btn {
    flex-shrink: 0;
    min-width: 100px;
}

@media (max-width: 768px) {
    .quick-sign-email-group {
        flex-direction: column;
        align-items: stretch;
    }

    .quick-sign-send-btn {
        min-width: auto;
    }
}

/* En-tete : la page arrive par un lien brut, le logo et la raison sociale disent de qui elle vient */
.spread-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 14px;
    border-bottom: 1px solid #e5e5e5;
}

.spread-brand__logo {
    flex-shrink: 0;
    max-height: 44px;
    max-width: 140px;
    object-fit: contain;
}

.spread-brand__name {
    font-size: 15px;
    font-weight: 600;
    color: #334155;
}

.spread-brand__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-left: auto;
}

/* Boutons sans libelle : la cible tactile doit rester tenable au doigt, d'ou le carre de 38px */
.spread-back-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    width: 38px;
    height: 38px;
    font-size: 15px;
    color: #334155;
    text-decoration: none;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
}

.spread-back-link:hover {
    color: #0f172a;
    border-color: #94a3b8;
}

/* Qui d'autre a pris connaissance du document : c'est le sujet meme de la diffusion */
.spread-signatories {
    margin-top: 16px;
    padding: 16px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.spread-signatories__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e5e5;
}

.spread-signatories__title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: #334155;
}

.spread-signatories__title i {
    color: #3b82f6;
}

.spread-signatories__count {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
}

.spread-signatories__empty {
    padding-top: 12px;
    font-size: 13px;
    color: #64748b;
}

.spread-signatories__list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.spread-signatories__item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    font-size: 13px;
    color: #475569;
    border-bottom: 1px solid #f1f5f9;
}

.spread-signatories__item:last-child {
    border-bottom: none;
}

.spread-signatories__item i {
    flex-shrink: 0;
    color: #cbd5e1;
}

.spread-signatories__item--signed i {
    color: #16a34a;
}

.spread-signatories__name {
    flex: 1 1 auto;
    min-width: 0;
    font-weight: 600;
    color: #334155;
    overflow-wrap: anywhere;
}

.spread-signatories__status {
    flex-shrink: 0;
    font-size: 12px;
    color: #64748b;
}

/* Deux carres de 38px tiennent a cote du logo meme sur un telephone : plus besoin de les passer
   sur une ligne a eux comme le faisaient les boutons avec libelle */
</style>

<?php
$isSignedPreventionPlan = (!empty($isPreventionPlan) && !empty($signSignatory) && $signSignatory->status == DoliletterSpreadSignature::STATUS_SIGNED);
?>
<div class="public-card__container" data-public-interface="true">

    <div class="public-card__header">
        <div class="public-card__content">
            <div class="spread-brand">
                <?php if (dol_strlen($spreadLogoUrl)) { ?>
                <img class="spread-brand__logo" src="<?php echo $spreadLogoUrl; ?>" alt="<?php echo dol_escape_htmltag($mysoc->name); ?>">
                <?php } ?>
                <span class="spread-brand__name"><?php echo dol_escape_htmltag($mysoc->name); ?></span>
                
                <span style="flex-grow: 1; text-align: center; font-weight: bold; font-size: 1.2em; color: #4a55d1; text-transform: uppercase;">
                    <?php echo $langs->transnoentities($objectsMetadata[$objectType]['langs'] ?? ucfirst($objectsMetadata[$objectType]['object']->element)); ?>
                </span>

                <?php if (dol_strlen($spreadCardUrl) || dol_strlen($spreadMobileUrl)) { ?>
                <!-- Deux icones sans texte : le crayon pour modifier le plan, la fleche pour
                     revenir dans Dolibarr. Sans libelle visible, l'intitule doit rester porte par
                     title et aria-label, sinon le bouton ne dit plus rien au survol ni au lecteur
                     d'ecran -->
                <div class="spread-brand__actions">
                    <?php if (dol_strlen($spreadMobileUrl)) {
                        $spreadEditLabel = $langs->trans('SpreadEditPlan');
                    ?>
                    <a class="spread-back-link" href="<?php echo $spreadMobileUrl; ?>" title="<?php echo dol_escape_htmltag($spreadEditLabel); ?>" aria-label="<?php echo dol_escape_htmltag($spreadEditLabel); ?>">
                        <i class="fas fa-edit"></i>
                    </a>
                    <?php } ?>
                    <?php if (dol_strlen($spreadCardUrl)) {
                        $spreadBackLabel = $langs->trans('SpreadBackToDolibarr');
                    ?>
                    <a class="spread-back-link" href="<?php echo $spreadCardUrl; ?>" title="<?php echo dol_escape_htmltag($spreadBackLabel); ?>" aria-label="<?php echo dol_escape_htmltag($spreadBackLabel); ?>">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>

            <div class="object-title-section">
                <?php
                // Un visiteur anonyme n'a rien a faire d'un lien vers la fiche : il n'y accede pas
                echo ($isLogged ? $objectsMetadata[$objectType]['object']->getNomUrl(1) : dol_escape_htmltag($objectRef)) . (!empty($objectLabel) ? ' - ' . dol_escape_htmltag($objectLabel) : '');
                ?>
            </div>

            <?php
            // Documents mis en avant : affiches en premier, avant le detail de l'objet, pour que
            // la personne diffusee tombe dessus sans avoir a faire defiler la page
            if (!empty($linkedFilesFavorite)) {
                foreach ($linkedFilesFavorite as $key => $file) {
                    if (dol_mimetype($file->filename) != 'video/mp4') {
                        // L'URL d'un <object> se donne dans data : avec data-src le navigateur
                        // n'appelle jamais le document et le cadre reste vide.
                        // attachment=0 : sans lui document.php repond Content-Disposition: attachment
                        // et le navigateur telecharge le fichier au lieu de l'afficher dans le cadre.
                        $filePreviewUrl = DOL_URL_ROOT . '/document.php?hashp=' . urlencode($file->share) . '&attachment=0';
                    ?>
                    <object
                        name="objectpreview"
                        type="<?php echo dol_mimetype($file->filename); ?>"
                        width="100%"
                        height="600px"
                        data="<?php echo $filePreviewUrl; ?>">
                        <a href="<?php echo $filePreviewUrl; ?>" target="_blank"><?php echo dol_escape_htmltag($file->filename); ?></a>
                    </object>
                    <?php } else { ?>
                        <video src="<?php echo DOL_URL_ROOT . '/document.php?hashp=' . urlencode($file->share); ?>" controls width="100%" height="600px">
                            Your browser does not support the video tag.
                        </video>
                    <?php } ?>
            <?php }} ?>

            <?php if ($isSignedPreventionPlan) { ?>
        <!-- SUCCESS SCREEN -->
    <div style="position: relative; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); padding: 50px 40px; text-align: center; margin-top: 40px; border: 1px solid #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
        <a href="javascript:void(0)" onclick="window.scrollTo({top:0, behavior:'smooth'})" style="position: absolute; top: 20px; right: 25px; color: #64748b; font-size: 20px; text-decoration: none;"><i class="fas fa-times"></i></a>
        
        <!-- Animated check icon -->
        <div style="margin-bottom: 25px;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 100px; height: 100px; background-color: #84cc16; border-radius: 50%; color: white; font-size: 50px; position: relative; box-shadow: 0 4px 15px rgba(132, 204, 22, 0.3);">
                <i class="fas fa-check"></i>
                <div style="position: absolute; top: 10px; left: -30px; width: 15px; height: 3px; background: #84cc16; border-radius: 2px; transform: rotate(15deg);"></div>
                <div style="position: absolute; top: -10px; left: -15px; width: 15px; height: 3px; background: #84cc16; border-radius: 2px; transform: rotate(-35deg);"></div>
                <div style="position: absolute; top: 10px; right: -30px; width: 15px; height: 3px; background: #84cc16; border-radius: 2px; transform: rotate(-15deg);"></div>
                <div style="position: absolute; top: -10px; right: -15px; width: 15px; height: 3px; background: #84cc16; border-radius: 2px; transform: rotate(35deg);"></div>
            </div>
        </div>
        
        <h1 style="font-size: 32px; color: #0f172a; margin-bottom: 12px; font-weight: 700;">Plan de prévention signé !</h1>
        <p style="font-size: 16px; color: #475569; margin-bottom: 40px; max-width: 650px; margin-left: auto; margin-right: auto; line-height: 1.6;">
            L'intervenant a signé le plan de prévention en validant l'analyse des risques et en ayant transmis tous les éléments demandés.
        </p>

        <!-- Stats row -->
        <div style="display: flex; flex-wrap: wrap; justify-content: center; border: 1px solid #f1f5f9; border-radius: 12px; margin-bottom: 40px; padding: 25px 0; background: #fdfdfd; box-shadow: 0 2px 10px rgba(0,0,0,0.01);">
            <div style="flex: 1; min-width: 150px; border-right: 1px solid #e2e8f0; padding: 0 15px;">
                <div style="width: 48px; height: 48px; background: #f0fdf4; border-radius: 50%; color: #166534; font-size: 20px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div style="font-size: 11px; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Statut</div>
                <div style="display: inline-block; background: #dcfce7; color: #16a34a; padding: 6px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;"><i class="fas fa-check" style="margin-right:6px;"></i>Signé</div>
            </div>

            <div style="flex: 1; min-width: 150px; border-right: 1px solid #e2e8f0; padding: 0 15px;">
                <div style="width: 48px; height: 48px; background: #f0fdf4; border-radius: 50%; color: #166534; font-size: 20px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                    <i class="fas fa-user"></i>
                </div>
                <div style="font-size: 11px; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Intervenant</div>
                <div style="font-size: 15px; color: #0f172a; font-weight: 600;"><?php echo dol_escape_htmltag($signSignatory->firstname . ' ' . $signSignatory->lastname); ?></div>
            </div>

            <div style="flex: 1; min-width: 150px; border-right: 1px solid #e2e8f0; padding: 0 15px;">
                <div style="width: 48px; height: 48px; background: #f0fdf4; border-radius: 50%; color: #166534; font-size: 20px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                    <i class="far fa-calendar-alt"></i>
                </div>
                <div style="font-size: 11px; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Date de signature</div>
                <div style="font-size: 15px; color: #0f172a; font-weight: 600;"><?php echo dol_print_date($signSignatory->signature_date, '%d/%m/%Y à %H:%M'); ?></div>
            </div>

            <div style="flex: 1; min-width: 150px; padding: 0 15px;">
                <div style="width: 48px; height: 48px; background: #f0fdf4; border-radius: 50%; color: #166534; font-size: 20px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div style="font-size: 11px; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Référence</div>
                <div style="font-size: 15px; color: #0f172a; font-weight: 600;"><?php echo dol_escape_htmltag($objectRef); ?></div>
            </div>
        </div>

        <!-- Etapes suivantes -->
        <div style="text-align: left; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 40px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 700; color: #3b82f6; margin-bottom: 25px; display: flex; align-items: center; letter-spacing: 0.5px;">
                <div style="width: 20px; height: 20px; background: #3b82f6; color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 11px;">
                    <i class="fas fa-info"></i>
                </div>
                ETAPES SUIVANTES
            </div>
            
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 15px; flex: 1; min-width: 160px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #dbeafe; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;"><i class="fas fa-user-friends"></i></div>
                    <div style="font-size: 13px; color: #0f172a; font-weight: 500; line-height: 1.4;">Informer les autres<br>intervenants</div>
                </div>
                <div style="color: #cbd5e1; font-size: 14px;"><i class="fas fa-arrow-right"></i></div>
                
                <div style="display: flex; align-items: center; gap: 15px; flex: 1; min-width: 160px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #dbeafe; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;"><i class="far fa-file-alt"></i></div>
                    <div style="font-size: 13px; color: #0f172a; font-weight: 500; line-height: 1.4;">Consulter et suivre<br>le plan de prévention</div>
                </div>
                <div style="color: #cbd5e1; font-size: 14px;"><i class="fas fa-arrow-right"></i></div>
                
                <div style="display: flex; align-items: center; gap: 15px; flex: 1; min-width: 160px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #dbeafe; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;"><i class="fas fa-shield-alt"></i></div>
                    <div style="font-size: 13px; color: #0f172a; font-weight: 500; line-height: 1.4;">Appliquer les mesures<br>de prévention</div>
                </div>
                <div style="color: #cbd5e1; font-size: 14px;"><i class="fas fa-arrow-right"></i></div>
                
                <div style="display: flex; align-items: center; gap: 15px; flex: 1; min-width: 160px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #dbeafe; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;"><i class="fas fa-clipboard-list"></i></div>
                    <div style="font-size: 13px; color: #0f172a; font-weight: 500; line-height: 1.4;">Réaliser et suivre<br>les actions</div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
            <a href="javascript:void(0)" onclick="window.scrollTo({top:0, behavior:'smooth'})" style="font-size: 15px; padding: 12px 24px; border: 1px solid #cbd5e1; color: #3b82f6; background: white; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; font-weight: 600; cursor: pointer;">
                <i class="far fa-file-alt" style="margin-right: 8px;"></i> Voir le plan de prévention
            </a>
            <?php $registerUrl = dol_buildpath('/doliletter/public/spread/add_spread.php', 1) . '?id=' . $id . '&object_type=' . $objectType; ?>
            <a href="<?php echo $registerUrl; ?>" style="font-size: 15px; padding: 12px 24px; background: #3b82f6; border: none; color: white; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);">
                <i class="fas fa-user-plus" style="margin-right: 8px;"></i> Ajouter un nouvel intervenant
            </a>
        </div>
    </div>
<?php } else { ?>
            <?php if (!empty($isPreventionPlan)) {
                require __DIR__ . '/preventionplan_public_info.tpl.php';
            } ?>

 
            <?php if (!empty($permissiontoadd) && empty($sign)) { ?>
            <div class="user-list-container">
                <div class="add-user-section tabsAction" style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" class="wpeo-button button-blue add-user-btn" data-type="internal">
                        <i class="fas fa-plus"></i> <?php echo $langs->trans('Signataire interne'); ?>
                    </button>
                    <button type="button" class="wpeo-button button-blue add-user-btn" data-type="external">
                        <i class="fas fa-plus"></i> <?php echo $langs->trans('Signataire externe'); ?>
                    </button>
                    <button type="button" class="wpeo-button button-blue copy-link-btn" style="padding: 8px 12px; font-size: 0.9em;" title="<?php echo dol_escape_htmltag($langs->trans('CopyLink')); ?>" onclick="navigator.clipboard.writeText(window.location.href).then(function() { $.jnotify('<?php echo dol_escape_js($langs->trans('LinkCopiedToClipboard')); ?>', 'success'); });">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>

                <div class="user-signatures-list" id="userSignaturesList">
                    <!-- Utilisateurs pré-signés par défaut -->


                    <?php

                    // If there are already added signatories, display them
                    $signatories = array_reverse($signatories, true);
                    foreach ($signatories as $index => $signatoryItem) {
                        // A signatory registered from the public page carries their own identity, no Dolibarr user behind it
                        $isExternalSignatory = ($signatoryItem->element_type == DOLILETTER_SPREAD_EXTERNAL_ELEMENT_TYPE);
                        $isSignatoryReady = false;
                        if ($isExternalSignatory) {
                            $isSignatoryReady = true;
                            if ($confExtFirstnameMandatory && trim($signatoryItem->first_name) === '') $isSignatoryReady = false;
                            if ($confExtLastnameMandatory && trim($signatoryItem->last_name) === '') $isSignatoryReady = false;
                            if ($confExtEmailMandatory && trim($signatoryItem->email) === '') $isSignatoryReady = false;
                            if ($confExtPhoneMandatory && trim($signatoryItem->phone) === '') $isSignatoryReady = false;
                        } else {
                            $isSignatoryReady = (!empty($signatoryItem->element_id) && $signatoryItem->element_id != -1);
                        }
                        if (empty($signatoryItem->signature)) {
                        ?>
                        <div class="user-signature-item signature-not-validated" data-user-index="<?php echo $signatoryItem->id; ?>">
                            <div class="user-info">
                                <div class="form-row">
                                    <div class="form-element">
                                        <label for="attendant_user"><?php echo $langs->trans('User'); ?></label>
                                        <div class="input-with-actions">
                                            <div class="user-status">
                                                <?php if ($isExternalSignatory) { ?>
                                                    <div class="external-signatory">
                                                        <?php if (empty($permissiontoadd)) { ?>
                                                            <span class="external-signatory__name"><?php echo dol_escape_htmltag(doliletter_spread_get_signatory_name($signatoryItem)); ?></span>
                                                            <span class="external-signatory__contact"><?php echo dol_escape_htmltag($signatoryItem->email); ?><?php echo dol_strlen($signatoryItem->phone) ? ' - ' . dol_escape_htmltag($signatoryItem->phone) : ''; ?></span>
                                                        <?php } else { ?>
                                                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                                                <div style="display: flex; gap: 8px;">
                                                                    <?php if ($confExtFirstnameVisible) { ?>
                                                                    <input type="text" class="external-signatory-input" data-field="first_name" <?php echo $confExtFirstnameMandatory ? 'required data-mandatory="1"' : ''; ?> value="<?php echo dol_escape_htmltag($signatoryItem->first_name); ?>" placeholder="<?php echo $langs->trans('Firstname') . ($confExtFirstnameMandatory ? ' *' : ''); ?>" style="width: 50%;">
                                                                    <?php } ?>
                                                                    <?php if ($confExtLastnameVisible) { ?>
                                                                    <input type="text" class="external-signatory-input" data-field="last_name" <?php echo $confExtLastnameMandatory ? 'required data-mandatory="1"' : ''; ?> value="<?php echo dol_escape_htmltag($signatoryItem->last_name); ?>" placeholder="<?php echo $langs->trans('Lastname') . ($confExtLastnameMandatory ? ' *' : ''); ?>" style="width: 50%;">
                                                                    <?php } ?>
                                                                </div>
                                                                <div style="display: flex; gap: 8px;">
                                                                    <?php if ($confExtEmailVisible) { ?>
                                                                    <input type="email" class="external-signatory-input" data-field="email" <?php echo $confExtEmailMandatory ? 'required data-mandatory="1"' : ''; ?> value="<?php echo dol_escape_htmltag($signatoryItem->email); ?>" placeholder="<?php echo $langs->trans('Email') . ($confExtEmailMandatory ? ' *' : ''); ?>" style="width: 50%;">
                                                                    <?php } ?>
                                                                    <?php if ($confExtPhoneVisible) { ?>
                                                                    <input type="text" class="external-signatory-input" data-field="phone" <?php echo $confExtPhoneMandatory ? 'required data-mandatory="1"' : ''; ?> value="<?php echo dol_escape_htmltag($signatoryItem->phone); ?>" placeholder="<?php echo $langs->trans('Phone') . ($confExtPhoneMandatory ? ' *' : ''); ?>" style="width: 50%;">
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                <?php } else {
                                                    print $form->select_dolusers(empty($signatoryItem->element_id) ? -1 : $signatoryItem->element_id, 'attendant_user_' . $signatoryItem->id, 1, [], 0, '', '', $conf->entity, 0, 0, '', 0, '', 'minwidth150 widthcentpercentminusx user-select-small', 1);
                                                } ?>
                                            </div>
                                            <div class="signature-status">
                                                <span class="badge badge-dot badge-status<?php echo $isSignatoryReady ? '1' : '0' ?> badge-status"></span>
                                                <i class="fas fa-signature"></i>
                                                <span>jj/mm/aaaa --:--</span>
                                            </div>
                                            <button type="button" class="wpeo-button button-<?php echo $isSignatoryReady ? 'primary' : 'disable' ?> sign-btn">
                                                <i class="fas fa-signature"></i>
                                            </button>
                                            <?php if (!empty($permissiontoadd)) { ?>
                                            <button type="button" class="wpeo-button button-<?php echo $isSignatoryReady ? 'primary' : 'disable' ?> send-email-btn">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                            <button type="button" class="wpeo-button button-red remove-user-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php
                    } else {
                    ?>
                        <div class="user-signature-item signature-validated" data-user-index="<?php echo $signatoryItem->id; ?>">
                            <div class="user-info">
                                <div class="form-row">
                                    <div class="form-element">
                                        <div class="input-with-actions">
                                            <div class="user-status">
                                                <?php if ($isExternalSignatory) { ?>
                                                    <div class="external-signatory">
                                                        <span class="external-signatory__name"><?php echo dol_escape_htmltag(doliletter_spread_get_signatory_name($signatoryItem)); ?></span>
                                                        <span class="external-signatory__contact"><?php echo dol_escape_htmltag($signatoryItem->email); ?><?php echo dol_strlen($signatoryItem->phone) ? ' - ' . dol_escape_htmltag($signatoryItem->phone) : ''; ?></span>
                                                    </div>
                                                <?php } else {
                                                    $tmpUser->fetch($signatoryItem->element_id);
                                                    echo $tmpUser->getNomUrl(1);
                                                } ?>
                                            </div>
                                            <div class="signature-status">
                                                <span class="badge badge-dot badge-status4 badge-status"></span>
                                                <i class="fas fa-signature"></i>
                                                <span><?php echo dol_print_date($signatoryItem->signature_date, '%d/%m/%Y %H:%M') ?></span>
                                            </div>
                                            <?php if ($permissiontoadd) { ?>
                                                <?php if (!empty($permissiontoshowsignature) && getDolGlobalInt('DOLILETTER_SPREAD_SHOW_SIGNATURE')) { ?>
                                                <a href="<?php echo DOL_URL_ROOT . '/custom/saturne/public/signature/add_signature.php?track_id=' . $signatoryItem->signature_url . '&entity=1&module_name=doliletter&object_type=doliletterattendancesheet'; ?>"
                                                    target="_blank" class="wpeo-button">
                                                    <i class="fas fa-eye" style="color:white"></i>
                                                </a>
                                                <?php } ?>
                                                <button type="button" class="wpeo-button button-disable send-email-btn" disabled>
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                                <button type="button" class="wpeo-button button-red remove-user-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php
                    }

                    // Prevention plan: one photo per required certification (document) for this signatory
                    if (!empty($isPreventionPlan) && !empty($ppCertifications)) {
                        print '<div class="pp-signatory-media-row" style="border-top: 1px dashed #e5e5e5; margin-top: 10px; padding-top: 10px;">';
                        print '<div class="pp-signatory-media-row__label"><i class="fas fa-id-badge"></i> Envoyer les éléments demandés</div>';
                        $certSignatoryId = $signatoryItem->id;
                        require __DIR__ . '/preventionplan_signatory_certs.tpl.php';
                        print '</div>';
                    }
                    ?>
                        </div> <!-- close user-signature-item -->
                    <?php
                    }
                    ?>

                </div>

                
            </div>
            <?php } ?>
        </div>
    </div>

            <?php if (!empty($signSignatory)) { ?>
            <!-- Single-person view: only this signatory's signature + their certification photos -->
            <div class="pp-single-person">
                <div class="user-signature-item <?php echo empty($signSignatory->signature) ? 'signature-not-validated' : 'signature-validated'; ?>" data-user-index="<?php echo $signSignatory->id; ?>">
                    <div class="user-info">
                        <div class="form-element">
                            <div class="input-with-actions">
                                <div class="user-status"><?php echo dol_escape_htmltag(trim($signSignatory->firstname . ' ' . $signSignatory->lastname)); ?></div>
                                <div class="signature-status">
                                    <?php if (empty($signSignatory->signature)) { ?>
                                        <span class="badge badge-dot badge-status1 badge-status"></span>
                                        <i class="fas fa-signature"></i>
                                        <span>jj/mm/aaaa --:--</span>
                                    <?php } else { ?>
                                        <span class="badge badge-dot badge-status4 badge-status"></span>
                                        <i class="fas fa-signature"></i>
                                        <span><?php echo dol_print_date($signSignatory->signature_date, '%d/%m/%Y %H:%M'); ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($isPreventionPlan) && !empty($ppCertifications)) {
                    $certSignatoryId = $signSignatory->id;
                    require __DIR__ . '/preventionplan_signatory_certs.tpl.php';
                } ?>

                <?php if (empty($signSignatory->signature) && !empty($ppRisks)) { ?>
                <!-- Every risk has to be acknowledged before signing. The count is kept up to date
                     client side as the visitor validates each risk block. -->
                <div class="pp-mandatory-pending pp-risks-pending <?php echo empty($ppPendingRisks) ? 'hidden' : ''; ?>">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <?php echo $langs->trans('SpreadRisksPending'); ?>
                        <strong class="pp-risks-pending__count"><?php echo count($ppPendingRisks); ?></strong>
                    </div>
                </div>
                <?php } ?>

                <?php if (empty($signSignatory->signature) && !empty($ppPendingCertifications)) { ?>
                <!-- Mandatory documents block signing until they are uploaded or waived -->
                <div class="pp-mandatory-pending">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <?php echo $langs->trans('SpreadMandatoryCertificationsPending'); ?>
                        <ul class="pp-mandatory-pending__list">
                            <?php foreach ($ppPendingCertifications as $pendingCertification) { ?>
                            <li data-cert-code="<?php echo dol_escape_htmltag($pendingCertification['code']); ?>"><?php echo dol_escape_htmltag($pendingCertification['label']); ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <?php } ?>

                <?php if (empty($signSignatory->signature)) { ?>
                <!-- Inline signature panel: no modal, the person signs directly on the page -->
                <div class="pp-inline-signature">
                    <div class="pp-inline-signature__title"><i class="fas fa-signature"></i> <?php echo $langs->trans('Signature'); ?></div>
                    <div class="signature-element">
                        <canvas id="signatureCanvas" class="canvas-container editable canvas-signature pp-inline-canvas" width="600" height="200"></canvas>
                        <div class="signature-erase wpeo-button button-square-40 button-rounded button-red">
                            <span><i class="fas fa-eraser"></i></span>
                        </div>
                    </div>
                    <div class="pp-inline-signature__actions">
                        <button type="button" class="wpeo-button button-disable validate-sign-btn" disabled>
                            <i class="fas fa-check"></i> <?php echo $langs->trans('ValidateSignature'); ?>
                        </button>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>

            <?php if (!empty($linkedLinksFavorite)) { ?>
                <div class="linked-links-section">
                    <?php foreach ($linkedLinksFavorite as $link) { 
                        if (strpos($link->url, "youtube.com/watch?v=") !== false) { ?>
                            <?php
                            $videoId = preg_replace('/.*v=([^&]+).*/', '$1', $link->url);
                            ?>
                            <iframe width="100%" height="600px" src="https://www.youtube.com/embed/<?php echo htmlspecialchars($videoId); ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                        <?php } else { ?>
                            <object
                                name="objectpreview"
                                data="<?php echo htmlspecialchars($link->url); ?>"
                                type="<?php echo dol_mimetype($link->url); ?>"
                                width="100%"
                                height="600px"
                                param="noparam">
                            </object>
                        <?php } ?>
                    <?php } ?>
                </div>
            <?php } ?>

            <!-- Linked Files Section -->
            <?php if (!empty($linkedFiles)) { ?>
            <div class="linked-files-section">
                <div class="linked-files-grid">
                    <?php foreach ($linkedFiles as $file) { 
                        $downloadUrl = DOL_URL_ROOT . '/document.php?hashp=' . urlencode($file->share);
                        $fileExtension = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));
                        $iconClass = getFileIcon($fileExtension);
                    ?>
                    <div class="file-box">
                        <div class="file-name" title="<?php echo htmlspecialchars($file->filename); ?>">
                            <?php echo htmlspecialchars($file->filename); ?>
                        </div>
                        <div class="file-actions-container">
                            <div class="file-icon">
                                <i class="<?php echo $iconClass; ?>"></i>
                            </div>
                            <div class="file-content">
                                <a href="<?php echo $downloadUrl; ?>" target="_blank" class="wpeo-button">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
            <?php if (!empty($linkedLinks)) { ?>
                <div class="linked-files-section">
                    <div class="linked-files-grid">
                        <?php foreach ($linkedLinks as $link) { ?>
                        <div class="file-box">
                            <div class="file-name" title="<?php echo htmlspecialchars($link->label); ?>">
                                <?php echo htmlspecialchars($link->label); ?>
                            </div>
                            <div class="file-actions-container">
                                <div class="file-icon">
                                    <i class="fas fa-link"></i>
                                </div>
                                <div class="file-content">
                                    <a href="<?php echo htmlspecialchars($link->url); ?>" target="_blank" class="wpeo-button">
                                        <i class="fas fa-external-link-alt" style="text-decoration: none;"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>

            <?php if (!empty($sign) && empty($signSignatory)) { ?>
            <!-- A sign token was provided but does not resolve: never fall back to the global list -->
            <div class="pp-invalid-link">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?php echo $langs->trans('ErrorInvalidSignatureLink'); ?></span>
            </div>
            <?php } ?>

                <?php
    $hidePublicNote = getDolGlobalInt('DIGIRISKDOLIBARR_SPREAD_HIDE_PUBLIC_NOTE', 1);
    if (!empty($isLogged) && empty($hidePublicNote)) { ?>
    <!-- Public Note Section moved to bottom -->
    <div class="public-note-section">
        <div class="public-note-header">
            <i class="fas fa-comment-dots"></i>
        </div>
        <div class="public-note-content">
            <textarea class="public-note-textarea" placeholder="<?php echo $langs->trans('EnterNotePublicHere'); ?>"><?php echo $attendanceSheet->note_public ?? ''; ?></textarea>
            <div class="public-note-actions tabsAction">
                <button type="button" class="wpeo-button button-grey save-public-note-btn">
                    <i class="fas fa-save"></i>
                </button>
            </div>
        </div>
    </div>
    <?php } ?>



    <?php if (!$isLogged) { ?>

        <?php if (!empty($publicRegisterEnabled) && empty($sign)) {
            require __DIR__ . '/public_spread_register.tpl.php';
        } ?>

        <?php if (getDolGlobalInt('DOLILETTER_SPREAD_QUICK_SIGN') && empty($sign)) { ?>

        <div class="quick-sign">
            <h3>
                <i class="fas fa-paper-plane"></i>
                <?php echo $langs->trans('QuickSignature'); ?>
            </h3>
            <p><?php echo $langs->trans('SpreadQuickSignatureInfo') ?></p>
            <div class="quick-sign-form">
                <div class="quick-sign-email-group">
                    <div class="quick-sign-email-field">
                        <label for="quick-sign-email"><?php echo $langs->trans('Email'); ?></label>
                        <input type="email" id="quick-sign-email" class="quick-sign-email-input" placeholder="votre.email@exemple.com" required>
                    </div>
                    <button type="button" class="wpeo-button button-blue quick-sign-send-btn">
                        <i class="fas fa-paper-plane"></i>
                        <?php echo $langs->trans('Send'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php } ?>


    <?php } ?>

    <?php
    // Signed list configuration
    $showCount   = getDolGlobalInt('DIGIRISKDOLIBARR_SPREAD_SHOW_SIGNATURE_COUNT', 1);
    $showName    = getDolGlobalInt('DIGIRISKDOLIBARR_SPREAD_SHOW_SIGNATORY_NAME', 1);
    $showContact = getDolGlobalInt('DIGIRISKDOLIBARR_SPREAD_SHOW_SIGNATORY_CONTACT', 0);

    // Une personne diffusee doit pouvoir voir qui d'autre a pris connaissance du document. La liste
    // modifiable ci-dessus est reservee aux gestionnaires : celle-ci est en lecture seule.
    // L'affichage est conditionn� par les param�tres ou s'il s'agit d'un visiteur public.
    if ((!$isLogged && !empty($signSignatory)) || $showCount || $showName || $showContact) {
        require __DIR__ . '/public_spread_signatories.tpl.php';
    }
    ?>
</div>

<?php
function getFileIcon($extension) {
    $icons = [
        'pdf' => 'fas fa-file-pdf text-danger',
        'doc' => 'fas fa-file-word text-primary',
        'docx' => 'fas fa-file-word text-primary',
        'xls' => 'fas fa-file-excel text-success',
        'xlsx' => 'fas fa-file-excel text-success',
        'ppt' => 'fas fa-file-powerpoint text-warning',
        'pptx' => 'fas fa-file-powerpoint text-warning',
        'txt' => 'fas fa-file-alt text-secondary',
        'jpg' => 'fas fa-file-image text-info',
        'jpeg' => 'fas fa-file-image text-info',
        'png' => 'fas fa-file-image text-info',
        'gif' => 'fas fa-file-image text-info',
        'zip' => 'fas fa-file-archive text-dark',
        'rar' => 'fas fa-file-archive text-dark',
        '7z' => 'fas fa-file-archive text-dark',
    ];
    return isset($icons[$extension]) ? $icons[$extension] : 'fas fa-file text-muted';
}
?>

<!-- Modal de signature (not rendered when a sign token is used: the signature panel is inline there) -->
<?php if (empty($sign)) { ?>
<div id="signatureModal" class="modal-spread">
    <div class="modal-spread-content">
        <div class="modal-spread-header">
            <h3><?php echo $langs->trans('Signature'); ?></h3>
            <span class="close-modal-spread">&times;</span>
        </div>
        <div class="modal-spread-body">
            <div class="signature-element">
                <canvas id="signatureCanvas" class="canvas-container editable canvas-signature modal-canvas-signature" width="600" height="200" style="touch-action: none;"></canvas>
                <div class="signature-erase wpeo-button button-square-40 button-rounded button-red">
                    <span><i class="fas fa-eraser"></i></span>
                </div>
            </div>
        </div>
        <div class="modal-spread-footer">
            <button type="button" class="wpeo-button button-grey cancel-signature-btn">
                <?php echo $langs->trans('Cancel'); ?>
            </button>
            <button type="button" class="wpeo-button button-disable validate-sign-btn" disabled>
                <i class="fas fa-check"></i> <?php echo $langs->trans('ValidateSignature'); ?>
            </button>
        </div>
    </div>
</div>
<?php } ?>

<?php } ?>


<script>
let currentUserIndex = null;

function openSignatureModal(userIndex = null) {
    if (typeof userIndex == 'object') {
        userIndex   = $(this).parents('.user-signature-item').eq(0).data('user-index');
    }

    currentUserIndex = userIndex;
    const modal = document.getElementById('signatureModal');
    modal.style.display = 'block';

    // Attendre que le modal soit visible pour calculer les bonnes dimensions
    setTimeout(() => {
        const modalBody = modal.querySelector('.modal-spread-body');
        const containerWidth = modalBody.clientWidth - 40; // 40px pour le padding
        const canvasWidth = Math.min(containerWidth, 600);
        const canvasHeight = 200;

        window.saturne.signature.canvas.width = canvasWidth;
        window.saturne.signature.canvas.height = canvasHeight;

        // Centrer le canvas
        const canvas = document.getElementById('signatureCanvas');
        canvas.style.width = canvasWidth + 'px';
        canvas.style.height = canvasHeight + 'px';
    }, 100);

    // Add event listeners to monitor canvas changes
    const canvas = document.getElementById('signatureCanvas');
    if (canvas) {
        canvas.addEventListener('mouseup', updateValidateButtonState);
        canvas.addEventListener('touchend', updateValidateButtonState);
    }

    // Initial button state
    updateValidateButtonState();
}

function closeSignatureModal() {
    const modal = document.getElementById('signatureModal');
    if (modal) {
        modal.style.display = 'none';
    }
    currentUserIndex = null;

    // Clear the canvas when closing
    clearSignature();
}

function isCanvasEmpty() {
    const canvas = document.getElementById('signatureCanvas');
    if (!canvas) return true;

    const context = canvas.getContext('2d');
    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);

    // Check if all pixels are transparent (alpha = 0) or white
    for (let i = 0; i < imageData.data.length; i += 4) {
        // Check alpha channel (transparency)
        if (imageData.data[i + 3] !== 0) {
            // Check if it's not white (RGB = 255,255,255)
            if (!(imageData.data[i] === 255 && imageData.data[i + 1] === 255 && imageData.data[i + 2] === 255)) {
                return false;
            }
        }
    }
    return true;
}

function updateValidateButtonState() {
    const validateBtn = $('.validate-sign-btn');
    if (validateBtn) {
        const isEmpty = isCanvasEmpty();
        validateBtn.prop('disabled', isEmpty);
        if (isEmpty) {
            validateBtn.addClass('button-disable');
        } else {
            validateBtn.removeClass('button-disable');
        }
    }
}

function clearSignature() {
    const canvas = document.getElementById('signatureCanvas');
    if (canvas) {
        const context = canvas.getContext('2d');
        context.clearRect(0, 0, canvas.width, canvas.height);
    }
    updateValidateButtonState();
}

function getResponseMessage(response, id) {
    return $('<div></div>').append(response).find('#' + id);
}

function validateSignature() {
    if (currentUserIndex !== null && !isCanvasEmpty()) {

        var signature = window.saturne.signature.canvas.toDataURL();

        // Risks read on the page: without a ?sign= link nothing could be recorded while the visitor
        // was ticking them, so they travel with the signature and are saved just before it
        var acknowledgedRisks = window.ppRiskAck ? window.ppRiskAck.getAcknowledgedCategories() : [];

        $.ajax({
            method: 'POST',
            url: document.URL + window.saturne.toolbox.getQuerySeparator(document.URL) + 'action=validate_signature&signatory_id=' + currentUserIndex,
            contentType: 'application/json; charset=utf-8',
            data: JSON.stringify({
                signature,
                acknowledged_risks: acknowledgedRisks
            }),
            success: function (response) {
                // Signature refused server-side, typically a mandatory document still waiting for an answer
                const error = getResponseMessage(response, 'error');
                if (error.length) {
                    $.jnotify(error.val(), {type: 'error'});
                    return;
                }

                                // Inline (single-person) mode: refresh the page to show the success screen.
                // The ?sign= token stays valid, so the response already holds the signed state.
                if ($('.pp-inline-signature').length) {
                    window.location.reload();
                    return;
                }

                $('.user-signature-item[data-user-index="' + currentUserIndex + '"]').replaceWith($(response).find('.user-signature-item[data-user-index="' + currentUserIndex + '"]'));

                // The device goes to the next attendee: they have to go through the risks themselves
                if (window.ppRiskAck) {
                    window.ppRiskAck.reset();
                }

                closeSignatureModal();

                // Add success notification
                $.jnotify('<?php echo dol_escape_js($langs->transnoentities('SignatureValidatedSuccessfully')); ?>', {type: 'success'});
            },
        });
    }
}

function addUser() {
    let type = $(this).data('type') || 'internal';
    let token          = window.saturne.toolbox.getToken();
    let querySeparator = window.saturne.toolbox.getQuerySeparator(document.URL);

    $.ajax({
        method: 'POST',
        url: document.URL + querySeparator + 'action=add_spread_user&type=' + type + '&token=' + token,
        processData: false,
        contentType: 'application/json charset=utf-8',
        success: function (resp) {
            let $newList = $(resp).find('.user-signatures-list').children();
            let firstItemIndex = -1;
            let nextItemIndex = -1;
            $newList.each(function(i) {
                if ($(this).hasClass('user-signature-item')) {
                    if (firstItemIndex === -1) {
                        firstItemIndex = i;
                    } else if (nextItemIndex === -1) {
                        nextItemIndex = i;
                    }
                }
            });
            if (firstItemIndex !== -1) {
                let $elementsToAdd = nextItemIndex !== -1 ? $newList.slice(firstItemIndex, nextItemIndex) : $newList.slice(firstItemIndex);
                $(document).find('.user-signatures-list').prepend($elementsToAdd);
            }
        }
    })
}

function removeUser() {
    const userIndex   = $(this).parents('.user-signature-item').eq(0).data('user-index');
    const userItem    = document.querySelector(`[data-user-index="${userIndex}"]`);
    const token       = window.saturne.toolbox.getToken();

    if (userItem) {
        $.ajax({
            method: 'POST',
            contentType: 'application/json; charset=utf-8',
            url: document.URL + window.saturne.toolbox.getQuerySeparator(document.URL) + 'action=remove_spread_user&signatory_id=' + userIndex + '&token=' + token,
            success: function (resp) {
                userItem.remove();
            },
        });
    }
}

function sendMail() {
    const userIndex   = $(this).parents('.user-signature-item').eq(0).data('user-index');

    const token       = window.saturne.toolbox.getToken();

    const button      = $(this);

    window.saturne.loader.display(button);

    $.ajax({
        method: 'POST',
        url: document.URL + window.saturne.toolbox.getQuerySeparator(document.URL) + 'action=send_email&signatory_id=' + userIndex + '&token=' + token,
        contentType: 'application/json charset=utf-8',
        success: function (resp) {

            let message = "Erreur inconnue";
            let isError = true;
            if (typeof resp === "string") {
                const matchSuccess = resp.match(/id="success"[^>]*value="([^"]+)"/i) || resp.match(/value="([^"]+)"[^>]*id="success"/i);
                const matchError = resp.match(/id="error"[^>]*value="([^"]+)"/i) || resp.match(/value="([^"]+)"[^>]*id="error"/i);
                if (matchSuccess) {
                    message = matchSuccess[1];
                    isError = false;
                } else if (matchError) {
                    message = matchError[1];
                } else {
                    message = resp.substring(0, 100);
                }
            } else {
                message = "Type non g�r�: " + (typeof resp);
            }

            if (isError) {
                $.jnotify(message, {type: 'error'});
            } else {
                $.jnotify(message, {type: 'success'});
            }

            window.saturne.loader.remove(button);
        }
    })
}

function savePublicNote() {
    const noteContent = $('.public-note-textarea').val();
    const token       = window.saturne.toolbox.getToken();
    const button      = $(this);

    window.saturne.loader.display(button);

    $.ajax({
        method: 'POST',
        url: document.URL + window.saturne.toolbox.getQuerySeparator(document.URL) + 'action=save_public_note&token=' + token,
        data: JSON.stringify({
            note_public: noteContent
        }),
        processData: false,
        contentType: 'application/json; charset=utf-8',
        success: function (resp) {
            window.saturne.loader.remove(button);
            button.addClass('button-disable');
        },
    });
}

function sendQuickSignEmail() {
    const email = $('#quick-sign-email').val();
    const token = window.saturne.toolbox.getToken();
    const button = $(this);

    if (!email || !email.includes('@')) {
        $.jnotify('<?php echo dol_escape_js($langs->transnoentities('PleaseEnterValidEmail')); ?>', {type: 'error'});
        return;
    }

    window.saturne.loader.display(button);

    $.ajax({
        method: 'POST',
        url: document.URL + window.saturne.toolbox.getQuerySeparator(document.URL) + 'action=send_quick_sign_email&token=' + token,
        data: JSON.stringify({
            email: email
        }),
        processData: false,
        contentType: 'application/json; charset=utf-8',
        success: function (resp) {
            let message = "Erreur inconnue";
            let isError = true;
            if (typeof resp === "string") {
                const matchSuccess = resp.match(/id="success"[^>]*value="([^"]+)"/i) || resp.match(/value="([^"]+)"[^>]*id="success"/i);
                const matchError = resp.match(/id="error"[^>]*value="([^"]+)"/i) || resp.match(/value="([^"]+)"[^>]*id="error"/i);
                if (matchSuccess) {
                    message = matchSuccess[1];
                    isError = false;
                } else if (matchError) {
                    message = matchError[1];
                } else {
                    message = resp.substring(0, 100);
                }
            } else {
                message = "Type non g�r�: " + (typeof resp);
            }

            if (isError) {
                $.jnotify(message, {type: 'error'});
            } else {
                $.jnotify('<?php echo dol_escape_js($langs->transnoentities('EmailSentSuccessfully')); ?>', {type: 'success'});
                $('#quick-sign-email').val('');
            }

            window.saturne.loader.remove(button);
        },
        error: function() {
            $.jnotify('<?php echo dol_escape_js($langs->transnoentities('ErrorSendingEmail')); ?>', {type: 'error'});
            window.saturne.loader.remove(button);
        }
    });
}

function registerPublicSignatory() {
    const button = $(this);
    const token  = window.saturne.toolbox.getToken();
    
    // Check for temporary documents
    let tmpSignatoryId = null;
    let notConcernedCodes = [];
    if ($('#public-register-tmp-id').length) {
        tmpSignatoryId = $('#public-register-tmp-id').val();
        const ncState = JSON.parse($('#public-register-not-concerned').val() || '{}');
        notConcernedCodes = Object.keys(ncState).filter(code => ncState[code]);
    }

    const fields = {
        firstname: $('#public-register-firstname').val(),
        lastname:  $('#public-register-lastname').val(),
        email:     $('#public-register-email').val(),
        phone:     $('#public-register-phone').val(),
        tmp_signatory_id: tmpSignatoryId,
        not_concerned_codes: notConcernedCodes
    };

    let hasError = false;
    <?php if ($confExtFirstnameMandatory) { ?>
    if (!fields.firstname) hasError = true;
    <?php } ?>
    <?php if ($confExtLastnameMandatory) { ?>
    if (!fields.lastname) hasError = true;
    <?php } ?>
    <?php if ($confExtPhoneMandatory) { ?>
    if (!fields.phone) hasError = true;
    <?php } ?>
    <?php if ($confExtEmailMandatory) { ?>
    if (!fields.email) hasError = true;
    <?php } ?>

    if (hasError) {
        $.jnotify('<?php echo dol_escape_js($langs->transnoentities('SpreadPublicRegisterMissingFields')); ?>', {type: 'error'});
        return;
    }

    // A single "@" is not a check: the server refuses what it rejects, and the visitor only found
    // out after a round trip with an error naming their own address
    <?php if ($confExtEmailVisible) { ?>
    if (fields.email && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(fields.email)) {
        $.jnotify('<?php echo dol_escape_js($langs->transnoentities('PleaseEnterValidEmail')); ?>', {type: 'error'});
        return;
    }
    <?php } ?>

    window.saturne.loader.display(button);

    $.ajax({
        method: 'POST',
        url: document.URL + window.saturne.toolbox.getQuerySeparator(document.URL) + 'action=register_public_signatory&token=' + token,
        data: JSON.stringify(fields),
        processData: false,
        contentType: 'application/json; charset=utf-8',
        success: function (response) {
            const error = getResponseMessage(response, 'error');
            if (error.length) {
                $.jnotify(error.val(), {type: 'error'});
                window.saturne.loader.remove(button);
                return;
            }

            // Land straight on the personal page: signature and required documents live there
            const redirect = getResponseMessage(response, 'redirect');
            if (redirect.length) {
                window.location.href = redirect.val();
            } else {
                window.saturne.loader.remove(button);
            }
        },
        error: function () {
            $.jnotify('<?php echo dol_escape_js($langs->transnoentities('Error')); ?>', {type: 'error'});
            window.saturne.loader.remove(button);
        }
    });
}

function toggleCertNotConcerned() {
    const button       = $(this);
    const certItem     = button.parents('.pp-cert-upload').eq(0);
    const notConcerned = !certItem.hasClass('pp-cert-upload--not-concerned');
    const token        = window.saturne.toolbox.getToken();
    const sigId        = certItem.data('cert-signatory-id');
    const certCode     = certItem.data('cert-code');

    if (typeof sigId === 'string' && sigId.startsWith('tmp_')) {
        if (notConcerned) {
            certItem.addClass('pp-cert-upload--not-concerned');
            button.addClass('pp-cert-not-concerned-btn--active');
            button.find('i').removeClass('fa-ban').addClass('fa-undo');
            button.find('span').text('<?php echo dol_escape_js($langs->transnoentities('SpreadIAmConcerned')); ?>');
        } else {
            certItem.removeClass('pp-cert-upload--not-concerned');
            button.removeClass('pp-cert-not-concerned-btn--active');
            button.find('i').removeClass('fa-undo').addClass('fa-ban');
            button.find('span').text('<?php echo dol_escape_js($langs->transnoentities('SpreadIAmNotConcerned')); ?>');
        }
        
        const ncInput = $('#public-register-not-concerned');
        if (ncInput.length) {
            const ncState = JSON.parse(ncInput.val() || '{}');
            ncState[certCode] = notConcerned;
            ncInput.val(JSON.stringify(ncState));
        }
        
        updateValidateButtonState();
        return;
    }

    $.ajax({
        method: 'POST',
        url: document.URL + window.saturne.toolbox.getQuerySeparator(document.URL) + 'action=set_cert_not_concerned&token=' + token,
        data: JSON.stringify({
            signatory_id:  certItem.data('cert-signatory-id'),
            cert_code:     certItem.data('cert-code'),
            not_concerned: notConcerned
        }),
        processData: false,
        contentType: 'application/json; charset=utf-8',
        success: function (response) {
            const error = getResponseMessage(response, 'error');
            if (error.length) {
                $.jnotify(error.val(), {type: 'error'});
                return;
            }

            certItem.toggleClass('pp-cert-upload--not-concerned', notConcerned);
            button.toggleClass('pp-cert-not-concerned-btn--active', notConcerned);
            button.find('i').attr('class', notConcerned ? 'fas fa-undo' : 'fas fa-ban');
            button.find('span').text(notConcerned ? '<?php echo dol_escape_js($langs->transnoentities('SpreadIAmConcerned')); ?>' : '<?php echo dol_escape_js($langs->transnoentities('SpreadIAmNotConcerned')); ?>');

            // The pending list is rebuilt server-side on the next load; keep it truthful meanwhile
            $('.pp-mandatory-pending__list li[data-cert-code="' + certItem.data('cert-code') + '"]').toggle(!notConcerned);
            $('.pp-mandatory-pending').toggle($('.pp-mandatory-pending__list li:visible').length > 0);
        }
    });
}

$(document).ready(function () {
    $(document).on('click', '.public-register-btn', registerPublicSignatory);

    $(document).on('click', '.pp-cert-not-concerned-btn', toggleCertNotConcerned);

    $(document).on('change', '.user-select-small', function () {
        let signatoryId = $(this).parents('.user-signature-item').eq(0).data('user-index');
        let val         = $(this).val();
        let token       = window.saturne.toolbox.getToken();

        $.ajax({
            method: 'POST',
            url: document.URL + window.saturne.toolbox.getQuerySeparator(document.URL) + 'action=update_spread_user&signatory_id=' + signatoryId + '&user_id=' + val + '&token=' + token,
            contentType: 'application/json; charset=utf-8',
            success: function (resp) {
                $('.user-signature-item[data-user-index="' + signatoryId + '"]').replaceWith($(resp).find('.user-signature-item[data-user-index="' + signatoryId + '"]'));
            }
        })
    });

    $(document).on('input change', '.external-signatory-input', function () {
        let $container = $(this).parents('.user-signature-item').eq(0);
        let signatoryId = $container.data('user-index');
        let $fn = $container.find('input[data-field="first_name"]');
        let $ln = $container.find('input[data-field="last_name"]');
        let $em = $container.find('input[data-field="email"]');
        let $ph = $container.find('input[data-field="phone"]');
        let first_name = $fn.length ? $fn.val() : '';
        let last_name = $ln.length ? $ln.val() : '';
        let email = $em.length ? $em.val() : '';
        let phone = $ph.length ? $ph.val() : '';
        let token = window.saturne.toolbox.getToken();

        let isReady = true;
        if ($fn.length && $fn.data('mandatory') == '1' && first_name.trim() === '') isReady = false;
        if ($ln.length && $ln.data('mandatory') == '1' && last_name.trim() === '') isReady = false;
        if ($em.length && $em.data('mandatory') == '1' && email.trim() === '') isReady = false;
        if ($ph.length && $ph.data('mandatory') == '1' && phone.trim() === '') isReady = false;

        let $signBtn = $container.find('.sign-btn');
        let $sendEmailBtn = $container.find('.send-email-btn');
        let $badge = $container.find('.badge-status');
        if (isReady) {
            $signBtn.removeClass('button-disable').addClass('button-primary');
            $sendEmailBtn.removeClass('button-disable').addClass('button-primary');
            $badge.removeClass('badge-status0').addClass('badge-status1');
        } else {
            $signBtn.removeClass('button-primary').addClass('button-disable');
            $sendEmailBtn.removeClass('button-primary').addClass('button-disable');
            $badge.removeClass('badge-status1').addClass('badge-status0');
        }


        let data = {
            first_name: first_name,
            last_name: last_name,
            email: email,
            phone: phone
        };

        $.ajax({
            method: 'POST',
            url: document.URL + window.saturne.toolbox.getQuerySeparator(document.URL) + 'action=update_spread_user_external&signatory_id=' + signatoryId + '&token=' + token,
            data: $.param(data),
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            success: function (resp) {
                // Optionally update UI if needed, for now just saved silently
            }
        })
    });

    $(document).on('click', '.close-modal-spread, .cancel-signature-btn', closeSignatureModal);
    $(document).on('click', '.add-user-btn', addUser);
    $(document).on('click', '.remove-user-btn', removeUser);

    $(document).on('click', '.sign-btn:not(.button-disable)', openSignatureModal);

    $(document).on('click', '.signature-erase', clearSignature);

    $(document).on('click', '.validate-sign-btn', validateSignature);

    $(document).on('click', '.save-public-note-btn', savePublicNote);

    $(document).on('input', '.public-note-textarea', function() {
        $('.save-public-note-btn').removeClass('button-disable');
        $('.save-public-note-btn').addClass('button-green');
    })

    $(document).on('click', '.send-email-btn:not(.button-disable)', sendMail);

    $(document).on('click', '.quick-sign-send-btn', sendQuickSignEmail);

    $('object[data-src]').each(function() {
        let $this = $(this);
        let src   = $this.data('src');

         fetch(src)
            .then(res => res.blob())
            .then(blob => {
                const blobUrl = URL.createObjectURL(blob);
                $this.attr('data', blobUrl + '#toolbar=0');
            })
            .catch(err => {
                console.error('Error loading object:', err);
            });
    });

    <?php if (!empty($signSignatory) && empty($signSignatory->signature)) { ?>
    // Single-person view: the signature panel is inline, bind it straight to this signatory (no modal)
    currentUserIndex = <?php echo (int) $signSignatory->id; ?>;
    (function () {
        const inlineCanvas = document.getElementById('signatureCanvas');
        if (inlineCanvas) {
            inlineCanvas.addEventListener('mouseup', updateValidateButtonState);
            inlineCanvas.addEventListener('touchend', updateValidateButtonState);
            setTimeout(function() {
                inlineCanvas.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 500);
        }
        updateValidateButtonState();
    })();
    <?php } elseif (!empty($directSignatoryId)) { ?>
    openSignatureModal(<?php echo (int) $directSignatoryId; ?>);
    <?php } ?>
});

</script>

