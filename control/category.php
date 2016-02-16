<?php

!defined('IN_TIPASK') && exit('Access Denied');

class categorycontrol extends base {

    function categorycontrol(& $get, & $post) {
        $this->base($get,$post);
        $this->load('category');
        $this->load('question');
    }

    //category/view/1/2/10
    //cid，status,第几页？
    function onview() {
        $this->load("expert");
        $cid = intval($this->get[2])?$this->get[2]:'all';
        $status = isset($this->get[3]) ? $this->get[3] : 'all';
        @$page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize; //每页面显示$pagesize条
        if ($cid != 'all') {
            $category = $this->category[$cid]; //得到分类信息
            $navtitle = $category['name'];
            $cfield = 'cid' . $category['grade'];
        } else {
            $category = $this->category;
            $navtitle = '全部分类';
            $cfield = '';
            $category['pid'] = 0;
        }
        $rownum = $_ENV['question']->rownum_by_cfield_cvalue_status($cfield, $cid, $status); //获取总的记录数
        $questionlist = $_ENV['question']->list_by_cfield_cvalue_status($cfield, $cid, $status, $startindex, $pagesize); //问题列表数据
        $departstr = page($rownum, $pagesize, $page, "category/view/$cid/$status"); //得到分页字符串
        $navlist = $_ENV['category']->get_navigation($cid); //获取导航
        $sublist = $_ENV['category']->list_by_cid_pid($cid, $category['pid']); //获取子分类
        $expertlist = $_ENV['expert']->get_by_cid($cid); //分类专家
        /* SEO */
        if ($this->setting['seo_category_title']) {
            $seo_title = str_replace("{wzmc}", $this->setting['site_name'], $this->setting['seo_category_title']);
            $seo_title = str_replace("{flmc}", $navtitle, $seo_title);
        }
        if ($this->setting['seo_category_description']) {
            $seo_description = str_replace("{wzmc}", $this->setting['site_name'], $this->setting['seo_category_description']);
            $seo_description = str_replace("{flmc}", $navtitle, $seo_description);
        }
        if ($this->setting['seo_category_keywords']) {
            $seo_keywords = str_replace("{wzmc}", $this->setting['site_name'], $this->setting['seo_category_keywords']);
            $seo_keywords = str_replace("{flmc}", $navtitle, $seo_keywords);
        }
        include template('category');
    }

    //category/list/1/10
    //status，第几页？
    function onlist() {
        $status = isset($this->get[2]) ? $this->get[2] : 'all';
        $navtitle = $statustitle = $this->statusarray[$status];
        @$page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize; //每页面显示$pagesize条
        $rownum = $_ENV['question']->rownum_by_cfield_cvalue_status('', 0, $status); //获取总的记录数
        $questionlist = $_ENV['question']->list_by_cfield_cvalue_status('', 0, $status, $startindex, $pagesize); //问题列表数据
        $departstr = page($rownum, $pagesize, $page, "category/list/$status"); //得到分页字符串
        $metakeywords = $navtitle;
        $metadescription = '问题列表' . $navtitle;
        include template('list');
    }

    function onrecommend() {
        $this->load('topic');
        $navtitle = '专题列表';
        @$page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $rownum = $this->db->fetch_total('topic');
        $topiclist = $_ENV['topic']->get_list(2,$startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "category/recommend");
        $metakeywords = $navtitle;
        $metadescription = '精彩推荐列表';
        include template('recommendlist');
    }

}

?>