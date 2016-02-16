<?php

!defined('IN_TIPASK') && exit('Access Denied');

class favoritecontrol extends base {

    function favoritecontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load("favorite");
    }

    function ondefault() {
        $navtitle = '我的收藏';
        @$page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize; //每页面显示$pagesize条
        $favoritelist = $_ENV['favorite']->get_list($startindex, $pagesize);
        $total = $_ENV['favorite']->rownum_by_uid();
        $departstr = page($total, $pagesize, $page, "favorite/default"); //得到分页字符串
        include template('favorite');
    }

    function ondelete() {
        if (isset($this->post['submit'])) {
            $ids = $this->post['id'];
            $_ENV['favorite']->remove($ids);
            $this->message("收藏删除成功！", 'favorite/default');
        }
    }

    function onadd() {
        $qid = intval($this->get[2]);
        $cid = intval($this->get[3]);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $message = "该问题已经收藏，不能重复收藏！";
        $this->load("favorite");
        if (!$_ENV['favorite']->get_by_qid($qid)) {
            $_ENV['favorite']->add($qid);
            $message = '问题收藏成功!';
        }
        $this->message($message, $viewurl);
    }

}

?>