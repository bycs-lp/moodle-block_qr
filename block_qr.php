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

use core\exception\moodle_exception;
use block_qr\output\mode_currenturl;
use block_qr\output\mode_courseurl;
use block_qr\output\mode_section;
use block_qr\output\mode_cmid;
use block_qr\output\mode_owncontent;
use block_qr\output\mode_event;
use block_qr\output\mode_geolocation;
use block_qr\output\mode_wifi;

/**
 * Class block_qr
 *
 * @package    block_qr
 * @copyright  2023 ISB Bayern
 * @author     Florian Dagner <florian.dagner@outlook.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_qr extends block_base {
    /**
     * Sets the block title.
     */
    public function init(): void {
        $this->title = get_string('pluginname', 'block_qr');
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return bool
     */
    public function has_config(): bool {
        return true;
    }

    /**
     * Create default config.
     */
    public function instance_create(): bool {
        $this->config = new stdClass();
        $this->config->options = 'currenturl';
        $this->instance_config_save($this->config);
        return true;
    }

    /**
     * Returns the contents.
     *
     * @return stdClass
     */
    public function get_content() {
        global $CFG, $USER, $OUTPUT;
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }

        $this->content = new stdClass();
        $context = new stdClass();

        if ($this->page->course) {
            $context->courseid = $this->page->course->id;
            $modinfo = get_fast_modinfo($context->courseid);

            if ($this->page->cm) {
                $context->cmid = $this->page->cm->id;
                $context->sectionid = $this->page->cm->section;
            } else {
                $context->cmid = null;
                $context->sectionid = optional_param('sectionid', 0, PARAM_INT);
            }
        }

        $renderable = null;

        switch ($this->config->options) {
            case 'currenturl':
                $renderable = new mode_currenturl($this->page->url);
                break;

            case 'courseurl':
                $renderable = new mode_courseurl(
                    $context->courseid,
                    $this->config->courseurldesc ?? null
                );
                break;

            case 'internalcontent':
                list($type, $id) = explode('=', $this->config->internal);
                switch ($type) {
                    case 'cmid':
                        $renderable = new mode_cmid(
                            $modinfo,
                            (int)$id,
                            $this->user_can_edit()
                        );
                        break;
                    case 'section':
                        $renderable = new mode_section(
                            $modinfo,
                            (int)$id,
                            $this->user_can_edit()
                        );
                        break;
                }
                break;

            case 'owncontent':
                $renderable = new mode_owncontent($this->config->owncontent ?? '');
                break;

            case 'event':
                $renderable = new mode_event(
                    $this->config->event_summary ?? '',
                    $this->config->event_location ?? '',
                    $this->config->event_start ?? 0,
                    $this->config->event_end ?? 0,
                    $this->config->allday ?? 0
                );
                break;

            case 'geolocation':
                $renderable = new mode_geolocation(
                    $this->config->geolocation_br ?? '',
                    $this->config->geolocation_lng ?? '',
                    $this->config->link ?? 'nolink'
                );
                break;

            case 'wifi':
                $renderable = new mode_wifi(
                    $this->config->wifiauthentication ?? '',
                    $this->config->wifissid ?? '',
                    $this->config->wifipasskey ?? '',
                    $this->config->wifissidoptions ?? ''
                );
                break;
        }

        if ($renderable === null) {
            return $this->content;
        }
        $data = $renderable->export_for_template($OUTPUT);
        $content = $OUTPUT->render($renderable);

        if (empty($USER->editing)) {
            $data['fullview'] = false;
        } else {
            $data['fullview'] = true;
        }

        $configshortlink = get_config('block_qr', 'configshortlink');
        $data['configshortlink'] = $configshortlink;

        if (empty($configshortlink)) {
            $data['urlshort'] = null;
        } else {
            if (isset($data['qrcodelink']) && $data['qrcodelink'] !== null) {
                $encodedqrcodelink = urlencode($data['qrcodelink']);
                $data['urlshort'] = str_replace('SHORTLINK', $encodedqrcodelink, $configshortlink);
            } else {
                $data['urlshort'] = null;
            }
        }

        if (array_key_exists('qrcodecontent', $data) && $data['qrcodecontent'] !== null) {
            $data['qrcodecontent_json'] = json_encode($data['qrcodecontent'], JSON_UNESCAPED_SLASHES);
        }

        $data['size'] = $this->config->size ?? null;
        $data['id'] = $this->context->id;
        $data['javascript'] = $CFG->wwwroot . '/blocks/qr/js/qrcode.min.js';
        $data['subcontent'] = $content;

        $this->content->text = $OUTPUT->render_from_template('block_qr/qr', $data);
        return $this->content;
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return ['all' => true];
    }

    /**
     * Allow multiple instances.
     *
     * @return boolean
     */
    public function instance_allow_multiple() {
        return true;
    }
}
