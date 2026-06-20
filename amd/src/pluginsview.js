/**
 * This file is part of Moodle - https://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * JavaScript AMD module for local_pluginsview background updates.
 *
 * @copyright  2026 Jean Lúcio
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/ajax', 'core/notification', 'core/str'], function(Ajax, Notification, Str) {
    return {
        init: async function() {
            const pendingElements = document.querySelectorAll('.local-pluginsview-status-pending');
            if (pendingElements.length === 0) {
                return;
            }

            try {
                const keys = [
                    {key: 'viewindirectory', component: 'local_pluginsview'},
                    {key: 'statusuptodate', component: 'local_pluginsview'},
                    {key: 'statusoutdated', component: 'local_pluginsview'},
                    {key: 'statusnotfound', component: 'local_pluginsview'},
                    {key: 'statuspending', component: 'local_pluginsview'}
                ];
                const strings = await Str.get_strings(keys);
                const stringMap = {
                    viewindirectory: strings[0],
                    uptodate: strings[1],
                    outdated: strings[2],
                    notfound: strings[3],
                    pending: strings[4]
                };

                pendingElements.forEach(async el => {
                    const component = el.dataset.component;
                    if (!component) {
                        return;
                    }

                    try {
                        const results = await Ajax.call([{
                            methodname: 'local_pluginsview_get_plugin_info',
                            args: {component}
                        }]);
                        const result = results[0];

                        // Update status cell.
                        el.textContent = stringMap[result.status] || result.status;
                        el.className = `local-pluginsview-status local-pluginsview-status-${result.status}`;
                        el.dataset.status = result.status;

                        // Find table row to update other cells.
                        const row = el.closest('tr');
                        if (!row) {
                            return;
                        }

                        const cells = row.cells;
                        if (cells.length < 8) {
                            return;
                        }

                        // Update availableversion cell (index 4).
                        if (result.release) {
                            cells[4].textContent = result.release;
                        } else if (result.availableversion) {
                            cells[4].textContent = result.availableversion;
                        } else {
                            cells[4].textContent = '-';
                        }

                        // Update releasedat cell (index 5).
                        if (result.releasedat) {
                            const date = new Date(result.releasedat * 1000);
                            cells[5].textContent = date.toLocaleDateString();
                        } else {
                            cells[5].textContent = '-';
                        }

                        // Update link cell (index 7).
                        if (result.pluginurl) {
                            const link = document.createElement('a');
                            link.href = result.pluginurl;
                            link.target = '_blank';
                            link.rel = 'noopener noreferrer';
                            link.textContent = stringMap.viewindirectory;
                            cells[7].innerHTML = '';
                            cells[7].appendChild(link);
                        } else {
                            cells[7].textContent = '-';
                        }
                    } catch (error) {
                        Notification.exception(error);
                    }
                });
            } catch (error) {
                Notification.exception(error);
            }
        }
    };
});
