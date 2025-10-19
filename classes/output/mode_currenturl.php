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
 * Class mode_currenturl
 *
 * @package     block_qr
 * @copyright   2025 ISB Bayern
 * @author      Thomas Schönlein
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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