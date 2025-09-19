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

$id      = required_param('id', PARAM_INT);              // coursemodule id
$eventid = optional_param('eventid', 0, PARAM_INT);      // seminarplanner_evt.id (0 = neu)

$cm       = get_coursemodule_from_id('seminarplanner', $id, 0, false, MUST_EXIST);
$course   = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$instance = $DB->get_record('seminarplanner', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/seminarplanner:manage', $context);

// Load dataset
$existing = null;
if ($eventid) {
    $existing = $DB->get_record('seminarplanner_evt',
        ['id' => $eventid, 'instanceid' => $instance->id], '*', MUST_EXIST);
}

/**
 * Form to create a date.
 */
class seminarplanner_event_form extends moodleform {
    /**
     * Definition.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'eventid');
        $mform->setType('eventid', PARAM_INT);

        // Titel.
        $mform->addElement('text', 'title', get_string('title', 'mod_seminarplanner'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');

        // Beschreibung (einfaches textarea statt editor, damit schlicht bleibt).
        $mform->addElement('textarea', 'description', get_string('description', 'mod_seminarplanner'),
            ['rows' => 5, 'cols' => 60]);
        $mform->setType('description', PARAM_TEXT);

        // Start / Ende (Unixzeit via date_time_selector).
        $mform->addElement('date_time_selector', 'starttime', get_string('start', 'mod_seminarplanner'));
        $mform->addRule('starttime', null, 'required', null, 'client');

        $mform->addElement('date_time_selector', 'endtime', get_string('end', 'mod_seminarplanner'));
        $mform->addRule('endtime', null, 'required', null, 'client');

        // Ort / Trainer / Kategorie / Zielgruppe.
        $mform->addElement('text', 'location', get_string('location', 'mod_seminarplanner'));
        $mform->setType('location', PARAM_TEXT);

        $mform->addElement('text', 'trainer', get_string('trainer', 'mod_seminarplanner'));
        $mform->setType('trainer', PARAM_TEXT);

        $mform->addElement('text', 'category', get_string('category', 'mod_seminarplanner'));
        $mform->setType('category', PARAM_TEXT);

        $mform->addElement('text', 'audience', get_string('audience', 'mod_seminarplanner'));
        $mform->setType('audience', PARAM_TEXT);

        // Buttons.
        $label = get_string('savechanges'); // Moodle-Standardtext.
       $this->add_action_buttons(true, $label);
    }

    /**
     * validation.
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

// Site configuration.
$PAGE->set_url('/mod/seminarplanner/manage.php', ['id' => $cm->id, 'eventid' => $eventid]);
$PAGE->set_title(format_string($instance->name));
$PAGE->set_heading(format_string($course->fullname));

$mform = new seminarplanner_event_form(null, []);
$defaults = [
    'id'      => $cm->id,
    'eventid' => $eventid,
];

// Defaults setting.
if ($existing) {
    $defaults += [
        'title'       => $existing->title,
        'description' => $existing->description ?? '',
        'starttime'   => (int)$existing->starttime,
        'endtime'     => (int)$existing->endtime,
        'location'    => $existing->location ?? '',
        'trainer'     => $existing->trainer ?? '',
        'category'    => $existing->category ?? '',
        'audience'    => $existing->audience ?? '',
    ];
}
$mform->set_data($defaults);

// Cancel.
if ($mform->is_cancelled()) {
    $target = new moodle_url('/mod/seminarplanner/view.php', ['id' => $cm->id]);
    redirect($target);
}

// Submit.
if ($data = $mform->get_data()) {
    require_sesskey();

    $record = (object)[
        'instanceid'   => $instance->id,
        'title'        => (string)$data->title,
        'description'  => (string)($data->description ?? ''),
        'starttime'    => (int)$data->starttime,
        'endtime'      => (int)$data->endtime,
        'location'     => (string)($data->location ?? ''),
        'trainer'      => (string)($data->trainer ?? ''),
        'category'     => (string)($data->category ?? ''),
        'audience'     => (string)($data->audience ?? ''),
        'timemodified' => time(),
    ];

    if (!empty($data->eventid)) {
        // Update.
        $record->id = (int)$data->eventid;
        $existingrec = $DB->get_record('seminarplanner_evt',
            ['id' => $record->id, 'instanceid' => $instance->id], '*', MUST_EXIST);
        $record->timecreated = (int)$existingrec->timecreated;
        $DB->update_record('seminarplanner_evt', $record);
        $msg = get_string('changessaved');
    } else {
        // Insert.
        $record->timecreated = time();
        $DB->insert_record('seminarplanner_evt', $record);
        $msg = get_string('changessaved');
    }

    // back to mainpage.
    $year = (int)userdate($record->starttime, '%Y');
    redirect(
        new moodle_url('/mod/seminarplanner/view.php', ['id' => $cm->id, 'year' => $year]),
        $msg,
        2,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();

// Headline.
$heading = $existing
    ? get_string('edit') . ': ' . format_string($existing->title)
    : get_string('addseminar', 'mod_seminarplanner');
echo $OUTPUT->heading($heading);

// show formular.
$mform->display();

echo $OUTPUT->footer();