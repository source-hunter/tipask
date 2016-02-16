<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_categorycontrol extends base {

    function admin_categorycontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('category');
    }

    function ondefault() {
        $category['grade'] = $pid = 0;
        $categorylist = $_ENV['category']->list_by_pid($pid);
        include template('categorylist', 'admin');
    }

    function onadd() {
        if (isset($this->post['submit'])) {
            $pid = 0;
            $category1 = $this->post['category1'];
            $category2 = $this->post['category2'];
            $category3 = $this->post['category3'];
            if ($category3) {
                $pid = $category3;
            } else if ($category2) {
                $pid = $category2;
            } else if ($category1) {
                $pid = $category1;
            }
            $lines = explode("\n", $this->post['categorys']);
            $_ENV['category']->add($lines, $pid);
            cleardir(TIPASK_ROOT . '/data/cache'); //清除缓存文件
            $this->ondefault();
        } else {
            $id = intval($this->get[2]);
            $selectedarray = array();
            if ($id) {
                $category = $this->category[$id];
                $item = $category;
                for ($grade = $category['grade']; $grade > 0; $grade--) {
                    $selectedarray[] = $item['id'];
                    $item['pid'] && $item = $this->category[$item['pid']];
                }
            }
            list($category1, $category2, $category3) = array_reverse($selectedarray);
            $categoryjs = $_ENV['category']->get_js();
            include template('addcategory', 'admin');
        }
    }

    function onedit() {
        $id = (isset($this->get[2])) ? $this->get[2] : $this->post['id'];
        if (isset($this->post['submit'])) {
            $name = trim($this->post['name']);
            $categorydir = '';
            $cid = 0;
            $category1 = $this->post['category1'];
            $category2 = $this->post['category2'];
            $category3 = $this->post['category3'];
            if ($category3) {
                $cid = $category3;
            } else if ($category2) {
                $cid = $category2;
            } else if ($category1) {
                $cid = $category1;
            }
            $_ENV['category']->update_by_id($id, $name, $categorydir, $cid);
            cleardir(TIPASK_ROOT . '/data/cache'); //清除缓存文件
            $this->post['cid'] ? $this->onview($this->post['cid']) : $this->ondefault();
        } else {
            $category = $this->category[$id];
            $item = $category;
            $selectedarray = array();
            for ($grade = $category['grade']; $grade > 1; $grade--) {
                $selectedarray[] = $item['pid'];
                $item = $this->category[$item['pid']];
            }
            list($category1, $category2, $category3) = array_reverse($selectedarray);
            $categoryjs = $_ENV['category']->get_js();
            include template('editcategory', 'admin');
        }
    }

    //后台分类管理查看一个分类
    function onview($cid = 0, $msg = '') {
        $cid = $cid ? $cid : intval($this->get[2]);
        $navlist = $_ENV['category']->get_navigation($cid); //获取导航
        $category = $this->category[$cid];
        $categorylist = $_ENV['category']->list_by_cid_pid($cid, $category['pid']); //获取子分类
        $pid = $cid;
        $msg && $message = $msg;
        include template('categorylist', 'admin');
    }

    //删除分类
    function onremove() {
        if (isset($this->post['cid'])) {
            $cids = implode(",", $this->post['cid']);
            $pid = intval($this->post['hiddencid']);
            $_ENV['category']->remove($cids);
            $this->onview($pid, '分类删除成功!');
        }
    }

    /* 后台分类排序 */

    function onreorder() {
        $orders = explode(",", $this->post['order']);
        foreach ($orders as $order => $cid) {
            $_ENV['category']->order_category(intval($cid), $order);
        }
        $this->cache->remove('category');
    }

}

?>