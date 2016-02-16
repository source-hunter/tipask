<?php
	define('TIPASK_ROOT', dirname(__FILE__));
	require TIPASK_ROOT.'/config.php';
	require TIPASK_ROOT.'/lib/db.class.php';
	require TIPASK_ROOT.'/lib/global.func.php';
	require TIPASK_ROOT.'/lib/cache.class.php';
	define('TIME', time() );
	/*??*/
	$db=new db(DB_HOST, DB_USER, DB_PW, DB_NAME , DB_CHARSET , DB_CONNECT);
	/*??*/
	$cache=new cache($db);
	$setting=$cache->load('setting');
	/*?get*/
	@extract($_GET,EXTR_SKIP);
	if(!$setting['passport_open']){
		exit('Passport disabled!');
	}
	elseif($verify != md5($action.$userdb.$forward.$setting['passport_key'])){
		exit('Illegal request!');
	}
	parse_str(authcode($userdb,'DECODE',$setting['passport_key']), $member);
	$member['cookietime'] = $member['cktime'] ? $member['cktime'] - TIME : 0;

	
	if ($action == 'login'){
		$member['username'] = preg_replace("/(c:\\con\\con$|[%,\*\"\s\t\<\>\&])/i", "", $member['username']);
		if(strlen($member['username']) > 20) $member['username'] = substr($member['username'], 0, 20);
		if(empty($member['time']) || empty($member['username']) || empty($member['password']) ){
			exit('Lack of required parameters!');
		}elseif($setting['passport_expire'] && TIME - $member['time'] > $setting['passport_expire']){
			exit('Request expired!');
		}
		$user = $db->fetch_first("SELECT * FROM ".DB_TABLEPRE."user WHERE username='".$member['username']."'");
		if($user){
			$uid=$user['uid'];		//	$user->edit($member);
		}else{
			$credit1=$setting['credit1_register'];$credit2=$setting['credit2_register'];
			$db->query("INSERT INTO ".DB_TABLEPRE."user(username,password,email,credit1,credit2) values ('$member[username]','$member[password]','$member[email]',$credit1,$credit2)");
			$uid= $db->insert_id();
			$db->query("INSERT INTO ".DB_TABLEPRE."credit(uid,time,operation,credit1,credit2) VALUES ($uid,".TIME.",'user/register',$credit1,$credit2) ");
		}
		$forward = empty($forward) ? $setting['passport_server'] : $forward;
		$auth = authcode("$uid\t".$member['password'],'ENCODE');
		tcookie('auth', $auth, 24*3600*365);
	}elseif ($action == 'logout' || $action == 'quit'){
		tcookie('sid','');
		tcookie('auth','');
		$forward = empty($forward) ? $setting['passport_server'] : $forward;	
	}

	header('location:'.$forward);
	


?>