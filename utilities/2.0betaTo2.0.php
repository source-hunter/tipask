
<?php

//本程序用于把Tipask2.0beta 升级到V2.0正式版

error_reporting(0);
@set_magic_quotes_runtime(0);
@set_time_limit(1000);
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', dirname(__FILE__));
require TIPASK_ROOT . '/config.php';
header("Content-Type: text/html; charset=" . TIPASK_CHARSET);
require TIPASK_ROOT . '/lib/global.func.php';
require TIPASK_ROOT . '/lib/db.class.php';
$action = ($_POST['action']) ? $_POST['action'] : $_GET['action'];
if (!stristr(strtolower(TIPASK_VERSION), '2.0beta')) {
    exit('本程序只能升级Tipask 2.0beta版 release 20120322 到Tipask2.0正式版 release 20120702,<br>请不要重复升级！');
}
$upgrade = <<<EOT
DROP TABLE ask_ad;
CREATE TABLE IF NOT EXISTS ask_ad (
  html text,
  page varchar(50) NOT NULL DEFAULT '',
  position varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`page`,`position`)
) ENGINE=MyISAM;
ALTER TABLE ask_answer ADD `ip` VARCHAR( 20 ) NULL AFTER `status` ;
ALTER TABLE ask_question ADD `ip` VARCHAR( 20 ) NULL  AFTER status;
ALTER TABLE ask_session ADD `ip` VARCHAR( 20 ) NULL  AFTER islogin;
ALTER TABLE ask_user ADD `regtime` INT( 10 ) NOT NULL DEFAULT '0'  AFTER `regip`; 
DROP TABLE ask_banned;
CREATE TABLE IF NOT EXISTS ask_banned (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `ip1` char(3) NOT NULL,
  `ip2` char(3) NOT NULL,
  `ip3` char(3) NOT NULL,
  `ip4` char(3) NOT NULL,
  `admin` varchar(15) NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `expiration` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
ALTER TABLE ask_nav ADD type tinyint(1) not null default 0 AFTER available;
TRUNCATE TABLE ask_nav;

INSERT INTO ask_nav (`id`, `name`, `title`, `url`, `target`, `available`, `type`, `displayorder`) VALUES(1, '问答首页', '问答首页', 'index/default', 0, 1, 1, 1);
INSERT INTO ask_nav (`id`, `name`, `title`, `url`, `target`, `available`, `type`, `displayorder`) VALUES(2, '分类大全', '分类大全', 'category/view', 0, 1, 1, 6);
INSERT INTO ask_nav (`id`, `name`, `title`, `url`, `target`, `available`, `type`, `displayorder`) VALUES(3, '问答专家', '问答专家', 'expert/default', 0, 1, 1, 5);
INSERT INTO ask_nav (`id`, `name`, `title`, `url`, `target`, `available`, `type`, `displayorder`) VALUES(4, '知识专题', '知识专题', 'category/recommend', 0, 1, 1, 3);
INSERT INTO ask_nav (`id`, `name`, `title`, `url`, `target`, `available`, `type`, `displayorder`) VALUES(5, '问答之星', '问答之星', 'user/famouslist', 0, 1, 1, 4);
INSERT INTO ask_nav (`id`, `name`, `title`, `url`, `target`, `available`, `type`, `displayorder`) VALUES(6, '标签大全', '标签大全', 'index/taglist', 0, 1, 1, 7);
INSERT INTO ask_nav (`id`, `name`, `title`, `url`, `target`, `available`, `type`, `displayorder`) VALUES(7, '礼品商店', '礼品商店', 'gift/default', 0, 1, 1, 8);


CREATE TABLE IF NOT EXISTS ask_crontab(
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `available` tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('user','system') NOT NULL DEFAULT 'user',
  `name` char(50) NOT NULL DEFAULT '',
  `method` varchar(50) NOT NULL DEFAULT '',
  `lastrun` int(10) unsigned NOT NULL DEFAULT '0',
  `nextrun` int(10) unsigned NOT NULL DEFAULT '0',
  `weekday` tinyint(1) NOT NULL DEFAULT '0',
  `day` tinyint(2) NOT NULL DEFAULT '0',
  `hour` tinyint(2) NOT NULL DEFAULT '0',
  `minute` char(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `nextrun` (`available`,`nextrun`)
) ENGINE=MyISAM ;
INSERT INTO ask_crontab (`id`, `available`, `type`, `name`, `method`, `lastrun`, `nextrun`, `weekday`, `day`, `hour`, `minute`) VALUES(1, 1, 'system', '每日分类统计', 'sum_category_question', 1341160751, 1341164351, -1, -1, -1, '60');
INSERT INTO ask_setting (`k`, `v`) VALUES ('editor_toolbars', 'FullScreen,Source,Undo,Redo,RemoveFormat,|,Bold,Italic,FontSize,FontFamily,ForeColor,|,InsertImage,attachment,Emotion,Map,gmap,|,JustifyLeft,JustifyCenter,JustifyRight,|,HighlightCode');
EOT;
if (!$action) {
    echo '<meta http-equiv=Content-Type content="text/html;charset=' . TIPASK_CHARSET . '">';
    echo"本程序仅用于升级 Tipask2.0beta 到 Tipask2.0正式版,请确认之前已经顺利安装Tipask2.0beta版本!<br><br><br>";
    echo"<b><font color=\"red\">运行本升级程序之前,请确认已经上传 Tipask2.0正式版的全部文件和目录</font></b><br><br>";
    echo"<b><font color=\"red\">本程序只能从Tipask2.0beta 到 Tipask2.0正式版,切勿使用本程序从其他版本升级,否则可能会破坏掉数据库资料.<br><br>强烈建议您升级之前备份数据库资料!</font></b><br><br>";
    echo"正确的升级方法为:<br>1. 上传 Tipask2.0正式版的全部文件和目录,覆盖服务器上的Tipask2.0beta版;<br>2. 上传本程序(2.0betaTo2.0.php)到 Tipask目录中;<br>3. 运行本程序,直到出现升级完成的提示;<br>4. 登录Tipask后台,更新缓存,升级完成。<br><br>";
    echo"<a href=\"$PHP_SELF?action=upgrade\">如果您已确认完成上面的步骤,请点这里升级</a>";
} else {
    $db = new db(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_CHARSET, DB_CONNECT);
    runquery($upgrade);
    $config = "<?php \r\ndefine('DB_HOST',  '" . DB_HOST . "');\r\n";
    $config .= "define('DB_USER',  '" . DB_USER . "');\r\n";
    $config .= "define('DB_PW',  '" . DB_PW . "');\r\n";
    $config .= "define('DB_NAME',  '" . DB_NAME . "');\r\n";
    $config .= "define('DB_CHARSET', '" . DB_CHARSET . "');\r\n";
    $config .= "define('DB_TABLEPRE',  '" . DB_TABLEPRE . "');\r\n";
    $config .= "define('DB_CONNECT', 0);\r\n";
    $config .= "define('TIPASK_CHARSET', '" . TIPASK_CHARSET . "');\r\n";
    $config .= "define('TIPASK_VERSION', '2.0');\r\n";
    $config .= "define('TIPASK_RELEASE', '20120702');\r\n";
    $fp = fopen(TIPASK_ROOT . '/config.php', 'w');
    fwrite($fp, $config);
    fclose($fp);
    cleardir(TIPASK_ROOT . '/data/cache');
    cleardir(TIPASK_ROOT . '/data/view');
    cleardir(TIPASK_ROOT . '/data/tmp');
    echo "<font color='red'>升级说明：请登录到tipask后台，重新更改一下用户组权限，还有其他一些新特性!</font><br />";
    echo "升级完成,请删除本升级文件,更新缓存以便完成升级,如果后台登录不进去，请直接删除data/view 目录下的所有.tpl文件，<font color='red'>切记需要保留view目录</font>";
}

function createtable($sql, $dbcharset) {
    $type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
    $type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
    return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql) .
            (mysql_get_server_info() > '4.1' ? " ENGINE=$type default CHARSET=$dbcharset" : " TYPE=$type");
}

function runquery($query) {
    global $db;
    $query = str_replace("\r", "\n", str_replace('ask_', DB_TABLEPRE, $query));
    $expquery = explode(";\n", $query);
    foreach ($expquery as $sql) {
        $sql = trim($sql);
        if ($sql == '' || $sql[0] == '#')
            continue;
        if (strtoupper(substr($sql, 0, 12)) == 'CREATE TABLE') {
            $db->query(createtable($sql, DB_CHARSET));
        } else {
            $db->query($sql);
        }
    }
}

?>