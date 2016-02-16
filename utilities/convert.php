<?php

// convert cyask  to tipask
//本程序用来把Cyask3.2转化为tipask1.1

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

@set_time_limit(1000);

define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', dirname(__FILE__));

$version_old = 'Cyask3.x';
$version_new = 'Tipask1.1';
$timestamp = time();
$sqlfile = TIPASK_ROOT.'/install/tipask.sql';
$lockfile = TIPASK_ROOT.'/data/install.lock';

define('CONFIG', TIPASK_ROOT.'/config.php');

require_once TIPASK_ROOT.'/config.inc.php';
require_once TIPASK_ROOT.'/lib/db.class.php';

define('SOFT_VERSION', '1.1');
define('SOFT_RELEASE', '20100802');
define('CHARSET', $charset);

echo $site_url="http://".$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'],0,-11);

header("Content-Type: text/html; charset=".CHARSET);
showheader();


if(PHP_VERSION < '4.1.0') {
	$_GET = &$HTTP_GET_VARS;
	$_POST = &$HTTP_POST_VARS;
	$_COOKIE = &$HTTP_COOKIE_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
	$_ENV = &$HTTP_ENV_VARS;
	$_FILES = &$HTTP_POST_FILES;
}

$action = ($_POST['action']) ? $_POST['action'] : $_GET['action'];
$step = $_GET['step'];
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;

$upgrademsg = array(
	1 => '转换第 1 步: 新增数据表<br /><br />',
	2 => '转换第 2 步: 初始化数据<br /><br />',
	3 => '转换第 3 步: 导入原数据<br /><br />',
	4 => '转换第 4 步: 转换全部完毕<br /><br />',
);

$errormsg = '';
if(!isset($dbhost)) {
	showerror("<span class=error>没有找到config.inc.php 文件!</span><br />请确认您已经上传了cyask的配置文件config.inc.php");
} elseif(!$dblink = @mysql_connect($dbhost, $dbuser, $dbpw)) {
	showerror("<span class=error>config.inc.php 配置文件错误!</span><br />请修改 config.inc.php 当中关于数据库的设置，然后再上传，重新开始转换。");
}

@mysql_close($dblink);
$db=new db($dbhost, $dbuser, $dbpw, $dbname , $dbcharset , $pconnect);


