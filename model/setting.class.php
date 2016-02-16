<?php

!defined('IN_TIPASK') && exit('Access Denied');

class settingmodel {

    var $db;
    var $base;

    function settingmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }


    function update($setting) {
        foreach($setting as $key=>$value) {
            $this->db->query("REPLACE INTO ".DB_TABLEPRE."setting (k,v) VALUES ('$key','$value')");
        }
        $this->base->cache->remove('setting');
    }

    /*读取view文件夹，获取模板的选项*/
    function tpl_list() {
        $tpllist=array();
        $filedir=TIPASK_ROOT.'/view';
        $handle=opendir($filedir);
        while($filename=readdir($handle)) {
            if (is_dir($filedir.'/'.$filename) && '.'!=$filename{0} && 'admin'!=$filename) {
                $tpllist[]=$filename;
            }
        }
        closedir($handle);
        return $tpllist;
    }

    /**
     * 分类问题数目校正
     */
    function regulate_category() {
        $query = $this->db->query("SELECT * FROM ".DB_TABLEPRE."category");
        while($category = $this->db->fetch_array($query)) {
            $q1=$this->db->fetch_total('question','cid1='.$category['id']);
            $q2=$this->db->fetch_total('question','cid2='.$category['id']);
            $q3=$this->db->fetch_total('question','cid3='.$category['id']);
            $questions=$q1+$q2+$q3;
            $this->db->query("UPDATE ".DB_TABLEPRE."category set questions=$questions where id=".$category['id']);
        }
    }
    /**
     * 问题回答数数目校正
     */
    function regulate_question() {
        $query = $this->db->query("SELECT * FROM ".DB_TABLEPRE."question");
        while($question = $this->db->fetch_array($query)) {
            $answers = $this->db->fetch_total('answer','qid='.$question['id']);
            $this->db->query("UPDATE ".DB_TABLEPRE."question set answers=$answers where id=".$question['id']);
        }
    }
    /**
     * 用户问题回答数目校正
     */
    function regulate_user() {
        $query = $this->db->query("SELECT * FROM ".DB_TABLEPRE."user");
        while($user = $this->db->fetch_array($query)) {
            $questions=$this->db->fetch_total('question','authorid='.$user['uid']);
            $answers=$this->db->fetch_total('answer','authorid='.$user['uid']);
            $this->db->query("UPDATE ".DB_TABLEPRE."user SET questions=$questions,answers=$answers where uid=".$user['uid']);
        }
    }
    
    function get_hot_words($hot_words) {
        $lines = explode("\n",$hot_words);
        $wordslist = array();
        foreach ($lines as $line){
            $words = explode("，",$line);
            if(is_array($words)){
                $word['w']=$words[0];
                $word['qid']=intval($words[1]);
                $wordslist[] = $word;
            }
            
        }
        return serialize($wordslist);
    }

}

?>