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
 * Version details
 *
 * @package   block_th_course_status
 * @copyright 2018 Manoj Solanki (Coventry University)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();

// Recommended since 2.0.2 (MDL-26035). Required since 3.0 (MDL-48494).
$plugin->component = 'block_th_course_status';
// YYYYMMDDHH (year, month, day, 24-hr time).
$plugin->version = 2022111800; // YYYYMMDDXX (year, month, day, 24-hr time)
$plugin->dependencies = array(
	'local_thlib' => '2021100000',
);
$plugin->requires = 2019052000;
