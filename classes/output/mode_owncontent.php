<?php
namespace block_qr\output;

defined('MOODLE_INTERNAL') || die();

class mode_owncontent implements \renderable, \templatable {
    private string $raw;

    public function __construct(string $raw) {
        $this->raw = trim($raw);
    }

    public function export_for_template( $output): array {
        $isurl = filter_var($this->raw, FILTER_VALIDATE_URL) !== false;
        return [
            'description'   => '',
            'qrurl'         => $isurl,
            'qrcodecontent' => $this->raw,
            'qrcodelink'    => $isurl ? $this->raw : null,
        ];
    }
}