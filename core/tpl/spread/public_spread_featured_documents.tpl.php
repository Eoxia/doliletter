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
 * \file    core/tpl/spread/public_spread_featured_documents.tpl.php
 * \ingroup doliletter
 * \brief   Documents featured on the spread public page (the document of the object to read), shown
 *          in full in the page. First part of the reading step, under the success screen once signed.
 *          Expects: $linkedFilesFavorite.
 */
?>
<style>
/* Detache du bloc qui le precede : les documents demandes a la personne, ou l'ecran de succes */
.spread-featured-documents { margin-top: 16px; }
</style>

<div class="spread-featured-documents">
<?php
foreach ($linkedFilesFavorite as $file) {
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
<?php } ?>
</div>
