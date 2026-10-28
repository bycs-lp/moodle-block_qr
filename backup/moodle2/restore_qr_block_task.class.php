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
 * Class block_qr
 *
 * @package    block_qr
 * @copyright  2023 ISB Bayern
 * @author     Florian Dagner <florian.dagner@outlook.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_qr_block_task extends restore_block_task {
    /**
     * Does nothing.
     *
     * @return void
     */
    protected function define_my_settings() {
    }

    /**
     * Does nothing.
     *
     * @return void
     */
    protected function define_my_steps() {
    }

    /**
     * This plugin has no fileareas yet.
     *
     * @return array
     */
    public function get_fileareas() {
        return [];
    }

    /**
     * This function returns an empty array as the restore functions cannot handle
     * arrays in configdata. The link decoding is done in after_restore().
     *
     * @return array
     */
    public function get_configdata_encoded_attributes() {
        return [];
    }

    /**
     * Returns empty array
     *
     * @return array
     */
    public static function define_decode_contents() {
        return [];
    }

    /**
     * Returns empty array
     *
     * @return array
     */
    public static function define_decode_rules() {
        return [];
    }

    /**
     * This method is called after the complete restore process is done. It calls the
     * link decoders again to handle the arrays internalurl and externalurl in configdata.
     *
     * @return void
     */
    public function after_restore() {
        global $DB;

        $blockid = $this->get_blockid();

        $courseid = $this->get_courseid();
        $modinfo = get_fast_modinfo($courseid);

        if ($configdata = $DB->get_field('block_instances', 'configdata', ['id' => $blockid])) {
            $config = $this->decode_configdata($configdata);

            $decoder = $this->get_decoder();
            $rules = restore_course_task::define_decode_rules();

            foreach ($rules as $rule) {
                $decoder->add_rule($rule);
            }

            if (isset($config->owncontent) && is_string($config->owncontent)) {
                // The decoder returns false for content without encoded links, keep the original then.
                $decoded = $decoder->decode_content($config->owncontent);
                if (is_string($decoded)) {
                    $config->owncontent = $decoded;
                }
            }

            if (isset($config->internal)) {
                // The internal config stores mode-specific data as "type=value" (e.g. "cmid=42", "section=3").
                // Splitting on the first "=" only; array_pad ensures both $type and $id are always set.
                [$type, $id] = array_pad(
                    explode('=', (string) $config->internal, 2),
                    2,
                    null
                );
                $type = trim((string) $type);
                $id   = $id !== null ? (int) $id : null;
                if ($type == 'cmid') {
                    $moduleid = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'course_module', $id);
                    if ($moduleid) {
                        $config->internal = 'cmid=' . $moduleid->newitemid;
                    } else {
                        try {
                            $modinfo->get_cm($config->cmid);
                        } catch (Exception $e) {
                            $modinfo->cmid = null;
                        }
                    }
                }
            }

            // Restored configdata bypasses the edit form, so re-apply its cleaning.
            $paramtypes = [
                'courseurldesc' => PARAM_TEXT,
                'owncontent' => PARAM_NOTAGS,
                'event_summary' => PARAM_TEXT,
                'event_location' => PARAM_TEXT,
                'wifissid' => PARAM_NOTAGS,
                'wifipasskey' => PARAM_NOTAGS,
                'wifiauthentication' => PARAM_TEXT,
                'wifissidoptions' => PARAM_TEXT,
            ];
            foreach ($paramtypes as $key => $type) {
                if (isset($config->$key)) {
                    if (is_string($config->$key)) {
                        $config->$key = clean_param($config->$key, $type);
                    } else {
                        // Arrays and objects are never valid for text keys.
                        unset($config->$key);
                    }
                }
            }
            $coordinates = ['geolocation_br', 'geolocation_lng'];
            foreach ($coordinates as $key) {
                if (isset($config->$key) && !is_numeric($config->$key)) {
                    $config->$key = '';
                }
            }

            // The typed constructor of mode_event only accepts int. Invalid values are removed,
            // block_qr falls back to 0 for missing keys.
            $integerkeys = ['event_start', 'event_end'];
            foreach ($integerkeys as $key) {
                if (isset($config->$key)) {
                    if (is_numeric($config->$key)) {
                        $config->$key = (int) $config->$key;
                    } else {
                        unset($config->$key);
                    }
                }
            }
            if (isset($config->allday)) {
                $isvalidallday = false;
                if (is_numeric($config->allday)) {
                    $isvalidallday = in_array((int) $config->allday, [0, 1], true);
                }
                if ($isvalidallday) {
                    $config->allday = (int) $config->allday;
                } else {
                    unset($config->allday);
                }
            }

            // Allowed values mirror the select options in edit_form.php (size uses its SIZE_* constants).
            // wifiauthentication and wifissidoptions are not restricted, existing instances hold other values.
            $whitelists = [
                'size' => ['150px', '200px', '275px'],
                'link' => ['nolink', 'osm'],
            ];
            foreach ($whitelists as $key => $allowedvalues) {
                if (isset($config->$key)) {
                    $isvalidvalue = false;
                    if (is_string($config->$key)) {
                        $isvalidvalue = in_array($config->$key, $allowedvalues, true);
                    }
                    if (!$isvalidvalue) {
                        unset($config->$key);
                    }
                }
            }

            // Without a valid mode the block would render nothing, instance_create() uses the same default.
            $modes = ['currenturl', 'courseurl', 'internalcontent', 'owncontent', 'event', 'geolocation', 'wifi'];
            $isvalidmode = false;
            if (isset($config->options) && is_string($config->options)) {
                $isvalidmode = in_array($config->options, $modes, true);
            }
            if (!$isvalidmode) {
                $config->options = 'currenturl';
            }

            $configdata = base64_encode(serialize($config));
            $DB->set_field('block_instances', 'configdata', $configdata, ['id' => $blockid]);
        }
    }
}
