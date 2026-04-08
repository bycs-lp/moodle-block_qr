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
 * Upgrade script for block_qr.
 *
 * @package    block_qr
 * @copyright  2026 ISB Bayern
 * @author     Thomas Schönlein
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Handles upgrading this block.
 *
 * @param int $oldversion
 * @param stdClass $block
 * @return bool
 * @throws downgrade_exception
 * @throws dml_exception
 * @throws upgrade_exception
 */
function xmldb_block_qr_upgrade($oldversion, $block): bool {
    global $CFG;

    require_once($CFG->dirroot . '/blocks/qr/db/upgradelib.php');

    if ($oldversion < 2026031600) {
        block_qr_migrate_section_num_to_id();
        upgrade_block_savepoint(true, 2026031600, 'qr');
    }

    return true;
}
