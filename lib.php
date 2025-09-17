<?php
defined('MOODLE_INTERNAL') || die();
function seminarplanner_supports($feature){switch($feature){
 case FEATURE_MOD_INTRO: return true;
 case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
 case FEATURE_BACKUP_MOODLE2: return true;
 default: return null;}}
function seminarplanner_add_instance($d,$m=null){global $DB;$r=(object)[
 'course'=>$d->course,'name'=>$d->name,'intro'=>$d->intro,'introformat'=>$d->introformat,
 'timecreated'=>time(),'timemodified'=>time()];return $DB->insert_record('seminarplanner',$r);}
function seminarplanner_update_instance($d,$m=null){global $DB;$r=$DB->get_record('seminarplanner',['id'=>$d->instance],'*',MUST_EXIST);
 $r->name=$d->name;$r->intro=$d->intro;$r->introformat=$d->introformat;$r->timemodified=time();
 return $DB->update_record('seminarplanner',$r);}
function seminarplanner_delete_instance($id){global $DB;if(!$DB->record_exists('seminarplanner',['id'=>$id]))return false;
 $DB->delete_records('seminarplanner_evt',['instanceid'=>$id]);$DB->delete_records('seminarplanner',['id'=>$id]);return true;}
