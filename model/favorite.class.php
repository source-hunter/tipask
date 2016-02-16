<?php

!defined('IN_TIPASK') && exit('Access Denied');

class favoritemodel {

    var $db;
    var $base;

    function favoritemodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_qid($qid) {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "favorite WHERE `qid`=$qid AND `uid`=" . $this->base->user['uid']);
    }

    function get_list($start = 0, $limit = 10) {
        $uid = $this->base->user['uid'];
        $questionlist = array();
        $query = $this->db->query("SELECT q.title,f.qid,f.id,f.time,f.uid FROM `" . DB_TABLEPRE . "question` as q ,`" . DB_TABLEPRE . "favorite` as f  WHERE q.id=f.qid AND f.uid=$uid  LIMIT $start,$limit");
        while ($question = $this->db->fetch_array($query)) {
            $question['format_time'] = tdate($question['time']);
            $questionlist[] = $question;
        }
        return $questionlist;
    }

    function rownum_by_uid($uid = 0) {
        (!$uid) && $uid = $this->base->user['uid'];
        $query = $this->db->query("SELECT count(*) as size  FROM `" . DB_TABLEPRE . "question` as q ,`" . DB_TABLEPRE . "favorite` as f  WHERE q.id=f.qid AND f.uid=$uid ");
        $favorite = $this->db->fetch_array($query);
        return $favorite['size'];
    }

    function add($qid) {
        $uid = $this->base->user['uid'];
        $this->db->query('REPLACE INTO `' . DB_TABLEPRE . "favorite`(`qid`,`uid`,`time`) values ($qid,$uid,{$this->base->time})");
        return $this->db->insert_id();
    }

    function remove($ids) {
        if (is_array($ids)) {
            $ids = implode(",", $ids);
        }
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "favorite` WHERE `id` IN($ids)");
    }

}

?>
