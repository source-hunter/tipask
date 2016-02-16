<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: mail.php 1059 2011-03-01 07:25:09Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class uc_mailcontrol extends uc_base {

	function __construct() {
		$this->uc_mailcontrol();
	}

	function uc_mailcontrol() {
		parent::__construct();
		$this->init_input();
	}

	function onadd() {
		$this->load('uc_mail');
		$mail = array();
		$mail['appid']		= UC_APPID;
		$mail['uids']		= explode(',', $this->input('uids'));
		$mail['emails']		= explode(',', $this->input('emails'));
		$mail['subject']	= $this->input('subject');
		$mail['message']	= $this->input('message');
		$mail['charset']	= $this->input('charset');
		$mail['htmlon']		= intval($this->input('htmlon'));
		$mail['level']		= abs(intval($this->input('level')));
		$mail['frommail']	= $this->input('frommail');
		$mail['dateline']	= $this->time;
		return $_ENV['uc_mail']->add($mail);
	}

}

?>