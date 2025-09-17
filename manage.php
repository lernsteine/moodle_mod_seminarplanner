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
require_once(__DIR__.'/../../config.php'); require_once($CFG->libdir.'/formslib.php');
$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('seminarplanner', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id'=>$cm->course], '*', MUST_EXIST);
$instance = $DB->get_record('seminarplanner', ['id'=>$cm->instance], '*', MUST_EXIST);
require_login($course, false, $cm); $context=context_module::instance($cm->id);
require_capability('mod/seminarplanner:manage',$context);
$PAGE->set_url('/mod/seminarplanner/manage.php',['id'=>$cm->id]);
$PAGE->set_title(get_string('addseminar','mod_seminarplanner')); $PAGE->set_heading(format_string($course->fullname));
class seminar_event_form extends moodleform{function definition(){$m=$this->_form;
 $m->addElement('hidden','id');$m->setType('id',PARAM_INT);
 $m->addElement('text','title',get_string('name'));$m->setType('title',PARAM_TEXT);$m->addRule('title',null,'required',null,'client');
 $m->addElement('date_time_selector','starttime',get_string('start','mod_seminarplanner'));
 $m->addElement('date_time_selector','endtime',get_string('end','mod_seminarplanner'));
 $m->addElement('text','location',get_string('location','mod_seminarplanner'));$m->setType('location',PARAM_TEXT);
 $m->addElement('text','trainer',get_string('trainer','mod_seminarplanner'));$m->setType('trainer',PARAM_TEXT);
 $m->addElement('text','category',get_string('category','mod_seminarplanner'));$m->setType('category',PARAM_TEXT);
 $m->addElement('text','audience',get_string('audience','mod_seminarplanner'));$m->setType('audience',PARAM_TEXT);
 $this->add_action_buttons(true,get_string('addseminar','mod_seminarplanner'));}}
$f=new seminar_event_form(new moodle_url('/mod/seminarplanner/manage.php',['id'=>$cm->id])); $f->set_data(['id'=>$cm->id]);
if($f->is_cancelled()){redirect(new moodle_url('/mod/seminarplanner/view.php',['id'=>$cm->id]));}
elseif($d=$f->get_data()){
 $rec=(object)['instanceid'=>$instance->id,'title'=>$d->title,'starttime'=>$d->starttime,'endtime'=>$d->endtime,'location'=>$d->location??'','trainer'=>$d->trainer??'','category'=>$d->category??'','audience'=>$d->audience??'','timecreated'=>time(),'timemodified'=>time()];
 $DB->insert_record('seminarplanner_evt',$rec); redirect(new moodle_url('/mod/seminarplanner/view.php',['id'=>$cm->id]));
}
echo $OUTPUT->header(); $f->display(); echo $OUTPUT->footer();
