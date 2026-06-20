# This file is part of Moodle - http://moodle.org/
#
# Moodle is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Moodle is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

# Behat tests for local_pluginsview overview page.
#
# @package    local_pluginsview
# @copyright  2026 Jean Lúcio
# @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_pluginsview
Feature: View installed additional plugins and use filters
  In order to check the status of additional plugins
  As an administrator
  I need to view the plugins view page, search, and apply filters

  Background:
    Given I log in as "admin"

  @javascript
  Scenario: Administrator can view, search, and filter additional plugins
    Given I am on homepage
    And I navigate to "local/pluginsview/index.php"
    Then I should see "Plugins view"
    And I should see "local_pluginsview"

    # Search filter.
    When I set the field "Search" to "pluginsview"
    And I press "Filter"
    Then I should see "local_pluginsview"

    # Type filter.
    When I set the field "Type" to "local"
    And I press "Filter"
    Then I should see "local_pluginsview"

    # Clear filters.
    When I click on "Clear"
    Then the field "Search" should match ""
