<?php
	
//本程序用于把Tipask V1.2 升级到V1.3beta版

error_reporting(0);
@set_magic_quotes_runtime(0);
@set_time_limit(1000);
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', dirname(__FILE__));
require  TIPASK_ROOT.'/config.php';
header("Content-Type: text/html; charset=".TIPASK_CHARSET);
require TIPASK_ROOT.'/lib/db.class.php';

if( '1.2'!= TIPASK_VERSION ){
	exit('本程序只能升级Tipask 1.2 正式版20101014 版本到Tipask1.3Beta版20110214,<br>请不要重复升级！');
}

$action = ($_POST['action']) ? $_POST['action'] : $_GET['action'];
$upgrade = <<<EOT
UPDATE ask_question SET url =1;
ALTER TABLE ask_answer DROP voted ;
ALTER TABLE ask_answer DROP votes ;
ALTER TABLE ask_question CHANGE url url VARCHAR( 255 ) NOT NULL DEFAULT '1';
ALTER TABLE ask_user ADD avatar varchar(100) NOT NULL after  email;
ALTER TABLE ask_answer ADD comment TINYTEXT NOT NULL default '' AFTER content; 
DROP TABLE ask_vote;
TRUNCATE TABLE ask_editor;
INSERT INTO  ask_editor  VALUES (1, 0, 'Cut', '', '', 1, '剪切(Ctrl+X)');
INSERT INTO  ask_editor  VALUES (2, 0, 'Copy', '', '', 2, '复制(Ctrl+C)');
INSERT INTO  ask_editor  VALUES (3, 0, 'Paste', '', '', 3, '粘贴(Ctrl+V)');
INSERT INTO  ask_editor  VALUES (4, 0, 'Pastetext', '', '', 4, '粘贴文本');
INSERT INTO  ask_editor  VALUES (5, 0, '|', '', '', 5, '分隔符');
INSERT INTO  ask_editor  VALUES (6, 0, 'Blocktag', '', '', 6, '段落标签');
INSERT INTO  ask_editor  VALUES (7, 0, 'Fontface', '', '', 7, '字体');
INSERT INTO  ask_editor  VALUES (8, 0, 'FontSize', '', '', 8, '字体大小');
INSERT INTO  ask_editor  VALUES (9, 1, 'Bold', '', '', 9, '加粗 (Ctrl+B)');
INSERT INTO  ask_editor  VALUES (10, 1, 'Italic', '', '', 10, '斜体 (Ctrl+I)');
INSERT INTO  ask_editor  VALUES (11, 0, 'Underline', '', '', 11, '下划线 (Ctrl+U)');
INSERT INTO  ask_editor  VALUES (12, 0, 'Strikethrough', '', '', 12, '删除线 (Ctrl+S)');
INSERT INTO  ask_editor  VALUES (13, 0, 'FontColor', '', '', 13, '字体颜色');
INSERT INTO  ask_editor  VALUES (14, 0, 'BackColor', '', '', 14, '背景颜色');
INSERT INTO  ask_editor  VALUES (15, 0, 'SelectAll', '', '', 15, '全选 (Ctrl+A)');
INSERT INTO  ask_editor  VALUES (16, 0, 'Removeformat', '', '', 16, '删除文字格式');
INSERT INTO  ask_editor  VALUES (17, 0, '|', '', '', 17, '分隔符');
INSERT INTO  ask_editor  VALUES (18, 0, 'Align', '', '', 18, '对齐');
INSERT INTO  ask_editor  VALUES (19, 0, 'List', '', '', 19, '列表');
INSERT INTO  ask_editor  VALUES (20, 0, 'Outdent', '', '', 20, '减少缩进 (Shift+Tab)');
INSERT INTO  ask_editor  VALUES (21, 0, 'Indent', '', '', 21, '增加缩进 (Tab)');
INSERT INTO  ask_editor  VALUES (22, 0, '|', '', '', 22, '分隔符');
INSERT INTO  ask_editor  VALUES (23, 1, 'Link', '', '', 23, '超链接 (Ctrl+K)');
INSERT INTO  ask_editor  VALUES (24, 0, 'Unlink', '', '', 24, '取消超链接');
INSERT INTO  ask_editor  VALUES (25, 1, 'Img', '', '', 25, '图片');
INSERT INTO  ask_editor  VALUES (26, 1, 'Flash', '', '', 26, 'Flash动画');
INSERT INTO  ask_editor  VALUES (27, 1, 'Media', '', '', 27, '多媒体文件');
INSERT INTO  ask_editor  VALUES (28, 1, 'Emot', '', '', 28, '表情');
INSERT INTO  ask_editor  VALUES (29, 1, 'Table', '', '', 29, '表格');
INSERT INTO  ask_editor  VALUES (30, 0, '|', '', '', 30, '源代码');
INSERT INTO  ask_editor  VALUES (31, 0, 'Source', '', '', 31, '源代码');
INSERT INTO  ask_editor  VALUES (32, 0, 'Preview', '', '', 32, '预览');
INSERT INTO  ask_editor  VALUES (33, 0, 'Print', '', '', 33, '打印 (Ctrl+P)');
INSERT INTO  ask_editor  VALUES (34, 0, 'Fullscreen', '', '', 34, '全屏编辑 (Esc)');