if(!$action) {

	if(!$tableinfo = loadtable('question')) {
		showerror("<span class=error>无法找到 Cyask的question数据表!</span><br />请修改 config.inc.php 当中关于数据库的设置，然后再上传，重新开始转换。");
	} elseif($db->version() > '4.1') {
		$old_dbcharset = substr($tableinfo['title']['Collation'], 0, strpos($tableinfo['title']['Collation'], '_'));
		if($old_dbcharset <> $dbcharset) {
			showerror("<span class=error>config.inc.php 数据库字符集设置错误!</span><br />".
				"<li>原来的字符集设置为：$old_dbcharset".
				"<li>当前使用的字符集为：$dbcharset".
				"<li>建议：修改 config.inc.php， 将其中的 <b>\$dbcharset = ''</b> 或者 <b>\$dbcharset = '$dbcharset'</b> 修改为： <b>\$dbcharset = '$old_dbcharset'</b>".
				"<li>修改完毕后上传 config.inc.php，然后重新开始转换。"
			);
		}
	}

	echo <<< EOT
<span class="red">
转换前请打开浏览器 JavaScript 支持,整个过程是自动完成的,不需人工点击和干预.<br />
转换之前务必备份数据库资料，否则转换失败无法恢复<br /></span><br />
正确的转换方法为:
<ol>
	<li>上传 $version_new 的全部文件和目录到服务器上</li>
	<li>上传转换程序<b>convert.php </b>和<b>cyask</b>的配置文件<b>config.inc.php </b>到 $version_new 的根目录中。</li>
	<li>运行本程序,直到出现转换完成的提示。</li>
</ol>
<a href="$PHP_SELF?action=upgrade&step=1"><font size="2" color="red"><b>&gt;&gt;&nbsp;如果您已确认完成上面的步骤,请点这里转换</b></font></a>
<br /><br />
EOT;
	showfooter();

} else {

	$step = intval($step);
	echo '&gt;&gt;'.$upgrademsg[$step];
	flush();

	if($step == 1) {
		
		$sql = file_get_contents($sqlfile);
		$sql = str_replace("\r\n", "\n", $sql);
		runquery($sql);

		echo "第 $step 步转换成功<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 2) {

		$db->query("REPLACE INTO `tipask_setting` SET `k`='site_url',`v`='".$site_url."'");
		$db->query("REPLACE INTO `tipask_setting` SET `k`='auth_key',`v`='".generate_key()."'");
		
		echo "第 $step 步转换成功<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 3) {

		$intables=array('sort','member','question','answer','notice','set','vote');
		
		if( isset($intables[$start]) ) {
			if($tableinfo = loadtable($intables[$start])){
				echo "导入数据表 [ $start ] {$tablepre}{$intables[$start]}:";
				$func='import_'.$intables[$start];
				$func();
			}else{
				echo "忽略数据表 [ $start ] {$tablepre}{$intables[$start]}:";
			}
			$start ++;
			redirect("?action=upgrade&step=$step&start=$start");
		}
		
		echo "第 $step 步转换成功<br /><br />";
		redirect("?action=upgrade&step=".($step+1));

	}elseif($step ==4){
	
		config_edit(); //生成Tipask的配置文件

		@mkdir(TIPASK_ROOT."/data/backup",0777);
		@mkdir(TIPASK_ROOT."/data/cache",0777);
		@mkdir(TIPASK_ROOT."/data/attach",0777);
		@mkdir(TIPASK_ROOT."/data/tmp",0777);
		@mkdir(TIPASK_ROOT."/data/view",0777);
		@cleardir(TIPASK_ROOT."/data/cache");
		@touch($lockfile);

		echo '<br />恭喜您Tipask数据转换成功，接下来请您：<ol><li><b>必删除本程序convert.php和config.inc.php共2个文件</b>'.
		'<li>使用管理员身份登录Tipask，进入后台，更新缓存'.
		'<li>进行Tipask注册、登录、提问题、回答问题等常规测试，看看运行是否正常'.
		'<li>如果您希望启用 <b>'.$version_new.'</b> 的强大功能，你还需要对于Tipask基本设置、用户组等进行重新设置</ol><br />'.
		'<b>感谢您选用我们的产品！</b><a href="index.php" target="_blank">您现在可以访问Tipask，查看转换情况</a>';
		showfooter();
	}
}

instfooter();

function cleardir($dir,$forceclear=false) {
	if(!is_dir($dir)){
		return;
	}
	$directory=dir($dir);
	while($entry=$directory->read()){
		$filename=$dir.'/'.$entry;
		if(is_file($filename)){
			@unlink($filename);
		}elseif(is_dir($filename)&&$forceclear&&$entry!='.'&&$entry!='..'){
			chmod($filename,0777);
			cleardir($filename,$forceclear);
			rmdir($filename);
		}
	}
	$directory->close();
}
	
function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(mysql_get_server_info() > '4.1' ? " ENGINE=$type default CHARSET=$dbcharset" : " TYPE=$type");
}


function instfooter() {
	echo '</table></body></html>';
}



function runquery($query) {
	global $db, $dbcharset;

	$query = str_replace("\r", "\n", str_replace('ask_', ' tipask_', $query));
	$expquery = explode(";\n", $query);
	foreach($expquery as $sql) {
		$sql = trim($sql);
		if($sql == '' || $sql[0] == '#') continue;

		if(strtoupper(substr($sql, 0, 12)) == 'CREATE TABLE') {
			$db->query(createtable($sql, $dbcharset));
		} else {
			$db->query($sql);
		}
	}
}

