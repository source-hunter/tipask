<?php

!defined('IN_TIPASK') && exit('Access Denied');

class rsscontrol extends base {


    function rsscontrol(& $get,& $post) {
        $this->base($get,$post);
        $this->load('category');
        $this->load('question');
        $this->load('answer');
    }

    /*
	分类下的RSS
	rss/category/1/1
    */
    function oncategory() {
        $cid=$this->get[2];
        $status=isset($this->get[3])?$this->get[3]:'all';
        $category=$_ENV['category']->get($cid); //得到分类信息
        $cfield='cid'.$category['grade'];	//查询条件
        $questionlist=$_ENV['question']->list_by_cfield_cvalue_status($cfield,$cid,$status,0,20);//问题列表数据
        $this->writerss($questionlist,'分类'.$category['name'].$this->statusarray[$status].'问题');
    }
    /*
	列表下的RSS
	rss/list/1
    */
    function onlist() {
        $status=isset($this->get[2])?$this->get[2]:'all';
        $questionlist=$_ENV['question']->list_by_cfield_cvalue_status('',0,$status,0,20);//问题列表数据
        $this->writerss($questionlist,$this->statusarray[$status].'问题');
    }
    /*
	查看一个未解决问题的RSS
	rss/question/1
    */
    function onquestion() {
        $qid=$this->get[2];
        $question=$_ENV['question']->get($qid);
        $question['category_name']=$this->category[$question['cid']];
        $answerlistarray=$_ENV['answer']->list_by_qid($qid);
        $answerlist=$answerlistarray[0];
        $items=array();
        foreach($answerlist as $answer) {
            $item['id']=$answer['qid'];
            $item['title']=$question['title'];
            $item['description']=$answer['content'];
            $item['category_name']=$question['category_name'];
            $item['author']=$answer['author'];
            $item['time']=$answer['time'];
            $items[]=$item;
        }
        $this->writerss($items,$question['title'].'所有回答');
    }


    function writerss($items,$title) {
        header("Content-type: application/xml");
        echo "<?xml version=\"1.0\" encoding=\"".TIPASK_CHARSET."\"?>\n".
                "<rss version=\"2.0\">\n".
                "  <channel>\n".
                "    <title>".$this->setting['site_name']."</title>\n".
                "    <link>".SITE_URL."</link>\n".
                "    <description>".$title."</description>\n".
                "    <copyright>Copyright(C) ".$this->setting['site_name']."</copyright>\n".
                "    <generator>Tulipask ! Powered by Tulipsoft Inc .</generator>\n".
                "    <lastBuildDate>".gmdate('r', $this->time)."</lastBuildDate>\n".
                "    <ttl>".$this->setting['rss_ttl']."</ttl>\n".
                "    <image>\n".
                "      <url>".SITE_URL."/css/default/logo.png</url>\n".
                "      <title>".$this->setting['site_name']."</title>\n".
                "      <link>".SITE_URL."</link>\n".
                "    </image>\n";

        foreach($items as $item) {
            echo "    <item>\n".
                    "      <title>".htmlspecialchars($item['title'])."</title>\n".
                    "      <link>".SITE_URL."index.php?question/view/$item[id]</link>\n".
                    "      <description><![CDATA[$$item[description]]]></description>\n".
                    "      <category>".htmlspecialchars($item['category_name'])."</category>\n".
                    "      <author>".htmlspecialchars($item['author'])."</author>\n".
                    "      <pubDate>".@gmdate('r', $item['time'])."</pubDate>\n".
                    "    </item>\n";
        }

        echo 	"  </channel>\n".
                "</rss>";
    }


}
?>