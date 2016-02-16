
<?php
//本程序用于把Tipask V1.4正式版 升级到V2.0beta版

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
if (!stristr(TIPASK_VERSION, '1.4')) {
    exit('本程序只能升级Tipask 1.4正式版 release 20111130 到Tipask2.0beta版 release 20120322,<br>请不要重复升级！');
}
$upgrade = <<<EOT
DROP TABLE IF EXISTS ask_answer_comment;
CREATE TABLE ask_answer_comment (
  id int(10) NOT NULL AUTO_INCREMENT,
  aid int(10) NOT NULL,
  authorid int(10) NOT NULL,
  author char(18) NOT NULL,
  content varchar(100) NOT NULL,
  credit smallint(6) NOT NULL DEFAULT '0',
  time int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS ask_expert;
CREATE TABLE ask_expert (
  uid int(10) NOT NULL,
  cid INT( 10 ) NOT NULL,
  PRIMARY KEY (uid)
)ENGINE=MyISAM;

DROP TABLE IF EXISTS ask_famous;
CREATE TABLE ask_famous(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reason` varchar(100) DEFAULT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS ask_question_tag;
CREATE TABLE ask_question_tag (
  tid int(10) NOT NULL,
  qid int(10) NOT NULL,
  tname varchar(20) NOT NULL,
  PRIMARY KEY (tid,qid)
)ENGINE=MyISAM;

DROP TABLE IF EXISTS ask_tag;
CREATE TABLE ask_tag(
`id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`letter` CHAR( 2 ) NULL ,
`name` VARCHAR( 20 ) NULL ,
`questions` INT( 10 ) NOT NULL DEFAULT '0'
) ENGINE = MYISAM ;

DROP TABLE IF EXISTS ask_tid_qid;
CREATE TABLE ask_tid_qid (
  tid int(10) NOT NULL DEFAULT '0',
  qid int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (tid,qid)
)ENGINE=MyISAM;

DROP TABLE IF EXISTS ask_topic;
CREATE TABLE ask_topic (
  id int(10) NOT NULL AUTO_INCREMENT,
  title varchar(50) DEFAULT NULL,
  describtion varchar(200) DEFAULT NULL,
  image varchar(100) DEFAULT NULL,
  displayorder INT( 10 ) NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
)ENGINE=MyISAM;

ALTER TABLE ask_user ADD `credit3` INT( 10 ) NOT NULL DEFAULT '0' AFTER `credit2` ;
ALTER TABLE ask_user ADD access_token VARCHAR( 50 )  NULL  AFTER `authstr` ;
ALTER TABLE ask_user ADD expert TINYINT( 2 ) NOT NULL DEFAULT '0' ;
ALTER TABLE ask_usergroup ADD `questionlimits` INT( 10 ) NOT NULL DEFAULT '0' AFTER `creditshigher`;
ALTER TABLE ask_usergroup ADD `answerlimits` INT( 10 ) NOT NULL DEFAULT '0' AFTER `questionlimits` ;
ALTER TABLE ask_usergroup ADD `credit3limits` INT( 10 ) NOT NULL DEFAULT '0' AFTER `answerlimits`;
ALTER TABLE ask_answer ADD `support` INT( 10 ) NOT NULL DEFAULT '0',ADD `against` INT( 10 ) NOT NULL DEFAULT '0';
ALTER TABLE ask_question DROP `t_words`,DROP `d_words`,DROP expert;
ALTER TABLE ask_question ADD `views` int(10) unsigned NOT NULL DEFAULT '0' AFTER answers;
ALTER TABLE ask_question ADD `search_words` VARCHAR( 500 ) NULL ,ADD FULLTEXT (`search_words`);
ALTER TABLE ask_credit  ADD `credit3` SMALLINT( 6 ) NOT NULL DEFAULT '0' AFTER `credit2` ;
DROP TABLE ask_recommend;
REPLACE INTO ask_setting VALUES ('editor_wordcount', 'true');
REPLACE INTO ask_setting VALUES ('editor_elementpath', 'false');
REPLACE INTO ask_setting VALUES ('editor_toolbars', 'FullScreen,Source,Undo,Redo,RemoveFormat,|,Bold,Italic,FontSize,FontFamily,ForeColor,|,InsertImage,Emotion,Map,|,JustifyLeft,JustifyCenter,JustifyRight,|,HighlightCode');
REPLACE INTO ask_setting VALUES ('gift_range', 'a:3:{i:0;s:2:"50";i:50;s:3:"100";i:100;s:3:"300";}');
REPLACE INTO ask_setting VALUES ('usernamepre', 'tipask_');
REPLACE INTO ask_setting VALUES ('usercount', '0');
REPLACE INTO ask_setting VALUES ('sum_onlineuser_time', '30');
REPLACE INTO ask_setting VALUES ('sum_category_time', '60');
REPLACE INTO ask_setting VALUES ('del_tmp_crontab', '1440');
REPLACE INTO ask_setting VALUES ('allow_credit3', '-10');
REPLACE INTO ask_setting VALUES ('apend_question_num', '5');
REPLACE INTO ask_setting VALUES ('time_friendly', '1');
REPLACE INTO ask_setting VALUES ('overdue_days', '60');
REPLACE INTO ask_setting VALUES ('msgtpl', 'a:4:{i:0;a:2:{s:5:"title";s:36:"您的问题{wtbt}有了新回答！";s:7:"content";s:51:"你在{wzmc}上的提出的问题有了新回答！";}i:1;a:2:{s:5:"title";s:54:"恭喜，您对问题{wtbt}的回答已经被采纳！";s:7:"content";s:42:"你在{wzmc}上的回答内容被采纳！";}i:2;a:2:{s:5:"title";s:78:"抱歉，您的问题{wtbt}由于长时间没有处理，现已过期关闭！";s:7:"content";s:69:"您的问题{wtbt}由于长时间没有处理，现已过期关闭！";}i:3;a:2:{s:5:"title";s:42:"您对{wtbt}的回答有了新的评分！";s:7:"content";s:36:"您的回答{hdnr}有了新评分！";}}');

REPLACE INTO ask_usergroup VALUES (1, '超级管理员', 1, 0, 1,0,0,0, '');
REPLACE INTO ask_usergroup VALUES (2, '管理员', 1, 0, 1, 0,0,0,'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove,admin_main/default,admin_main/header,admin_main/menu,admin_main/stat,admin_main/login,admin_main/logout,admin_category/default,admin_category/add,admin_category/edit,admin_category/view,admin_category/remove,admin_category/reorder,admin_question/default,admin_question/searchquestion,admin_question/searchanswer,admin_question/removequestion,admin_question/removeanswer,admin_question/edit,admin_question/editanswer,admin_question/verifyanswer,admin_question/verify,admin_question/recommend,admin_question/inrecommend,admin_question/close,admin_question/delete,admin_question/renametitle,admin_question/editquescont,admin_question/movecategory,admin_question/nosolve,admin_question/editanswercont,admin_question/deleteanswer,admin_user/default,admin_user/search,admin_user/add,admin_user/remove,admin_user/edit,admin_usergroup/default,admin_usergroup/add,admin_usergroup/remove,admin_usergroup/edit,admin_note/default,admin_note/add,admin_note/edit,admin_note/remove');
REPLACE INTO ask_usergroup VALUES (3, '分类员', 1, 0, 1,0,0,0, 'user/scorelist,index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,gift/default,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove,admin_main/default,admin_main/header,admin_main/menu,admin_main/stat,admin_main/login,admin_main/logout');
REPLACE INTO ask_usergroup VALUES (6, '游客', 1, 0, 1,1,1,0, 'user/qqlogin,index/tagquestion,expert/default,index/taglist,user/famouslist,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,question/search,user/editimg');
REPLACE INTO ask_usergroup VALUES (7, '书童', 2, 0, 80,3,3,1, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (8, '书生', 2, 80, 400,5,3,3, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (9, '秀才', 2, 400, 800,5,5,5, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (10, '举人', 2, 800, 2000,6,6,6, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (11, '解元', 2, 2000, 4000,7,7,7, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (12, '贡士', 2, 4000, 7000,8,8,8, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (13, '会元', 2, 7000, 10000,9,9,9, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (14, '同进士出身', 2, 10000, 14000,10,10,10, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (15, '大学士', 2, 14000, 18000, 11,11,10,'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (16, '探花', 2, 18000, 22000,12,12,11, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (17, '榜眼', 2, 22000, 32000,13,13,11, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (18, '状元', 2, 32000, 45000,14,14,12, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (19, '编修', 2, 45000, 60000,14,15,12, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (20, '府丞', 2, 60000, 100000,14,16,12, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (21, '翰林学士', 2, 100000, 150000,15,14,13, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (22, '御史中丞', 2, 150000, 250000,16,15,13, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (23, '詹士', 2, 250000, 400000,18,16,14, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (24, '侍郎', 2, 400000, 700000, 20,18,16,'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (25, '大学士', 2, 700000, 1000000,24,20,18, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');
REPLACE INTO ask_usergroup VALUES (26, '文曲星', 2, 1000000, 999999999,0,0,0, 'index/tagquestion,question/answercomment,user/exchange,expert/default,index/taglist,user/famouslist,user/favorite,question/addfavorite,user/space_ask,user/space_answer,user/saveimg,user/editimg,category/recommend,user/register,index/default,category/view,category/list,question/view,note/list,note/view,rss/category,rss/list,rss/question,user/space,user/scorelist,question/search,question/add,question/tagask,gift/default,gift/search,gift/add,user/register,user/default,user/score,user/ask,user/answer,user/profile,user/uppass,attach/upload,question/answer,question/adopt,question/govote,question/close,question/supply,question/add,question/addscore,question/editanswer,question/search,message/send,message/new,message/personal,message/system,message/outbox,message/view,message/remove');



EOT;
if (!$action) {
	echo '<meta http-equiv=Content-Type content="text/html;charset='.TIPASK_CHARSET.'">';
    echo"本程序仅用于升级 Tipask V1.4正式版 到 Tipask2.0beta正式版,请确认之前已经顺利安装Tipask V1.4正式版!<br><br><br>";
    echo"<b><font color=\"red\">运行本升级程序之前,请确认已经上传 Tipask2.0beta正式版的全部文件和目录</font></b><br><br>";
    echo"<b><font color=\"red\">本程序只能从 Tipask V1.4正式版 到 Tipask2.0beta正式版,切勿使用本程序从其他版本升级,否则可能会破坏掉数据库资料.<br><br>强烈建议您升级之前备份数据库资料!</font></b><br><br>";
    echo"正确的升级方法为:<br>1. 上传 Tipask2.0beta 正式版的全部文件和目录,覆盖服务器上的 Tipask V1.4正式版版;<br>2. 上传本程序(1.4To2.0beta.php)到 Tipask目录中;<br>3. 运行本程序,直到出现升级完成的提示;<br>4. 登录Tipask后台,更新缓存,升级完成。<br><br>";
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
    $config .= "define('TIPASK_VERSION', '2.0Beta');\r\n";
    $config .= "define('TIPASK_RELEASE', '20120322');\r\n";
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