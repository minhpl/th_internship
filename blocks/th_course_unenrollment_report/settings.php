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
 * Settings for the th_course_unenrollment_report block
 *
 * @package    block_th_activatecourses
 * @copyright  2019 Tom Dickman <tomdickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
	// Presentation options heading.
	$settings->add(new admin_setting_heading('block_th_course_unenrollment_report/appearance',
		get_string('restrictsetting', 'block_th_course_unenrollment_report'),
		''));

	$settings->add(new admin_setting_configtext('block_th_course_unenrollment_report/restrict_date',
		get_string('retrictday', 'block_th_course_unenrollment_report'),
		get_string('retrictdaydesc', 'block_th_course_unenrollment_report'),
		30, PARAM_INT));

	$settings->add(new admin_setting_configtext('block_th_course_unenrollment_report/restrict_week',
		get_string('retrictweek', 'block_th_course_unenrollment_report'),
		get_string('retrictweekdesc', 'block_th_course_unenrollment_report'),
		8, PARAM_INT));

	$settings->add(new admin_setting_configtext('block_th_course_unenrollment_report/restrict_month',
		get_string('retrictmonth', 'block_th_course_unenrollment_report'),
		get_string('retrictmonthdesc', 'block_th_course_unenrollment_report'),
		6, PARAM_INT));

}
