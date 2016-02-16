<?php

!defined('IN_TIPASK') && exit('Access Denied');

class doingmodel {

    var $db;
    var $base;
    var $actiontable = array(
        '1' => '提出了问题',
        '2' => '回答了该问题',
        '3' => '评论该回答',
        '4' => '关注了该问题',
        '5' => '赞同了该回答',
        '6' => '对该回答进行了追问',
        '7' => '继续回答了该问题',
        '8' => '采纳了回答'
    );

    function doingmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function add($authorid, $author, $action, $qid, $content = '', $referid = 0, $refer_authorid = 0, $refer_content = '') {
        $content && $content = strip_tags($content);
        $refer_content && $refer_content = strip_tags($refer_content);
        $this->db->query("INSERT INTO " . DB_TABLEPRE . "doing(doingid,authorid,author,action,questionid,content,referid,refer_authorid,refer_content,createtime) VALUES (NULL,$authorid,'$author',$action,$qid,'$content',$referid,$refer_authorid,'$refer_content',{$this->base->time})");
    }

    function list_by_type($searchtype = 'all', $uid = 0, $start = 0, $limit = 20) {
        $doinglist = array();
        $sql = "SELECT q.title,q.attentions,q.answers,q.views,q.time,q.hidden,d.* FROM " . DB_TABLEPRE . "doing AS d," . DB_TABLEPRE . "question AS q WHERE q.id=d.questionid";
        ($searchtype == 'my') && $sql .= " AND d.authorid=$uid";
        ($searchtype == 'atentto') && $sql .=" AND q.id IN (SELECT qid FROM " . DB_TABLEPRE . "question_attention WHERE followerid=$uid)";
        $sql .=" ORDER BY d.createtime DESC LIMIT $start,$limit";
        $query = $this->db->query($sql);
        while ($doing = $this->db->fetch_array($query)) {
            $doing['question_time'] = tdate($doing['time']);
            $doing['doing_time'] = tdate($doing['createtime']);
            $doing['avatar'] = get_avatar_dir($doing['authorid']);
            $doing['actiondesc'] = $this->actiontable[$doing['action']];
            if ($doing['refer_authorid']) {
                $doing['refer_avatar'] = get_avatar_dir($doing['refer_authorid']);
            }
            $doinglist[] = $doing;
        }
        return $doinglist;
    }

    function rownum_by_type($searchtype = 'all', $uid = 0) {
        $sql = "SELECT count(d.questionid) FROM " . DB_TABLEPRE . "doing AS d," . DB_TABLEPRE . "question AS q WHERE q.id=d.questionid";
        ($searchtype == 'my') && $sql .= " AND d.authorid=$uid";
        ($searchtype == 'atentto') && $sql .=" AND q.id IN (SELECT qid FROM " . DB_TABLEPRE . "question_attention WHERE followerid=$uid)";
        return $this->db->result_first($sql);
    }

    /**
     * 推荐关注用户
     */
    function recommend_user($limit = 6) {
        $this->base->load("user");
        $userlist = array();
        $usercount = $this->db->fetch_total("user", " 1=1");
        if ($usercount > 100) {
            $usercount = 101;
        }
        $start = rand(0, $usercount-1);
        $loginuid = $this->base->user['uid'];
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user  WHERE uid<>$loginuid AND uid NOT IN (SELECT uid FROM ".DB_TABLEPRE."user_attention WHERE followerid=$loginuid)  ORDER BY followers DESC,answers DESC,regtime DESC LIMIT $start,$limit ");
        while ($user = $this->db->fetch_array($query)) {
            $user['avatar'] = get_avatar_dir($user['uid']);
            $user['category'] = $_ENV['user']->get_category($user['uid']);
            $userlist[] = $user;
        }
        return $userlist;
    }

}
