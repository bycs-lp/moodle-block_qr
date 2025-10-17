<?php
namespace block_qr\output;

defined('MOODLE_INTERNAL') || die();

class mode_event implements \renderable, \templatable {
    private string $summary;
    private string $location;
    private int $start;
    private int $end;
    private int $allday;

    public function __construct(string $summary, string $location, int $start, int $end, int $allday) {
        $this->summary = $summary;
        $this->location = $location;
        $this->start = $start;
        $this->end = $end;
        $this->allday = $allday;
    }

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