<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Upgrade library for block_qr.
 *
 * @package    block_qr
 * @copyright  2026 ISB Bayern
 * @author     Thomas Schönlein
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Migrates legacy section-number based internal links to section ids.
 *
 * @return int Number of migrated block instances
 * @throws coding_exception
 * @throws dml_exception
 */
function block_qr_migrate_section_num_to_id(): int {
    global $CFG, $DB, $PAGE;

    require_once($CFG->libdir . '/blocklib.php');
    require_once($CFG->libdir . '/pagelib.php');

    $updated = 0;
    $page = $PAGE ?? new moodle_page();
    $instances = $DB->get_records('block_instances', ['blockname' => 'qr']);

    foreach ($instances as $instance) {
        try {
            $courseid = block_qr_get_courseid_for_block_instance($instance);
            if ($courseid === null) {
                continue;
            }

            $block = block_instance('qr', $instance, $page);
            if (
                empty($block->config->options)
                || $block->config->options !== 'internalcontent'
                || empty($block->config->internal)
                || !preg_match('/^section=(\d+)$/', (string) $block->config->internal, $matches)
            ) {
                continue;
            }

            $sectionid = block_qr_get_migrated_section_id($courseid, (int) $matches[1]);
            if ($sectionid === null || $sectionid === (int) $matches[1]) {
                continue;
            }

            $block->config->internal = 'section=' . $sectionid;
            $block->instance_config_save($block->config);
            $updated++;
        } catch (\Exception $e) {
            debugging(
                'block_qr: Failed to migrate instance ' . $instance->id . ': ' . $e->getMessage(),
                DEBUG_DEVELOPER
            );
        }
    }

    return $updated;
}

/**
 * Resolves the course id for a block instance parent context.
 *
 * @package    block_qr
 * @param stdClass $instance Block instance record
 * @return ?int
 * @throws dml_exception
 */
function block_qr_get_courseid_for_block_instance(stdClass $instance): ?int {
    global $DB;

    $parentcontext = \core\context::instance_by_id($instance->parentcontextid, IGNORE_MISSING);
    if (!$parentcontext) {
        return null;
    }

    if ($parentcontext->contextlevel === CONTEXT_COURSE) {
        return (int) $parentcontext->instanceid;
    }

    if ($parentcontext->contextlevel === CONTEXT_MODULE) {
        $courseid = $DB->get_field('course_modules', 'course', ['id' => $parentcontext->instanceid], IGNORE_MISSING);
        return $courseid === false ? null : (int) $courseid;
    }

    return null;
}

/**
 * Resolves the new section id for a legacy stored section number.
 *
 * Already migrated section ids are left untouched.
 *
 * @package    block_qr
 * @param int $courseid Course id
 * @param int $storedvalue Stored section reference
 * @return ?int
 * @throws dml_exception
 */
function block_qr_get_migrated_section_id(int $courseid, int $storedvalue): ?int {
    global $DB;

    $sectionbyid = $DB->get_record(
        'course_sections',
        ['id' => $storedvalue, 'course' => $courseid],
        'id, section',
        IGNORE_MISSING
    );
    if ($sectionbyid && (int) $sectionbyid->section !== $storedvalue) {
        return null;
    }

    $sectionbynumber = $DB->get_record(
        'course_sections',
        ['course' => $courseid, 'section' => $storedvalue],
        'id',
        IGNORE_MISSING
    );
    if (!$sectionbynumber) {
        return null;
    }

    return (int) $sectionbynumber->id;
}
