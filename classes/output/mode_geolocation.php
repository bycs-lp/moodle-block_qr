<?php
namespace block_qr\output;

defined('MOODLE_INTERNAL') || die();

class mode_geolocation implements \renderable, \templatable {
    private string $lat;
    private string $lng;
    private ?string $linktype; // 'nolink' | 'osm'

    public function __construct(string $lat, string $lng, ?string $linktype) {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->linktype = $linktype;
    }

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