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
     * The manager returns only additional plugins, excluding Moodle core plugins.
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

        $this->assertArrayHasKey('local_pluginsview', $bycomponent);
        $this->assertArrayNotHasKey('mod_forum', $bycomponent);

        $self = $bycomponent['local_pluginsview'];
        $this->assertSame('local', $self->type);
        $this->assertNotEmpty($self->displayname);
        $this->assertNotEmpty($self->versiondb);
    }

    /**
     * The status is pending when no cached directory info exists.
     *
     * @return void
     */
    public function test_determine_status_pending(): void {
        $manager = new pluginsview_manager();
        $this->assertSame(pluginsview_manager::STATUS_PENDING, $manager->determine_status('2026010100', null));
    }

    /**
     * The status reflects the comparison between installed and available versions.
     *
     * @return void
     */
    public function test_determine_status_version_comparison(): void {
        $manager = new pluginsview_manager();

        $outdated = (object) ['status' => api\moodle_directory_api::STATUS_FOUND, 'version' => '2026050100'];
        $this->assertSame(
            pluginsview_manager::STATUS_OUTDATED,
            $manager->determine_status('2026010100', $outdated)
        );

        $uptodate = (object) ['status' => api\moodle_directory_api::STATUS_FOUND, 'version' => '2026010100'];
        $this->assertSame(
            pluginsview_manager::STATUS_UPTODATE,
            $manager->determine_status('2026010100', $uptodate)
        );
    }

    /**
     * A plugin missing from the directory is reported as not found.
     *
     * @return void
     */
    public function test_determine_status_not_found(): void {
        $manager = new pluginsview_manager();
        $info = (object) ['status' => api\moodle_directory_api::STATUS_NOTFOUND, 'version' => null];
        $this->assertSame(pluginsview_manager::STATUS_NOTFOUND, $manager->determine_status('2026010100', $info));
    }
}
