<?php

require_once '../../config.php';
require_once 'th_course_unenrollment_report_form.php';
require_once $CFG->dirroot . '/local/thlib/lib.php';
require_once $CFG->dirroot . '/blocks/th_course_unenrollment_report/classes/lib.php';
require_once $CFG->dirroot . '/blocks/th_course_unenrollment_report/classes/external.php';

global $DB, $OUTPUT, $PAGE, $COURSE;

$id = optional_param('id', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $COURSE->id))) {
	print_error('invalidcourse', 'block_th_course_unenrollment_report', $COURSE->id);
}
require_login($COURSE->id);

$title = get_string('pluginname', 'block_th_course_unenrollment_report');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_heading($title);
$PAGE->set_title($title);
$PAGE->set_url('/blocks/th_course_unenrollment_report/view.php');
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.table', "TH course unenrollment report", $lang));
$PAGE->requires->js_call_amd('block_th_course_unenrollment_report/ajaxcalls');

$editurl = new moodle_url('/blocks/th_course_unenrollment_report/view.php');
$settingsnode = $PAGE->settingsnav->add($title, $editurl);
$settingsnode->make_active();

$mform = new th_course_unenrollment_report_form();
$formdata = $mform->get_data();

if ($mform->is_cancelled()) {
	// Cancelled forms redirect to the course main page.
	$courseurl = new moodle_url('/my');
	redirect($courseurl);
}

echo $OUTPUT->header();
$mform->display();

