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
use core\exception\moodle_exception;

/**
 * Class mode_cmid
 *
 * @package     block_qr
 * @copyright   2025 ISB Bayern
 * @author      Thomas Schönlein
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mode_cmid implements \renderable, \templatable {
    /**
     * Constructor.
     * @param \course_modinfo $modinfo
     * @param int $cmid Module ID
     * @param bool $usercanedit User can edit
     */
    public function __construct(
        /** @var \course_modinfo $modinfo */
        private readonly \course_modinfo $modinfo,
        /** @var int $cmid Module ID */
        private readonly int $cmid,
        /** @var bool $usercanedit User can edit */
        private readonly bool $usercanedit,
    ) {
    }

    #[\Override]
    public function export_for_template($output): array {
        $errorreturn = [
            'qrurl' => false,
            'qrcodecontent' => '',
            'description' => get_string('errormodulenotavailable', 'block_qr'),
        ];

        try {
            $cm = $this->modinfo->get_cm($this->cmid);
        } catch (moodle_exception $e) {
            return $errorreturn;
        }

        if ($cm->deletioninprogress) {
            return $errorreturn;
        }

        if (!$cm->uservisible && !$this->usercanedit) {
            return $errorreturn;
        }

        if ($cm->url) {
            $url = $cm->url;
        } else {
            $url = new moodle_url('/course/view.php', [
                'id' => $this->modinfo->get_course_id(),
            ]);
            $url->set_anchor('module-' . $this->cmid);
        }

        return [
            'description' => $cm->name,
            'qrurl' => true,
            'qrcodecontent' => $url->out(false),
            'qrcodelink' => $url->out(false),
        ];
    }
}