function loadtable($table, $force = 0) {
	global $db, $tablepre, $dbcharset;
	static $tables = array();

	if(!isset($tables[$table]) || $force) {
		if($db->version() > '4.1') {
			$query = $db->query("SHOW FULL COLUMNS FROM {$tablepre}$table", 'SILENT');
		} else {
			$query = $db->query("SHOW COLUMNS FROM {$tablepre}$table", 'SILENT');
		}
		while($field = @$db->fetch_array($query)) {
			$tables[$table][$field['Field']] = $field;
		}
	}
	return $tables[$table];
}


function showheader() {
	global $version_old, $version_new;

	print <<< EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Tipask 转换程序( $version_old &gt;&gt; $version_new)</title>
<meta name="MSSmartTagsPreventParsing" content="TRUE">
<meta http-equiv="MSThemeCompatible" content="Yes">
<style>
a:visited	{color: #FF0000; text-decoration: none}
a:link		{color: #FF0000; text-decoration: none}
a:hover		{color: #FF0000; text-decoration: underline}
body,table,td	{color: #3a4273; font-family: Tahoma, verdana, arial; font-size: 12px; line-height: 20px; scrollbar-base-color: #e3e3ea; scrollbar-arrow-color: #5c5c8d}
input		{color: #085878; font-family: Tahoma, verdana, arial; font-size: 12px; background-color: #3a4273; color: #ffffff; scrollbar-base-color: #e3e3ea; scrollbar-arrow-color: #5c5c8d}
.install	{font-family: Arial, Verdana; font-size: 14px; font-weight: bold; color: #000000}
.header		{font: 12px Tahoma, Verdana; font-weight: bold; background-color: #3a4273 }
.header	td	{color: #ffffff}
.red		{color: red; font-weight: bold}
.bg1		{background-color: #e3e3ea}
.bg2		{background-color: #eeeef6}
</style>
</head>

<body bgcolor="#3A4273" text="#000000">
<table width="95%" height="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="center">
<tr>
<td>
<table width="98%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
<td class="install" height="30" valign="bottom"><font color="#FF0000">&gt;&gt;</font>
Tipask 转换程序( $version_old &gt;&gt; $version_new)</td>
</tr>
<tr>
<td>
<hr noshade align="center" width="100%" size="1">
</td>
</tr>
<tr>
<td align="center">
<b>本转换程序只能从 $version_old 转换到 $version_new ，运行之前，请确认已经上传所有文件，并做好数据备份<br />
转换当中有任何问题请访问技术支持站点 <a href="http://bbs.tipask.com" target="_blank">http://bbs.tipask.com</a></b>
</td>
</tr>
<tr>
<td>
<hr noshade align="center" width="100%" size="1">
</td>
</tr>
<tr><td>
EOT;
}

function showfooter() {
	echo <<< EOT
</td></tr></table></td></tr>
<tr><td height="100%">&nbsp;</td></tr>
</table>
</body>
</html>
EOT;
	exit();
}

function showerror($message, $break = 1) {
	echo '<br /><br />'.$message.'<br /><br />';
	if($break) showfooter();
}

function redirect($url) {

	$url = $url.(strstr($url, '&') ? '&' : '?').'t='.time();

	echo <<< EOT
<hr size=1>
<script language="JavaScript">
	function redirect() {
		window.location.replace('$url');
	}
	setTimeout('redirect();', 1000);
</script>
<br /><br />
&gt;&gt;<a href="$url">浏览器会自动跳转页面，无需人工干预。除非当您的浏览器长时间没有自动跳转时，请点击这里</a>
<br /><br />
EOT;
	showfooter();
}


function random($length) {
	$hash = '';
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$max = strlen($chars) - 1;
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}


function generate_key() {
	$random = random(32);
	$info = md5($_SERVER['SERVER_SOFTWARE'].$_SERVER['SERVER_NAME'].$_SERVER['SERVER_ADDR'].$_SERVER['SERVER_PORT'].$_SERVER['HTTP_USER_AGENT'].time());
	$return = '';
	for($i=0; $i<64; $i++) {
		$p = intval($i/2);
		$return[$i] = $i % 2 ? $random[$p] : $info[$p];
	}
	return implode('', $return);
}

function config_edit() {
	extract($GLOBALS, EXTR_SKIP);
	$config = "<?php \r\ndefine('DB_HOST', '$dbhost');\r\n";
	$config .= "define('DB_USER', '$dbuser');\r\n";
	$config .= "define('DB_PW', '$dbpw');\r\n";
	$config .= "define('DB_NAME', '$dbname');\r\n";
	$config .= "define('DB_CHARSET', '$dbcharset');\r\n";
	$config .= "define('DB_TABLEPRE', 'tipask_');\r\n";
	$config .= "define('DB_CONNECT', 0);\r\n";
	$config .= "define('TIPASK_CHARSET', '".CHARSET."');\r\n";
	$config .= "define('TIPASK_VERSION', '".SOFT_VERSION."');\r\n";
	$config .= "define('TIPASK_RELEASE', '".SOFT_RELEASE."');\r\n";
	$fp = fopen(CONFIG, 'w');
	fwrite($fp, $config);
	fclose($fp);
}


/* 需要用到的表admin和member */
function import_member() {
	global $db, $tablepre;
	$count=$db->result_first("select max(uid) from {$tablepre}member");
	for($i=1;$i<=$count;$i++){
		$member=$db->fetch_first("select * from {$tablepre}member where uid=$i");
		if($member){
			$uid=$member['uid']; 
			$username=addslashes($member['username']); 
			$password=$member['password']; 
			$email=$member['email']; 
			$credit1=$member['allscore']; 
			$regip=$member['regip']; 
			$lastlogin=$member['lastlogin']; 
			$gender=$member['gender']; 
			$bday=$member['bday']; 
			$qq=addslashes($member['qq']); 
			$msn=addslashes($member['msn']);  
			$signature=addslashes($member['signature']);  
			$groupid=(5==$member['adminid'])? 7 : 1;
			$db->query("REPLACE INTO  tipask_user (uid,username,password,email,credit1,regip,lastlogin,gender,bday,qq,msn,signature,groupid) VALUES ($uid,'$username','$password','$email',$credit1,'$regip','$lastlogin','$gender','$bday','$qq','$msn','$signature','$groupid')");
		}
	}
}


 
 /* 需要用到的表question','question_1'*/
function import_question() {
	global $db, $tablepre;
	$count=$db->result_first("select max(qid) from `{$tablepre}question` ");
	for($i=1;$i<=$count;$i++){
		$question=$db->fetch_first("select * from  `{$tablepre}question` where qid=$i");
		if($question){
			$qid = $i;
 			$uid = $question['uid'];
 			$username = addslashes($question['username']);
 			$cid1 = $question['sid1'];
 			$cid2 = $question['sid2'];
 			$cid3 = $question['sid3'];
 			$cid = $question['sid'];
 			$title = addslashes($question['title']);
 			$price = $question['score'];
 			$time = $question['asktime'];
 			$answers = $question['answercount'];
 			$endtime = $question['endtime'];
 			$hidanswer = $question['hidanswer'];
 			$status = $question['status'];
			(7==$status) && ($status=2); //分享的问题自动变为已解决
 			$question_1=$db->fetch_first("select supplement from {$tablepre}question_1 where qid=$qid ");
			$description =$question_1 ? $question_1['supplement'] : '';
 			$description =addslashes($description);
 			
	 		$db->query( "INSERT INTO tipask_question SET id=$qid,cid='$cid',cid1='$cid1',cid2='$cid2',cid3='$cid3',authorid='$uid',author='$username',
	 		title='$title',description='$description',price='$price',time='$time',endtime='$endtime',hidden='$hidanswer',status='$status',answers=0" );

			$cid1=intval($cid1);$cid2=intval($cid2);$cid3=intval($cid3);
			$db->query("UPDATE tipask_category SET questions=questions+1 WHERE  id IN ($cid1,$cid2,$cid3) ");
			$db->query("UPDATE tipask_user SET questions=questions+1 WHERE  uid =$uid");

		}
	}
}


 /* 需要用到的表'answer','answer_1' */
function import_answer() {
	global $db, $tablepre;
	$count=$db->result_first("select max(aid) from `{$tablepre}answer` ");
	for($i=1;$i<=$count;$i++){
		$answer=$db->fetch_first("select * from  `{$tablepre}answer` where aid=$i");
		if($answer){
			$aid = $i;
 			$uid = $answer['uid'];
 			$qid = $answer['qid'];
 			$voted = $answer['joinvote'];
 			$votes = $answer['votevalue'];
 			$time = $answer['answertime'];
 			$adopttime = $answer['adopttime'];
 
 			$answer_1=$db->fetch_first("select *  from {$tablepre}answer_1 where aid=$aid ");
			$content =addslashes($answer_1['content']);
 			$username =addslashes($answer_1['username']);
 			
 			$question=$db->fetch_first("select title from {$tablepre}question where qid=$qid ");
			$title =addslashes($question['title']);
	 
			$db->query( "INSERT INTO tipask_answer SET qid='$qid',title='$title',author='$username',authorid='$uid',time='$time',adopttime='$adopttime',content='$content'" );
			$db->query("UPDATE tipask_user SET answers=answers+1 WHERE  uid =$uid");
			$db->query("UPDATE tipask_question SET answers=answers+1 WHERE  id =$qid");
		}
	}
}

 	
 /* 需要用到的表 vote */
function import_vote() {
	global $db, $tablepre;
	
	$db->query("INSERT INTO tipask_vote(qid,aid,uid,ip)
		SELECT  qid,aid,uid,uip	FROM `{$tablepre}vote` ");
	
 }


 /* 需要用到的表 notice*/
function import_notice() {
	global $db, $tablepre;

 	$db->query("INSERT INTO tipask_note(id,author,title,content,time,url)
		SELECT  id,author,title,content,time,url FROM `{$tablepre}notice` ");
	
}


 /* 需要用到的表 sort*/
function import_sort() {
	global $db, $tablepre;
	$query = $db->query("SELECT * FROM `{$tablepre}sort` ");
	while($sort = $db->fetch_array($query)){
		$grade=$sort['grade'];
		$indexname='sort'.$grade;
		$name=addslashes($sort[$indexname]);
		
		$dir=md5($name);
		$pid=0;
		if(2==$grade){
			$pid=$sort['sid1'];
		}
		if(3==$grade){
			$pid=$sort['sid2'];
		}
		$displayorder=$sort['orderid'];
		$id=$sort['sid'];
		$db->query("INSERT INTO `tipask_category` set id=$id ,`name`='$name' ,`dir`='$dir' ,`pid`= '$pid' , `grade`='$grade' , displayorder='$displayorder' ");
	}
}

 /* 需要用到的表 set*/
function import_set() {
	global $db, $tablepre;
	
	$db->query("REPLACE INTO tipask_setting(k,v) SELECT  'site_name',v FROM `{$tablepre}set` WHERE k='site_name' ");
	$db->query("REPLACE INTO tipask_setting(k,v) SELECT  'admin_email',v FROM `{$tablepre}set` WHERE k='admin_email' ");
	$db->query("REPLACE INTO tipask_setting(k,v) SELECT  'meta_description',v FROM `{$tablepre}set` WHERE k='meta_description' ");
	$db->query("REPLACE INTO tipask_setting(k,v) SELECT  'meta_keywords',v FROM `{$tablepre}set` WHERE k='meta_keywords' ");

}


 


?>