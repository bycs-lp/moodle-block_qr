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

use core\output\core_renderer;
use moodle_url;
use core\exception\moodle_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Class mode_cmid
 *
 * @package     block_qr
 * @copyright   2025 ISB Bayern
 * @author      Thomas Schönlein
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mode_cmid implements \renderable, \templatable {
    private \course_modinfo $modinfo;
    private int $cmid;
    private bool $usercanedit;

    /**
     * mode_cmid constructor.
     * @param \course_modinfo $modinfo
     * @param int $cmid
     * @param bool $usercanedit
     */
    public function __construct(\course_modinfo $modinfo, int $cmid, bool $usercanedit) {
        $this->modinfo = $modinfo;
        $this->cmid = $cmid;
        $this->usercanedit = $usercanedit;
    }

    /**
     * Export for template
     *
     * @param \core_renderer $output renderer for output
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public function export_for_template($output): array {
        try {
            $cm = $this->modinfo->get_cm($this->cmid);
        } catch (moodle_exception $e) {
            return [
                    'qrurl'         => false,
                    'qrcodecontent' => '',
                    'description'   => get_string('errormodulenotavailable', 'block_qr'),
            ];
        }

        global $DB;
        if (!$DB->record_exists('course_modules', ['id' => $this->cmid])) {
            return [
                'qrurl'         => false,
                'qrcodecontent' => '',
                'description'   => get_string('errormodulenotavailable', 'block_qr'),
            ];
        }

        if($cm->deletioninprogress){
            return [
                    'qrurl'         => false,
                    'qrcodecontent' => '',
                    'description'   => get_string('errormodulenotavailable', 'block_qr'),
            ];
        }

        if (!$cm->uservisible && !$this->usercanedit) {
            return [
                    'qrurl'         => false,
                    'qrcodecontent' => '',
                    'description'   => get_string('errormodulenotavailable', 'block_qr'),
            ];
        }

        if ($cm->url) {
            $url = $cm->url;
        } else {
            $url = new moodle_url('/course/view.php', [
                    'id' => $this->modinfo->get_course_id()
            ]);
            $url->set_anchor('module-' . $this->cmid);
        }

        return [
                'description'   => $cm->name,
                'qrurl'         => true,
                'qrcodecontent' => $url->out(false),
                'qrcodelink'    => $url->out(false),
        ];
    }
}