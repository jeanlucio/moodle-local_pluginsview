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

namespace local_pluginsview\output;

use html_writer;
use moodle_url;
use stdClass;

/**
 * Renders the installed plugins as a flexible table.
 *
 * @package    local_pluginsview
 * @copyright  2026 Jean Lúcio
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pluginsview_table extends \flexible_table {
    /**
     * Builds the table with its columns and headers.
     *
     * @param string $uniqueid A unique identifier for the table.
     * @param moodle_url $baseurl The page URL used as the table base URL.
     */
    public function __construct(string $uniqueid, moodle_url $baseurl) {
        parent::__construct($uniqueid);

        $this->define_columns([
            'displayname', 'component', 'type', 'versiondb',
            'availableversion', 'releasedat', 'status', 'link',
        ]);
        $this->define_headers([
            get_string('colname', 'local_pluginsview'),
            get_string('colcomponent', 'local_pluginsview'),
            get_string('coltype', 'local_pluginsview'),
            get_string('colversiondb', 'local_pluginsview'),
            get_string('colavailableversion', 'local_pluginsview'),
            get_string('colreleasedate', 'local_pluginsview'),
            get_string('colstatus', 'local_pluginsview'),
            get_string('collink', 'local_pluginsview'),
        ]);
        $this->define_baseurl($baseurl);
        $this->sortable(false);
        $this->collapsible(false);
    }

    /**
     * Builds the row cells for an enriched plugin record.
     *
     * @param stdClass $plugin Enriched plugin data from the manager.
     * @return string[] The formatted, escaped cells for the row.
     */
    public function build_row(stdClass $plugin): array {
        return [
            format_string($plugin->displayname),
            s($plugin->component),
            s($plugin->type),
            s($plugin->versiondb ?? '-'),
            $this->col_availableversion($plugin),
            $this->col_releasedat($plugin),
            $this->col_status($plugin),
            $this->col_link($plugin),
        ];
    }

    /**
     * Formats the available version cell.
     *
     * @param stdClass $plugin Enriched plugin data.
     * @return string The available release or version, or a dash.
     */
    protected function col_availableversion(stdClass $plugin): string {
        if (!empty($plugin->release)) {
            return s($plugin->release);
        }
        if (!empty($plugin->availableversion)) {
            return s($plugin->availableversion);
        }
        return '-';
    }

    /**
     * Formats the release date cell.
     *
     * @param stdClass $plugin Enriched plugin data.
     * @return string The localised release date, or a dash.
     */
    protected function col_releasedat(stdClass $plugin): string {
        if (empty($plugin->releasedat)) {
            return '-';
        }
        return userdate($plugin->releasedat, get_string('strftimedate', 'core_langconfig'));
    }

    /**
     * Formats the status cell as a labelled marker carrying the component for lazy resolution.
     *
     * @param stdClass $plugin Enriched plugin data.
     * @return string The status marker HTML.
     */
    protected function col_status(stdClass $plugin): string {
        return html_writer::span(
            get_string('status' . $plugin->status, 'local_pluginsview'),
            'local-pluginsview-status local-pluginsview-status-' . $plugin->status,
            ['data-component' => $plugin->component, 'data-status' => $plugin->status]
        );
    }

    /**
     * Formats the directory link cell.
     *
     * @param stdClass $plugin Enriched plugin data.
     * @return string An external link to the directory page, or a dash.
     */
    protected function col_link(stdClass $plugin): string {
        if (empty($plugin->pluginurl)) {
            return '-';
        }
        return html_writer::link(
            new moodle_url($plugin->pluginurl),
            get_string('viewindirectory', 'local_pluginsview'),
            ['target' => '_blank', 'rel' => 'noopener noreferrer']
        );
    }
}
