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
use local_pluginsview\local\api\moodle_directory_api;
use stdClass;

/**
 * Business logic: collects and enriches information about the installed plugins.
 *
 * @package    local_pluginsview
 * @copyright  2026 Jean Lúcio
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pluginsview_manager {
    /** @var string The directory has not been queried yet for this plugin. */
    const STATUS_PENDING = 'pending';

    /** @var string The installed version matches the latest available version. */
    const STATUS_UPTODATE = 'uptodate';

    /** @var string A newer version is available in the directory. */
    const STATUS_OUTDATED = 'outdated';

    /** @var string The plugin is not published in the directory. */
    const STATUS_NOTFOUND = 'notfound';

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

    /**
     * Returns the installed plugins enriched with directory data read from the cache.
     *
     * Network is never hit here: plugins missing from the cache are reported as
     * pending so they can be resolved lazily or by the scheduled task.
     *
     * @return stdClass[] Plugins with the extra status, availableversion, release, releasedat and pluginurl fields.
     */
    public function get_enriched_plugins(): array {
        $plugins = $this->get_installed_plugins();
        $components = array_map(static fn(stdClass $plugin): string => $plugin->component, $plugins);

        $api = new moodle_directory_api();
        $cached = $api->get_cached_many($components);

        foreach ($plugins as $plugin) {
            $info = $cached[$plugin->component] ?? null;
            $plugin->status = $this->determine_status($plugin->versiondb, $info);
            $plugin->availableversion = $info->version ?? null;
            $plugin->release = $info->release ?? null;
            $plugin->releasedat = $info->releasedat ?? null;
            $plugin->pluginurl = $info->pluginurl ?? null;
        }

        return $plugins;
    }

    /**
     * Determines the display status for a plugin given its cached directory info.
     *
     * @param string|null $versiondb The installed version number.
     * @param stdClass|null $info The cached directory result, or null when not cached.
     * @return string One of the STATUS_* constants.
     */
    public function determine_status(?string $versiondb, ?stdClass $info): string {
        if ($info === null) {
            return self::STATUS_PENDING;
        }

        if ($info->status === moodle_directory_api::STATUS_NOTFOUND) {
            return self::STATUS_NOTFOUND;
        }

        if ($info->status === moodle_directory_api::STATUS_FOUND) {
            if ($info->version !== null && $versiondb !== null && (int) $info->version > (int) $versiondb) {
                return self::STATUS_OUTDATED;
            }
            return self::STATUS_UPTODATE;
        }

        return self::STATUS_PENDING;
    }
}
