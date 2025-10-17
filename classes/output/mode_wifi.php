<?php
namespace block_qr\output;

defined('MOODLE_INTERNAL') || die();

class mode_wifi implements \renderable, \templatable {
    private string $auth;
    private string $ssid;
    private string $passkey;
    private string $hidden;

    public function __construct(string $auth, string $ssid, string $passkey, string $hidden) {
        $this->auth = $auth;
        $this->ssid = $ssid;
        $this->passkey = $passkey;
        $this->hidden = $hidden;
    }

    public function export_for_template( $output): array {
        $content = "WIFI:T:{$this->auth};S:{$this->ssid};P:{$this->passkey};H:{$this->hidden};";
        return [
            'description'       => get_string('wifi', 'block_qr'),
            'qrurl'             => false,
            'qrcodecontent'     => $content,
            'wifissid'          => $this->ssid,
            'wifipasskey'       => $this->passkey,
            'wifiauthentication'=> $this->auth,
        ];
    }
}