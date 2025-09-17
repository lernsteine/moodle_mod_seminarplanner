<?php
require_once(__DIR__.'/../../config.php');

$id  = required_param('id', PARAM_INT);
$year = optional_param('year', (int)date('Y'), PARAM_INT);
$q    = optional_param('q', '', PARAM_RAW_TRIMMED);
$cat  = optional_param('cat', '', PARAM_RAW_TRIMMED);
$page = optional_param('page', 0, PARAM_INT);
$perpage = 50;

$cm = get_coursemodule_from_id('seminarplanner', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id'=>$cm->course], '*', MUST_EXIST);
$instance = $DB->get_record('seminarplanner', ['id'=>$cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/seminarplanner/search.php', ['id'=>$cm->id, 'year'=>$year, 'q'=>$q, 'cat'=>$cat, 'page'=>$page]);
$PAGE->set_title(format_string($instance->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($instance->name) . ' – ' . get_string('yearview','mod_seminarplanner'));

// Build query
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
if ($cat !== '') { $where .= ' AND category = :cat'; $params['cat'] = $cat; }

$total = $DB->count_records_select('seminarplanner_evt', $where, $params);
$records = $DB->get_records_select('seminarplanner_evt', $where, $params, 'starttime ASC', '*', $page * $perpage, $perpage);

// Render filters summary
$summary = [];
if ($q !== '') { $summary[] = 'Suche: <strong>'.s($q).'</strong>'; }
if ($cat !== '') { $summary[] = 'Kategorie: <strong>'.s($cat).'</strong>'; }
$summary[] = 'Jahr: <strong>'.s($year).'</strong>';
echo html_writer::div(implode(' · ', $summary), 'mb-2 text-muted');

// Results table
$table = new html_table();
$table->head = [
    get_string('start','mod_seminarplanner'),
    get_string('end','mod_seminarplanner'),
    get_string('events','mod_seminarplanner'),
    get_string('location','mod_seminarplanner'),
    get_string('trainer','mod_seminarplanner'),
    get_string('category','mod_seminarplanner'),
];
foreach ($records as $e) {
    $url = new moodle_url('/mod/seminarplanner/event.php', ['id'=>$cm->id, 'eventid'=>$e->id]);
    $table->data[] = [
        userdate($e->starttime, get_string('strftimedatetime','langconfig')),
        userdate($e->endtime,   get_string('strftimedatetime','langconfig')),
        html_writer::link($url, format_string($e->title)),
        s($e->location),
        s($e->trainer),
        s($e->category),
    ];
}
if (empty($records)) {
    $table->data[] = [html_writer::span(get_string('none','mod_seminarplanner'), 'text-muted'), '', '', '', '', ''];
}
echo html_writer::table($table);

// Pagination
$baseurl = new moodle_url('/mod/seminarplanner/search.php', ['id'=>$cm->id, 'year'=>$year, 'q'=>$q, 'cat'=>$cat]);
echo $OUTPUT->paging_bar($total, $page, $perpage, $baseurl);

// Back to year view link
$back = new moodle_url('/mod/seminarplanner/view.php', ['id'=>$cm->id, 'year'=>$year, 'q'=>$q, 'cat'=>$cat]);
echo html_writer::div(html_writer::link($back, get_string('yearview','mod_seminarplanner')), 'mt-3');

echo $OUTPUT->footer();
