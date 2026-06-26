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
 * Entry point: read-only overview of installed plugins.
 *
 * @package    local_pluginsview
 * @copyright  2026 Jean Lúcio
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/tablelib.php');

use local_pluginsview\local\pluginsview_manager;
use local_pluginsview\output\pluginsview_table;

require_login();
$context = context_system::instance();
require_capability('local/pluginsview:view', $context);

$download = optional_param('download', '', PARAM_ALPHA);

// Handle custom JSON export before setting up Moodle page output.
if ($download === 'json') {
    $manager = new pluginsview_manager();
    $plugins = $manager->get_enriched_plugins();

    $search = optional_param('search', '', PARAM_TEXT);
    $filtertype = optional_param('type', '', PARAM_ALPHANUMEXT);
    $filterstatus = optional_param('status', '', PARAM_ALPHANUMEXT);
    if ($search !== '' || $filtertype !== '' || $filterstatus !== '') {
        $plugins = array_filter($plugins, function ($p) use ($search, $filtertype, $filterstatus) {
            if ($search !== '') {
                $searchlower = core_text::strtolower($search);
                $namelower = core_text::strtolower($p->displayname);
                $componentlower = core_text::strtolower($p->component);
                if (strpos($namelower, $searchlower) === false && strpos($componentlower, $searchlower) === false) {
                    return false;
                }
            }
            if ($filtertype !== '' && $p->type !== $filtertype) {
                return false;
            }
            if ($filterstatus !== '' && $p->status !== $filterstatus) {
                return false;
            }
            return true;
        });
    }

    $tsort = optional_param('local-pluginsview-list_tsort', 'displayname', PARAM_ALPHANUMEXT);
    $tdir = optional_param('local-pluginsview-list_tdir', SORT_ASC, PARAM_INT);

    usort($plugins, function ($a, $b) use ($tsort, $tdir) {
        $vala = $a->$tsort ?? '';
        $valb = $b->$tsort ?? '';

        if (is_numeric($vala) && is_numeric($valb)) {
            $cmp = (int) $vala <=> (int) $valb;
        } else {
            $cmp = core_text::strcasecmp((string) $vala, (string) $valb);
        }

        return ($tdir == SORT_DESC) ? -$cmp : $cmp;
    });

    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="plugins_overview.json"');

    $exportdata = [];
    foreach ($plugins as $plugin) {
        $exportdata[] = [
            'name' => $plugin->displayname,
            'component' => $plugin->component,
            'type' => $plugin->type,
            'versiondb' => $plugin->versiondb,
            'availableversion' => $plugin->availableversion ?? '',
            'releasedat' => $plugin->releasedat ? date('Y-m-d H:i:s', $plugin->releasedat) : '',
            'status' => $plugin->status,
            'pluginurl' => $plugin->pluginurl ?? '',
        ];
    }
    echo json_encode($exportdata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    die();
}

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/pluginsview/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_pluginsview'));
$PAGE->set_heading(get_string('pluginname', 'local_pluginsview'));

$manager = new pluginsview_manager();
$plugins = $manager->get_enriched_plugins();

// Extract types for filter dropdown before filtering.
$types = [];
foreach ($plugins as $p) {
    $types[$p->type] = $p->type;
}
asort($types);

$search = optional_param('search', '', PARAM_TEXT);
$filtertype = optional_param('type', '', PARAM_ALPHANUMEXT);
$filterstatus = optional_param('status', '', PARAM_ALPHANUMEXT);

// Apply filters.
if ($search !== '' || $filtertype !== '' || $filterstatus !== '') {
    $plugins = array_filter($plugins, function ($p) use ($search, $filtertype, $filterstatus) {
        if ($search !== '') {
            $searchlower = core_text::strtolower($search);
            $namelower = core_text::strtolower($p->displayname);
            $componentlower = core_text::strtolower($p->component);
            if (strpos($namelower, $searchlower) === false && strpos($componentlower, $searchlower) === false) {
                return false;
            }
        }
        if ($filtertype !== '' && $p->type !== $filtertype) {
            return false;
        }
        if ($filterstatus !== '' && $p->status !== $filterstatus) {
            return false;
        }
        return true;
    });
}

// Build table.
$table = new pluginsview_table('local-pluginsview-list', $PAGE->url);
$table->is_downloading($download, 'plugins_overview', get_string('pluginname', 'local_pluginsview'));
$table->show_download_buttons_at([TABLE_P_BOTTOM]);

$isdownloading = $table->is_downloading();

if (!$isdownloading) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'local_pluginsview'));

    // Prepare template data.
    $templatedata = [
        'actionurl' => $PAGE->url->out(false),
        'search' => $search,
        'types' => [],
        'statuses' => [],
    ];

    $typeoptions = ['' => get_string('filterall', 'local_pluginsview')];
    foreach ($types as $t) {
        $typeoptions[$t] = $t;
    }
    foreach ($typeoptions as $val => $name) {
        $templatedata['types'][] = [
            'value' => $val,
            'name' => $name,
            'selected' => ($filtertype === (string)$val),
        ];
    }

    $statusoptions = [
        '' => get_string('filterall', 'local_pluginsview'),
        pluginsview_manager::STATUS_UPTODATE => get_string('statusuptodate', 'local_pluginsview'),
        pluginsview_manager::STATUS_OUTDATED => get_string('statusoutdated', 'local_pluginsview'),
        pluginsview_manager::STATUS_NOTFOUND => get_string('statusnotfound', 'local_pluginsview'),
        pluginsview_manager::STATUS_PENDING => get_string('statuspending', 'local_pluginsview'),
    ];
    foreach ($statusoptions as $val => $name) {
        $templatedata['statuses'][] = [
            'value' => $val,
            'name' => $name,
            'selected' => ($filterstatus === (string)$val),
        ];
    }

    $jsonurl = new moodle_url($PAGE->url, [
        'download' => 'json',
        'search' => $search,
        'type' => $filtertype,
        'status' => $filterstatus,
    ]);
    $templatedata['jsonurl'] = $jsonurl->out(false);

    echo $OUTPUT->render_from_template('local_pluginsview/pluginsview_page', $templatedata);

    $PAGE->requires->js_call_amd('local_pluginsview/pluginsview', 'init');
    $table->pagesize(50, count($plugins));
}

// Table setup reads query params for sorting/pagination.
$table->setup();

// Apply sorting to the array in memory.
$sortcolumns = $table->get_sort_columns();
if (!empty($sortcolumns)) {
    $sortfield = key($sortcolumns);
    $sortdir = current($sortcolumns);

    usort($plugins, function ($a, $b) use ($sortfield, $sortdir) {
        $vala = $a->$sortfield ?? '';
        $valb = $b->$sortfield ?? '';

        if (is_numeric($vala) && is_numeric($valb)) {
            $cmp = (int) $vala <=> (int) $valb;
        } else {
            $cmp = core_text::strcasecmp((string) $vala, (string) $valb);
        }

        return ($sortdir === SORT_DESC) ? -$cmp : $cmp;
    });
}

// Slice the array for pagination.
$displayplugins = $plugins;
if (!$isdownloading) {
    $start = $table->get_page_start();
    $size = $table->get_page_size();
    $displayplugins = array_slice($plugins, $start, $size);
}

// Feed data to table.
foreach ($displayplugins as $plugin) {
    $table->add_data($table->build_row($plugin));
}

// Finish output.
$table->finish_output();

if (!$isdownloading) {
    echo $OUTPUT->footer();
}
