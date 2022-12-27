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
 * th_activatecourses block rendrer
 *
 * @package    block_th_activatecourses
 * @copyright  2016 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_th_activatecourses\output;
defined('MOODLE_INTERNAL') || die;

use core_course_renderer;
use renderable;

/**
 * th_activatecourses block renderer
 *
 * @package    block_th_activatecourses
 * @copyright  2016 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends core_course_renderer {

	/**
	 * Return the main content for the block overview.
	 *
	 * @param main $main The main renderable
	 * @return string HTML string
	 */
	public function render_main(main $main) {
		return $this->render_from_template('block_th_activatecourses/main', $main->export_for_template($this));
	}
}