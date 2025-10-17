<?php
namespace block_qr\output;

defined('MOODLE_INTERNAL') || die();

class mode_currenturl implements \renderable, \templatable {
    /** @var \moodle_url */
    private $url;

    public function __construct(\moodle_url $url) {
        $this->url = $url;
    }

    public function export_for_template($output): array {
        $href = $this->url->out(false);
        return [
            'description'   => get_string('thisurl', 'block_qr'),
            'qrurl'         => true,
            'qrcodecontent' => $href,
            'qrcodelink'    => $href,
        ];
    }
}