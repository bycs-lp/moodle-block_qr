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
 * Tests for the own content mode of block_qr.
 *
 * @package     block_qr
 * @copyright   2026 ISB Bayern
 * @author      Thomas Schönlein
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class mode_owncontent_test extends \advanced_testcase {
    /**
     * Only http and https may produce a link, the QR code keeps the raw value.
     *
     * @covers \block_qr\output\mode_owncontent::export_for_template
     * @dataProvider no_link_provider
     * @param string $raw Configured own content.
     */
    public function test_export_for_template_creates_no_link(string $raw): void {
        global $PAGE;
        $this->resetAfterTest();

        $owncontent = new mode_owncontent($raw);
        $data = $owncontent->export_for_template($PAGE->get_renderer('core'));

        $this->assertFalse($data['qrurl']);
        $this->assertNull($data['qrcodelink']);
        $this->assertSame($raw, $data['qrcodecontent']);
    }

    /**
     * Values that must not become a link.
     *
     * @return array
     */
    public static function no_link_provider(): array {
        return [
            'javascript with newline' => ['javascript://x%0Aalert(1)'],
            'javascript uppercase' => ['JAVASCRIPT:alert(1)'],
            'data scheme' => ['data:text/html,x'],
            'ftp scheme' => ['ftp://example.org'],
            'plain text' => ['Hallo Welt'],
            'empty' => [''],
        ];
    }

    /**
     * Valid http and https URLs stay clickable, regardless of the case of the scheme.
     *
     * @covers \block_qr\output\mode_owncontent::export_for_template
     * @dataProvider link_provider
     * @param string $raw Configured own content.
     */
    public function test_export_for_template_creates_link(string $raw): void {
        global $PAGE;
        $this->resetAfterTest();

        $owncontent = new mode_owncontent($raw);
        $data = $owncontent->export_for_template($PAGE->get_renderer('core'));

        $this->assertTrue($data['qrurl']);
        $this->assertSame($raw, $data['qrcodelink']);
        $this->assertSame($raw, $data['qrcodecontent']);
    }

    /**
     * Values that must become a link.
     *
     * @return array
     */
    public static function link_provider(): array {
        return [
            'https with query' => ['https://example.org/pfad?a=1&b=2'],
            'http' => ['http://example.org/Pfad?T=AbC'],
            'uppercase scheme' => ['HTTPS://example.org/x'],
        ];
    }
}
