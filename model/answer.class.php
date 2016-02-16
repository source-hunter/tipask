<?php

!defined('IN_TIPASK') && exit('Access Denied');

class answermodel {

    var $db;
    var $base;
    var $statustable = array(
        'all' => ' AND status!=0',
        '0' => ' AND status=0',
        '1' => ' AND status!=0 AND adopttime=0',
        '2' => ' AND status!=0 AND adopttime>0',
    );

    function answermodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    /* 根据aid获取一个答案的内容，暂时无用 */

    function get($id) {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "answer WHERE id='$id'");
    }

    //获取得最佳答案
    function get_best($qid) {
        $bestanswer = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "answer WHERE qid=$qid AND adopttime > 0");
        $bestanswer['format_adopttime'] = tdate($bestanswer['adopttime']);
        $bestanswer['format_time'] = tdate($bestanswer['time']);
        $bestanswer['author_avartar'] = get_avatar_dir($bestanswer['authorid']);
        $bestanswer['appends'] = $this->get_appends($bestanswer['id']);
        $bestanswer['userinfo'] = array();
        $author = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user WHERE uid='" . $bestanswer['authorid'] . "'");
        if ($author) {
            $bestanswer['author_groupname'] = $this->base->usergroup[$author['groupid']]['grouptitle'];
            $bestanswer['author_grouptype'] = $this->base->usergroup[$author['groupid']]['grouptype'];
            $bestanswer['adoption_rate'] = round($author['adopts'] / $author['answers'], 2) * 100;
        }
        return $bestanswer;
    }

    function get_comment_options($groupcredit, $type = 1) {
        $maxcredit = ($groupcredit == 0 || $groupcredit > 10) ? 10 : $groupcredit;
        $optionlist = range(1, $maxcredit);
        $optionstr = '<select name="credit3">';
        foreach ($optionlist as $val) {
            if ($type)
                $optionstr .= '<option value="' . $val . '">+' . $val . '</option>';
            else
                $optionstr .= '<option value="-' . $val . '">-' . $val . '</option>';
        }
        $optionstr .= '</select>';
        return $optionstr;
    }

    /* 根据qid获取答案的列表，用于在浏览一个问题的时候显示用 */

    function list_by_qid($qid, $ordertype = 1, $rownum = 0, $start = 0, $limit = 10) {
        $answerlist = array();
        $already = 0;
        if (1 == $ordertype) {
            $timeorder = 'ASC';
            $floor = $start + 1;
        } else {
            $timeorder = 'DESC';
            $floor = ($start) ? ($rownum - $start) : $rownum;
        }
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "answer WHERE qid=$qid AND status>0 AND adopttime =0  ORDER BY supports DESC,time $timeorder LIMIT $start,$limit");
        while ($answer = $this->db->fetch_array($query)) {
            if ($answer['authorid'] == $this->base->user['uid']) {
                $already = 1;
            }
            $answer['floor'] = $floor;
            $answer['time'] = tdate($answer['time']);
            $answer['ip'] = formatip($answer['ip']);
            $answer['author_avartar'] = get_avatar_dir($answer['authorid']);
            $answer['appends'] = $this->get_appends($answer['id']);
            $answerlist[] = $answer;
            if (1 == $floor) {
                $floor++;
            } else {
                $floor--;
            }
        }
        return array($answerlist, $already);
    }

    /* 根据uid获取答案的列表，用于在用户中心，我的回答显示 */

    function list_by_uid($uid, $status, $start = 0, $limit = 5) {
        $answerlist = array();
        $sql = 'SELECT * FROM `' . DB_TABLEPRE . 'answer` WHERE `authorid`=' . $uid;
        $sql .=$this->statustable[$status] . ' ORDER BY `time` DESC LIMIT ' . $start . ',' . $limit;
        $query = $this->db->query($sql);
        while ($answer = $this->db->fetch_array($query)) {
            $answer['time'] = tdate($answer['time']);
            $answerlist[] = $answer;
        }
        return $answerlist;
    }

    /* 添加答案 */

    function add($qid, $title, $content, $status = 0) {
        $uid = $this->base->user['uid'];
        $username = $this->base->user['username'];
        $this->db->query("INSERT INTO " . DB_TABLEPRE . "answer SET qid='$qid',title='$title',author='$username',authorid='$uid',time='{$this->base->time}',content='$content',status=$status,ip='{$this->base->ip}'");
        $this->db->query("UPDATE " . DB_TABLEPRE . "question SET  answers=answers+1  WHERE id=" . $qid);
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET answers=answers+1 WHERE  uid =$uid");
        $aid = $this->db->insert_id();
    }

    /* 采纳指定的答案，问题状态变为2 已解决 */

    function adopt($qid, $answer) {
        $time = $this->base->time;
        $ret = $this->db->query("UPDATE " . DB_TABLEPRE . "answer SET adopttime='$time' WHERE id=" . $answer['id'] . " AND qid=$qid");
        if ($ret) {
            $this->db->query("UPDATE " . DB_TABLEPRE . "question SET status=2 ,`endtime`='$time' WHERE id=" . $qid);
            $this->db->query("UPDATE " . DB_TABLEPRE . "user SET adopts=adopts+1 WHERE  uid=" . $answer['authorid']);
        }
        return $ret;
    }

    /* 添加追问--追问--回答 */

    function append($answerid, $author, $authorid, $content) {
        $this->db->query("INSERT INTO " . DB_TABLEPRE . "answer_append(appendanswerid,answerid,author,authorid,content,time) VALUES (NULL,$answerid,'$author',$authorid,'$content',{$this->base->time})");
        return $this->db->insert_id();
    }

    /* 获取追问信息列表 */

    function get_appends($answerid, $start = 0, $limit = 20) {
        $appendlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "answer_append WHERE answerid='$answerid' ORDER BY time ASC LIMIT $start,$limit");
        while ($append = $this->db->fetch_array($query)) {
            $append['format_time'] = tdate($append['time']);
            $appendlist[] = $append;
        }
        return $appendlist;
    }

    /* 修改回答，同时重置回答的状态 */

    function update_content($aid, $content, $status = 0) {
        $this->db->query("UPDATE `" . DB_TABLEPRE . "answer` set content='$content',status=$status  WHERE `id` =$aid");
    }

    /* 后台回答搜索 */

    function list_by_search($title = '', $author = '', $keyword = '', $datestart = '', $dateend = '', $start = 0, $limit = 10) {
        $sql = "SELECT * FROM `" . DB_TABLEPRE . "answer` WHERE 1=1 ";
        $title && ($sql.=" AND `title` like '$title%' ");
        $author && ($sql.=" AND `author`='$author'");
        $keyword && ($sql.=" AND `content` like '%$keyword%' ");
        $datestart && ($sql .= " AND `time`>= " . strtotime($datestart));
        $dateend && ($sql .=" AND `time`<= " . strtotime($dateend));
        $sql.=" ORDER BY `id` DESC LIMIT $start,$limit ";
        $answerlist = array();
        $query = $this->db->query($sql);
        while ($answer = $this->db->fetch_array($query)) {
            $answer['time'] = tdate($answer['time']);
            $answerlist[] = $answer;
        }
        return $answerlist;
    }

    function rownum_by_search($title = '', $author = '', $keyword = '', $datestart = '', $dateend = '') {
        $condition = " 1=1 ";
        $title && ($condition.=" AND `title` like '$title%' ");
        $author && ($condition.=" AND `author`='$author'");
        $keyword && ($condition.=" AND `content` like '$keyword%' ");
        $datestart && ($condition .= " AND `time`>= " . strtotime($datestart));
        $dateend && ($condition .=" AND `time`<= " . strtotime($dateend));
        return $this->db->fetch_total('answer', $condition);
    }

    /* 时间段内问题数目 */

    function rownum_by_time($uid, $hours = 1) {
        $starttime = strtotime(date("Y-m-d H:00:00", $this->base->time));
        $endtime = $starttime + $hours * 3600;
        return $this->db->fetch_total('answer', " `time`>$starttime AND `time`<$endtime AND authorid=$uid");
    }

    function list_by_condition($condition, $start = 0, $limit = 10) {
        $answerlist = array();
        $query = $this->db->query("SELECT * FROM `" . DB_TABLEPRE . "answer` WHERE $condition ORDER BY `time` DESC limit $start,$limit");
        while ($answer = $this->db->fetch_array($query)) {
            $answer['time'] = tdate($answer['time']);
            $answerlist[] = $answer;
        }
        return $answerlist;
    }

    function remove($aids) {
        //更新问题回答数
        $query = $this->db->query("SELECT qid,count(*) as answers FROM " . DB_TABLEPRE . "answer WHERE `id` IN ($aids) GROUP BY `qid`");
        while (list($qid, $answers) = $this->db->fetch_row($query)) {
            $this->db->query("UPDATE " . DB_TABLEPRE . "question SET answers=answers-$answers WHERE `id`=$qid");
        }
        //更新回答人回答数
        $query = $this->db->query("SELECT authorid,count(*) as answers FROM " . DB_TABLEPRE . "answer WHERE `id` IN ($aids) GROUP BY `authorid`");
        while (list($authorid, $answers) = $this->db->fetch_row($query)) {
            $this->db->query("UPDATE " . DB_TABLEPRE . "user SET answers=answers-$answers WHERE `uid`=$authorid");
        }
        //删除回答
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "answer_comment ` WHERE `aid` IN ($aids)");
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "answer_support ` WHERE `aid` IN ($aids)");
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "answer` WHERE `id` IN ($aids)");
    }

    function remove_by_qid($aid, $qid) {
        $this->db->query("DELETE FROM `" . DB_TABLEPRE . "answer` WHERE `id`=$aid");
        $this->db->query("UPDATE `" . DB_TABLEPRE . "question` SET answers=answers-1 WHERE `id`=$qid");
    }

    function update_time_content($aid, $time, $content) {
        $this->db->query("UPDATE `" . DB_TABLEPRE . "answer` SET `content`='$content',`time`=$time WHERE `id`=$aid");
    }

    function change_to_verify($aids) {
        $this->db->query("UPDATE `" . DB_TABLEPRE . "answer` SET `status`=1 WHERE `status`=0 AND `id` IN ($aids)");
    }

    function get_support_by_sid_aid($sid, $aid) {
        return $this->db->fetch_total("answer_support", " sid='$sid' AND aid=$aid ");
    }

    function add_support($sid, $aid, $authorid) {
        $this->db->query("REPLACE INTO " . DB_TABLEPRE . "answer_support(sid,aid,time) VALUES ('$sid',$aid,{$this->base->time})");
        $this->db->query("UPDATE `" . DB_TABLEPRE . "answer` SET `supports`=supports+1 WHERE `id`=$aid");
        $this->db->query("UPDATE `" . DB_TABLEPRE . "user` SET `supports`=supports+1 WHERE `uid`=$authorid");
    }

}

?>
