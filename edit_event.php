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
require_once($CFG->libdir . '/formslib.php');

$id      = required_param('id', PARAM_INT);        // Course module id.
$eventid = required_param('eventid', PARAM_INT);   // seminarplanner_evt.id.

$cm       = get_coursemodule_from_id('seminarplanner', $id, 0, false, MUST_EXIST);
$course   = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$instance = $DB->get_record('seminarplanner', ['id' => $cm->instance], '*', MUST_EXIST);
$evt      = $DB->get_record('seminarplanner_evt', ['id' => $eventid, 'instanceid' => $instance->id], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/seminarplanner:manage', $context);

// Page setup.
$PAGE->set_url('/mod/seminarplanner/edit_event.php', ['id' => $cm->id, 'eventid' => $evt->id]);
$PAGE->set_title(get_string('edit'));
$PAGE->set_heading(format_string($course->fullname));

/**
 * Form to edit a seminar event.
 */
class seminarplanner_event_edit_form extends moodleform {

    /** @var stdClass|null */
    protected $evt;

    /**
     * Definition.
     */
    public function definition() {
        $m = $this->_form;

        $m->addElement('hidden', 'id');
        $m->setType('id', PARAM_INT);

        $m->addElement('hidden', 'eventid');
        $m->setType('eventid', PARAM_INT);

        // Title.
        $m->addElement('text', 'title', get_string('title', 'mod_seminarplanner'));
        $m->setType('title', PARAM_TEXT);
        $m->addRule('title', null, 'required', null, 'client');

        // Start / End.
        $m->addElement('date_time_selector', 'starttime', get_string('start', 'mod_seminarplanner'));
        $m->addRule('starttime', null, 'required', null, 'client');

        $m->addElement('date_time_selector', 'endtime', get_string('end', 'mod_seminarplanner'));
        $m->addRule('endtime', null, 'required', null, 'client');

        // Location / Trainer / Category / Audience.
        $m->addElement('text', 'location', get_string('location', 'mod_seminarplanner'));
        $m->setType('location', PARAM_TEXT);

        $m->addElement('text', 'trainer', get_string('trainer', 'mod_seminarplanner'));
        $m->setType('trainer', PARAM_TEXT);

        $m->addElement('text', 'category', get_string('category', 'mod_seminarplanner'));
        $m->setType('category', PARAM_TEXT);

        $m->addElement('text', 'audience', get_string('audience', 'mod_seminarplanner'));
        $m->setType('audience', PARAM_TEXT);

        // Buttons.
        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['starttime']) && !empty($data['endtime'])) {
            if ((int)$data['endtime'] <= (int)$data['starttime']) {
                $errors['endtime'] = get_string('error_end_before_start', 'mod_seminarplanner');
            }
        }
        return $errors;
    }
}

$form = new seminarplanner_event_edit_form(new moodle_url('/mod/seminarplanner/edit_event.php'));
$form->set_data([
    'id'       => $cm->id,
    'eventid'  => $evt->id,
    'title'    => $evt->title,
    'starttime'=> (int)$evt->starttime,
    'endtime'  => (int)$evt->endtime,
    'location' => $evt->location ?? '',
    'trainer'  => $evt->trainer ?? '',
    'category' => $evt->category ?? '',
    'audience' => $evt->audience ?? '',
]);

// Cancel.
if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/seminarplanner/event.php', ['id' => $cm->id, 'eventid' => $evt->id]));
}

// Submit.
if ($data = $form->get_data()) {
    require_sesskey();

    $evt->title        = (string)$data->title;
    $evt->starttime    = (int)$data->starttime;
    $evt->endtime      = (int)$data->endtime;
    $evt->location     = (string)($data->location ?? '');
    $evt->trainer      = (string)($data->trainer ?? '');
    $evt->category     = (string)($data->category ?? '');
    $evt->audience     = (string)($data->audience ?? '');
    $evt->timemodified = time();

    $DB->update_record('seminarplanner_evt', $evt);

    redirect(new moodle_url('/mod/seminarplanner/event.php', ['id' => $cm->id, 'eventid' => $evt->id]));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('edit') . ': ' . format_string($evt->title));
$form->display();
echo $OUTPUT->footer();
