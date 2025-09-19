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
 * Block definition for mod_seminarplanner.
 *
 * @package    mod_seminarplanner
 * @copyright  2025 Ralf Hagemeister <ralf.hagemeister@lernsteine.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Declares supported features for this module.
 *
 * @param string $feature One of the FEATURE_* constants.
 * @return mixed|null
 */
function seminarplanner_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;

        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;

        case FEATURE_BACKUP_MOODLE2:
            return true;

        default:
            return null;
    }
}

/**
 * Creates a new seminarplanner instance.
 *
 * @param stdClass $data  Form data from mod_form.
 * @param MoodleQuickForm|null $mform The form instance (unused).
 * @return int The ID of the new record.
 */
function seminarplanner_add_instance($data, $mform = null) {
    global $DB;

    $record = (object) [
        'course'       => $data->course,
        'name'         => $data->name,
        'intro'        => $data->intro ?? '',
        'introformat'  => $data->introformat ?? FORMAT_HTML,
        'timecreated'  => time(),
        'timemodified' => time(),
    ];

    return $DB->insert_record('seminarplanner', $record);
}

/**
 * Updates an existing seminarplanner instance.
 *
 * @param stdClass $data  Form data from mod_form (contains ->instance).
 * @param MoodleQuickForm|null $mform The form instance (unused).
 * @return bool True on success.
 */
function seminarplanner_update_instance($data, $mform = null) {
    global $DB;

    $record = $DB->get_record('seminarplanner', ['id' => $data->instance], '*', MUST_EXIST);
    $record->name         = $data->name;
    $record->intro        = $data->intro ?? '';
    $record->introformat  = $data->introformat ?? FORMAT_HTML;
    $record->timemodified = time();

    return $DB->update_record('seminarplanner', $record);
}

/**
 * Deletes a seminarplanner instance and its events.
 *
 * @param int $id The instance ID.
 * @return bool True on success, false if the record did not exist.
 */
function seminarplanner_delete_instance($id) {
    global $DB;

    if (!$DB->record_exists('seminarplanner', ['id' => $id])) {
        return false;
    }

    $DB->delete_records('seminarplanner_evt', ['instanceid' => $id]);
    $DB->delete_records('seminarplanner', ['id' => $id]);

    return true;
}