if ($formdata) {
	$start_date = $formdata->startdate;
	$end_date = $formdata->enddate + 24 * 60 * 60 - 1;
	$total_course_overall = 0;
	$now = (strtotime("now"));

	$table = new html_table();
	$headrows = new html_table_row();

	$cell = new html_table_cell(get_string('course_short', 'block_th_course_unenrollment_report'));
	$cell->attributes['class'] = 'cell headingcell';
	$cell->header = true;
	$headrows->cells[] = $cell;

	$cell = new html_table_cell(get_string('course_full', 'block_th_course_unenrollment_report'));
	$cell->attributes['class'] = 'cell headingcell';
	$cell->header = true;
	$headrows->cells[] = $cell;

	if (empty($formdata->courseid)) {
		$courseid_sql = $DB->get_records_sql("SELECT c.id, c.fullname FROM {course} as c WHERE visible = 1 AND id <> 1");
		foreach ($courseid_sql as $course) {
			$formdata->courseid[] = $course->id;
		}

	}
	$courseid_arr = $formdata->courseid;

	// filter by daily
	if ($formdata->filter == 'day') {
		$count_date = 0;
		$total_by_column = array();

		for ($i = $start_date; $i <= $end_date; $i = $i + 24 * 60 * 60) {
			$cell = new html_table_cell(date('d/m/Y', $i));
			$cell->attributes['class'] = 'cell headingcell';
			$cell->header = true;
			$headrows->cells[] = $cell;
			$total_by_column[$count_date] = 0;
			$count_date++;
		}

		foreach ($courseid_arr as $key => $courseid) {
			$coursesql = "SELECT c.id, c.fullname, c.shortname
            FROM {course} as c
            WHERE c.id = :courseid";

			$params = array('courseid' => $courseid, 'start_date' => $start_date, 'end_date' => $end_date, 'now' => $now);
			$temp = $DB->get_records_sql($coursesql, $params);

			$value = $temp[$courseid];
			$row = new html_table_row();
			$row->id = $courseid;
			$cell = new html_table_cell($value->shortname);
			$row->cells[] = $cell;

			$link_course = $CFG->wwwroot . '/user/index.php?id=' . $courseid;
			$course_fullname = html_writer::link($link_course, $value->fullname);

			$cell = new html_table_cell($course_fullname);
			$row->cells[] = $cell;

			$total_by_row = 0;
			$count_date = 0;
			$count_users_by_date = array();

			$unenroll_user_sql = "
                    SELECT ue.id, ue.timeend, ue.userid as count_usr, c.fullname, c.shortname
                    FROM {course} as c, {user_enrolments} as ue, {enrol} as e
                    WHERE e.courseid = c.id AND ue.enrolid = e.id AND c.id = :courseid
                    AND ue.timeend >= :start_date and ue.timeend <= :end_date;
                ";
			$temp_user = $DB->get_records_sql($unenroll_user_sql, $params);

			for ($i = $start_date; $i <= $end_date; $i = $i + 24 * 60 * 60) {
				$count_users_by_date[$count_date] = 0;
				$count_user = 0;

				foreach ($temp_user as $key1 => $value) {
					if (date('d/m/Y', $i) == date('d/m/Y', $value->timeend)) {
						$count_users_by_date[$count_date] += 1;
					}
				}

				$total_by_column[$count_date] += $count_users_by_date[$count_date];
				$total_by_row += $count_users_by_date[$count_date];

				$cell = new html_table_cell($count_users_by_date[$count_date]);
				if ($count_users_by_date[$count_date] != 0) {
					$cell->attributes = array('class' => 'click');
				}
				$row->cells[] = $cell;
				$count_date++;

			}
			// total by row
			$cell = new html_table_cell($total_by_row);
			$cell->header = true;
			$row->cells[] = $cell;

			// course overall
			if (!empty($formdata->wholecourse)) {
				$unenroll_course_overall = "
					SELECT ue.*
					FROM {course} as c, {user_enrolments} as ue, {enrol} as e
					WHERE e.courseid = c.id AND ue.enrolid = e.id AND c.id = :courseid
					AND ue.timeend <= :now AND ue.timeend != 0;
				";
				$temp_user1 = $DB->get_records_sql($unenroll_course_overall, $params);

				$cell = new html_table_cell(count($temp_user1));
				$row->cells[] = $cell;

				$total_course_overall += count($temp_user1);
			}
			$table->data[] = $row;
		}
	}

	// filter by weekly
	else if ($formdata->filter == 'week') {
		$start_week_monday = strtotime("this week monday", $start_date);
		$end_week_monday = strtotime("this week monday", $end_date);
		$end_week_sunday = strtotime("this week sunday", $end_date);

		$count_week = ($end_week_monday - $start_week_monday) / (7 * 24 * 60 * 60) + 1;

		$week_date_from_to = array();
		for ($i = 0; $i < $count_week; $i++) {
			$week_date_from_to[$i] = date('d/m/Y', $start_week_monday) . ' - ' . date('d/m/Y', strtotime("this week sunday", $start_week_monday));
			$start_week_monday += 7 * 24 * 60 * 60;
		}

		$total_by_column = array();
		for ($i = 0; $i < $count_week; $i++) {
			$cell = new html_table_cell($week_date_from_to[$i]);
			$cell->attributes['class'] = 'cell headingcell';
			$cell->header = true;
			$headrows->cells[] = $cell;
			$total_by_column[$i] = 0;
		}

		foreach ($courseid_arr as $key => $courseid) {
			$coursesql = "SELECT c.id, c.fullname, c.shortname
            FROM {course} as c
            WHERE c.id = :courseid";

			$start_week_monday = strtotime("this week monday", $start_date);

			$params = array('courseid' => $courseid, 'start_date' => $start_week_monday, 'end_date' => $end_week_sunday, 'now' => $now);
			$temp = $DB->get_records_sql($coursesql, $params);

			$unenroll_user_sql = "
				SELECT ue.id, ue.timeend, ue.userid as count_usr, c.fullname, c.shortname
				FROM {course} as c, {user}_enrolments as ue, {enrol} as e
				WHERE e.courseid = c.id AND ue.enrolid = e.id AND c.id = :courseid
				AND ue.timeend >= :start_date and ue.timeend <= :end_date
			";
			$temp_user = $DB->get_records_sql($unenroll_user_sql, $params);

			$value = $temp[$courseid];
			$row = new html_table_row();
			$row->id = $courseid;
			$cell = new html_table_cell($value->shortname);
			$row->cells[] = $cell;

			$link_course = $CFG->wwwroot . '/user/index.php?id=' . $courseid;
			$course_fullname = html_writer::link($link_course, $value->fullname);

			$cell = new html_table_cell($course_fullname);
			$row->cells[] = $cell;

			$total_by_row = 0;
			$count_users_by_week = array();

			for ($i = 0; $i < $count_week; $i++) {
				$count_users_by_date = 0;
				for ($j = $start_week_monday; $j <= strtotime("this week sunday", $start_week_monday); $j = $j + 24 * 60 * 60) {
					foreach ($temp_user as $key1 => $value) {
						if (date('d/m/Y', $j) == date('d/m/Y', $value->timeend)) {
							$count_users_by_date += 1;
						}
					}
				}
				$count_users_by_week[$i] = $count_users_by_date;
				$total_by_column[$i] += $count_users_by_week[$i];
				$total_by_row += $count_users_by_week[$i];

				$cell = new html_table_cell($count_users_by_week[$i]);
				if ($count_users_by_week[$i] != 0) {
					$cell->attributes = array('class' => 'click');
				}
				$row->cells[] = $cell;

				$start_week_monday += 7 * 24 * 60 * 60;
			}
			$cell = new html_table_cell($total_by_row);
			$cell->header = true;
			$row->cells[] = $cell;

			// course overall
			if (!empty($formdata->wholecourse)) {
				$unenroll_course_overall = "
					SELECT ue.*
					FROM {course} as c, {user_enrolments} as ue, {enrol} as e
					WHERE e.courseid = c.id AND ue.enrolid = e.id AND c.id = :courseid
					AND ue.timeend <= :now AND ue.timeend != 0;
				";
				$temp_user1 = $DB->get_records_sql($unenroll_course_overall, $params);

				$cell = new html_table_cell(count($temp_user1));
				$row->cells[] = $cell;

				$total_course_overall += count($temp_user1);
			}
			$table->data[] = $row;
		}
	}

	// filter by monthly
	else {
		$start_month_first_day = strtotime("first day of this month", $start_date);
		$start_month_last_day = strtotime("last day of this month", $start_date);
		$end_month_first_day = strtotime("first day of this month", $end_date);
		$end_month_last_day = strtotime("last day of this month", $end_date);
		$count_month = 0;
		for ($i = $start_month_first_day; $i <= $end_month_first_day; $i = strtotime("first day of this month +1 month", $i)) {
			$month_date_from_to[$count_month] = date('m/Y', $i);
			$count_month++;
		}

		$total_by_column = array();
		for ($i = 0; $i < $count_month; $i++) {
			$cell = new html_table_cell($month_date_from_to[$i]);
			$cell->attributes['class'] = 'cell headingcell';
			$cell->header = true;
			$headrows->cells[] = $cell;
			$total_by_column[$i] = 0;
		}

		foreach ($courseid_arr as $key => $courseid) {
			$coursesql = "SELECT c.id, c.fullname, c.shortname
            FROM {course} as c
            WHERE c.id = :courseid";

			$start_month_first_day = strtotime("first day of this month", $start_date);

			$params = array('courseid' => $courseid, 'start_date' => $start_month_first_day, 'end_date' => $end_month_last_day, 'now' => $now);
			$temp = $DB->get_records_sql($coursesql, $params);

			$unenroll_user_sql = "
				SELECT ue.id, ue.timeend, ue.userid as count_usr, c.fullname, c.shortname
				FROM {course} as c, {user}_enrolments as ue, {enrol} as e
				WHERE e.courseid = c.id AND ue.enrolid = e.id AND c.id = :courseid
				AND ue.timeend >= :start_date and ue.timeend <= :end_date
			";
			$temp_user = $DB->get_records_sql($unenroll_user_sql, $params);

			$value = $temp[$courseid];
			$row = new html_table_row();
			$row->id = $courseid;
			$cell = new html_table_cell($value->shortname);
			$row->cells[] = $cell;

			$link_course = $CFG->wwwroot . '/user/index.php?id=' . $courseid;
			$course_fullname = html_writer::link($link_course, $value->fullname);

			$cell = new html_table_cell($course_fullname);
			$row->cells[] = $cell;

			$total_by_row = 0;
			$count_users_by_month = array();
			for ($i = 0; $i < $count_month; $i++) {
				$count_users_by_date = 0;
				for ($j = $start_month_first_day; $j <= strtotime("last day of this month", $start_month_first_day); $j = $j + 24 * 60 * 60) {
					foreach ($temp_user as $key1 => $value) {
						if (date('d/m/Y', $j) == date('d/m/Y', $value->timeend)) {
							$count_users_by_date += 1;
						}
					}
				}
				$count_users_by_month[$i] = $count_users_by_date;
				$total_by_column[$i] += $count_users_by_month[$i];
				$total_by_row += $count_users_by_month[$i];

				$cell = new html_table_cell($count_users_by_month[$i]);
				if ($count_users_by_month[$i] != 0) {
					$cell->attributes = array('class' => 'click');
				}
				$row->cells[] = $cell;

				$start_month_first_day = strtotime("first day of this month +1 month", $start_month_first_day);
			}
			$cell = new html_table_cell($total_by_row);
			$cell->header = true;
			$row->cells[] = $cell;

			// course overall
			if (!empty($formdata->wholecourse)) {
				$unenroll_course_overall = "
					SELECT ue.*
					FROM {course} as c, {user_enrolments} as ue, {enrol} as e
					WHERE e.courseid = c.id AND ue.enrolid = e.id AND c.id = :courseid
					AND ue.timeend <= :now AND ue.timeend != 0;
				";
				$temp_user1 = $DB->get_records_sql($unenroll_course_overall, $params);

				$cell = new html_table_cell(count($temp_user1));
				$row->cells[] = $cell;

				$total_course_overall += count($temp_user1);
			}
			$table->data[] = $row;
		}
	}

	// add row: total, calculate total by column date/week/month
	$row = new html_table_row();
	$cell = new html_table_cell('Total');
	$cell->header = true;
	$row->cells[] = $cell;

	$cell = new html_table_cell('');
	$cell->header = true;
	$row->cells[] = $cell;

	$total_total = 0;
	for ($i = 0; $i < count($total_by_column); $i++) {
		$cell = new html_table_cell($total_by_column[$i]);
		$cell->header = true;
		$row->cells[] = $cell;
		$total_total += $total_by_column[$i];
	}

	for ($i = 0; $i < count($total_by_column) + 4; $i++) {
		if ($i != 0 && $i != 1) {
			$table->align[$i] = 'center';
		}

	}

	$cell = new html_table_cell($total_total);
	$cell->header = true;
	$row->cells[] = $cell;
	$table->data[] = $row;

	// add column: total, calculate total by course
	$cell = new html_table_cell('Total');
	$cell->header = true;
	$headrows->cells[] = $cell;

	// course overall cell
	if (!empty($formdata->wholecourse)) {
		$cell = new html_table_cell('Course Overall Total');
		$cell->header = true;
		$headrows->cells[] = $cell;

		$cell = new html_table_cell($total_course_overall);
		$row->cells[] = $cell;
	}

	$table->head = $headrows->cells;
	$table->attributes = array('class' => 'table', 'border' => '1');

	echo html_writer::table($table);
}

echo $OUTPUT->footer();

?>
<script type="text/javascript">
  	$(document).ready(function() {
		$('td.click').css("cursor", "pointer");
});
</script>