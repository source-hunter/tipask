<?php
	
//本程序用于把Tipask V1.3Beta 升级到V1.3正式版

error_reporting(0);
@set_magic_quotes_runtime(0);
@set_time_limit(1000);
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', dirname(__FILE__));
require  TIPASK_ROOT.'/config.php';
header("Content-Type: text/html; charset=".TIPASK_CHARSET);
require TIPASK_ROOT.'/lib/global.func.php';
require TIPASK_ROOT.'/lib/db.class.php';
if( '1.3'!= TIPASK_VERSION ){
	exit('本程序只能升级Tipask 1.3版20110619 版本到Tipask1.4beta版,<br>请不要重复升级！');
}

$action = ($_POST['action']) ? $_POST['action'] : $_GET['action'];
$upgrade = <<<EOT
ALTER TABLE ask_answer ADD `tag` TEXT NOT NULL default '' COMMENT '追问';
INSERT INTO ask_setting VALUES ('sum_category_time', '60');
INSERT INTO ask_setting VALUES ('del_tmp_crontab', '1440');
EOT;
if(!$action) {
	echo"本程序仅用于升级 Tipask 1.3 到 Tipask 1.4beta,请确认之前已经顺利安装Tipask V1.3正式版!<br><br><br>";
	echo"<b><font color=\"red\">运行本升级程序之前,请确认已经上传 Tipask 1.4beta的全部文件和目录</font></b><br><br>";
	echo"<b><font color=\"red\">本程序只能从 Tipask V1.3正式版 到 Tipask1.4beta正式版,切勿使用本程序从其他版本升级,否则可能会破坏掉数据库资料.<br><br>强烈建议您升级之前备份数据库资料!</font></b><br><br>";
	echo"正确的升级方法为:<br>1. 上传 Tipask1.3beta版的全部文件和目录,覆盖服务器上的 Tipask v1.3正式版;<br>2. 上传本程序(upgrade1.4beta.php)到 Tipask目录中;<br>3. 运行本程序,直到出现升级完成的提示;<br>4. 登录Tipask后台,更新缓存,升级完成。<br><br>";
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
	$config .= "define('TIPASK_VERSION', '1.4beta');\r\n";
	$config .= "define('TIPASK_RELEASE', '20111023');\r\n";
	$fp = fopen(TIPASK_ROOT.'/config.php', 'w');
	fwrite($fp, $config);
	fclose($fp);
	cleardir(TIPASK_ROOT.'/data/cache');
	cleardir(TIPASK_ROOT.'/data/view');
	cleardir(TIPASK_ROOT.'/data/tmp');
	echo "<font color='red'>升级说明：请登录到tipask后台，重新添加用户组的提问权限以及站点设置的分类问题数目统计</font><br />";
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