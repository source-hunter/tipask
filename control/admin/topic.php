<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_topiccontrol extends base {

    function admin_topiccontrol(& $get, & $post) {
        $this->base($get,$post);
        $this->load("topic");
    }

    function ondefault($message='', $type='correctmsg') {
        $topiclist = $_ENV['topic']->get_list();
        include template("topiclist", 'admin');
    }

    function onadd() {
        if (isset($this->post['submit'])) {
            $title = $this->post['title'];
            $desrc = $this->post['desc'];
            $imgname = strtolower($_FILES['image']['name']);
            if ('' == $title || '' == $desrc) {
                $this->ondefault('请完整填写专题相关参数!', 'errormsg');
                exit;
            }
            $type = substr(strrchr($imgname, '.'), 1);
            if (!isimage($type)) {
                $this->ondefault('当前图片图片格式不支持，目前仅支持jpg、gif、png格式！', 'errormsg');
                exit;
            }
            $upload_tmp_file = TIPASK_ROOT . '/data/tmp/topic_' . random(6, 0) . '.' . $type;

            $filepath = '/data/attach/topic/topic' . random(6, 0) . '.' . $type;
            forcemkdir(TIPASK_ROOT . '/data/attach/topic');
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_tmp_file)) {
                image_resize($upload_tmp_file, TIPASK_ROOT . $filepath, 270, 220);

                $_ENV['topic']->add($title, $desrc, $filepath);
                $this->ondefault('添加成功！');
            } else {
                $this->ondefault('服务器忙，请稍后再试！');
            }
        } else {
            include template("addtopic", 'admin');
        }
    }

    /**
     * 后台修改专题
     */
    function onedit() {
        if (isset($this->post['submit'])) {
            $title = $this->post['title'];
            $desrc = $this->post['desc'];
            $tid = intval($this->post['id']);
            $imgname = strtolower($_FILES['image']['name']);
            if ('' == $title || '' == $desrc) {
                $this->ondefault('请完整填写专题相关参数!', 'errormsg');
                exit;
            }
            if ($imgname) {
                $type = substr(strrchr($imgname, '.'), 1);
                if (!isimage($type)) {
                    $this->ondefault('当前图片图片格式不支持，目前仅支持jpg、gif、png格式！', 'errormsg');
                    exit;
                }
                $filepath = '/data/attach/topic/topic' . random(6, 0) . '.' . $type;
                $upload_tmp_file = TIPASK_ROOT . '/data/tmp/topic_' . random(6, 0) . '.' . $type;
                forcemkdir(TIPASK_ROOT . '/data/attach/topic');
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_tmp_file)) {
                    image_resize($upload_tmp_file, TIPASK_ROOT . $filepath, 270, 220);
                    $_ENV['topic']->update($tid, $title, $desrc, $filepath);
                    $this->ondefault('专题修改成功！');
                } else {
                    $this->ondefault('服务器忙，请稍后再试！');
                }
            } else {
                $_ENV['topic']->update($tid, $title, $desrc);
                $this->ondefault('专题修改成功！');
            }
        } else {
            $topic = $_ENV['topic']->get(intval($this->get[2]));
            include template("addtopic", 'admin');
        }
    }

    //专题删除
    function onremove() {
        if (isset($this->post['tid'])) {
            $tids = implode(",", $this->post['tid']);
            $_ENV['topic']->remove($tids);
            $this->ondefault('专题删除成功！');
        }
    }

    /* 后台分类排序 */

    function onreorder() {
        $orders = explode(",", $this->post['order']);
        foreach ($orders as $order => $tid) {
            $_ENV['topic']->order_topic(intval($tid), $order);
        }
        $this->cache->remove('topic');
    }

    function onajaxgetselect() {
        echo $_ENV['topic']->get_select();
        exit;
    }

}

?>