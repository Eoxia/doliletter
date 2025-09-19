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
 * Parameters : $objectType
 * Objects    : $object, $$signatories
 * Variable   : $moduleNameLowerCase, $moreParams
 */

// Initialize Form object for user selection
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
$tmpUser = new User($db);
$form    = new Form($db);
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
</style>

<div class="public-card__container" data-public-interface="true">
    <div class="public-card__header">
        <div class="public-card__content">
            <div class="object-title-section">
                <?php echo $objectsMetadata[$objectType]['object']->getNomUrl(1) . (!empty($objectLabel) ? ' - ' . $objectLabel : '' ); ?>
            </div>

            <?php if (!empty($linkedFilesFavorite)) {
                foreach ($linkedFilesFavorite as $key => $file) {
                    $modulepart   =  explode('/', $file->filepath, 2)[0];
                    $relativepath = explode('/', $file->filepath, 2)[1] . '/' . $file->filename;
                    $filePath     = DOL_URL_ROOT.'/document.php?modulepart='.urlencode($modulepart).'&attachment=0&file='.urlencode($relativepath).'&entity='.urlencode($file->entity).'#toolbar=0&navpanes=0&scrollbar=0';
                    ?>
                    <object
                        name="objectpreview"
                        data="<?php echo $filePath; ?>"
                        type="<?php echo dol_mimetype($file->filename); ?>"
                        width="100%"
                        height="600px"
                        param="noparam">
                    </object>
            <?php }} ?>

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

            <div class="user-list-container">
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
                                                print $form->select_dolusers(empty($signatoryItem->element_id) ? -1 : $signatoryItem->element_id, 'attendant_user_' . $signatoryItem->id, 1, [], 0, '', '', $conf->entity, 0, 0, '', 0, '', 'minwidth150 widthcentpercentminusx user-select-small');
                                                ?>
                                            </div>
                                            <div class="signature-status">
                                                <span class="badge badge-dot badge-status<?php echo empty($signatoryItem->element_id) || $signatoryItem->element_id == -1 ? '0' : '1' ?> badge-status"></span>
                                                <i class="fas fa-signature"></i>
                                                <span>jj/mm/aaaa --:--</span>
                                            </div>
                                            <button type="button" class="wpeo-button button-<?php echo empty($signatoryItem->element_id) || $signatoryItem->element_id == -1 ? 'disable' : 'primary' ?> sign-btn">
                                                <i class="fas fa-signature"></i>
                                            </button>
                                            <button type="button" class="wpeo-button button-<?php echo (empty($signatoryItem->element_id) || $signatoryItem->element_id == -1 || empty($permissiontoadd)) ? 'disable' : 'primary' ?> send-email-btn">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                            <?php if (!empty($permissiontoadd)) { ?>
                                            <button type="button" class="wpeo-button button-red remove-user-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php } ?>
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
                                            <div class="signature-status">
                                                <span class="badge badge-dot badge-status4 badge-status"></span>
                                                <i class="fas fa-signature"></i>
                                                <span><?php echo dol_print_date($signatoryItem->signature_date, '%d/%m/%Y %H:%M') ?></span>
                                            </div>
                                            <a href="<?php echo DOL_URL_ROOT . '/custom/saturne/public/signature/add_signature.php?track_id=' . $signatoryItem->signature_url . '&entity=1&module_name=doliletter&object_type=doliletterattendancesheet'; ?>"
                                                target="_blank" class="wpeo-button">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="wpeo-button button-disable send-email-btn" disabled>
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                            <?php if (!empty($permissiontoadd)) { ?>
                                            <button type="button" class="wpeo-button button-red remove-user-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php } ?>
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

                <?php if (!empty($permissiontoadd)) { ?>
                <div class="add-user-section tabsAction">
                    <button type="button" class="wpeo-button button-blue add-user-btn">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

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
                <div class="signature-erase wpeo-button button-square-40 button-rounded button-red">
                    <span><i class="fas fa-eraser"></i></span>
                </div>
            </div>
        </div>
        <div class="modal-spread-footer">
            <button type="button" class="wpeo-button button-disable close-modal-spread">
                <?php echo $langs->trans('Cancel'); ?>
            </button>
            <button type="button" class="wpeo-button button-disable validate-sign-btn" disabled>
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

function validateSignature() {
    if (currentUserIndex !== null && !isCanvasEmpty()) {

        var signature = window.saturne.signature.canvas.toDataURL();

        $.ajax({
            method: 'POST',
            url: document.URL + window.saturne.toolbox.getQuerySeparator(document.URL) + 'action=validate_signature&signatory_id=' + currentUserIndex,
            contentType: 'application/json; charset=utf-8',
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
        contentType: 'application/json charset=utf-8',
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

            const message = $(resp).val();
            const isError = $(resp).attr('id') === 'error';

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

$(document).ready(function () {
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
    })

    $(document).on('click', '.close-modal-spread', closeSignatureModal);
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
});

</script>