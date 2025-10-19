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
 * Class mode_event
 * @package     block_qr
 * @copyright   2025 ISB Bayern
 * @author      Thomas Schönlein
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mode_event implements \renderable, \templatable {
    /**
     * @var string Discription of the event
     */
    private string $summary;
    /**
     * @var string Location of the event
     */
    private string $location;
    /**
     * @var int Start time of the event
     */
    private int $start;
    /**
     * @var int End time of the event
     */
    private int $end;
    /**
     * @var int All day event
     */
    private int $allday;

    /**
     * mode_event constructor.
     * @param string $summary
     * @param string $location
     * @param int $start
     * @param int $end
     * @param int $allday
     */
    public function __construct(string $summary, string $location, int $start, int $end, int $allday) {
        $this->summary = $summary;
        $this->location = $location;
        $this->start = $start;
        $this->end = $end;
        $this->allday = $allday;
    }

    /**
     * Export for template
     * @param \core_renderer $output renderer to create output
     * @return array
     * @throws \coding_exception
     */
    public function export_for_template($output): array {
        $qrcodecontent = "BEGIN:VCALENDAR\n";
        $qrcodecontent .= "VERSION:2.0\n";
        $qrcodecontent .= "BEGIN:VEVENT\n";
        $qrcodecontent .= "SUMMARY:" . $this->summary . "\n";
        $qrcodecontent .= "LOCATION:" . $this->location . "\n";

        $calendarstart = '';
        $calendarend = '';

        switch ($this->allday) {
            case 0:
                $dateformat = get_string('strftimedate', 'langconfig');
                $timeformat = get_string('strftimedatetime', 'langconfig');
                $qrcodecontent .= "DTSTART:" . date('Ymd\THis', $this->start) . "\n";
                $qrcodecontent .= "DTEND:" . date('Ymd\THis', $this->end) . "\n";

                if (date('ymd', $this->end) != date('ymd', $this->start)) {
                    $calendarstart = userdate($this->start, $dateformat) . " - ";
                    $calendarend = userdate($this->end, $dateformat);
                } else {
                    $calendarstart = userdate($this->start, $dateformat) . " - ";
                    $calendarend = userdate($this->end, $timeformat);
                }
                break;

            case 1:
                // All day event
                $dateformat = get_string('strftimedate', 'langconfig');
                $qrcodecontent .= "DTSTART:" . date('Ymd', $this->start) . "\n";
                $qrcodecontent .= "DTEND:" . date('Ymd', $this->end) . "\n";

                if (date('ymd', $this->end) != date('ymd', $this->start)) {
                    // Different days
                    $calendarstart = userdate($this->start, $dateformat) . " - ";
                    $calendarend = userdate($this->end, $dateformat);
                } else {
                    // Same day
                    $calendarstart = userdate($this->start, $dateformat);
                    $calendarend = '';
                }
                break;
        }

        $qrcodecontent .= "END:VEVENT\n";
        $qrcodecontent .= "END:VCALENDAR\n";

        return [
            'qrcodecontent' => $qrcodecontent,
            'qrurl' => false,
            'calendarsummary' => !empty($this->summary) ? $this->summary : null,
            'calendarlocation' => !empty($this->location) ? $this->location : null,
            'calendarstart' => $calendarstart,
            'calendarend' => $calendarend,
        ];
    }
}