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

/**
 * Unit tests for the Moodle Plugins Directory API client.
 *
 * @package    local_pluginsview
 * @copyright  2026 Jean Lúcio
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_pluginsview\local\api\moodle_directory_api
 */
final class moodle_directory_api_test extends \advanced_testcase {
    /** @var string A realistic successful pluginfo.php response body. */
    const SAMPLE_OK = '{"status":"OK","apiver":"1.3","pluginfo":{"id":1036,"name":"Level Up XP",'
        . '"component":"block_xp","timelastreleased":1776740334,"version":{"id":41357,'
        . '"version":"2026042001","release":"20.0","maturity":200,"downloadurl":'
        . '"https://moodle.org/plugins/download.php/41357/block_xp.zip"}}}';

    /**
     * A successful response is parsed into a complete found result.
     *
     * @return void
     */
    public function test_parse_pluginfo_found(): void {
        $api = new moodle_directory_api();
        $result = $api->parse_pluginfo('block_xp', self::SAMPLE_OK);

        $this->assertSame(moodle_directory_api::STATUS_FOUND, $result->status);
        $this->assertSame('Level Up XP', $result->name);
        $this->assertSame('2026042001', $result->version);
        $this->assertSame('20.0', $result->release);
        $this->assertSame(200, $result->maturity);
        $this->assertSame(1776740334, $result->releasedat);
        $this->assertSame('https://moodle.org/plugins/block_xp', $result->pluginurl);
    }

    /**
     * A non-OK status or invalid JSON is treated as not found.
     *
     * @return void
     */
    public function test_parse_pluginfo_not_found(): void {
        $api = new moodle_directory_api();

        $this->assertSame(
            moodle_directory_api::STATUS_NOTFOUND,
            $api->parse_pluginfo('x_y', '{"status":"ERROR"}')->status
        );
        $this->assertSame(
            moodle_directory_api::STATUS_NOTFOUND,
            $api->parse_pluginfo('x_y', 'not json at all')->status
        );
    }

    /**
     * A found result is cached, so the HTTP layer is hit only once.
     *
     * @return void
     */
    public function test_get_plugin_info_caches_found(): void {
        $this->resetAfterTest();

        $api = $this->getMockBuilder(moodle_directory_api::class)
            ->onlyMethods(['fetch'])
            ->getMock();
        $api->expects($this->once())
            ->method('fetch')
            ->willReturn(['httpcode' => 200, 'body' => self::SAMPLE_OK]);

        $first = $api->get_plugin_info('block_xp');
        $second = $api->get_plugin_info('block_xp');

        $this->assertSame(moodle_directory_api::STATUS_FOUND, $first->status);
        $this->assertEquals($first, $second);
    }

    /**
     * A 404 response is normalised to not found and cached.
     *
     * @return void
     */
    public function test_get_plugin_info_not_found(): void {
        $this->resetAfterTest();

        $api = $this->getMockBuilder(moodle_directory_api::class)
            ->onlyMethods(['fetch'])
            ->getMock();
        $api->expects($this->once())
            ->method('fetch')
            ->willReturn(['httpcode' => 404, 'body' => '']);

        $first = $api->get_plugin_info('local_doesnotexist');
        $second = $api->get_plugin_info('local_doesnotexist');

        $this->assertSame(moodle_directory_api::STATUS_NOTFOUND, $first->status);
        $this->assertEquals($first, $second);
    }

    /**
     * A transport error yields unavailable and is not cached, so a retry happens.
     *
     * @return void
     */
    public function test_get_plugin_info_unavailable_not_cached(): void {
        $this->resetAfterTest();

        $api = $this->getMockBuilder(moodle_directory_api::class)
            ->onlyMethods(['fetch'])
            ->getMock();
        $api->expects($this->exactly(2))
            ->method('fetch')
            ->willReturn(null);

        $first = $api->get_plugin_info('mod_attendance');
        $second = $api->get_plugin_info('mod_attendance');

        $this->assertSame(moodle_directory_api::STATUS_UNAVAILABLE, $first->status);
        $this->assertSame(moodle_directory_api::STATUS_UNAVAILABLE, $second->status);
    }
}
