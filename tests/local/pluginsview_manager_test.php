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

/**
 * Unit tests for the plugins view manager.
 *
 * @package    local_pluginsview
 * @copyright  2026 Jean Lúcio
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_pluginsview\local\pluginsview_manager
 */
final class pluginsview_manager_test extends \advanced_testcase {
    /**
     * The manager returns the installed plugins with the expected structure.
     *
     * @return void
     */
    public function test_get_installed_plugins(): void {
        $this->resetAfterTest();

        $manager = new pluginsview_manager();
        $plugins = $manager->get_installed_plugins();

        $this->assertNotEmpty($plugins);

        $bycomponent = [];
        foreach ($plugins as $plugin) {
            $bycomponent[$plugin->component] = $plugin;
        }

        $this->assertArrayHasKey('mod_forum', $bycomponent);
        $this->assertArrayHasKey('local_pluginsview', $bycomponent);

        $forum = $bycomponent['mod_forum'];
        $this->assertSame('mod', $forum->type);
        $this->assertNotEmpty($forum->displayname);
        $this->assertNotEmpty($forum->versiondb);
    }
}
