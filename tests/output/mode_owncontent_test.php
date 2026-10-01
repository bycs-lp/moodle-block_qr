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
 * @author      Dr. Peter Mayer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class mode_owncontent_test extends \advanced_testcase {
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * Only http and https URLs are exported as link target.
     *
     * @covers \block_qr\output\mode_owncontent::export_for_template
     */
    public function test_export_for_template_restricts_link_schemes(): void {
        global $PAGE;
        $output = $PAGE->get_renderer('core');

        $payload = 'javascript://example.com/%0aalert(document.domain)';
        $data = (new mode_owncontent($payload))->export_for_template($output);
        $this->assertFalse($data['qrurl']);
        $this->assertNull($data['qrcodelink']);
        $this->assertSame($payload, $data['qrcodecontent']);

        $data = (new mode_owncontent('https://example.com/path?a=1'))->export_for_template($output);
        $this->assertTrue($data['qrurl']);
        $this->assertSame('https://example.com/path?a=1', $data['qrcodelink']);
    }
}
