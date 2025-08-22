<?php
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
 * Parameters : $objectType, $trackID
 * Objects    : $object, $signatory
 * Variable   : $fileExists, $moduleNameLowerCase, $moreParams
 */

// Initialize Form object for user selection
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
$tmpUser = new User($db);
$form = new Form($db);
?>

<div class="public-card__container" data-public-interface="true">
    <div class="public-card__header">
        <div class="public-card__content">
            <div class="user-list-container">
                <div class="user-list-header">
                    <h3><?php echo $langs->trans('UserSignatureList'); ?></h3>
                </div>
                <div class="user-signatures-list" id="userSignaturesList">
                    <!-- Utilisateurs pré-signés par défaut -->

                    <?php

                    // If there are already added signatories, display them
                    foreach ($signatories as $index => $signatoryItem) {
                        if (empty($signatoryItem->signature)) {
                        ?>
                        <div class="user-signature-item signature-not-validated" data-user-index="<?php echo $signatoryItem->id; ?>">
                            <div class="user-info">
                                <div class="form-row">
                                    <div class="form-element">
                                        <label for="attendant_user"><?php echo $langs->trans('User'); ?></label>
                                        <div class="input-with-actions">
                                            <div class="user-status">
                                                <?php
                                                print $form->select_dolusers($signatoryItem->element_id, 'attendant_user_' . $signatoryItem->id, 1, [], 0, '', '', $conf->entity, 0, 0, '', 0, '', 'minwidth200 widthcentpercentminusx user-select-small');
                                                ?>
                                            </div>
                                            <div class="signature-date">
                                                <button type="button" class="wpeo-button button-<?php echo empty($signatoryItem->element_id) || $signatoryItem->element_id == -1 ? 'grey' : 'primary' ?> sign-btn">
                                                    <i class="fas fa-signature"></i>
                                                </button>
                                            </div>
                                            <button type="button" class="wpeo-button button-red remove-user-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
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
                                                <?php 
                                                $tmpUser->fetch($signatoryItem->element_id);
                                                echo $tmpUser->getNomUrl(1);
                                                ?>
                                            </div>
                                            <div class="signature-date">
                                                <small><?php echo $langs->trans('SignedOn'); ?> <?php echo dol_print_date($signatoryItem->signature_date, '%d/%m/%Y %H:%M') ?></small>
                                                <div class="signature-preview">
                                                    <img src="<?php echo $signatoryItem->signature; ?>" alt="Signature" class="signature-image">
                                                </div>
                                            </div>
                                            <button type="button" class="wpeo-button button-red remove-user-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                    }
                    ?>

                </div>

                <div class="add-user-section">
                    <button type="button" class="wpeo-button button-blue add-user-btn">
                        <i class="fas fa-plus"></i> <?php echo $langs->trans('AddLine'); ?>
                    </button>
                </div>

                <div class="private-note-section">
                    <div class="private-note-header">
                        <h3><?php echo $langs->trans('NotePrivate'); ?></h3>
                    </div>
                    <div class="private-note-content">
                        <textarea class="private-note-textarea" placeholder="<?php echo $langs->trans('EnterNotePrivateHere'); ?>"><?php echo $attendanceSheet->note_private ?? ''; ?></textarea>
                        <div class="private-note-actions">
                            <button type="button" class="wpeo-button save-private-note-btn">
                                <i class="fas fa-save"></i> <?php echo $langs->trans('Save'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de signature -->
<div id="signatureModal" class="modal-spread">
    <div class="modal-spread-content">
        <div class="modal-spread-header">
            <h3><?php echo $langs->trans('Signature'); ?></h3>
            <span class="close-modal-spread">&times;</span>
        </div>
        <div class="modal-spread-body">
            <div class="signature-element">
                <canvas id="signatureCanvas" class="canvas-container editable canvas-signature modal-canvas-signature" width="600" height="200" style="touch-action: none;"></canvas>
                <div class="signature-erase wpeo-button button-square-40 button-rounded button-grey">
                    <span><i class="fas fa-eraser"></i></span>
                </div>
            </div>
        </div>
        <div class="modal-spread-footer">
            <button type="button" class="wpeo-button button-grey close-modal-spread">
                <?php echo $langs->trans('Cancel'); ?>
            </button>
            <button type="button" class="wpeo-button button-grey validate-sign-btn" disabled>
                <i class="fas fa-check"></i> <?php echo $langs->trans('ValidateSignature'); ?>
            </button>
        </div>
    </div>
</div>

<script>
let currentUserIndex = null;

function openSignatureModal() {
    const userIndex   = $(this).parents('.user-signature-item').eq(0).data('user-index');
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
    modal.style.display = 'none';
    currentUserIndex = null;
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
            validateBtn.addClass('button-grey');
        } else {
            validateBtn.removeClass('button-grey');
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

function validateSignature() {
    if (currentUserIndex !== null && !isCanvasEmpty()) {

        var signature = window.saturne.signature.canvas.toDataURL();

        $.ajax({
            method: 'POST',
            url: document.URL + window.saturne.toolbox.getQuerySeparator(document.URL) + 'action=validate_signature&signatory_id=' + currentUserIndex,
            data: JSON.stringify({
                signature
            }),
            success: function (response) {

                $('.user-signature-item[data-user-index="' + currentUserIndex + '"]').replaceWith($(response).find('.user-signature-item[data-user-index="' + currentUserIndex + '"]'));

                closeSignatureModal();
            },
        });
    }
}

function addUser() {
    let token          = window.saturne.toolbox.getToken();
    let querySeparator = window.saturne.toolbox.getQuerySeparator(document.URL);

    $.ajax({
        method: 'POST',
        url: document.URL + querySeparator + 'action=add_spread_user' + '&token=' + token,
        processData: false,
        contentType: false,
        success: function (resp) {
            $(document).find('.user-signatures-list').append($(resp).find('.user-signature-item').last());
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
            url: document.URL + window.saturne.toolbox.getQuerySeparator(document.URL) + 'action=remove_spread_user&signatory_id=' + userIndex + '&token=' + token,
            success: function (resp) {
                userItem.remove();
            },
        });
    }
}

function savePrivateNote() {
    const noteContent = $('.private-note-textarea').val();
    const token       = window.saturne.toolbox.getToken();
    const button      = $(this);

    window.saturne.loader.display(button);

    $.ajax({
        method: 'POST',
        url: document.URL + window.saturne.toolbox.getQuerySeparator(document.URL) + 'action=save_private_note&token=' + token,
        data: JSON.stringify({
            note_private: noteContent
        }),
        processData: false,
        success: function (resp) {
            console.log($(this));
            window.saturne.loader.remove(button);
        },
    });
}

$(document).ready(function () {
    $(document).on('change', '.user-select-small', function () {
        let signatoryId = $(this).parents('.user-signature-item').eq(0).data('user-index');
        let val         = $(this).val();
        let token       = window.saturne.toolbox.getToken();

        $.ajax({
            method: 'POST',
            url: document.URL + window.saturne.toolbox.getQuerySeparator(document.URL) + 'action=update_spread_user&signatory_id=' + signatoryId + '&user_id=' + val + '&token=' + token,
            success: function (resp) {
                $('.user-signature-item[data-user-index="' + signatoryId + '"]').replaceWith($(resp).find('.user-signature-item[data-user-index="' + signatoryId + '"]'));
            }
        })
    })

    $(document).on('click', '.close-modal-spread', closeSignatureModal);
    $(document).on('click', '.add-user-btn', addUser);
    $(document).on('click', '.remove-user-btn', removeUser);

    $(document).on('click', '.sign-btn:not(.button-grey)', openSignatureModal);

    $(document).on('click', '.signature-erase', clearSignature);

    $(document).on('click', '.validate-sign-btn', validateSignature);

    $(document).on('click', '.save-private-note-btn', savePrivateNote);
});

</script>

<style>

.modal-canvas-signature {
    display: block !important;
    margin: 0 auto !important;
    border: 2px solid #ddd !important;
    border-radius: 6px !important;
    background-color: #fff !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    position: relative;
}

.user-list-container {
    width: 100%;
}

.user-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #ddd;
}

.user-signature-item {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 10px;
    margin-bottom: 10px;
    background: #fff;
}

.user-signature-item.signature-validated {
    border-color: #28a745;
    background-color: #f8f9fa;
}

.form-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 8px;
}

.form-element {
    flex: 1;
}

.input-with-actions {
    display: flex;
    gap: 15px;
    width: 100%;
    align-items: center;
}

.user-status {
    display: flex;
    align-items: center;
    min-width: 150px;
    flex: 1;
}

.add-user-section {
    text-align: center;
    margin-top: 12px;
    padding-top: 12px;
}

.form-element label {
    display: block;
    margin-bottom: 2px;
    font-weight: bold;
    font-size: 12px;
}

.signature-image {
    border: 2px solid #ddd;
    border-radius: 3px;
    background: #fff;
    display: block;
    margin: 0 auto;
    max-width: 100px;
    max-height: 40px;
    width: auto;
    height: auto;
    object-fit: contain;
}

.signature-element {
    position: relative;
    text-align: center;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 3px;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
}

.canvas-container {
    display: block !important;
    margin: 0 auto !important;
    border: 2px solid #ddd;
    border-radius: 6px;
    background: #fff;
    touch-action: none;
    position: relative;
}

.signature-erase {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: #6c757d;
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.signature-erase:hover {
    background-color: #5a6268;
}

.signature-date {
    color: #666;
    font-style: italic;
    margin: 0;
    white-space: nowrap;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
    font-size: 11px;
    flex: 1;
    max-width: 140px;
}

.private-note-section {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
}

.private-note-header {
    margin-bottom: 10px;
}

.private-note-header h3 {
    margin: 0;
    color: #333;
    font-size: 16px;
    font-weight: bold;
}

.private-note-content {
    width: 100%;
}

.private-note-textarea {
    width: 100%;
    min-height: 120px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-family: inherit;
    font-size: 14px;
    line-height: 1.4;
    resize: vertical;
    background-color: #fff;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
}

.private-note-textarea:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.private-note-textarea::placeholder {
    color: #999;
    font-style: italic;
}

.private-note-actions {
    margin-top: 10px;
    text-align: right;
}

.user-signature-item.signature-validated .input-with-actions {
    align-items: center;
}

.user-signature-item.signature-validated .remove-user-btn {
    align-self: center;
    margin-top: 0;
}

.user-select-small {
    flex: 1;
    padding: 4px 6px;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 12px;
    max-width: 200px;
}

@media (max-width: 768px) {
    .public-card__container {
        padding: 6px;
        margin: 0;
    }
    
    .user-signature-item {
        padding: 8px;
        margin-bottom: 8px;
    }
    
    .form-row {
        flex-direction: column;
        gap: 6px;
        align-items: stretch;
    }
    
    .input-with-actions {
        gap: 12px;
        flex-direction: row;
        align-items: center;
    }
    
    .user-status {
        min-width: 120px;
        justify-content: flex-start;
        flex: 1;
    }
    
    .signature-element {
        padding: 6px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
    }
    
    .signature-image {
        width: auto !important;
        max-width: 80px !important;
        max-height: 35px !important;
        height: auto !important;
        box-sizing: border-box;
    }
    
    .signature-erase {
        top: 8px;
        right: 8px;
        width: 26px;
        height: 26px;
    }
    
    .user-list-header h3 {
        font-size: 15px;
    }
    
    .signature-date {
        margin: 0;
        text-align: center;
        flex-direction: column;
        align-items: center;
        font-size: 10px;
        flex: 1;
        max-width: 120px;
    }
    
    .remove-user-btn {
        margin-top: 0;
        align-self: center;
    }
    
    .sign-btn {
        min-width: auto;
        width: auto;
        padding: 4px 6px;
    }
    
    .private-note-section {
        margin-top: 15px;
        padding-top: 12px;
    }
    
    .private-note-header h3 {
        font-size: 15px;
    }
    
    .private-note-textarea {
        min-height: 100px;
        padding: 8px;
        font-size: 13px;
    }
    
    .modal-canvas-signature {
        width: calc(100% - 20px) !important;
        max-width: calc(100vw - 60px) !important;
        height: 150px !important;
    }
    
    .canvas-container {
        width: calc(100% - 20px) !important;
        max-width: calc(100vw - 60px) !important;
        height: 150px !important;
        border: 2px solid #ddd;
        border-radius: 6px;
        background: #fff;
    }
}

@media (max-width: 480px) {
    .signature-image {
        width: auto !important;
        max-width: 65px !important;
        max-height: 25px !important;
        height: auto !important;
    }
    
    .canvas-container {
        width: 100% !important;
        max-width: calc(100vw - 25px);
        height: 140px !important;
    }
    
    .signature-element {
        padding: 4px;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
    }
    
    .user-signature-item {
        padding: 6px;
    }
    
    .form-element label {
        font-size: 11px;
    }
    
    .signature-date {
        margin: 0;
        font-size: 9px;
        max-width: 100px;
    }
    
    .input-with-actions {
        gap: 8px;
        align-items: center;
    }
    
    .remove-user-btn {
        margin-top: 0;
        align-self: center;
    }
    
    .sign-btn {
        min-width: auto;
        width: auto;
        padding: 4px 6px;
    }
    
    .private-note-section {
        margin-top: 12px;
        padding-top: 10px;
    }
    
    .private-note-header h3 {
        font-size: 14px;
    }
    
    .private-note-textarea {
        min-height: 80px;
        padding: 6px;
        font-size: 12px;
    }
    
    .modal-canvas-signature {
        width: calc(100% - 10px) !important;
        max-width: calc(100vw - 40px) !important;
        height: 120px !important;
    }
    
    .canvas-container {
        width: calc(100% - 10px) !important;
        max-width: calc(100vw - 40px) !important;
        height: 120px !important;
    }
}

.modal-spread {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.modal-spread-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    border-radius: 8px;
    width: 80%;
    max-width: 700px;
    max-height: 90vh;
    overflow: auto;
}

.modal-spread-header {
    padding: 15px 20px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #ddd;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-spread-header h3 {
    margin: 0;
    font-size: 18px;
}

.modal-spread-body {
    padding: 20px;
}

.modal-spread-footer {
    padding: 15px 20px;
    background-color: #f8f9fa;
    border-top: 1px solid #ddd;
    border-radius: 0 0 8px 8px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.close-modal-spread {
    cursor: pointer;
}

@media (max-width: 768px) {
    .modal-spread-content {
        width: 95%;
        margin: 2% auto;
    }
    
    .modal-spread-header,
    .modal-spread-footer {
        padding: 12px 15px;
    }
    
    .modal-spread-body {
        padding: 15px;
    }
    
    .modal-spread-footer {
        flex-direction: column;
        gap: 8px;
    }
    
    .modal-spread-footer .btn {
        width: 100%;
    }
}
</style>