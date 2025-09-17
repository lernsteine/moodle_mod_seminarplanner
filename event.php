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
require_once(__DIR__.'/../../config.php');
$id = optional_param('id',0,PARAM_INT); $eventid = required_param('eventid',PARAM_INT);
$evt=$DB->get_record('seminarplanner_evt',['id'=>$eventid],'*',MUST_EXIST);
if($id){ $cm=get_coursemodule_from_id('seminarplanner',$id,0,false,MUST_EXIST); $course=$DB->get_record('course',['id'=>$cm->course],'*',MUST_EXIST); $instance=$DB->get_record('seminarplanner',['id'=>$cm->instance],'*',MUST_EXIST); }
else { $instance=$DB->get_record('seminarplanner',['id'=>$evt->instanceid],'*',MUST_EXIST); $course=$DB->get_record('course',['id'=>$instance->course],'*',MUST_EXIST); $cm=get_coursemodule_from_instance('seminarplanner',$instance->id,$course->id,false,MUST_EXIST); }
require_login($course,true,$cm); $context=context_module::instance($cm->id);
$PAGE->set_url('/mod/seminarplanner/event.php',['id'=>$cm->id,'eventid'=>$eventid]); $PAGE->set_title(format_string($evt->title)); $PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();
$heading = format_string($evt->title);
if(has_capability('mod/seminarplanner:manage',$context)){ $editurl=new moodle_url('/mod/seminarplanner/edit_event.php',['id'=>$cm->id,'eventid'=>$evt->id]); $icon=$OUTPUT->pix_icon('i/edit',get_string('edit')); $heading.= html_writer::span(html_writer::link($editurl,$icon),'ml-2'); }
if (has_capability('mod/seminarplanner:manage', $context)) {
    $delurl = new moodle_url('/mod/seminarplanner/delete.php', ['id'=>$cm->id, 'eventid'=>$evt->id]);
    echo html_writer::div($OUTPUT->single_button($delurl, get_string('delete'), 'get'), 'mt-3');
}
echo html_writer::tag('h2',$heading);
$info=new html_table(); $info->attributes['class']='generaltable';
$info->data=[[get_string('start','mod_seminarplanner'),userdate($evt->starttime,get_string('strftimedatetime','langconfig'))],[get_string('end','mod_seminarplanner'),userdate($evt->endtime,get_string('strftimedatetime','langconfig'))],[get_string('location','mod_seminarplanner'),s($evt->location)],[get_string('trainer','mod_seminarplanner'),s($evt->trainer)],[get_string('category','mod_seminarplanner'),s($evt->category)],[get_string('audience','mod_seminarplanner'),s($evt->audience)]];
echo html_writer::table($info);
echo $OUTPUT->footer();
