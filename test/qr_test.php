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

use PHPUnit\Framework\Attributes\DataProvider;

defined('MOODLE_INTERNAL') || die();

/**
 * Block QR functions unit tests
 *
 * @package     block_qr
 * @copyright   2025 ISB Bayern
 * @author      Thomas Schönlein
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class qr_test extends \advanced_testcase {

    private $course;
    private $sectionid;
    private $cmid;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        date_default_timezone_set('UTC');

        $gen = self::getDataGenerator();
        $this->course = $gen->create_course(['format' => 'topics']);

        $modinfo = get_fast_modinfo($this->course->id);
        $secinfo = $modinfo->get_section_info(1);
        $this->sectionid = $secinfo->id;

        $page = $gen->create_module('page', [
            'course'  => $this->course->id,
            'section' => 1,
            'name'    => 'Testseite',
        ]);
        $this->cmid = $page->cmid;

        global $CFG;
        require_once($CFG->dirroot.'/lib/blocklib.php');
    }

    /**
     * Tests the QR-Block content
     * @param string $mode QR-Block mode
     * @param array $config QR-Block data
     * @param moodle_url $pageurl Input URL
     * @param array $expect Expected output
     */
    #[DataProvider('get_content_provider')]
    public function test_get_content(string $mode, array $config, \moodle_url $pageurl, array $expect): void {
        global $PAGE;

        $PAGE->set_course($this->course);
        $PAGE->set_url($pageurl);

        if (!array_key_exists('internal', $config) || !is_string($config['internal'])) {
            $config['internal'] = '';
        }

        $config['internal'] = strtr($config['internal'], [
            '__COURSEID__'  => (string)$this->course->id,
            '__SECTIONID__' => (string)$this->sectionid,
            '__CMID__'      => (string)$this->cmid,
        ]);
        foreach ($expect['contains'] as $key => $value) {
            $expect['contains'][$key] = strtr($value, [
                '__COURSEID__'  => (string)$this->course->id,
                '__SECTIONID__' => (string)$this->sectionid,
                '__CMID__'      => (string)$this->cmid,
            ]);
        }

        $config['options'] = $mode;
        $block = block_instance('qr');
        $block->instance = (object)['id' => 1];
        $block->config   = (object)$config;
        $block->page     = $PAGE;
        $block->context  = \context_system::instance();

        $content = $block->get_content();

        $this->assertNotEmpty($content->text, 'HTML should not be empty');

        foreach ($expect['contains'] as $test) {
            $this->assertStringContainsString($test, $content->text);
        }
    }

    /**
     * dataProvider for test_get_content
     * @return array Sets of data for test_get_content
     */
    public static function get_content_provider(): array {
        return [
            'currenturl' => [
                'currenturl',
                [],
                new \moodle_url('/my/courses.php'),
                [
                    'contains' => [
                        (new \moodle_url('/my/courses.php'))->out(false),
                        get_string('thisurl', 'block_qr'),
                    ],
                ],
            ],
            'courseurl' => [
                'courseurl',
                ['courseid' => '__COURSEID__'],
                new \moodle_url('/course/view.php'),
                [
                    'contains' => [
                        (new \moodle_url('/course/view.php', ['id' => '__COURSEID__']))->out(false),
                    ],
                ],
            ],
            'owncontent_url' => [
                'owncontent',
                ['owncontent' => 'https://example.com/abc'],
                new \moodle_url('/my/courses.php'),
                [
                    'contains' => ['https://example.com/abc'],
                ],
            ],
            'owncontent_text' => [
                'owncontent',
                ['owncontent' => 'nur Text'],
                new \moodle_url('/my/courses.php'),
                [
                    'contains' => ['Show in full screen'],
                ],
            ],
            'event' => [
                'event',
                [
                    'event_summary'  => 'Hackathon',
                    'event_location' => 'HS06',
                    'event_start'    => gmmktime(10,0,0,10,15,2025),
                    'event_end'      => gmmktime(12,0,0,10,17,2025),
                    'allday'         => 0,
                ],
                new \moodle_url('/my/courses.php'),
                [
                    'contains' => ['Hackathon', 'HS06'],
                ],
            ],
            'geolocation_osm' => [
                'geolocation',
                ['geolocation_br' => '48.137', 'geolocation_lng' => '11.575', 'link' => 'osm'],
                new \moodle_url('/my/courses.php'),
                [
                    'contains' => ['openstreetmap.org', '48.137', '11.575'],
                ],
            ],
            'wifi' => [
                'wifi',
                [
                    'wifissid'           => 'SchoolNet',
                    'wifipasskey'        => 'secret',
                    'wifiauthentication' => 'WPA',
                    'wifissidoptions'    => 'false',
                ],
                new \moodle_url('/my/courses.php'),
                [
                    'contains' => ['SchoolNet', 'WPA', 'secret'],
                ],
            ],
        ];
    }
}
