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

namespace local_pluginsview\local;

use core_plugin_manager;
use stdClass;

/**
 * Business logic: collects information about the installed plugins.
 *
 * @package    local_pluginsview
 * @copyright  2026 Jean Lúcio
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pluginsview_manager {
    /**
     * Returns every installed plugin with its locally available metadata.
     *
     * @return stdClass[] List of plugins, each with component, displayname, type and versiondb.
     */
    public function get_installed_plugins(): array {
        $pluginman = core_plugin_manager::instance();
        $plugins = [];

        foreach ($pluginman->get_plugins() as $typeplugins) {
            foreach ($typeplugins as $plugin) {
                $info = new stdClass();
                $info->component = $plugin->component;
                $info->displayname = $plugin->displayname;
                $info->type = $plugin->type;
                $info->versiondb = $plugin->versiondb;
                $plugins[] = $info;
            }
        }

        return $plugins;
    }
}
