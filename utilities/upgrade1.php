<?php
	
//本程序用于把Tipask1.0Beta 升级到1.0 正式版

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);
@set_time_limit(1000);
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', dirname(__FILE__));
require  TIPASK_ROOT.'/config.php';
header("Content-Type: text/html; charset=".TIPASK_CHARSET);
require TIPASK_ROOT.'/lib/db.class.php';

if('20100618'!=TIPASK_RELEASE){
	exit('本程序只能升级Beta 20100618 版本到Tipask正式版20100707,<br>请不要重复升级！');
}

$action = ($HTTP_POST_VARS["action"]) ? $HTTP_POST_VARS["action"] : $HTTP_GET_VARS["action"];

$upgrade = <<<EOT

alter table ask_question change `description` `description` text  not null default '';

alter table ask_question add supply text NOT NULL default '' after  description;

alter table `ask_session` drop `referer`;

alter table ask_user add isnotify tinyint(1) unsigned NOT NULL default '7' after  adopts;

CREATE TABLE ask_badword (
  id smallint(6) unsigned NOT NULL auto_increment,
  admin varchar(15) NOT NULL default '',
  find varchar(255) NOT NULL default '',
  replacement varchar(255) NOT NULL default '',
  findpattern varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY `find` (`find`)
) Type=MyISAM;


CREATE TABLE ask_link (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  displayorder tinyint(3) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  url varchar(255) NOT NULL DEFAULT '',
  description mediumtext NOT NULL,
  logo varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) TYPE=MyISAM;


CREATE TABLE ask_nav (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(50) NOT NULL,
  title char(255) NOT NULL,
  url char(255) NOT NULL,
  target tinyint(1) NOT NULL DEFAULT '0',
  available tinyint(1) NOT NULL DEFAULT '0',
  displayorder tinyint(3) NOT NULL,
  PRIMARY KEY (id)
) TYPE=MyISAM;


CREATE TABLE ask_ad (
  advid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  available tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(50) NOT NULL DEFAULT '0',
  displayorder tinyint(3) NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL DEFAULT '',
  targets text NOT NULL,
  parameters text NOT NULL,
  `code` text NOT NULL,
  starttime int(10) unsigned NOT NULL DEFAULT '0',
  endtime int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (advid)
) TYPE=MyISAM;


CREATE TABLE ask_attach (
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  qid mediumint(8) unsigned NOT NULL DEFAULT '0',
  aid int(10) unsigned NOT NULL DEFAULT '0',
  time int(10) unsigned NOT NULL DEFAULT '0',
  filename char(100) NOT NULL DEFAULT '',
  filetype char(50) NOT NULL DEFAULT '',
  filesize int(10) unsigned NOT NULL DEFAULT '0',
  location char(100) NOT NULL DEFAULT '',
  downloads mediumint(8) NOT NULL DEFAULT '0',
  isimage tinyint(1) NOT NULL DEFAULT '0',
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY qid (qid,aid),
  KEY uid (uid),
  KEY time (time, isimage, downloads)
) TYPE=MyISAM;


replace into ask_setting values ('tpl_dir', 'default');

replace into ask_setting values ('verify_question', '0');

replace into ask_setting values ('index_life', '0');


EOT;


if(!$action) {
	echo"本程序用于升级 Tipask1.0 Beta 到 Tipask1.0正式版,请确认之前已经顺利安装Tipask1.0 Beta<br><br><br>";
	echo"<b><font color=\"red\">运行本升级程序之前,请确认已经上传 Tipask1.0正式版的全部文件和目录</font></b><br><br>";
	echo"<b><font color=\"red\">本程序只能从 Tipask1.0 Beta 到 Tipask1.0正式版,切勿使用本程序从其他版本升级,否则可能会破坏掉数据库资料.<br><br>强烈建议您升级之前备份数据库资料!</font></b><br><br>";
	echo"正确的升级方法为:<br>1. 上传 Tipask1.0 正式版的全部文件和目录,覆盖服务器上的 Tipask1.0 Beta版;<br>2. 上传本程序(upgrade1.php)到 Tipask目录中;<br>3. 运行本程序,直到出现升级完成的提示;<br>4. 登录Tipask后台,更新缓存,升级完成。<br><br>";
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
	$config .= "define('TIPASK_VERSION', '1.0');\r\n";
	$config .= "define('TIPASK_RELEASE', '20100707');\r\n";
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