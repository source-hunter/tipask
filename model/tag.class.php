<?php

!defined('IN_TIPASK') && exit('Access Denied');

class tagmodel {

    var $db;
    var $base;

    function tagmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_qid($qid) {
        $taglist = array();
        $query = $this->db->query("SELECT DISTINCT name FROM `" . DB_TABLEPRE . "question_tag` WHERE qid=$qid ORDER BY `time` ASC LIMIT 0,10");
        while ($tag = $this->db->fetch_array($query)) {
            $taglist[] = $tag['name'];
        }
        return $taglist;
    }

    function list_by_name($name) {
        return $this->db->fetch_first("SELECT * FROM `" . DB_TABLEPRE . "question_tag` WHERE name='$name'");
    }

    function get_list($start = 0, $limit = 100) {
        $taglist = array();
        $query = $this->db->query("SELECT count(qid) as questions ,name FROM " . DB_TABLEPRE . "question_tag GROUP BY name ORDER BY questions DESC LIMIT $start,$limit");
        while ($tag = $this->db->fetch_array($query)) {
            $taglist[] = $tag;
        }
        return $taglist;
    }

    function rownum() {
        $query = $this->db->query("SELECT count(name) FROM " . DB_TABLEPRE . "question_tag GROUP BY name");
        return $this->db->num_rows($query);
    }

    function multi_add($namelist, $qid) {
        if (empty($namelist))
            return false;
        $this->db->query("DELETE FROM " . DB_TABLEPRE . "question_tag WHERE qid=$qid");
        $insertsql = "INSERT INTO " . DB_TABLEPRE . "question_tag(`qid`,`name`,`time`) VALUES ";
        foreach ($namelist as $name) {
            $insertsql .= "($qid,'".  htmlspecialchars($name)."',{$this->base->time}),";
        }
        $this->db->query(substr($insertsql, 0, -1));
    }

    function remove_by_name($names) {
        $namestr = "'" . implode("','", $names) . "'";
        $this->db->query("DELETE FROM " . DB_TABLEPRE . "question_tag WHERE `name` IN ($namestr)");
    }

}

?>
