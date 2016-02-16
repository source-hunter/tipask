<?php
	
//本程序用于把Tipask V1.0 升级到V1.1

error_reporting(7);
@set_magic_quotes_runtime(0);
@set_time_limit(1000);
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', dirname(__FILE__));
require  TIPASK_ROOT.'/config.php';
header("Content-Type: text/html; charset=".TIPASK_CHARSET);
require TIPASK_ROOT.'/lib/db.class.php';

if( '1.0'!= TIPASK_VERSION ){
	exit('本程序只能升级Tipask 1.0 正式版20100707 版本到Tipask1.1正式版20100802,<br>请不要重复升级！');
}


$action = ($_POST['action']) ? $_POST['action'] : $_GET['action'];

$upgrade = <<<EOT

alter table ask_user add `elect` int(10) NOT NULL DEFAULT '0' after  isnotify;

alter table ask_answer add `status` tinyint(1) unsigned NOT NULL DEFAULT '1' after  content;

CREATE TABLE IF NOT EXISTS ask_gather (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `counts` int(11) NOT NULL,
  `site` varchar(20) NOT NULL,
  `srcid` varchar(20) NOT NULL,
  `qstatus` tinyint(2) NOT NULL DEFAULT '2',
  `qcids` varchar(10) NOT NULL,
  `askusers` text NOT NULL,
  `answerusers` text NOT NULL,
  `gathertime` int(10) NOT NULL DEFAULT '0',
  `gathers` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS ask_visit (
  `ip` varchar(15) NOT NULL,
  `time` int(10) NOT NULL DEFAULT '0',
  KEY `ip` (`ip`),
  KEY `time` (`time`)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS ask_banned (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `ip1` smallint(3) NOT NULL DEFAULT '0',
  `ip2` smallint(3) NOT NULL DEFAULT '0',
  `ip3` smallint(3) NOT NULL DEFAULT '0',
  `ip4` smallint(3) NOT NULL DEFAULT '0',
  `admin` varchar(15) NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `expiration` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) TYPE=MyISAM;


INSERT INTO ask_setting VALUES ('msgtpl', 'a:6:{i:0;a:2:{s:5:"title";s:32:"您的问题title有新回答！";s:7:"content";s:39:"您在site_name上的问题有新回答";}i:1;a:2:{s:5:"title";s:44:"您对问题title的回答已经被采纳！";s:7:"content";s:45:"您在site_name上的回答已经被采纳！";}i:2;a:2:{s:5:"title";s:68:"您的问题title由于长时间没有处理，已经超时关闭！";s:7:"content";s:48:"您在site_name上的问题已经超时关闭！";}i:3;a:2:{s:5:"title";s:47:"您的问题titie即将过期，请及时处理";s:7:"content";s:60:"您在site_name上的问题即将过期，请及时处理！";}i:4;a:2:{s:5:"title";s:50:"系统为您的问题title选择了最佳答案！";s:7:"content";s:42:"你在site_name上的问题已经解决！";}i:5;a:2:{s:5:"title";s:56:"你的问题title已经转入投票流程，请查看！";s:7:"content";s:54:"您在site_name上的问题已经转入投票流程！";}}');
INSERT INTO ask_setting VALUES ('allow_outer', '0'),('stopcopy_on', '0'),('stopcopy_allowagent', 'webkit\r\nopera\r\nmsie\r\ncompatible\r\nbaiduspider\r\ngoogle\r\nsoso\r\nsogou\r\ngecko\r\nmozilla'),('stopcopy_stopagent', ''),('stopcopy_maxnum', '60');

UPDATE ask_setting SET `v`='?' WHERE `k`='seo_prefix';
UPDATE ask_usergroup SET `regulars` = 'index/default,category/view,category/list,note/list,note/view,rss/category,rss/list,rss/question,user/code,user/register,user/login,user/logout,user/getpass,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,user/space,user/scorelist,question/view,question/add,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/new,message/personal,message/system,message/outbox,message/view,message/remove,admin_main/default' WHERE groupid=1;
UPDATE ask_usergroup SET `regulars` = 'index/default,category/view,category/list,note/list,note/view,rss/category,rss/list,rss/question,user/code,user/register,user/login,user/logout,user/getpass,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,user/space,user/scorelist,question/view,question/add,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/new,message/personal,message/system,message/outbox,message/view,message/remove,admin_main/default' WHERE groupid=3;
UPDATE ask_usergroup SET `regulars` = 'index/default,category/view,category/list,note/list,note/view,rss/category,rss/list,rss/question,user/code,user/register,user/login,user/getpass,question/view,question/search' WHERE groupid=6 ;

EOT;


if(!$action) {
	echo"本程序仅用于升级 Tipask V1.0 到 Tipask1.1正式版,请确认之前已经顺利安装Tipask V1.0!<br><br><br>";
	echo"<b><font color=\"red\">运行本升级程序之前,请确认已经上传 Tipask1.0正式版的全部文件和目录</font></b><br><br>";
	echo"<b><font color=\"red\">本程序只能从 Tipask V1.0 到 Tipask1.1,切勿使用本程序从其他版本升级,否则可能会破坏掉数据库资料.<br><br>强烈建议您升级之前备份数据库资料!</font></b><br><br>";
	echo"正确的升级方法为:<br>1. 上传 Tipask1.1 正式版的全部文件和目录,覆盖服务器上的 Tipask v1.0版;<br>2. 上传本程序(upgrade1.1.php)到 Tipask目录中;<br>3. 运行本程序,直到出现升级完成的提示;<br>4. 登录Tipask后台,更新缓存,升级完成。<br><br>";
	echo"<a href=\"$PHP_SELF?action=upgrade\">如果您已确认完成上面的步骤,请点这里升级</a>";
} else {


	$db=new db(DB_HOST, DB_USER, DB_PW, DB_NAME , DB_CHARSET , DB_CONNECT);
	runquery($upgrade);
	
	$config = "<?php \r\ndefine('DB_HOST',  '".DB_HOST."');\r\n";
	$config .= "define('DB_USER',  '".DB_USER."');\r\n";
	$config .= "define('DB_PW',  '".DB_PW."');\r\n";
	$config .= "define('DB_NAME',  '".DB_NAME."');\r\n";
	$config .= "define('DB_CHARSET', '".DB_CHARSET."');\r\n";
	$config .= "define('DB_TABLEPRE',  '".DB_TABLEPRE."');\r\n";
	$config .= "define('DB_CONNECT', 0);\r\n";
	$config .= "define('TIPASK_CHARSET', '".TIPASK_CHARSET."');\r\n";
	$config .= "define('TIPASK_VERSION', '1.1');\r\n";
	$config .= "define('TIPASK_RELEASE', '20100802');\r\n";
	$fp = fopen(TIPASK_ROOT.'/config.php', 'w');
	fwrite($fp, $config);
	fclose($fp);

	echo "升级完成,请删除本升级文件,更新缓存以便完成升级。";

}



function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(mysql_get_server_info() > '4.1' ? " ENGINE=$type default CHARSET=$dbcharset" : " TYPE=$type");
}


function runquery($query) {
	global $db;
	$query = str_replace("\r", "\n", str_replace('ask_', DB_TABLEPRE, $query));
	$expquery = explode(";\n", $query);
	foreach($expquery as $sql) {
		$sql = trim($sql);
		if($sql == '' || $sql[0] == '#') continue;
		if(strtoupper(substr($sql, 0, 12)) == 'CREATE TABLE') {
			$db->query(createtable($sql, DB_CHARSET));
		} else {
			$db->query($sql);
		}
	}
}


?>