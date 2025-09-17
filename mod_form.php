<?php
require_once($CFG->dirroot.'/course/moodleform_mod.php');
class mod_seminarplanner_mod_form extends moodleform_mod {
  public function definition(){
    $m=$this->_form;
    $m->addElement('text','name',get_string('name'),['size'=>64]);
    $m->setType('name',PARAM_TEXT);
    $m->addRule('name',null,'required',null,'client');
    $this->standard_intro_elements();
    $this->standard_coursemodule_elements();
    $this->add_action_buttons();
  }
}
