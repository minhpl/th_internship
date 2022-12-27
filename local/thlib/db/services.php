<?php

$services = array(
	'th_service' => array( //the name of the web service
		'functions' => array('local_thlib_loadsettings', 'local_thlib_updatesettings', 'local_thlib_loadcourses'), //web service functions of this service
		'requiredcapability' => '', //if set, the web service user need this capability to access
		//any function of this service. For example: 'some/capability:specified'
		'restrictedusers' => 0, //if enabled, the Moodle administrator must link some user to this service
		//into the administration
		'enabled' => 1, //if enabled, the service can be reachable on a default installation
		'shortname' => 'th_service', //the short name used to refer to this service from elsewhere including when fetching a token
	),
);

$functions = array(
	'local_thlib_loadsettings' => array(
		'classname' => 'local_thlib_external',
		'methodname' => 'loadsettings',
		'classpath' => 'local/thlib/externallib.php',
		'description' => 'Load settings data',
		'type' => 'read',
		'ajax' => true,
		'loginrequired' => true,
	),
	'local_thlib_updatesettings' => array(
		'classname' => 'local_thlib_external',
		'methodname' => 'updatesettings',
		'classpath' => 'local/thlib/externallib.php',
		'description' => 'Update settings data',
		'type' => 'write',
		'ajax' => true,
		'loginrequired' => true,
	),
	'local_thlib_loadcourses' => array(
		'classname' => 'local_thlib_external',
		'methodname' => 'loadcourses',
		'classpath' => 'local/thlib/externallib.php',
		'description' => 'fetch course id',
		'type' => 'write',
		'ajax' => true,
		'loginrequired' => true,
	),
	// 'local_thlib_load_registeredcourses_by_userid' => array(
	// 	'classname' => 'local_thlib_external',
	// 	'methodname' => 'load_registeredcourses_by_userid',
	// 	'classpath' => 'local/thlib/externallib.php',
	// 	'description' => 'fetch registered courses by userid',
	// 	'type' => 'write',
	// 	'ajax' => true,
	// 	'loginrequired' => true,
	// ),
);
