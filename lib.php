<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Library functions for local_pluginsview.
 *
 * @package    local_pluginsview
 * @copyright  2026 Jean Lúcio
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Adds a flat-navigation entry to the plugin list for authorised users.
 *
 * @param global_navigation $navigation The global navigation tree.
 * @return void
 */
function local_pluginsview_extend_navigation(global_navigation $navigation): void {
    if (!has_capability('local/pluginsview:view', context_system::instance())) {
        return;
    }

    $node = $navigation->add(
        get_string('pluginname', 'local_pluginsview'),
        new moodle_url('/local/pluginsview/index.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'local_pluginsview',
        new pix_icon('i/report', '')
    );
    $node->showinflatnavigation = true;
}
