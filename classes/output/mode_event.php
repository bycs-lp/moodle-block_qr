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
 * Class mode_event
 * @package     block_qr
 * @copyright   2025 ISB Bayern
 * @author      Thomas Schönlein
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mode_event implements \renderable, \templatable {
    /**
     * Constructor.
     * @param string $summary Description of the event
     * @param string $location Location of the event
     * @param int $start Start time of the event
     * @param int $end End time of the event
     * @param int $allday All day event
     */
    public function __construct(
        private readonly string $summary,
        private readonly string $location,
        private readonly int $start,
        private readonly int $end,
        private readonly int $allday,
    ) {}

    #[\Override]
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
                // All day event.
                $dateformat = get_string('strftimedate', 'langconfig');
                $qrcodecontent .= "DTSTART:" . date('Ymd', $this->start) . "\n";
                $qrcodecontent .= "DTEND:" . date('Ymd', $this->end) . "\n";

                if (date('ymd', $this->end) != date('ymd', $this->start)) {
                    // Different days.
                    $calendarstart = userdate($this->start, $dateformat) . " - ";
                    $calendarend = userdate($this->end, $dateformat);
                } else {
                    // Same day.
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
