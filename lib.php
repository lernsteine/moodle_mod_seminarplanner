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
