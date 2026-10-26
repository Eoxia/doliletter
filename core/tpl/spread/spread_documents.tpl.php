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
 * \file    core/tpl/spread/spread_documents.tpl.php
 * \ingroup doliletter
 * \brief   Documents of the spread, in their own tab next to the Diffusion one: a document is added right
 *          here, and what the people of the spread get of each one is picked from three buttons. It
 *          replaces the star of the attached files tab, which had to be found and only worked once the
 *          file was shared. Rendered by view/spread_documents.php (inherits its scope).
 *          Expects: $langs, $spreadDocuments, $permissiontomanagedocuments, $fromId, $fromType.
 */

$documentsFormUrl = $_SERVER['PHP_SELF'] . '?fromid=' . ((int) $fromId) . '&fromtype=' . urlencode($fromType);
$canAddDocuments  = $permissiontomanagedocuments && getDolGlobalString('MAIN_UPLOAD_DOC');

// From the least to the most visible on the public page. The help of a label is its key + 'Help'.
$documentModes = [
    DOLILETTER_SPREAD_DOCUMENT_HIDDEN   => ['icon' => 'fa-eye-slash', 'label' => 'SpreadDocumentHidden'],
    DOLILETTER_SPREAD_DOCUMENT_DOWNLOAD => ['icon' => 'fa-download', 'label' => 'SpreadDocumentDownload'],
    DOLILETTER_SPREAD_DOCUMENT_READ     => ['icon' => 'fa-book-open', 'label' => 'SpreadDocumentRead'],
];

// Right of the title, the button is the label of a file input kept out of sight: the files picked go up
// at once, see js/modules/spreaddocuments.js
$addDocumentButton = '';
if ($canAddDocuments) {
    $addDocumentButton  = '<form class="spread-documents__add" method="POST" enctype="multipart/form-data" action="' . $documentsFormUrl . '">';
    $addDocumentButton .= '<input type="hidden" name="token" value="' . newToken() . '">';
    $addDocumentButton .= '<input type="hidden" name="action" value="add_spread_document">';
    $addDocumentButton .= '<input id="spread-document-input" class="spread-documents__add-input" type="file" name="spread_document[]" multiple>';
    $addDocumentButton .= '<label class="spread-chip spread-chip--primary" for="spread-document-input"><i class="fas fa-plus"></i>' . $langs->trans('SpreadDocumentAdd') . '</label>';
    $addDocumentButton .= '</form>';
}
?>
<div id="spread-documents" class="spread-documents">
    <?php print load_fiche_titre($langs->trans('SpreadDocuments'), $addDocumentButton, ''); ?>
    <p class="opacitymedium spread-documents__help"><?php echo $langs->trans('SpreadDocumentsHelp'); ?></p>

    <div class="div-table-responsive-no-min">
        <table class="noborder centpercent spread-documents__table">
            <tr class="liste_titre">
                <td><?php echo $langs->trans('SpreadDocument'); ?></td>
                <td class="center"><?php echo $langs->trans('Date'); ?></td>
                <td class="center"><?php echo $langs->trans('SpreadDocumentOnPublicPage'); ?></td>
            </tr>
            <?php if (empty($spreadDocuments)) { ?>
            <tr class="oddeven">
                <td colspan="3"><span class="opacitymedium"><?php echo $langs->trans('SpreadNoDocument'); ?></span></td>
            </tr>
            <?php } ?>
            <?php foreach ($spreadDocuments as $spreadDocument) {
                $isLink = ($spreadDocument['type'] == 'link'); ?>
            <tr class="oddeven">
                <td class="spread-documents__name">
                    <?php echo $isLink ? img_picto('', 'globe', 'class="pictofixedwidth"') : img_mime($spreadDocument['name'], '', 'pictofixedwidth'); ?>
                    <?php if (dol_strlen($spreadDocument['url']) > 0) { ?>
                    <a href="<?php echo dol_escape_htmltag($spreadDocument['url']); ?>" target="_blank" rel="noopener"><?php echo dol_escape_htmltag($spreadDocument['name']); ?></a>
                    <?php } else {
                        echo dol_escape_htmltag($spreadDocument['name']);
                    } ?>
                    <?php if ($spreadDocument['generated']) {
                        echo ' ' . dolGetBadge($langs->trans('SpreadDocumentGenerated'), '', 'secondary');
                    } ?>
                </td>
                <td class="center nowraponall"><?php echo dol_print_date($spreadDocument['date'], 'dayhour'); ?></td>
                <td class="center">
                    <form class="spread-document-mode" method="POST" action="<?php echo $documentsFormUrl; ?>">
                        <input type="hidden" name="token" value="<?php echo newToken(); ?>">
                        <input type="hidden" name="action" value="set_spread_document_mode">
                        <input type="hidden" name="document_type" value="<?php echo $spreadDocument['type']; ?>">
                        <input type="hidden" name="document_id" value="<?php echo $spreadDocument['id']; ?>">
                        <?php foreach ($documentModes as $modeKey => $documentMode) {
                            // A link is always offered on the public page: it cannot be hidden from it
                            if ($isLink && $modeKey == DOLILETTER_SPREAD_DOCUMENT_HIDDEN) {
                                continue;
                            }
                            $isCurrent = ($spreadDocument['mode'] == $modeKey);
                            $labelKey  = ($isLink && $modeKey == DOLILETTER_SPREAD_DOCUMENT_DOWNLOAD) ? 'SpreadDocumentLink' : $documentMode['label']; ?>
                        <button type="<?php echo $isCurrent ? 'button' : 'submit'; ?>" name="document_mode" value="<?php echo $modeKey; ?>"
                                class="spread-document-mode__option spread-document-mode__option--<?php echo $modeKey . ($isCurrent ? ' spread-document-mode__option--current' : ''); ?>"
                                aria-pressed="<?php echo $isCurrent ? 'true' : 'false'; ?>"
                                title="<?php echo dol_escape_htmltag($langs->trans($labelKey . 'Help')); ?>"<?php echo $permissiontomanagedocuments ? '' : ' disabled'; ?>>
                            <i class="fas <?php echo $documentMode['icon']; ?>"></i> <?php echo $langs->trans($labelKey); ?>
                        </button>
                        <?php } ?>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
