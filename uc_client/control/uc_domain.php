<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: domain.php 1059 2011-03-01 07:25:09Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class uc_domaincontrol extends uc_base {

	function __construct() {
		$this->uc_domaincontrol();
	}

	function uc_domaincontrol() {
		parent::__construct();
		$this->init_input();
		$this->load('uc_domain');
	}

	function onls() {
		return $_ENV['uc_domain']->get_list(1, 9999, 9999);
	}
}

?>