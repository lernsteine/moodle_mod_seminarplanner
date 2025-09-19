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
$id    = required_param('id',PARAM_INT); 
$year  = required_param('year',PARAM_INT); 
$month = required_param('month',PARAM_INT);

$cm        = get_coursemodule_from_id('seminarplanner',$id,0,false,MUST_EXIST); 
$course    = $DB->get_record('course',['id'=>$cm->course],'*',MUST_EXIST); 
$instance  = $DB->get_record('seminarplanner',['id'=>$cm->instance],'*',MUST_EXIST);
require_login($course,true,$cm); 
$PAGE->set_url('/mod/seminarplanner/month.php',['id'=>$cm->id,'year'=>$year,'month'=>$month]); $PAGE->set_title(get_string('yearview','mod_seminarplanner')); $PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();
$monthname = userdate(make_timestamp($year,$month,1,0,0,0),'%B'); echo $OUTPUT->heading($monthname.' '.$year);
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
