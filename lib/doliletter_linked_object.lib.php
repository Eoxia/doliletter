<?php
/* Copyright (C) 2026 EVARISK <technique@evarisk.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    lib/doliletter_linked_object.lib.php
 * \ingroup doliletter
 * \brief   Library files with functions for the objects DoliLetter may spread
 */

dol_include_once('/saturne/lib/object.lib.php');
dol_include_once('/saturne/lib/linked_object.lib.php');

// Configuration constant prefix driving the spread of every object.
if (!defined('DOLILETTER_SPREAD_LINK_CONST_PREFIX')) {
    define('DOLILETTER_SPREAD_LINK_CONST_PREFIX', 'DOLILETTER_SPREAD_LINK_');
}

// DoliLetter objects are never spread: the attendance sheet is the spread itself.
if (!defined('DOLILETTER_SPREAD_LINK_EXCLUDED_PREFIX')) {
    define('DOLILETTER_SPREAD_LINK_EXCLUDED_PREFIX', 'doliletter_');
}

// A spread carries no extrafield, so the usage counter of the shared admin template is the
// number of attendance sheets instead.
if (!defined('DOLILETTER_SPREAD_LINK_USAGE_KEY')) {
    define('DOLILETTER_SPREAD_LINK_USAGE_KEY', 'spread');
}

/**
 * Get the objects a spread may be attached to
 *
 * @return array<string, array<string, mixed>> Subset of saturne_get_objects_metadata()
 */
function doliletter_get_spreadable_objects(): array
{
    global $conf;

    if (!function_exists('saturne_get_objects_metadata') || !function_exists('saturne_filter_linkable_objects')) {
        return [];
    }

    if (empty($conf->cache['doliletterObjectsMetadata'])) {
        $conf->cache['doliletterObjectsMetadata'] = saturne_get_objects_metadata();
    }

    return saturne_filter_linkable_objects($conf->cache['doliletterObjectsMetadata'], [DOLILETTER_SPREAD_LINK_EXCLUDED_PREFIX]);
}

/**
 * Get the object types whose spread is enabled
 *
 * @return string[] List of enabled object types
 */
function doliletter_get_enabled_spread_object_types(): array
{
    if (!function_exists('saturne_get_enabled_linked_object_types')) {
        return [];
    }

    return saturne_get_enabled_linked_object_types(doliletter_get_spreadable_objects(), DOLILETTER_SPREAD_LINK_CONST_PREFIX);
}

/**
 * Measure how much each spreadable object is used
 *
 * Both counters hold the same number of attendance sheets : the shared admin template reads the
 * first one for the usage column and the second one to size the confirmation shown before a
 * spread is switched off.
 *
 * @return array<string, array{links: int, extrafields: array<string, int>}> objectType => usage counters
 */
function doliletter_get_spread_usage(): array
{
    global $db;

    $usage            = [];
    $objectTypeByLink = [];

    foreach (doliletter_get_spreadable_objects() as $objectType => $objectMetadata) {
        $usage[$objectType] = ['links' => 0, 'extrafields' => [DOLILETTER_SPREAD_LINK_USAGE_KEY => 0]];

        // An attendance sheet stores the link name of its object, the metadata key is only used
        // by the objects contributed through the Saturne metadata hook.
        $objectTypeByLink[$objectMetadata['link_name']] = $objectType;
        $objectTypeByLink[$objectType]                  = $objectType;
    }

    $sql  = 'SELECT object_type, COUNT(*) as nb FROM ' . MAIN_DB_PREFIX . 'doliletter_attendance_sheet';
    $sql .= ' WHERE entity IN (' . getEntity('doliletter_attendance_sheet') . ')';
    $sql .= ' GROUP BY object_type';

    $resql = $db->query($sql);
    if ($resql) {
        while ($obj = $db->fetch_object($resql)) {
            if (!isset($objectTypeByLink[$obj->object_type])) {
                continue;
            }

            $objectType = $objectTypeByLink[$obj->object_type];

            $usage[$objectType]['links']                                        += (int) $obj->nb;
            $usage[$objectType]['extrafields'][DOLILETTER_SPREAD_LINK_USAGE_KEY] += (int) $obj->nb;
        }
        $db->free($resql);
    }

    return $usage;
}

/**
 * Align the tabs and hooks on the enabled spreads
 *
 * Idempotent : replaying it converges to the same state whatever the starting point.
 * Must be called from a web request, see saturne_refresh_module_registrations().
 *
 * @return array{tabs: int, hooks: int, errors: int} Synchronisation report
 */
function doliletter_sync_spread_objects(): array
{
    if (!function_exists('saturne_refresh_module_registrations')) {
        return ['tabs' => 0, 'hooks' => 0, 'errors' => 1];
    }

    return saturne_refresh_module_registrations('doliletter', 'modDoliLetter');
}

/**
 * Enable the spread of every object that has no explicit choice yet
 *
 * Played once, so an existing installation keeps the Diffusion tab everywhere it had it and the
 * administrator is the one who trims the list. A spread explicitly disabled stays disabled.
 *
 * @return string[] List of object types enabled by this call
 */
function doliletter_run_spread_backward(): array
{
    global $conf, $db;

    $enabledObjectTypes = [];

    foreach (array_keys(doliletter_get_spreadable_objects()) as $objectType) {
        $constName = DOLILETTER_SPREAD_LINK_CONST_PREFIX . strtoupper($objectType);

        if (getDolGlobalString($constName) !== '') {
            continue;
        }

        dolibarr_set_const($db, $constName, 1, 'integer', 0, '', $conf->entity);
        $enabledObjectTypes[] = $objectType;
    }

    return $enabledObjectTypes;
}
