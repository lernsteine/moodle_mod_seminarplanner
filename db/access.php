<?php
defined('MOODLE_INTERNAL') || die();
$capabilities = [
 'mod/seminarplanner:view'=>['captype'=>'read','contextlevel'=>CONTEXT_MODULE,'archetypes'=>['student'=>CAP_ALLOW,'teacher'=>CAP_ALLOW,'manager'=>CAP_ALLOW]],
 'mod/seminarplanner:manage'=>['captype'=>'write','contextlevel'=>CONTEXT_MODULE,'archetypes'=>['editingteacher'=>CAP_ALLOW,'manager'=>CAP_ALLOW]],
 'mod/seminarplanner:import'=>['captype'=>'write','contextlevel'=>CONTEXT_MODULE,'archetypes'=>['editingteacher'=>CAP_ALLOW,'manager'=>CAP_ALLOW]],
];
