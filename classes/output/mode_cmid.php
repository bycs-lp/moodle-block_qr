<?php
namespace block_qr\output;

use moodle_url;
use core\exception\moodle_exception;
defined('MOODLE_INTERNAL') || die();

class mode_cmid implements \renderable, \templatable {
    private \course_modinfo $modinfo;
    private int $cmid;
    private bool $usercanedit;

    public function __construct(\course_modinfo $modinfo, int $cmid, bool $usercanedit) {
        $this->modinfo = $modinfo;
        $this->cmid = $cmid;
        $this->usercanedit = $usercanedit;
    }

    public function export_for_template( $output): array {
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