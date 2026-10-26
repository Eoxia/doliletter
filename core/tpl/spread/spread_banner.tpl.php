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
 * \file    core/tpl/spread/spread_banner.tpl.php
 * \ingroup doliletter
 * \brief   Tabs and banner of the diffused object, shared by the Diffusion and the documents tabs: the
 *          buttons opening the public page and copying its address come first under the title, where
 *          a line of text under the notes went unnoticed. Rendered by view/spread.php and
 *          view/spread_documents.php (inherits their scope).
 *          Expects: $langs, $object (attendance sheet), $objectsMetadata, $fromType, $fromId, $spreadTab
 *          (code of the active tab).
 */

saturne_get_fiche_head($objectsMetadata[$fromType]['object'], $spreadTab, '');
$linkBack = '<a href="' . dol_buildpath($fromType . '/list.php?restore_lastsearch_values=1', 1) . '">' . $langs->trans('BackToList') . '</a>';

$publicUrl = dol_buildpath('/doliletter/public/spread/add_spread.php', 3) . '?id=' . ((int) $fromId) . '&object_type=' . urlencode($fromType);

$moreHtmlRef  = '<div class="spread-banner-actions">';
$moreHtmlRef .= '<a class="spread-chip spread-chip--primary" href="' . dol_escape_htmltag($publicUrl) . '" target="_blank" rel="noopener">';
$moreHtmlRef .= '<i class="fas fa-external-link-alt"></i>' . $langs->trans('SpreadOpenPublicPage') . '</a>';
$moreHtmlRef .= showValueWithClipboardCPButton($publicUrl, 0, $langs->trans('SpreadCopyPublicLink'));
$moreHtmlRef .= '</div>';

// Note privée
$moreHtmlRef .= '<div class=" opacitymedium">';
$moreHtmlRef .= '<strong>' . $langs->trans('NotePrivate') . ':</strong>';
$moreHtmlRef .= dol_string_nohtmltag($object->note_private, 1);
$moreHtmlRef .= '</div>';

// Note publique
$moreHtmlRef .= '<div class=" opacitymedium">';
$moreHtmlRef .= '<strong>' . $langs->trans('NotePublic') . ':</strong>';
$moreHtmlRef .= dol_string_nohtmltag($object->note_public, 1);
$moreHtmlRef .= '</div>';

saturne_banner_tab($objectsMetadata[$fromType]['object'], 'fromtype=' . $fromType . '&fromid', $linkBack, 1, 'rowid', ($fromType == 'productlot' ? 'batch' : 'ref'), $moreHtmlRef);
