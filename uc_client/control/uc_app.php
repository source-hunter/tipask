<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: app.php 1059 2011-03-01 07:25:09Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class uc_appcontrol extends uc_base {

	function __construct() {
		$this->uc_appcontrol();
	}

	function uc_appcontrol() {
		parent::__construct();
		$this->load('uc_app');
	}

	function onls() {
		$this->init_input();
		$applist = $_ENV['uc_app']->get_apps('appid, type, name, url, tagtemplates, viewprourl, synlogin');
		$applist2 = array();
		foreach($applist as $key => $app) {
			$app['tagtemplates'] = $this->unserialize($app['tagtemplates']);
			$applist2[$app['appid']] = $app;
		}
		return $applist2;
	}

	function onadd() {
	}

	function onucinfo() {
	}

	function _random($length, $numeric = 0) {
	}

	function _generate_key() {
	}

	function _format_notedata($notedata) {
	}
}

?>