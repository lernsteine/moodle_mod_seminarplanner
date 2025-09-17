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
require_once(__DIR__.'/../../config.php'); require_once($CFG->libdir.'/formslib.php'); require_once(__DIR__.'/classes/importer/xls_importer.php');
$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('seminarplanner',$id,0,false,MUST_EXIST);
$course = $DB->get_record('course',['id'=>$cm->course],'*',MUST_EXIST);
$instance = $DB->get_record('seminarplanner',['id'=>$cm->instance],'*',MUST_EXIST);
require_login($course,false,$cm); $context=context_module::instance($cm->id); require_capability('mod/seminarplanner:import',$context);
$PAGE->set_url('/mod/seminarplanner/import.php',['id'=>$cm->id]); $PAGE->set_title(get_string('import','mod_seminarplanner')); $PAGE->set_heading(format_string($course->fullname));
class import_form extends moodleform{function definition(){$m=$this->_form;$m->addElement('hidden','id');$m->setType('id',PARAM_INT);
 $m->addElement('filepicker','file',get_string('import','mod_seminarplanner'));$m->addRule('file',null,'required',null,'client');
 $this->add_action_buttons(true,get_string('import','mod_seminarplanner'));}}
$f=new import_form(new moodle_url('/mod/seminarplanner/import.php',['id'=>$cm->id])); $f->set_data(['id'=>$cm->id]);
echo $OUTPUT->header();
if(!\mod_seminarplanner\importer\xls_importer::is_available()){ echo $OUTPUT->notification(get_string('xlsmissing','mod_seminarplanner'),'notifyproblem'); echo html_writer::div('<code>cd moodle/mod/seminarplanner && composer install</code>');
 echo html_writer::div('Header: <code>title, startdate, starttime, enddate, endtime, location, trainer, category, audience</code>'); }
if($f->is_cancelled()){ redirect(new moodle_url('/mod/seminarplanner/view.php',['id'=>$cm->id])); }
elseif($d=$f->get_data()){ $draftid=file_get_submitted_draft_itemid('file'); $fs=get_file_storage(); $userctx=context_user::instance($USER->id);
 file_prepare_draft_area($draftid,$userctx->id,'user','draft',$draftid); $files=$fs->get_area_files($userctx->id,'user','draft',$draftid,'id',false);
 foreach($files as $file){ $path=make_temp_directory('seminarplanner').'/'.$file->get_contenthash().'.tmp'; $file->copy_content_to($path);
  $imp=new \mod_seminarplanner\importer\xls_importer(); $events=$imp->parse($path);
  foreach($events as $e){ $rec=(object)['instanceid'=>$instance->id,'title'=>$e->title,'starttime'=>$e->starttime,'endtime'=>$e->endtime,'location'=>$e->location??'','trainer'=>$e->trainer??'','category'=>$e->category??'','audience'=>$e->audience??'','timecreated'=>time(),'timemodified'=>time()]; $DB->insert_record('seminarplanner_evt',$rec); }
  @unlink($path);
 }
 redirect(new moodle_url('/mod/seminarplanner/view.php',['id'=>$cm->id])); }
$f->display(); echo $OUTPUT->footer();
