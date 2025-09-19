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
require_once(__DIR__ . '/../../config.php');

$cmid    = required_param('id', PARAM_INT);
$eventid = required_param('eventid', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$cm        = get_coursemodule_from_id('seminarplanner', $cmid, 0, false, MUST_EXIST);
$course    = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$instance  = $DB->get_record('seminarplanner', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/seminarplanner:manage', $context);

$event = $DB->get_record('seminarplanner_evt', [
    'id' => $eventid,
    'instanceid' => $instance->id,
], '*', MUST_EXIST);

$PAGE->set_url('/mod/seminarplanner/delete.php', ['id' => $cm->id, 'eventid' => $eventid]);
$PAGE->set_title(get_string('delete', 'moodle'));
$PAGE->set_heading(format_string($course->fullname));

if ($confirm && confirm_sesskey()) {
    $year = (int)userdate($event->starttime, '%Y');
    $DB->delete_records('seminarplanner_evt', ['id' => $event->id, 'instanceid' => $instance->id]);

    redirect(
        new moodle_url('/mod/seminarplanner/view.php', ['id' => $cm->id, 'year' => $year]),
        get_string('deleted', 'moodle'),
        2,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();

$eventname = format_string($event->title);
$prompt    = get_string('deletecheck', 'moodle', $eventname);

$yesurl = new moodle_url('/mod/seminarplanner/delete.php', [
    'id'      => $cm->id,
    'eventid' => $event->id,
    'confirm' => 1,
    'sesskey' => sesskey(),
]);

$cancelurl = new moodle_url('/mod/seminarplanner/event.php', [
    'id'      => $cm->id,
    'eventid' => $event->id,
]);

echo $OUTPUT->confirm($prompt, $yesurl, $cancelurl);

echo $OUTPUT->footer();
