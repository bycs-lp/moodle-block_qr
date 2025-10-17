<?php
namespace block_qr\output;

use moodle_url;
defined('MOODLE_INTERNAL') || die();

class mode_courseurl implements \renderable, \templatable {
    private int $courseid;
    private ?string $desc;

    public function __construct(int $courseid, ?string $desc) {
        $this->courseid = $courseid;
        $this->desc = $desc;
    }

    public function export_for_template( $output): array {
        $url = new moodle_url('/course/view.php', ['id' => $this->courseid]);
        return [
            'description'   => $this->desc,
            'qrurl'         => true,
            'qrcodecontent' => $url->out(false),
            'qrcodelink'    => $url->out(false),
        ];
    }
}