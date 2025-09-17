<?php
require_once(__DIR__.'/../../config.php');

$id = optional_param('id', 0, PARAM_INT);
$n  = optional_param('n', 0, PARAM_INT);
$year = optional_param('year', (int)date('Y'), PARAM_INT);
$q    = optional_param('q', '', PARAM_RAW_TRIMMED);
$cat  = optional_param('cat', '', PARAM_RAW_TRIMMED);

if ($id) {
    $cm = get_coursemodule_from_id('seminarplanner', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id'=>$cm->course], '*', MUST_EXIST);
    $instance = $DB->get_record('seminarplanner', ['id'=>$cm->instance], '*', MUST_EXIST);
} else if ($n) {
    $instance = $DB->get_record('seminarplanner', ['id'=>$n], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id'=>$instance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('seminarplanner', $instance->id, $course->id, false, MUST_EXIST);
} else {
    throw new moodle_exception('missingparam', 'error', '', 'id');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/seminarplanner/view.php', ['id'=>$cm->id, 'year'=>$year]);
$PAGE->set_title(format_string($instance->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('yearview','mod_seminarplanner') . ' – ' . format_string($instance->name));

$toolbar = html_writer::div(
    html_writer::link(new moodle_url('/mod/seminarplanner/manage.php', ['id'=>$cm->id]), get_string('addseminar','mod_seminarplanner'), ['class'=>'btn btn-primary mr-2']) .
    html_writer::link(new moodle_url('/mod/seminarplanner/import.php', ['id'=>$cm->id]), get_string('import','mod_seminarplanner'), ['class'=>'btn btn-secondary']),
    'mb-3'
);
echo $toolbar;

// Category dropdown options
$catrecs = $DB->get_records_sql("SELECT DISTINCT category FROM {seminarplanner_evt} WHERE instanceid = :i AND category IS NOT NULL AND category <> '' ORDER BY category ASC", ['i'=>$instance->id]);
$categories = [];
foreach ($catrecs as $r) {
    $categories[] = ['name' => $r->category, 'selected' => ($r->category === $cat)];
}

// Load events (optionally filtered)
$from = make_timestamp($year, 1, 1, 0, 0, 0);
$to   = make_timestamp($year, 12, 31, 23, 59, 59);
$params = ['i'=>$instance->id, 'f'=>$from, 't'=>$to];
$where = 'instanceid = :i AND starttime BETWEEN :f AND :t';
if ($q !== '') {
    $like = $DB->sql_like('title', ':q1', false, false)
        . ' OR ' . $DB->sql_like('location', ':q2', false, false)
        . ' OR ' . $DB->sql_like('trainer', ':q3', false, false)
        . ' OR ' . $DB->sql_like('audience', ':q4', false, false);
    $where .= ' AND (' . $like . ')';
    $params['q1'] = '%'.$q.'%';
    $params['q2'] = '%'.$q.'%';
    $params['q3'] = '%'.$q.'%';
    $params['q4'] = '%'.$q.'%';
}
if ($cat !== '') {
    $where .= ' AND category = :cat';
    $params['cat'] = $cat;
}
$records = $DB->get_records_select('seminarplanner_evt', $where, $params, 'starttime ASC');

// Build results list
$results = [];
foreach ($records as $e) {
    $url = new moodle_url('/mod/seminarplanner/event.php', ['id'=>$cm->id, 'eventid'=>$e->id]);
    $results[] = [
        'title'     => format_string($e->title),
        'url'       => $url->out(false),
        'date'      => userdate($e->starttime, get_string('strftimedate','langconfig')),
        'time_start'=> userdate($e->starttime, '%H:%M'),
        'date_end'  => userdate($e->endtime, get_string('strftimedate','langconfig')),
        'time_end'  => userdate($e->endtime, '%H:%M'),
        'location'  => s($e->location ?? ''),
        'trainer'   => s($e->trainer ?? ''),
        'category'  => s($e->category ?? ''),
    ];
}

// Build months grid from same filtered set
$months = [];
for ($m = 1; $m <= 12; $m++) {
    $monthname = userdate(make_timestamp($year, $m, 1, 0, 0, 0), '%B');
    $evts = [];
    foreach ($records as $r) {
        if ((int)userdate($r->starttime, '%m') === $m) { $evts[] = $r; }
    }
    $evts_out = [];
    foreach ($evts as $e) {
        $u = new moodle_url('/mod/seminarplanner/event.php', ['id'=>$cm->id,'eventid'=>$e->id]);
        $evts_out[] = [
            'title'=>format_string($e->title),
            'url'=>$u->out(false),
            'date'=>userdate($e->starttime, get_string('strftimedate','langconfig')),
            'time'=>userdate($e->starttime, '%H:%M').'–'.userdate($e->endtime, '%H:%M'),
            'location'=>s($e->location ?? ''),
            'trainer'=>s($e->trainer ?? ''),
        ];
    }
    $monthurl = new moodle_url('/mod/seminarplanner/month.php', ['id'=>$cm->id, 'year'=>$year, 'month'=>$m]);
    $months[] = [
        'idx'=>$m, 'name'=>$monthname, 'year'=>$year,
        'monthurl'=>$monthurl->out(false),
        'count'=>count($evts_out),
        'events'=>array_slice($evts_out, 0, 6),
        'hasmore'=>count($evts_out) > 6
    ];
}

// Render template
$data = [
    'year'=>$year,
    'cmid'=>$cm->id,
    'q'=>$q,
    'categories'=>$categories,
    'searchmode'=>($q !== '' || $cat !== ''),
    'resultcount'=>count($results),
    'searchurl'=>(new moodle_url('/mod/seminarplanner/search.php', ['id'=>$cm->id,'year'=>$year,'q'=>$q,'cat'=>$cat]))->out(false),
    'results'=>$results,
    'months'=>$months
];

echo $OUTPUT->render_from_template('mod_seminarplanner/year_grid', $data);
echo $OUTPUT->footer();
