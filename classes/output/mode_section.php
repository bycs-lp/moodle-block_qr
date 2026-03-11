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

use core_course\modinfo;
use moodle_url;

/**
 * Class mode_section
 * @package     block_qr
 * @copyright   2025 ISB Bayern
 * @author      Thomas Schönlein
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mode_section implements \renderable, \templatable {
    /**
     * @param modinfo $modinfo
     * @param int $sectionid Section ID
     * @param bool $usercanedit User can edit
     */
    public function __construct(
        /** @var modinfo $modinfo */
        private readonly modinfo $modinfo,
        /** @var int $sectionid Section ID */
        private readonly int $sectionid,
        /** @var bool $usercanedit User can edit */
        private readonly bool $usercanedit,
    ) {
    }

    #[\Override]
    public function export_for_template($output): array {
        $sectioninfo = null;
        try {
            if (method_exists($this->modinfo, 'get_section_info_by_id')) {
                $sectioninfo = $this->modinfo->get_section_info_by_id($this->sectionid);
            }
        } catch (\Throwable $t) {
            $sectioninfo = null;
        }

        if (!$sectioninfo) {
            return [
                'qrurl' => false,
                'qrcodecontent' => '',
                'description' => get_string('errorsectionnotavailable', 'block_qr'),
            ];
        }

        if (!$sectioninfo->uservisible && !$this->usercanedit) {
            return [
                'qrurl' => false,
                'qrcodecontent' => '',
                'description' => get_string('errorsectionnotavailable', 'block_qr'),
            ];
        }

        if (!empty($sectioninfo->name)) {
            $description = $sectioninfo->name;
        } else {
            if ((int)$sectioninfo->section === 0) {
                $description = get_string('general');
            } else {
                $description = get_string('section') . ' ' . $sectioninfo->section;
            }
        }

        $format = course_get_format($sectioninfo->course);
        if ($format->get_format() === 'tiles') {
            $url = new moodle_url('/course/section.php', ['id' => $sectioninfo->id]);
        } else {
            $url = $format->get_view_url($sectioninfo, ['navigation' => true]);
        }

        return [
            'description' => $description,
            'qrurl' => true,
            'qrcodecontent' => $url->out(false),
            'qrcodelink' => $url->out(false),
        ];
    }
}
