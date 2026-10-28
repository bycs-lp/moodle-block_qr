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

/**
 * Tests for the geolocation mode of block_qr.
 *
 * @package     block_qr
 * @copyright   2026 ISB Bayern
 * @author      Dr. Peter Mayer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class mode_geolocation_test extends \advanced_testcase {
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * Non-numeric coordinates must not end up in the OpenStreetMap link.
     *
     * @covers \block_qr\output\mode_geolocation::export_for_template
     */
    public function test_export_for_template_rejects_non_numeric_coordinates(): void {
        global $PAGE;
        $this->resetAfterTest();

        $geolocation = new mode_geolocation('48" onmouseover="alert(document.domain)', '11.575', 'osm');
        $data = $geolocation->export_for_template($PAGE->get_renderer('core'));

        $this->assertFalse($data['qrurl']);
        $this->assertNull($data['qrcodelink']);
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * Numeric coordinates produce the OpenStreetMap link.
     *
     * @covers \block_qr\output\mode_geolocation::export_for_template
     */
    public function test_export_for_template_builds_osm_link(): void {
        global $PAGE;
        $this->resetAfterTest();

        $geolocation = new mode_geolocation('48.137', '-11.575', 'osm');
        $data = $geolocation->export_for_template($PAGE->get_renderer('core'));

        $this->assertTrue($data['qrurl']);
        $this->assertSame(
            'https://www.openstreetmap.org/?mlat=48.137&mlon=-11.575#map=10/48.137/-11.575',
            $data['qrcodelink']
        );
    }
}
