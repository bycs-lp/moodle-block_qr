<?php
namespace block_qr\output;

use moodle_url;

defined('MOODLE_INTERNAL') || die();

class mode_section implements \renderable, \templatable {
    private \course_modinfo $modinfo;
    private int $sectionid;
    private bool $usercanedit;

    public function __construct(\course_modinfo $modinfo, int $sectionid, bool $usercanedit) {
        $this->modinfo     = $modinfo;
        $this->sectionid   = $sectionid;
        $this->usercanedit = $usercanedit;
    }

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
                    'qrurl'         => false,
                    'qrcodecontent' => '',
                    'description'   => get_string('errorsectionnotavailable', 'block_qr'),
            ];
        }

        if (!$sectioninfo->uservisible && !$this->usercanedit) {
            return [
                    'qrurl'         => false,
                    'qrcodecontent' => '',
                    'description'   => get_string('errorsectionnotavailable', 'block_qr'),
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
        if($format->get_format()==='tiles') {
            $url = new moodle_url('/course/section.php', ['id' => $sectioninfo->id]);
        } else {
            $url = $format->get_view_url($sectioninfo, ['navigation' => true]);
        }

        return [
                'description'   => $description,
                'qrurl'         => true,
                'qrcodecontent' => $url->out(false),
                'qrcodelink'    => $url->out(false),
        ];
    }
}