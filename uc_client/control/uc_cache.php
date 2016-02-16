<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: cache.php 1059 2011-03-01 07:25:09Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class uc_cachecontrol extends uc_base {

	function __construct() {
		$this->uc_cachecontrol();
	}

	function uc_cachecontrol() {
		parent::__construct();
	}

	function onupdate($arr) {
		$this->load("uc_cache");
		$_ENV['uc_cache']->updatedata();
	}

}

?>