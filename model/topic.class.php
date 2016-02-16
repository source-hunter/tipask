<?php

!defined('IN_TIPASK') && exit('Access Denied');

class topicmodel {

    var $db;
    var $base;

    function topicmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    /* 获取分类信息 */

    function get($id) {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "topic WHERE id='$id'");
    }

    function get_list($showquestion=0, $start=0, $limit=6,$questionsize=10) {
        $topiclist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "topic order by displayorder asc LIMIT $start,$limit");
        while ($topic = $this->db->fetch_array($query)) {
            ($showquestion == 1) && $topic['questionlist'] = $this->get_questions($topic['id'],0,$questionsize); //首页专题掉用
            ($showquestion == 2) && $topic['questionlist'] = $this->get_questions($topic['id']); //专题列表页掉用
            $topiclist[] = $topic;
        }
        return $topiclist;
    }

    function get_questions($id, $start=0, $limit=5) {
        $questionlist = array();
        $query = $this->db->query("SELECT q.title,q.id FROM " . DB_TABLEPRE . "tid_qid as t," . DB_TABLEPRE . "question as q WHERE t.qid=q.id AND t.tid=$id LIMIT $start,$limit");
        while ($question = $this->db->fetch_array($query)) {
            $questionlist[] = $question;
        }
        return $questionlist;
    }

    function get_select() {
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "topic   LIMIT 0,50");
        $select = '<select name="topiclist">';
        while ($topic = $this->db->fetch_array($query)) {
            $select .= '<option value="' . $topic['id'] . '">' . $topic['title'] . '</option>';
        }
        $select .='</select>';
        return $select;
    }

    /* 后台管理编辑专题 */

    function update($id, $title, $desrc, $filepath='') {
        if ($filepath)
            $this->db->query("UPDATE `" . DB_TABLEPRE . "topic` SET  `title`='$title' ,`describtion`='$desrc' , `image`='$filepath'  WHERE `id`=$id");
        else
            $this->db->query("UPDATE `" . DB_TABLEPRE . "topic` SET  `title`='$title' ,`describtion`='$desrc'  WHERE `id`=$id");
    }

    /* 后台添加专题 */

    function add($title, $desc, $image) {
        $this->db->query("INSERT INTO `" . DB_TABLEPRE . "topic`(`title`,`describtion`,`image`) VALUES ('$title','$desc','$image')");
    }

    function addtotopic($qids, $tid) {
        $qidlist = explode(",", $qids);
        $sql = "INSERT INTO " . DB_TABLEPRE . "tid_qid (`tid`,`qid`) VALUES ";
        foreach ($qidlist as $qid) {
            $sql .=" ($tid,$qid),";
        }
        $this->db->query(substr($sql, 0, -1));
    }

    /* 后台管理删除分类 */

    function remove($tids) {
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "topic` WHERE `id` IN  ($tids)");
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "tid_qid` WHERE `tid` IN ($tids)");
    }

    /* 后台管理移动分类顺序 */

    function order_topic($id, $order) {
        $this->db->query("UPDATE `" . DB_TABLEPRE . "topic` SET `displayorder` = '{$order}' WHERE `id` = '{$id}'");
    }

}

?>