REPLACE INTO ask_usergroup VALUES (2, '管理员', 1, 0, 1, 'user/favorite,user/space_ask,user/space_answer,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove,admin_main/default,admin_main/header,admin_main/menu,admin_main/stat,admin_main/login,admin_main/logout,admin_category/default,admin_category/add,admin_category/edit,admin_category/view,admin_category/remove,admin_category/reorder,admin_question/default,admin_question/searchquestion,admin_question/searchanswer,admin_question/removequestion,admin_question/removeanswer,admin_question/edit,admin_question/editanswer,admin_question/verifyanswer,admin_question/verify,admin_question/recommend,admin_question/inrecommend,admin_question/close,admin_question/delete,admin_question/renametitle,admin_question/editquescont,admin_question/movecategory,admin_question/nosolve,admin_question/editanswercont,admin_question/deleteanswer,admin_user/default,admin_user/search,admin_user/add,admin_user/remove,admin_user/edit,admin_usergroup/default,admin_usergroup/add,admin_usergroup/remove,admin_usergroup/edit,admin_note/default,admin_note/add,admin_note/edit,admin_note/remove');
REPLACE INTO ask_usergroup VALUES (3, '分类员', 1, 0, 1, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove,admin_main/default,admin_main/header,admin_main/menu,admin_main/stat,admin_main/login,admin_main/logout');
REPLACE INTO ask_usergroup VALUES (6, '游客', 1, 0, 1, 'user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add');
REPLACE INTO ask_usergroup VALUES (7, '书童', 2, 0, 80, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (8, '书生', 2, 80, 400, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (9, '秀才', 2, 400, 800, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (10, '举人', 2, 800, 2000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (11, '解元', 2, 2000, 4000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (12, '贡士', 2, 4000, 7000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (13, '会元', 2, 7000, 10000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (14, '同进士出身', 2, 10000, 14000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (15, '大学士', 2, 14000, 18000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (16, '探花', 2, 18000, 22000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (17, '榜眼', 2, 22000, 32000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (18, '状元', 2, 32000, 45000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (19, '编修', 2, 45000, 60000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (20, '府丞', 2, 60000, 100000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (21, '翰林学士', 2, 100000, 150000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (22, '御史中丞', 2, 150000, 250000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (23, '詹士', 2, 250000, 400000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (24, '侍郎', 2, 400000, 700000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (25, '大学士', 2, 700000, 1000000, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (26, '文曲星', 2, 1000000, 999999999, 'user/favorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');


INSERT INTO ask_setting VALUES ('credit1_adopt', '5');
INSERT INTO ask_setting VALUES ('credit2_adopt', '2');

UPDATE ask_setting SET v ='Bold,Italic,Img,flash,Media,Table,Emot,Link' WHERE k='editor_items';

DROP TABLE IF EXISTS ask_famous;
CREATE TABLE IF NOT EXISTS ask_famous(
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  username char(18) NOT NULL,
  qid int(10) unsigned DEFAULT '0',
  title char(50) DEFAULT NULL,
  time int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  KEY time (time)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS ask_inform;
CREATE TABLE IF NOT EXISTS ask_inform (
  qid int(10) NOT NULL,
  title varchar(100) NOT NULL,
  content text NOT NULL,
  keywords varchar(100) NOT NULL,
  counts int(11) NOT NULL DEFAULT '1',
  time int(10) NOT NULL,
  PRIMARY KEY (qid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS ask_recommend;
CREATE TABLE ask_recommend (
	qid int(10) unsigned NOT NULL default '0',
	cid smallint(5) unsigned NOT NULL default '0',
	title char(50) NOT NULL,
	description text NOT NULL default '',
	image varchar(255) NOT NULL default '',
	url varchar(255) NOT NULL default '',
	time int(10) unsigned NOT NULL default '0',
	PRIMARY KEY  (qid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS ask_favorite;
CREATE TABLE ask_favorite (
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  qid mediumint(10) unsigned NOT NULL DEFAULT '0',
  cid smallint(5) unsigned NOT NULL DEFAULT '0',
  KEY uid(uid)
) TYPE=MyISAM;



EOT;
if(!$action) {
	echo"本程序仅用于升级 Tipask V1.2 到 Tipask1.3Beta版,请确认之前已经顺利安装Tipask V1.2!<br><br><br>";
	echo"<b><font color=\"red\">运行本升级程序之前,请确认已经上传 Tipask1.2正式版的全部文件和目录</font></b><br><br>";
	echo"<b><font color=\"red\">本程序只能从 Tipask V1.2 到 Tipask1.3Beta,切勿使用本程序从其他版本升级,否则可能会破坏掉数据库资料.<br><br>强烈建议您升级之前备份数据库资料!</font></b><br><br>";
	echo"正确的升级方法为:<br>1. 上传 Tipask1.3Beta 正式版的全部文件和目录,覆盖服务器上的 Tipask v1.2版;<br>2. 上传本程序(upgrade1.3Beta.php)到 Tipask目录中;<br>3. 运行本程序,直到出现升级完成的提示;<br>4. 登录Tipask后台,更新缓存,升级完成。<br><br>";
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
	$config .= "define('TIPASK_VERSION', '1.3Beta');\r\n";
	$config .= "define('TIPASK_RELEASE', '20110214');\r\n";
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