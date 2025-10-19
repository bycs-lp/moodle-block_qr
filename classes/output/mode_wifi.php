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

defined('MOODLE_INTERNAL') || die();

/**
 * Class mode_wifi
 *
 * @package     block_qr
 * @copyright   2025 ISB Bayern
 * @author      Thomas Schönlein
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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