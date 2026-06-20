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
 * External web service to get updated plugin info.
 *
 * @package    local_pluginsview
 * @copyright  2026 Jean Lúcio
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pluginsview\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use local_pluginsview\local\api\moodle_directory_api;
use local_pluginsview\local\pluginsview_manager;

/**
 * External web service class.
 */
class get_plugin_info extends external_api {
    /**
     * Describes the parameters accepted by the web service.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'Frankenstyle name of the plugin.'),
        ]);
    }

    /**
     * Fetches fresh information for a plugin and returns it.
     *
     * @param string $component Frankenstyle name of the plugin.
     * @return array The enriched plugin information.
     */
    public static function execute(string $component): array {
        $params = self::validate_parameters(self::execute_parameters(), ['component' => $component]);

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/pluginsview:view', $context);

        $pluginman = \core_plugin_manager::instance();
        $plugininfo = $pluginman->get_plugin_info($params['component']);
        if ($plugininfo === null || $plugininfo->is_standard()) {
            throw new \moodle_exception('invalidplugin', 'local_pluginsview');
        }

        $api = new moodle_directory_api();
        $info = $api->refresh_plugin_info($params['component']);

        $manager = new pluginsview_manager();
        $status = $manager->determine_status($plugininfo->versiondb, $info);

        return [
            'component' => $params['component'],
            'status' => $status,
            'availableversion' => $info->version !== null ? (string)$info->version : '',
            'release' => $info->release !== null ? (string)$info->release : '',
            'releasedat' => $info->releasedat !== null ? (int)$info->releasedat : 0,
            'pluginurl' => $info->pluginurl !== null ? (string)$info->pluginurl : '',
        ];
    }

    /**
     * Describes the value returned by the web service.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'component' => new external_value(PARAM_COMPONENT, 'Frankenstyle name of the plugin.'),
            'status' => new external_value(PARAM_ALPHA, 'Status of the plugin (uptodate, outdated, notfound, pending).'),
            'availableversion' => new external_value(PARAM_RAW, 'Latest version number available.'),
            'release' => new external_value(PARAM_RAW, 'Latest release name available.'),
            'releasedat' => new external_value(PARAM_INT, 'Timestamp when latest version was released.'),
            'pluginurl' => new external_value(PARAM_URL, 'URL to the plugin page in the directory.'),
        ]);
    }
}
