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

namespace local_pluginsview\task;

use core\task\scheduled_task;
use local_pluginsview\local\api\moodle_directory_api;
use local_pluginsview\local\pluginsview_manager;

/**
 * Scheduled task that pre-warms the plugins directory cache.
 *
 * @package    local_pluginsview
 * @copyright  2026 Jean Lúcio
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class refresh_plugin_cache extends scheduled_task {
    /**
     * Returns the task name shown in the scheduled tasks admin screen.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('taskrefreshcache', 'local_pluginsview');
    }

    /**
     * Queries the directory for every additional plugin and stores the result in the cache.
     *
     * @return void
     */
    public function execute(): void {
        $manager = new pluginsview_manager();
        $api = new moodle_directory_api();
        $plugins = $manager->get_installed_plugins();

        $counts = [
            moodle_directory_api::STATUS_FOUND => 0,
            moodle_directory_api::STATUS_NOTFOUND => 0,
            moodle_directory_api::STATUS_UNAVAILABLE => 0,
        ];

        foreach ($plugins as $plugin) {
            $result = $api->refresh_plugin_info($plugin->component);
            $counts[$result->status]++;
        }

        mtrace('local_pluginsview: refreshed ' . count($plugins) . ' additional plugins ('
            . $counts[moodle_directory_api::STATUS_FOUND] . ' found, '
            . $counts[moodle_directory_api::STATUS_NOTFOUND] . ' not in directory, '
            . $counts[moodle_directory_api::STATUS_UNAVAILABLE] . ' unavailable).');
    }
}
