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

use moodle_url;

/**
 * Renders the installed plugins as a flexible table.
 *
 * @package    local_pluginsview
 * @copyright  2026 Jean Lúcio
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pluginsview_table extends \flexible_table {
    /**
     * Builds the table with its local columns.
     *
     * @param string $uniqueid A unique identifier for the table.
     * @param moodle_url $baseurl The page URL used as the table base URL.
     */
    public function __construct(string $uniqueid, moodle_url $baseurl) {
        parent::__construct($uniqueid);

        $this->define_columns(['displayname', 'component', 'type', 'versiondb']);
        $this->define_headers([
            get_string('colname', 'local_pluginsview'),
            get_string('colcomponent', 'local_pluginsview'),
            get_string('coltype', 'local_pluginsview'),
            get_string('colversiondb', 'local_pluginsview'),
        ]);
        $this->define_baseurl($baseurl);
        $this->sortable(false);
        $this->collapsible(false);
    }
}
