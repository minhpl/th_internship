<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->libdir.'/mathslib.php';
require_once $CFG->dirroot . '/blocks/th_export_support_dcct/upload_gvcn_qlht_form.php';
require_once $CFG->dirroot . '/blocks/th_export_support_dcct/lib.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$th_upload_gvcn_qlht_key = optional_param('key', 0, PARAM_ALPHANUMEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_export_support_dcct', $courseid);
}

require_login($courseid);
require_capability('block/th_export_support_dcct:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_export_support_dcct/view2.php';
$title = get_string('title', 'block_th_export_support_dcct');
$context = context_system::instance();
$PAGE->set_url('/blocks/th_export_support_dcct/view2.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_export_support_dcct', 'block_th_export_support_dcct'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_export_support_dcct'));

$editurl = new moodle_url('/blocks/th_export_support_dcct/view2.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_export_support_dcct'), $editurl);
$settingsnode->make_active();

if (empty($th_upload_gvcn_qlht_key)) {

	$upload_form = new upload_gvcn_qlht_form();

	if ($upload_form->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/my');
		redirect($courseurl);
	} else if ($fromform = $upload_form->get_data()) {

		$content = $upload_form->get_file_content('data_file');
		$content_arr = th_export_parse($content);
		$list_data = th_export_get_content($content_arr);

		$checked = new stdClass();
		$checked->error_messages = array();
		$checked->support_dcct = array();
		$checked->valid_found = 0;

		foreach($list_data as $k => $data) {

			$line = $k + 2;
			$data_arr = explode(',', $data);

			$ma_lop = trim($data_arr[0]);
			$ma_lop = str_replace(';', ',', $ma_lop);
			$ho_ten = trim($data_arr[1]);
			$sdt = trim($data_arr[2]);
			$email = trim($data_arr[3]);
			$chuc_vu = trim($data_arr[4]);
			$gioi_tinh = trim($data_arr[5]);

			if (!empty($ma_lop) && strlen($ho_ten) <= 200 && strlen($sdt) <= 20 && strlen($email) <= 100 && is_numeric($sdt) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($ho_ten) && !empty($sdt) && !empty($email)) {
				if (!empty($chuc_vu) || $chuc_vu != 'GVCN' || $chuc_vu != 'QLHT') {
					if(!empty($gioi_tinh) || $gioi_tinh != 'Nam' || $gioi_tinh != 'N???') {

						if (!$DB->record_exists_sql("SELECT * FROM {th_export_support_dcct} WHERE email = '$email'")) {
							$checked->valid_found ++;
							$data = new stdClass();
							$data->ma_lop = $ma_lop;
							$data->ho_ten = $ho_ten;
							$data->sdt = $sdt;
							$data->email = $email;

							if ($chuc_vu == 'GVCN') {
								$data->chuc_vu = 1;
							} else {
								$data->chuc_vu = 2;
							}

							if ($chuc_vu == 'Nam') {
								$data->gioi_tinh = 1;
							} else {
								$data->gioi_tinh = 2;
							}
							
							$checked->support_dcct[] = $data;
						} else {
							$checked->error_messages[] = "GVCN/QLHT c?? email (<strong>$email</strong>) ???? t???n t???i. Vui l??ng ki???m tra l???i h??ng $line.";
						}
					} else {
						$checked->error_messages[] = "Kh??ng t??m th???y th??ng tin gi???i t??nh gvcn/qlht n??o ho???c ?????nh d???ng ch??a ????ng. Vui l??ng ki???m tra l???i h??ng $line.";
					}
				} else {
					$checked->error_messages[] = "Kh??ng t??m th???y th??ng tin ch???c v??? gvcn/qlht n??o ho???c ?????nh d???ng ch??a ????ng. Vui l??ng ki???m tra l???i h??ng $line.";
				}		
			} else {
				$checked->error_messages[] = "Kh??ng t??m th???y th??ng tin m?? l???p, h??? t??n, s??t ho???c email n??o ho???c b??? sai ?????nh d???ng. Vui l??ng ki???m tra l???i h??ng $line.";
			}
		}

		// Save data in Session.
		$th_upload_gvcn_qlht_key = $courseid . '_' . time();
		$SESSION->th_export_support_dcct[$th_upload_gvcn_qlht_key] = $checked;

	} else {
		// form didn't validate or this is the first display
		echo $OUTPUT->header();
		echo $OUTPUT->heading('<center>UPLOAD DANH S??CH H??? TR??? DCCT</center>');
		echo "</br>";

		$baseurl = new moodle_url('/blocks/th_export_support_dcct/view2.php');
		if ($editcontrols = local_th_export_support_dcct_controls($context, $baseurl)) {
			echo $OUTPUT->render($editcontrols);
		}

		$upload_form->display();
		echo $OUTPUT->footer();
	}
}

if ($th_upload_gvcn_qlht_key) {

	$form2 = new confirm_form2(null, array('th_upload_gvcn_qlht_key' => $th_upload_gvcn_qlht_key));

	if ($form2->is_cancelled()) {
		// Cancelled forms redirect to the course main page.
		$courseurl = new moodle_url('/blocks/th_export_support_dcct/view2.php');
		redirect($courseurl);
	} else if ($formdata = $form2->get_data()) {
		if (
			!empty($th_upload_gvcn_qlht_key) && !empty($SESSION->th_export_support_dcct) &&
			array_key_exists($th_upload_gvcn_qlht_key, $SESSION->th_export_support_dcct)
		) {

			$data = $SESSION->th_export_support_dcct[$th_upload_gvcn_qlht_key];

			if (!empty($data->support_dcct)) {
				foreach ($data->support_dcct as $k => $data) {

					$dataobjects = new stdClass();
					$dataobjects->ma_lop = $data->ma_lop;
					$dataobjects->ho_ten = $data->ho_ten;
					$dataobjects->sdt = $data->sdt;
					$dataobjects->email = $data->email;
					$dataobjects->role = $data->chuc_vu;
					$dataobjects->gioi_tinh = $data->gioi_tinh;

					$email = $data->email;

					if (!$DB->record_exists_sql("SELECT * FROM {th_export_support_dcct} WHERE email = '$email'")) {
						$DB->insert_record('th_export_support_dcct', $dataobjects, false);
					}
				}
				$returnurl = new moodle_url('/blocks/th_export_support_dcct/index.php');
				redirect($returnurl, 'Upload th??nh c??ng', null, \core\output\notification::NOTIFY_SUCCESS);
			}
		}
	} else {
		echo $OUTPUT->header();
		echo $OUTPUT->heading('<center>UPLOAD DANH S??CH H??? TR??? ??CCT</center>');
		if (
			!empty($th_upload_gvcn_qlht_key) && !empty($SESSION->th_export_support_dcct) &&
			array_key_exists($th_upload_gvcn_qlht_key, $SESSION->th_export_support_dcct)
		) {

			$data = $SESSION->th_export_support_dcct[$th_upload_gvcn_qlht_key];

			if (!empty($data->error_messages)) {

				$errors = $data->error_messages;
				$html1 = th_display_table_export_dcct_error($errors);
				echo $OUTPUT->heading(get_string('Hints', 'block_th_bulk_override'));
				echo $html1;
			}

			if (!empty($data->support_dcct)) {
				$support_dcct = $data->support_dcct;
				$html = th_display_table_import_dcct($support_dcct);
				echo $OUTPUT->heading('<center><h3>DANH S??CH H??? TR??? S??? ???????C TH??M</h3></center>');
				echo $html;
			}
		}

		if (
			!empty($data) && isset($data->valid_found) &&
			empty($data->valid_found)
		) {

			$url    = new moodle_url('/blocks/th_export_support_dcct/view2.php');
			$wn = "Kh??ng t??m th???y gi?? tr??? h???p l???.<br />Vui l??ng <a href='$url'>quay l???i v?? ki???m tra th??ng tin ?????u v??o c???a b???n.</a>.";
			$notification = new \core\output\notification(
				$wn,
				\core\output\notification::NOTIFY_WARNING
			);
			$notification->set_show_closebutton(false);
			echo $OUTPUT->render($notification);
		} else {
			echo $form2->display();
		}
		echo $OUTPUT->footer();
	}
}

?>
