<?php
require_once(__DIR__.'/../../config.php');
$id=required_param('id',PARAM_INT); $year=required_param('year',PARAM_INT); $month=required_param('month',PARAM_INT);
$cm=get_coursemodule_from_id('seminarplanner',$id,0,false,MUST_EXIST); $course=$DB->get_record('course',['id'=>$cm->course],'*',MUST_EXIST); $instance=$DB->get_record('seminarplanner',['id'=>$cm->instance],'*',MUST_EXIST);
require_login($course,true,$cm); $PAGE->set_url('/mod/seminarplanner/month.php',['id'=>$cm->id,'year'=>$year,'month'=>$month]); $PAGE->set_title(get_string('yearview','mod_seminarplanner')); $PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();
$monthname=userdate(make_timestamp($year,$month,1,0,0,0),'%B'); echo $OUTPUT->heading($monthname.' '.$year);
$from=make_timestamp($year,$month,1,0,0,0); $to=make_timestamp($year,$month,31,23,59,59);
$events=$DB->get_records_select('seminarplanner_evt','instanceid=:i AND starttime BETWEEN :f AND :t',['i'=>$instance->id,'f'=>$from,'t'=>$to],'starttime ASC');
if(!$events){ echo html_writer::div(get_string('none','mod_seminarplanner')); } else {
 $t=new html_table(); $t->head=[get_string('start','mod_seminarplanner'),get_string('end','mod_seminarplanner'),get_string('events','mod_seminarplanner'),get_string('location','mod_seminarplanner'),get_string('trainer','mod_seminarplanner')];
 foreach($events as $e){ $u=new moodle_url('/mod/seminarplanner/event.php',['id'=>$cm->id,'eventid'=>$e->id]);
  $t->data[]=[userdate($e->starttime,get_string('strftimedatetime','langconfig')),userdate($e->endtime,get_string('strftimedatetime','langconfig')),html_writer::link($u,format_string($e->title)),s($e->location),s($e->trainer)];
 }
 echo html_writer::table($t);
}
echo $OUTPUT->footer();
