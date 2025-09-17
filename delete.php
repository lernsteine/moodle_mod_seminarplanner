<?php
require_once(__DIR__.'/../../config.php');

$cmid    = required_param('id', PARAM_INT);
$eventid = required_param('eventid', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$cm      = get_coursemodule_from_id('seminarplanner', $cmid, 0, false, MUST_EXIST);
$course  = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$instance= $DB->get_record('seminarplanner', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/seminarplanner:manage', $context);

$event = $DB->get_record('seminarplanner_evt', ['id'=>$eventid, 'instanceid'=>$instance->id], '*', MUST_EXIST);

$PAGE->set_url('/mod/seminarplanner/delete.php', ['id'=>$cm->id, 'eventid'=>$eventid]);
$PAGE->set_title(get_string('delete'));
$PAGE->set_heading(format_string($course->fullname));

if ($confirm && confirm_sesskey()) {
    $year = (int)userdate($event->starttime, '%Y');
    $DB->delete_records('seminarplanner_evt', ['id' => $event->id, 'instanceid'=>$instance->id]);
    redirect(new moodle_url('/mod/seminarplanner/view.php', ['id'=>$cm->id, 'year'=>$year]),
        get_string('deleted', 'moodle'), 2, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

$eventname = format_string($event->title);
$prompt = get_string('deletecheck', 'moodle', $eventname);

$yesurl = new moodle_url('/mod/seminarplanner/delete.php', [
    'id' => $cm->id,
    'eventid' => $event->id,
    'confirm' => 1,
    'sesskey' => sesskey()
]);
$cancelurl = new moodle_url('/mod/seminarplanner/event.php', ['id'=>$cm->id, 'eventid'=>$event->id]);

echo $OUTPUT->confirm($prompt, $yesurl, $cancelurl);

echo $OUTPUT->footer();