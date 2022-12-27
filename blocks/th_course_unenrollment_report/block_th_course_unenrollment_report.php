<?php

class block_th_course_unenrollment_report extends block_base
{

    function init() {
        $this->title = get_string('pluginname', 'block_th_course_unenrollment_report');
    }

    function has_config() {
        return true;
    }

    public function get_content() {
        if ($this->content !== null)
            return $this->content;

        $this->content         =  new stdClass;
        $this->content->text   = get_string('contenttext', 'block_th_course_unenrollment_report');

        // The other code.
        global $COURSE;

        $url = new moodle_url('/blocks/th_course_unenrollment_report/view.php');
        $this->content->footer = html_writer::link($url, get_string('addpage', 'block_th_course_unenrollment_report'));

        return $this->content;
    }
}
