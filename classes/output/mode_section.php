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
 * Class mode_section
 * @package     block_qr
 * @copyright   2025 ISB Bayern
 * @author      Thomas Schönlein
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mode_section implements \renderable, \templatable {
    /**
     * @var \course_modinfo $modinfo Mode type
     */
    private \course_modinfo $modinfo;
    /**
     * @var int $sectionid Section ID
     */
    private int $sectionid;
    /**
     * @var bool $usercanedit User can edit
     */
    private bool $usercanedit;

    /**
     * mode_section constructor.
     * @param \course_modinfo $modinfo
     * @param int $sectionid
     * @param bool $usercanedit
     */
    public function __construct(\course_modinfo $modinfo, int $sectionid, bool $usercanedit) {
        $this->modinfo = $modinfo;
        $this->sectionid = $sectionid;
        $this->usercanedit = $usercanedit;
    }

    /**
     * Export for template
     * @param \core_renderer $output renderer to create output
     * @return array
     * @throws \coding_exception
     * @throws \core\exception\moodle_exception
     */
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
