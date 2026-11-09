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
 *          A file the browser cannot show (text document, spreadsheet…) gets a card to download it.
 *          Expects: $linkedFilesFavorite.
 */

// Type of a file shown as a card, from its extension: icon, colour (modifier class) and label
$featuredFileKinds = [
    'pdf'     => ['icon' => 'fa-file-pdf',        'label' => 'SpreadFileKindPdf',     'extensions' => ['pdf']],
    'text'    => ['icon' => 'fa-file-word',       'label' => 'SpreadFileKindText',    'extensions' => ['odt', 'ott', 'doc', 'docx', 'rtf']],
    'sheet'   => ['icon' => 'fa-file-excel',      'label' => 'SpreadFileKindSheet',   'extensions' => ['ods', 'ots', 'xls', 'xlsx', 'csv']],
    'slides'  => ['icon' => 'fa-file-powerpoint', 'label' => 'SpreadFileKindSlides',  'extensions' => ['odp', 'otp', 'ppt', 'pptx']],
    'archive' => ['icon' => 'fa-file-archive',    'label' => 'SpreadFileKindArchive', 'extensions' => ['zip', 'rar', '7z', 'gz', 'tar']],
    'other'   => ['icon' => 'fa-file',            'label' => 'SpreadFileKindOther',   'extensions' => []],
];
?>
<style>
/* Detache du bloc qui le precede : les documents demandes a la personne, ou l'ecran de succes */
.spread-featured-documents { margin-top: 16px; }

/* Fichier que le navigateur ne sait pas afficher (odt, docx, xlsx…) : le cadre ne montrait que son nom
   en texte brut. Meme carte que les signataires, avec le type, le poids et le telechargement */
.spread-featured-file {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 12px;
    padding: 16px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.spread-featured-file__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    width: 48px;
    height: 48px;
    font-size: 22px;
    color: #475569;
    background: #f1f5f9;
    border-radius: 10px;
}

.spread-featured-file__icon--pdf { color: #dc2626; background: #fef2f2; }
.spread-featured-file__icon--text { color: #2563eb; background: #eff6ff; }
.spread-featured-file__icon--sheet { color: #16a34a; background: #f0fdf4; }
.spread-featured-file__icon--slides { color: #ea580c; background: #fff7ed; }

.spread-featured-file__info {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    gap: 2px;
    min-width: 0;
}

/* Un nom de fichier genere n'a pas d'espace : sans coupure il deborde de la carte sur telephone */
.spread-featured-file__name {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    overflow-wrap: anywhere;
}

.spread-featured-file__meta {
    font-size: 12px;
    color: #64748b;
}

/* Couleur du texte posee sur chaque etat du lien : sinon celle des liens du theme l'emporte et laisse
   un texte bleu fonce sur le bouton bleu */
.spread-featured-documents a.spread-featured-file__download,
.spread-featured-documents a.spread-featured-file__download:visited,
.spread-featured-documents a.spread-featured-file__download:hover,
.spread-featured-documents a.spread-featured-file__download:focus {
    color: #fff;
    text-decoration: none;
}

.spread-featured-documents a.spread-featured-file__download {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    gap: 6px;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 600;
    background: #3b82f6;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
    transition: background 0.15s;
}

.spread-featured-documents a.spread-featured-file__download:hover,
.spread-featured-documents a.spread-featured-file__download:focus {
    background: #2563eb;
}

/* Sur telephone le bouton passe sous le nom, sur toute la largeur : plus facile a toucher. Le nom part
   d'une base nulle pour rester a cote de l'icone, sinon sa longueur le renvoie seul a la ligne */
@media (max-width: 576px) {
    .spread-featured-file { flex-wrap: wrap; }
    .spread-featured-file__info { flex-basis: 0; }
    .spread-featured-documents a.spread-featured-file__download { flex-basis: 100%; padding: 10px 14px; }
}
</style>

<div class="spread-featured-documents">
<?php
foreach ($linkedFilesFavorite as $file) {
    $fileMimeType = dol_mimetype($file->filename);
    $fileUrl      = DOL_URL_ROOT . '/document.php?hashp=' . urlencode($file->share);

    // Carte de telechargement : seule a l'ecran pour un fichier que le navigateur ne sait pas
    // afficher, et en repli d'un PDF qu'il n'affiche pas (navigateur de telephone sans lecteur integre)
    $fileExtension = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));
    $fileKind      = 'other';
    foreach ($featuredFileKinds as $kind => $kindData) {
        if (in_array($fileExtension, $kindData['extensions'])) {
            $fileKind = $kind;
            break;
        }
    }
    $fileMeta = [$langs->transnoentities($featuredFileKinds[$fileKind]['label'])];
    if ($fileExtension != '') {
        $fileMeta[] = strtoupper($fileExtension);
    }
    // Chemin de l'index ECM relatif a DOL_DATA_ROOT, entite comprise
    $fileSize = dol_filesize(DOL_DATA_ROOT . '/' . $file->filepath . '/' . $file->filename);
    if ($fileSize > 0) {
        if ($fileSize < 1048576) {
            $fileMeta[] = max(1, round($fileSize / 1024)) . ' ' . $langs->transnoentities('Kb');
        } else {
            $fileMeta[] = str_replace('.', $langs->transnoentitiesnoconv('SeparatorDecimal'), (string) round($fileSize / 1048576, 1)) . ' ' . $langs->transnoentities('Mb');
        }
    }

    $fileCard  = '<div class="spread-featured-file">';
    $fileCard .= '<span class="spread-featured-file__icon spread-featured-file__icon--' . $fileKind . '"><i class="fas ' . $featuredFileKinds[$fileKind]['icon'] . '"></i></span>';
    $fileCard .= '<span class="spread-featured-file__info">';
    $fileCard .= '<span class="spread-featured-file__name">' . dol_escape_htmltag($file->filename) . '</span>';
    $fileCard .= '<span class="spread-featured-file__meta">' . dol_escape_htmltag(implode(' · ', $fileMeta)) . '</span>';
    $fileCard .= '</span>';
    $fileCard .= '<a class="spread-featured-file__download" href="' . $fileUrl . '" download><i class="fas fa-download"></i>' . dol_escape_htmltag($langs->transnoentities('Download')) . '</a>';
    $fileCard .= '</div>';

    if ($fileMimeType == 'video/mp4') { ?>
        <video src="<?php echo $fileUrl; ?>" controls width="100%" height="600px">
            Your browser does not support the video tag.
        </video>
    <?php } elseif (in_array($fileMimeType, ['application/pdf', 'text/plain']) || image_format_supported($file->filename) >= 0) {
        // L'URL d'un <object> se donne dans data : avec data-src le navigateur
        // n'appelle jamais le document et le cadre reste vide.
        // attachment=0 : sans lui document.php repond Content-Disposition: attachment
        // et le navigateur telecharge le fichier au lieu de l'afficher dans le cadre.
        $filePreviewUrl = $fileUrl . '&attachment=0';
    ?>
    <object
        name="objectpreview"
        type="<?php echo $fileMimeType; ?>"
        width="100%"
        height="600px"
        data="<?php echo $filePreviewUrl; ?>">
        <?php echo $fileCard; ?>
    </object>
    <?php } else {
        // Un odt, un docx, un tableur... : le navigateur ne l'affiche pas, l'<object> ne montrait que le
        // nom du fichier en texte brut
        echo $fileCard;
    } ?>
<?php } ?>
</div>
