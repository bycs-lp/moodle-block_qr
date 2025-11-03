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

/**
 * Block QR functions unit tests
 *
 * @package     block_qr
 * @copyright   2025 ISB Bayern
 * @author      Thomas Schönlein
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class qr_test extends \advanced_testcase {
    /** @var \stdClass $course */
    private $course;
    /** @var int $sectionid */
    private $sectionid;
    /** @var int $sectionnum */
    private $sectionnum;
    /** @var int $secondsectionid */
    private $secondsectionid;
    /** @var int $cmid */
    private $cmid;
    /** @var \stdClass $student */
    private $student;

    /**
     * Setup test environment
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        date_default_timezone_set('UTC');

        global $CFG;

        $gen = self::getDataGenerator();
        $this->course = $gen->create_course(['format' => 'topics']);

        require_once($CFG->dirroot . '/lib/blocklib.php');
        require_once($CFG->dirroot . '/course/lib.php');

        $modinfo = get_fast_modinfo($this->course->id);
        $sectioninfo = $modinfo->get_section_info(1);

        course_update_section($this->course, $sectioninfo, ['name' => 'Testabschnitt 1']);
        rebuild_course_cache($this->course->id, true);

        $modinfo = get_fast_modinfo($this->course->id);
        $secinfo = $modinfo->get_section_info_by_id($sectioninfo->id);
        $this->sectionid = $secinfo->id;
        $this->sectionnum = $secinfo->section;
        $page = $gen->create_module('page', [
            'course' => $this->course->id,
            'section' => 1,
            'name' => 'Testseite',
        ]);
        $this->cmid = $page->cmid;

        $secondsection = course_create_section($this->course, 0);
        $this->secondsectionid = $secondsection->id;

        $this->student = $gen->create_user();
        $gen->enrol_user($this->student->id, $this->course->id, 'student');
    }

    /**
     * Tests the QR-Block content output for the provided data set.
     *
     * @dataProvider get_content_provider
     * @param string $mode QR-Block mode
     * @param array $config QR-Block data
     * @param \moodle_url $pageurl Input URL
     * @param array $expect Expected output
     * @return void
     */
    public function test_get_content(string $mode, array $config, \moodle_url $pageurl, array $expect): void {
        global $PAGE;

        $PAGE->set_course($this->course);
        $PAGE->set_url($pageurl);

        $replacements = [
            '__COURSEID__' => (string)$this->course->id,
            '__SECTIONID__' => (string)$this->sectionid,
            '__CMID__' => (string)$this->cmid,
        ];

        foreach ($config as $key => $value) {
            if (is_string($value)) {
                $config[$key] = strtr($value, $replacements);
            }
        }

        if (!array_key_exists('internal', $config) || !is_string($config['internal'])) {
            $config['internal'] = '';
        } else {
            $config['internal'] = strtr($config['internal'], $replacements);
        }

        if (!empty($expect['contains']) && is_array($expect['contains'])) {
            foreach ($expect['contains'] as $index => $value) {
                if (is_string($value)) {
                    $expect['contains'][$index] = strtr($value, $replacements);
                }
            }
        }

        $config['options'] = $mode;
        $block = $this->create_block_with_config($config, \context_system::instance());

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
        $urlcourse = new \moodle_url('/course/view.php');
        $urlmycourses = new \moodle_url('/my/courses.php');

        $cases = [
            'currenturl' => [
                'mode' => 'currenturl',
                'config' => [],
                'pageurl' => $urlmycourses,
                'expect' => [
                    'contains' => [
                        $urlmycourses->out(false),
                        get_string('thisurl', 'block_qr'),
                    ],
                ],
            ],
            'courseurl' => [
                'mode' => 'courseurl',
                'config' => ['courseid' => '__COURSEID__'],
                'pageurl' => $urlcourse,
                'expect' => [
                    'contains' => [
                        (new \moodle_url('/course/view.php', ['id' => '__COURSEID__']))->out(false),
                    ],
                ],
            ],
            'owncontent_url' => [
                'mode' => 'owncontent',
                'config' => ['owncontent' => 'https://example.com/abc'],
                'pageurl' => $urlmycourses,
                'expect' => [
                    'contains' => ['https://example.com/abc'],
                ],
            ],
            'owncontent_text' => [
                'mode' => 'owncontent',
                'config' => ['owncontent' => 'nur Text'],
                'pageurl' => $urlmycourses,
                'expect' => [
                    'contains' => ['Show in full screen'],
                ],
            ],
            'event' => [
                'mode' => 'event',
                'config' => [
                    'event_summary' => 'Hackathon',
                    'event_location' => 'HS06',
                    'event_start' => gmmktime(10, 0, 0, 10, 15, 2025),
                    'event_end' => gmmktime(12, 0, 0, 10, 17, 2025),
                    'allday' => 0,
                ],
                'pageurl' => $urlmycourses,
                'expect' => [
                    'contains' => ['Hackathon', 'HS06'],
                ],
            ],
            'geolocation_osm' => [
                'mode' => 'geolocation',
                'config' => [
                    'geolocation_br' => '48.137',
                    'geolocation_lng' => '11.575',
                    'link' => 'osm',
                ],
                'pageurl' => $urlmycourses,
                'expect' => [
                    'contains' => ['openstreetmap.org', '48.137', '11.575'],
                ],
            ],
            'wifi' => [
                'mode' => 'wifi',
                'config' => [
                    'wifissid' => 'SchoolNet',
                    'wifipasskey' => 'secret',
                    'wifiauthentication' => 'WPA',
                    'wifissidoptions' => 'false',
                ],
                'pageurl' => $urlmycourses,
                'expect' => [
                    'contains' => ['SchoolNet', 'WPA', 'secret'],
                ],
            ],
        ];

        foreach ($cases as $name => $case) {
            $cases[$name] = [
                $case['mode'],
                $case['config'],
                $case['pageurl'],
                $case['expect'],
            ];
        }

        return $cases;
    }

    /**
     * Exercises the internal content handling using the supplied scenario.
     *
     * @dataProvider internalcontent_provider
     * @param string $mode Target type (cmid or section)
     * @param string $action Action performed (move, hide, delete)
     * @param bool $usercanedit Whether user can edit
     * @param bool $expectvisible Expectation if QR link should be shown
     * @param string|null $errorexpected Expected error string identifier
     * @param string|null $expectdescription Expected description placeholder/value
     * @return void
     */
    public function test_get_content_internal(
        string $mode,
        string $action,
        bool $usercanedit,
        bool $expectvisible,
        ?string $errorexpected,
        ?string $expectdescription
    ): void {
        global $PAGE, $USER, $DB;

        $coursecontext = \context_course::instance($this->course->id);

        $PAGE->set_course($this->course);
        $PAGE->set_url(new \moodle_url('/course/view.php', ['id' => $this->course->id]));
        $PAGE->set_context($coursecontext);

        $this->setAdminUser();
        $USER->editing = 1;

        if ($mode === 'cmid') {
            if ($action === 'move') {
                $modinfo = get_fast_modinfo($this->course->id);
                $cm = $modinfo->get_cm($this->cmid);
                $section = $DB->get_record('course_sections', ['id' => $this->secondsectionid], '*', MUST_EXIST);
                moveto_module($cm, $section);
            } else if ($action === 'hide') {
                set_coursemodule_visible($this->cmid, 0);
            } else if ($action === 'delete') {
                course_delete_module($this->cmid);
            }
        } else {
            if ($action === 'move') {
                move_section_to($this->course, $this->sectionnum, $this->sectionnum + 1);
                $this->sectionnum = $this->sectionnum + 1;
            } else if ($action === 'hide') {
                set_section_visible($this->course->id, $this->sectionnum, 0);
            } else if ($action === 'delete') {
                course_delete_section($this->course, $this->sectionnum);
            }
        }

        rebuild_course_cache($this->course->id, true);

        if ($usercanedit) {
            $this->setAdminUser();
            $USER->editing = 1;
        } else {
            $this->setUser($this->student);
            $USER->editing = 0;
        }

        $internalvalue = '';
        if ($mode === 'cmid') {
            $internalvalue = 'cmid=' . $this->cmid;
        } else {
            $internalvalue = 'section=' . $this->sectionid;
        }

        $blockconfig = [
            'options' => 'internalcontent',
            'internal' => $internalvalue,
        ];

        $block = $this->create_block_with_config($blockconfig);

        $content = $block->get_content();

        $sectionnameexpected = null;
        if ($expectdescription === '__MODULENAME__') {
            $modinfo = get_fast_modinfo($this->course->id);
            $cm = $modinfo->get_cm($this->cmid);
            $expectdescription = $cm->name;
        } else if ($expectdescription === '__SECTIONNAME__') {
            $modinfo = get_fast_modinfo($this->course->id);
            $sectioninfo = $modinfo->get_section_info_by_id($this->sectionid);
            if ($sectioninfo) {
                $sectionnameexpected = get_section_name($this->course, $sectioninfo->section);
            }
        }

        if ($expectvisible) {
            $expectedurl = null;

            if ($mode === 'cmid') {
                $modinfo = get_fast_modinfo($this->course->id);
                $cm = $modinfo->get_cm($this->cmid);

                if ($cm->url) {
                    $expectedurl = $cm->url->out(false);
                } else {
                    $url = new \moodle_url('/course/view.php', ['id' => $this->course->id]);
                    $url->set_anchor('module-' . $this->cmid);
                    $expectedurl = $url->out(false);
                }

                $this->assertNotNull($expectedurl, 'Expected URL should be available.');
                $this->assertStringContainsString($expectedurl, $content->text);
                $this->assertStringContainsString($cm->name, $content->text);

                if (!empty($expectdescription)) {
                    $this->assertStringContainsString($expectdescription, $content->text);
                }
            } else {
                $modinfo = get_fast_modinfo($this->course->id);
                $sectioninfo = $modinfo->get_section_info_by_id($this->sectionid);

                if ($sectioninfo) {
                    $sectionnameexpected = get_section_name($this->course, $sectioninfo->section);
                    $format = course_get_format($sectioninfo->course);
                    $url = $format->get_view_url($sectioninfo, ['navigation' => true]);
                    if ($url) {
                        $expectedurl = $url->out(false);
                    }
                }

                $this->assertNotNull($expectedurl, 'Expected URL should be available.');
                $this->assertStringContainsString($expectedurl, $content->text);
                $this->assertNotNull($sectionnameexpected, 'Section name should be resolved.');

                if (!empty($sectionnameexpected)) {
                    $this->assertStringContainsString($sectionnameexpected, $content->text);
                }
            }
        } else {
            $this->assertNotNull($errorexpected, 'Error string identifier expected for invisible case.');
            $this->assertStringContainsString(get_string($errorexpected, 'block_qr'), $content->text);
        }
    }

    /**
     * Data provider for internal content scenarios.
     *
     * @return array
     */
    public static function internalcontent_provider(): array {
        return [
            'cmid default editor' => ['cmid', 'none', true, true, null, '__MODULENAME__'],
            'cmid default viewer' => ['cmid', 'none', false, true, null, '__MODULENAME__'],
            'cmid move editor' => ['cmid', 'move', true, true, null, '__MODULENAME__'],
            'cmid move viewer' => ['cmid', 'move', false, true, null, '__MODULENAME__'],
            'cmid hide editor' => ['cmid', 'hide', true, true, null, '__MODULENAME__'],
            'cmid hide viewer' => ['cmid', 'hide', false, false, 'errormodulenotavailable', null],
            'cmid delete editor' => ['cmid', 'delete', true, false, 'errormodulenotavailable', null],
            'cmid delete viewer' => ['cmid', 'delete', false, false, 'errormodulenotavailable', null],
            'section default editor' => ['section', 'none', true, true, null, '__SECTIONNAME__'],
            'section default viewer' => ['section', 'none', false, true, null, '__SECTIONNAME__'],
            'section move editor' => ['section', 'move', true, true, null, '__SECTIONNAME__'],
            'section move viewer' => ['section', 'move', false, true, null, '__SECTIONNAME__'],
            'section hide editor' => ['section', 'hide', true, true, null, '__SECTIONNAME__'],
            'section hide viewer' => ['section', 'hide', false, false, 'errorsectionnotavailable', null],
            'section delete editor' => ['section', 'delete', true, false, 'errorsectionnotavailable', null],
            'section delete viewer' => ['section', 'delete', false, false, 'errorsectionnotavailable', null],
        ];
    }

    /**
     * Creates a QR block instance with the provided config.
     *
     * @param array $config Configuration data to apply
     * @param \context|null $context Optional context for the block
     * @return \block_qr
     */
    private function create_block_with_config(array $config, ?\context $context = null): \block_qr {
        global $PAGE;

        $block = block_instance('qr');
        if ($context === null) {
            if (!empty($PAGE->context)) {
                $context = $PAGE->context;
            } else {
                $context = \context_course::instance($this->course->id);
            }
        }

        $block->instance = (object)[
            'id' => 1,
            'blockname' => 'qr',
            'parentcontextid' => $context->id,
        ];
        $block->config = (object)$config;
        $block->page = $PAGE;
        $block->context = $context;
        return $block;
    }
}
