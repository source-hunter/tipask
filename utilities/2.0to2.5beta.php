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
if (!stristr(strtolower(TIPASK_VERSION), '2.0')) {
    exit('本程序只能升级Tipask 2.0版release 201201210 到Tipask2.5请不要重复升级！');
}
$upgrade = <<<EOT
ALTER TABLE ask_answer DROP comment;
ALTER TABLE ask_answer DROP support;
ALTER TABLE ask_answer DROP against;
ALTER TABLE ask_answer ADD comments int(10) NOT NULL DEFAULT '0';
ALTER TABLE ask_answer ADD supports int(10) NOT NULL DEFAULT '0';
ALTER TABLE ask_answer_comment DROP credit; 
DROP TABLE IF EXISTS ask_answer_comment;
CREATE TABLE  ask_answer_comment (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) NOT NULL,
  `authorid` int(10) NOT NULL,
  `author` char(18) NOT NULL,
  `content` varchar(100) NOT NULL,
  `time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

ALTER TABLE ask_attach DROP qid;       
ALTER TABLE ask_attach DROP aid;  
ALTER TABLE ask_credit ADD `id` int(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE ask_credit DROP credit3;
ALTER TABLE ask_favorite DROP cid;
ALTER TABLE ask_favorite ADD `id` int(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE ask_favorite ADD `time` int(10) NOT NULL;
ALTER TABLE ask_note ADD `authorid` int(10) NOT NULL DEFAULT '0';
ALTER TABLE ask_note ADD `comments` int(10) NOT NULL DEFAULT '0';
ALTER TABLE ask_note ADD `views` int(10) NOT NULL DEFAULT '0';
DROP TABLE IF EXISTS ask_note_comment;
CREATE TABLE  ask_note_comment (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `noteid` int(10) NOT NULL,
  `authorid` int(10) NOT NULL,
  `author` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
ALTER TABLE ask_question  DROP url;
ALTER TABLE ask_question  DROP search_words;
ALTER TABLE ask_question  ADD  `attentions` int(10) NOT NULL DEFAULT '0';
DROP TABLE IF EXISTS ask_question_supply;
CREATE TABLE  ask_question_supply (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `qid` int(10) NOT NULL,
  `content` text NOT NULL,
  `time` int(10) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `qid` (`qid`)
) ENGINE=MyISAM;
UPDATE ask_setting SET v='bold,forecolor,insertimage,autotypeset,attachment,link,unlink,insertvideo,map,insertcode,fullscreen' WHERE k='editor_toolbars';
ALTER TABLE ask_question_tag DROP PRIMARY KEY ;       
ALTER TABLE ask_question_tag  DROP tid;
ALTER TABLE ask_question_tag  change tname  name varchar(20) NOT NULL;
ALTER TABLE ask_question_tag  ADD `time` int(10) NOT NULL DEFAULT '0';
ALTER TABLE ask_question_tag ADD PRIMARY KEY ( `qid` , `name` ) ;
TRUNCATE TABLE ask_session;
ALTER TABLE ask_session CHANGE sid sid char(16) NOT NULL ; 
ALTER TABLE ask_user DROP authstr;
ALTER TABLE ask_user DROP access_token;
ALTER TABLE ask_user ADD `introduction` varchar(200) DEFAULT NULL;
ALTER TABLE ask_user ADD `supports` int(10) NOT NULL DEFAULT '0';
DROP TABLE IF EXISTS ask_user_category;
CREATE TABLE  ask_user_category (
  `uid` int(10) NOT NULL,
  `cid` int(4) NOT NULL,
  PRIMARY KEY (`uid`,`cid`)
) ENGINE=MyISAM;
ALTER TABLE ask_usergroup ADD `level` int(4) NOT NULL DEFAULT '1';
DROP TABLE ask_gather;
DROP TABLE ask_tag;
EOT;
if (!$action) {
    echo '<meta http-equiv=Content-Type content="text/html;charset=' . TIPASK_CHARSET . '">';
    echo"本程序仅用于升级 Tipask2.0 到 Tipask2.5Beta版,请确认之前已经顺利安装Tipask2.0版本!<br><br><br>";
    echo"<b><font color=\"red\">运行本升级程序之前,请确认已经上传 Tipask2.5Beta版的全部文件和目录</font></b><br><br>";
    echo"<b><font color=\"red\">本程序只能从Tipask2.0正式版到 Tipask2.5Beta版,切勿使用本程序从其他版本升级,否则可能会破坏掉数据库资料.<br><br>强烈建议您升级之前备份数据库资料!</font></b><br><br>";
    echo"正确的升级方法为:<br>1. 上传 Tipask2.5Beta版的全部文件和目录,覆盖服务器上的Tipask2.0正式版;<br>2. 上传本程序(2.0To2.5beta.php)到 Tipask目录中;<br>3. 运行本程序,直到出现升级完成的提示;<br>4. 登录Tipask后台,更新缓存,升级完成。<br><br>";
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
    $config .= "define('TIPASK_VERSION', '2.5Beta');\r\n";
    $config .= "define('TIPASK_RELEASE', '20140326');\r\n";
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
