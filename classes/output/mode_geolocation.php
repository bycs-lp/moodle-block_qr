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
 * Class mode_geolocation
 * @package     block_qr
 * @copyright   2025 ISB Bayern
 * @author      Thomas Schönlein
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mode_geolocation implements \renderable, \templatable {
    private string $lat;
    private string $lng;
    private ?string $linktype; // 'nolink' | 'osm'

    /**
     * mode_geolocation constructor.
     * @param string $lat
     * @param string $lng
     * @param string|null $linktype
     */
    public function __construct(string $lat, string $lng, ?string $linktype) {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->linktype = $linktype;
    }

    /**
     * Export for template
     * @param \core_renderer $output renderer to create output
     * @return array
     * @throws \coding_exception
     */
    public function export_for_template($output): array {
        $geo = "geo:{$this->lat},{$this->lng}";
        $link = null;
        if ($this->linktype === 'osm') {
            $link = 'https://www.openstreetmap.org/?mlat='.$this->lat.'&mlon='.$this->lng.'#map=10/'.$this->lat.'/'.$this->lng;
        }
        return [
            'description'   => get_string('geolocation', 'block_qr'),
            'qrurl'         => $link !== null,
            'qrcodecontent' => $geo,
            'qrcodelink'    => $link,
            'geocoordinates'=> "{$this->lat}, {$this->lng}",
        ];
    }
}