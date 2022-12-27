<?php
$plugin->component = 'block_th_vmc_campaign'; // Recommended since 2.0.2 (MDL-26035). Required since 3.0 (MDL-48494)
$plugin->version = 2021102500; // YYYYMMDDXX (year, month, day, 24-hr time)
$plugin->dependencies = array(
	'local_thlib' => '2021100000',
);
$plugin->requires = 2010031008; // YYYYMMDDHH (This is the release version for Moodle 2.0)
