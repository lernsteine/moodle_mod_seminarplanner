<?php
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
