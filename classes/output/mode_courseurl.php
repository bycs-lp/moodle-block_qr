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

namespace block_qr\output;

use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Class mode_courseurl
 * @package     block_qr
 * @copyright   2025 ISB Bayern
 * @author      Thomas Schönlein
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mode_courseurl implements \renderable, \templatable {
    /**
     * @var int $courseid Course ID
     */
    private int $courseid;
    /**
     * @var string|null $desc Description
     */
    private ?string $desc;

    /**
     * Constructor
     * @param int $courseid
     * @param string|null $desc
     */
    public function __construct(int $courseid, ?string $desc) {
        $this->courseid = $courseid;
        $this->desc = $desc;
    }

    /**
     * Export for template
     * @param \core_renderer $output renderer to create output
     * @return array
     * @throws \core\exception\moodle_exception
     */
    public function export_for_template($output): array {
        $url = new moodle_url('/course/view.php', ['id' => $this->courseid]);
        return [
            'description' => $this->desc,
            'qrurl' => true,
            'qrcodecontent' => $url->out(false),
            'qrcodelink' => $url->out(false),
        ];
    }
}