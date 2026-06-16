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

namespace local_pluginsview\local\api;

use cache;
use stdClass;

/**
 * Client for the Moodle Plugins Directory pluginfo.php end-point.
 *
 * Wraps the public download.moodle.org API, normalises the response into a
 * small result object and caches the answer via MUC. The plugin is found,
 * not found or temporarily unavailable; only the first two are cached.
 *
 * @package    local_pluginsview
 * @copyright  2026 Jean Lúcio
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moodle_directory_api {
    /** @var string The plugin was found in the directory. */
    const STATUS_FOUND = 'found';

    /** @var string The plugin is not published in the directory. */
    const STATUS_NOTFOUND = 'notfound';

    /** @var string The directory could not be reached. */
    const STATUS_UNAVAILABLE = 'unavailable';

    /** @var string Root of the public plugins directory API. */
    const APIROOT = 'https://download.moodle.org/api/1.3/pluginfo.php';

    /**
     * Returns directory information for the given plugin, using the cache when possible.
     *
     * @param string $component Frankenstyle name of the plugin.
     * @return stdClass Result object with a status property and, when found, the version metadata.
     */
    public function get_plugin_info(string $component): stdClass {
        $cache = cache::make('local_pluginsview', 'plugindirectorydata');

        $cached = $cache->get($component);
        if ($cached !== false) {
            return $cached;
        }

        $response = $this->fetch($component);
        if ($response === null) {
            return $this->make_result(self::STATUS_UNAVAILABLE);
        }

        if ((int) $response['httpcode'] === 404) {
            $result = $this->make_result(self::STATUS_NOTFOUND);
        } else if ((int) $response['httpcode'] === 200) {
            $result = $this->parse_pluginfo($component, (string) $response['body']);
        } else {
            $result = $this->make_result(self::STATUS_NOTFOUND);
        }

        $cache->set($component, $result);

        return $result;
    }

    /**
     * Returns cached directory results for several plugins without hitting the network.
     *
     * @param string[] $components Frankenstyle names to look up.
     * @return array<string, stdClass|null> Map of component to cached result, or null when not cached.
     */
    public function get_cached_many(array $components): array {
        if (empty($components)) {
            return [];
        }

        $cache = cache::make('local_pluginsview', 'plugindirectorydata');
        $results = $cache->get_many($components);

        foreach ($results as $component => $value) {
            if ($value === false) {
                $results[$component] = null;
            }
        }

        return $results;
    }

    /**
     * Parses a raw pluginfo.php JSON body into a normalised result object.
     *
     * @param string $component Frankenstyle name of the plugin.
     * @param string $body Raw JSON response body.
     * @return stdClass Result object with status found or notfound.
     */
    public function parse_pluginfo(string $component, string $body): stdClass {
        $data = json_decode($body);

        if (!is_object($data) || !isset($data->status) || $data->status !== 'OK' || empty($data->pluginfo)) {
            return $this->make_result(self::STATUS_NOTFOUND);
        }

        $pluginfo = $data->pluginfo;
        $version = !empty($pluginfo->version) && is_object($pluginfo->version) ? $pluginfo->version : null;

        $result = $this->make_result(self::STATUS_FOUND);
        $result->name = isset($pluginfo->name) ? (string) $pluginfo->name : null;
        $result->version = $version !== null && isset($version->version) ? (string) $version->version : null;
        $result->release = $version !== null && isset($version->release) ? (string) $version->release : null;
        $result->maturity = $version !== null && isset($version->maturity) ? (int) $version->maturity : null;
        $result->releasedat = isset($pluginfo->timelastreleased) ? (int) $pluginfo->timelastreleased : null;
        $result->pluginurl = 'https://moodle.org/plugins/' . $component;

        return $result;
    }

    /**
     * Performs the HTTP request to the directory API.
     *
     * Returns the HTTP status code and body, or null on a network or SSL error
     * so the caller can mark the plugin as temporarily unavailable.
     *
     * @param string $component Frankenstyle name of the plugin.
     * @return array|null Array with httpcode and body keys, or null on transport error.
     */
    protected function fetch(string $component): ?array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $params = [
            'plugin' => $component,
            'format' => 'json',
            'minversion' => 0,
            'branch' => $this->get_branch(),
        ];

        $curl = new \curl();
        $body = $curl->get(self::APIROOT, $params, [
            'CURLOPT_SSL_VERIFYHOST' => 2,
            'CURLOPT_SSL_VERIFYPEER' => true,
            'CURLOPT_TIMEOUT' => 20,
            'CURLOPT_CONNECTTIMEOUT' => 10,
        ]);

        if (!empty($curl->get_errno())) {
            return null;
        }

        $info = $curl->get_info();
        if (isset($info['ssl_verify_result']) && $info['ssl_verify_result'] != 0) {
            return null;
        }

        return [
            'httpcode' => $info['http_code'] ?? 0,
            'body' => $body,
        ];
    }

    /**
     * Returns the running Moodle core branch in the X.Y format expected by the API.
     *
     * @return string Branch such as 4.5 or 5.1.
     */
    protected function get_branch(): string {
        global $CFG;

        $branch = (string) $CFG->branch;
        if (strpos($branch, '.') !== false) {
            return $branch;
        }

        $intbranch = (int) $branch;
        if ($intbranch >= 310) {
            $major = (int) floor($intbranch / 100);
            $minor = $intbranch - 100 * $major;
            return $major . '.' . $minor;
        }

        return substr($branch, 0, -1) . '.' . substr($branch, -1);
    }

    /**
     * Builds an empty result object for the given status.
     *
     * @param string $status One of the STATUS_* constants.
     * @return stdClass Result object with null metadata fields.
     */
    protected function make_result(string $status): stdClass {
        $result = new stdClass();
        $result->status = $status;
        $result->name = null;
        $result->version = null;
        $result->release = null;
        $result->maturity = null;
        $result->releasedat = null;
        $result->pluginurl = null;

        return $result;
    }
}
