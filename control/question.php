<?php

!defined('IN_TIPASK') && exit('Access Denied');

//0、未审核 1、待解决、2、已解决 4、悬赏的 9、 已关闭问题

class questioncontrol extends base {

    function questioncontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load("question");
        $this->load("category");
        $this->load("answer");
        $this->load("expert");
        $this->load("tag");
        $this->load("user");
        $this->load("userlog");
        $this->load("doing");
    }

    /* 提交问题 */

    function onadd() {
        $navtitle = "提出问题";
        if (isset($this->post['submit'])) {
            $title = htmlspecialchars($this->post['title']);
            $description = $this->post['description'];
            $cid1 = $this->post['cid1'];
            $cid2 = $this->post['cid2'];
            $cid3 = $this->post['cid3'];
            $cid = $this->post['cid'];
            $hidanswer = intval($this->post['hidanswer']) ? 1 : 0;
            $price = abs($this->post['givescore']);
            $askfromuid = $this->post['askfromuid'];
            $this->setting['code_ask'] && $this->checkcode(); //检查验证码
            $offerscore = $price;
            ($hidanswer) && $offerscore+=10;
            (intval($this->user['credit2']) < $offerscore) && $this->message("财富值不够!", 'BACK');
            //检查审核和内容外部URL过滤
            $status = intval(1 != (1 & $this->setting['verify_question']));
            $allow = $this->setting['allow_outer'];
            if (3 != $allow && has_outer($description)) {
                0 == $allow && $this->message("内容包含外部链接，发布失败!", 'BACK');
                1 == $allow && $status = 0;
                2 == $allow && $description = filter_outer($description);
            }
            //检查标题违禁词
            $contentarray = checkwords($title);
            1 == $contentarray[0] && $status = 0;
            2 == $contentarray[0] && $this->message("问题包含非法关键词，发布失败!", 'BACK');
            $title = $contentarray[1];

            //检查问题描述违禁词
            $descarray = checkwords($description);
            1 == $descarray[0] && $status = 0;
            2 == $descarray[0] && $this->message("问题描述包含非法关键词，发布失败!", 'BACK');
            $description = $descarray[1];

            /* 检查提问数是否超过组设置 */
            ($this->user['questionlimits'] && ($_ENV['userlog']->rownum_by_time('ask') >= $this->user['questionlimits'])) &&
                    $this->message("你已超过每小时最大提问数" . $this->user['questionlimits'] . ',请稍后再试！', 'BACK');

            $qid = $_ENV['question']->add($title, $description, $hidanswer, $price, $cid, $cid1, $cid2, $cid3, $status);

            //增加用户积分，扣除用户悬赏的财富
            if ($this->user['uid']) {
                $this->credit($this->user['uid'], 0, -$offerscore, 0, 'offer');
                $this->credit($this->user['uid'], $this->setting['credit1_ask'], $this->setting['credit2_ask']);
            }
            $viewurl = urlmap('question/view/' . $qid, 2);
            /* 如果是向别人提问，则需要发个消息给别人 */
            if ($askfromuid) {
                $this->load("message");
                $this->load("user");
                $touser = $_ENV['user']->get_by_uid($askfromuid);
                $username = addslashes($this->user['username']);
                $_ENV['message']->add($username, $this->user['uid'], $touser['uid'], '问题求助:' . $title, $description . '<br /> <a href="' . SITE_URL . $this->setting['seo_prefix'] . $viewurl . $this->setting['seo_suffix'] . '">点击查看问题</a>');
                sendmail($touser, '问题求助:' . $title, $description . '<br /> <a href="' . SITE_URL . $this->setting['seo_prefix'] . $viewurl . $this->setting['seo_suffix'] . '">点击查看问题</a>');
            }
            //如果ucenter开启，则postfeed
            if ($this->setting["ucenter_open"] && $this->setting["ucenter_ask"]) {
                $this->load('ucenter');
                $_ENV['ucenter']->ask_feed($qid, $title, $description);
            }
            $_ENV['userlog']->add('ask');
            $_ENV['doing']->add($this->user['uid'], $this->user['username'], 1, $qid, $description);
            if (0 == $status) {
                $this->message('问题发布成功！为了确保问答的质量，我们会对您的提问内容进行审核。请耐心等待......', 'BACK');
            } else {
                $this->message("问题发布成功!", $viewurl);
            }
        } else {
            if (0 == $this->user['uid']) {
                $this->setting["ucenter_open"] && $this->message("UCenter开启后游客不能提问!", 'BACK');
            }
            $categoryjs = $_ENV['category']->get_js();
            $word = $this->post['word'];
            $askfromuid = intval($this->get['2']);
            if ($askfromuid)
                $touser = $_ENV['user']->get_by_uid($askfromuid);
            include template('ask');
        }
    }

    /* 浏览问题 */

    function onview() {
        $this->setting['stopcopy_on'] && $_ENV['question']->stopcopy(); //是否开启了防采集功能
        $qid = intval($this->get[2]); //接收qid参数
        $_ENV['question']->add_views($qid); //更新问题浏览次数
        $question = $_ENV['question']->get($qid);
        empty($question) && $this->message('问题已经被删除！');
        (0 == $question['status']) && $this->message('问题正在审核中,请耐心等待！');
        /* 问题过期处理 */
        if ($question['endtime'] < $this->time && ($question['status'] == 1 || $question['status'] == 4)) {
            $question['status'] = 9;
            $_ENV['question']->update_status($qid, 9);
            $this->send($question['authorid'], $question['id'], 2);
        }
        $asktime = tdate($question['time']);
        $endtime = timeLength($question['endtime'] - $this->time);
        $solvetime = tdate($question['endtime']);
        $supplylist = $_ENV['question']->get_supply($question['id']);
        if (isset($this->get[3]) && $this->get[3] == 1) {
            $ordertype = 2;
            $ordertitle = '倒序查看回答';
        } else {
            $ordertype = 1;
            $ordertitle = '正序查看回答';
        }
        //回答分页        
        @$page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $rownum = $this->db->fetch_total("answer", " qid=$qid AND status>0 AND adopttime =0");
        $answerlistarray = $_ENV['answer']->list_by_qid($qid, $this->get[3], $rownum, $startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "question/view/$qid/" . $this->get[3]);
        $answerlist = $answerlistarray[0];
        $already = $answerlistarray[1];
        $solvelist = $_ENV['question']->list_by_cfield_cvalue_status('cid', $question['cid'], 2);
        $nosolvelist = $_ENV['question']->list_by_cfield_cvalue_status('cid', $question['cid'], 1);
        $navlist = $_ENV['category']->get_navigation($question['cid'], true);
        $expertlist = $_ENV['expert']->get_by_cid($question['cid']);
        $typearray = array('1' => 'nosolve', '2' => 'solve', '4' => 'nosolve', '6' => 'solve', '9' => 'close');
        $typedescarray = array('1' => '待解决', '2' => '已解决', '4' => '高悬赏', '6' => '已推荐', '9' => '已关闭');
        $navtitle = $question['title'];
        $dirction = $typearray[$question['status']];
        ('solve' == $dirction) && $bestanswer = $_ENV['answer']->get_best($qid);
        $categoryjs = $_ENV['category']->get_js();
        $taglist = $_ENV['tag']->get_by_qid($qid);
        $expertlist = $_ENV['expert']->get_by_cid($question['cid']);
        $is_followed = $_ENV['question']->is_followed($qid, $this->user['uid']);
        $followerlist = $_ENV['question']->get_follower($qid);
        /* SEO */
        $curnavname = $navlist[count($navlist) - 1]['name'];
        if (!$bestanswer) {
            $bestanswer = array();
            $bestanswer['content'] = '';
        }
        if ($this->setting['seo_question_title']) {
            $seo_title = str_replace("{wzmc}", $this->setting['site_name'], $this->setting['seo_question_title']);
            $seo_title = str_replace("{wtbt}", $question['title'], $seo_title);
            $seo_title = str_replace("{wtzt}", $typedescarray[$question['status']], $seo_title);
            $seo_title = str_replace("{flmc}", $curnavname, $seo_title);
        }
        if ($this->setting['seo_question_description']) {
            $seo_description = str_replace("{wzmc}", $this->setting['site_name'], $this->setting['seo_question_description']);
            $seo_description = str_replace("{wtbt}", $question['title'], $seo_description);
            $seo_description = str_replace("{wtzt}", $typedescarray[$question['status']], $seo_description);
            $seo_description = str_replace("{flmc}", $curnavname, $seo_description);
            $seo_description = str_replace("{wtms}", strip_tags($question['description']), $seo_description);
            $seo_description = str_replace("{zjda}", strip_tags($bestanswer['content']), $seo_description);
        }
        if ($this->setting['seo_question_keywords']) {
            $seo_keywords = str_replace("{wzmc}", $this->setting['site_name'], $this->setting['seo_question_keywords']);
            $seo_keywords = str_replace("{wtbt}", $question['title'], $seo_keywords);
            $seo_keywords = str_replace("{wtzt}", $typedescarray[$question['status']], $seo_keywords);
            $seo_keywords = str_replace("{flmc}", $curnavname, $seo_keywords);
            $seo_keywords = str_replace("{wtbq}", implode(",", $taglist), $seo_keywords);
            $seo_keywords = str_replace("{description}", strip_tags($question['description']), $seo_keywords);
            $seo_keywords = str_replace("{zjda}", strip_tags($bestanswer['content']), $seo_keywords);
        }
        include template($dirction);
    }

    /* 提交回答 */

    function onanswer() {
        //只允许专家回答问题
        if (isset($this->setting['allow_expert']) && $this->setting['allow_expert'] && !$this->user['expert']) {
            $this->message('站点已设置为只允许专家回答问题，如有疑问请联系站长.');
        }
        $qid = $this->post['qid'];
        $question = $_ENV['question']->get($qid);
        if (!$question) {
            $this->message('提交回答失败,问题不存在!');
        }
        if ($this->user['uid'] == $question['authorid']) {
            $this->message('提交回答失败，不能自问自答！', 'question/view/' . $qid);
        }
        $this->setting['code_ask'] && $this->checkcode(); //检查验证码
        $already = $_ENV['question']->already($qid, $this->user['uid']);
        $already && $this->message('不能重复回答同一个问题，可以修改自己的回答！', 'question/view/' . $qid);
        $title = $this->post['title'];
        $content = $this->post['content'];
        //检查审核和内容外部URL过滤
        $status = intval(2 != (2 & $this->setting['verify_question']));
        $allow = $this->setting['allow_outer'];
        if (3 != $allow && has_outer($content)) {
            0 == $allow && $this->message("内容包含外部链接，发布失败!", 'BACK');
            1 == $allow && $status = 0;
            2 == $allow && $content = filter_outer($content);
        }
        //检查违禁词
        $contentarray = checkwords($content);
        1 == $contentarray[0] && $status = 0;
        2 == $contentarray[0] && $this->message("内容包含非法关键词，发布失败!", 'BACK');
        $content = $contentarray[1];

        /* 检查提问数是否超过组设置 */
        ($this->user['answerlimits'] && ($_ENV['userlog']->rownum_by_time('answer') >= $this->user['answerlimits'])) &&
                $this->message("你已超过每小时最大回答数" . $this->user['answerlimits'] . ',请稍后再试！', 'BACK');

        $_ENV['answer']->add($qid, $title, $content, $status);
        //回答问题，添加积分
        $this->credit($this->user['uid'], $this->setting['credit1_answer'], $this->setting['credit2_answer']);
        //给提问者发送通知
        $this->send($question['authorid'], $question['id'], 0);
        //如果ucenter开启，则postfeed
        if ($this->setting["ucenter_open"] && $this->setting["ucenter_answer"]) {
            $this->load('ucenter');
            $_ENV['ucenter']->answer_feed($question, $content);
        }
        $viewurl = urlmap('question/view/' . $qid, 2);
        $_ENV['userlog']->add('answer');
        $_ENV['doing']->add($this->user['uid'], $this->user['username'], 2, $qid, $content);
        if (0 == $status) {
            $this->message('提交回答成功！为了确保问答的质量，我们会对您的回答内容进行审核。请耐心等待......', 'BACK');
        } else {
            $this->message('提交回答成功！', $viewurl);
        }
    }

    /* 采纳答案 */

    function onadopt() {
        $qid = intval($this->post['qid']);
        $aid = intval($this->post['aid']);
        $comment = $this->post['content'];
        $question = $_ENV['question']->get($qid);
        $answer = $_ENV['answer']->get($aid);
        $ret = $_ENV['answer']->adopt($qid, $answer);
        if ($ret) {
            $this->load("answer_comment");
            $_ENV['answer_comment']->add($aid, $comment, $question['authorid'], $question['author']);
            $this->credit($answer['authorid'], $this->setting['credit1_adopt'], intval($question['price'] + $this->setting['credit2_adopt']), 0, 'adopt');
            $this->send($answer['authorid'], $question['id'], 1);
            $viewurl = urlmap('question/view/' . $qid, 2);
            $_ENV['doing']->add($question['authorid'], $question['author'], 8, $qid, $comment, $answer['id'], $answer['authorid'], $answer['content']);
        }

        $this->message('采纳答案成功！', $viewurl);
    }

    /* 结束问题，没有满意的回答，还可直接结束提问，关闭问题。 */

    function onclose() {
        $qid = intval($this->get[2]) ? intval($this->get[2]) : $this->post['qid'];
        $_ENV['question']->update_status($qid, 9);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $this->message('关闭问题成功！', $viewurl);
    }

    /* 补充提问细节 */

    function onsupply() {
        $qid = $this->get[2] ? $this->get[2] : $this->post['qid'];
        $question = $_ENV['question']->get($qid);
        if (!$question) {
            $this->message("问题不存在或已被删除!", "STOP");
        }
        if ($question['authorid'] != $this->user['uid'] || $this->user['uid'] == 0) {
            $this->message("非法操作!", "STOP");
            exit;
        }
        $navlist = $_ENV['category']->get_navigation($question['cid'], true);
        if (isset($this->post['submit'])) {
            $content = $this->post['content'];
            //检查审核和内容外部URL过滤
            $status = intval(1 != (1 & $this->setting['verify_question']));
            $allow = $this->setting['allow_outer'];
            if (3 != $allow && has_outer($content)) {
                0 == $allow && $this->message("内容包含外部链接，发布失败!", 'BACK');
                1 == $allow && $status = 0;
                2 == $allow && $content = filter_outer($content);
            }
            //检查违禁词
            $contentarray = checkwords($content);
            1 == $contentarray[0] && $status = 0;
            2 == $contentarray[0] && $this->message("内容包含非法关键词，发布失败!", 'BACK');
            $content = $contentarray[1];

            $question = $_ENV['question']->get($qid);
            //问题最大补充数限制
            (count(unserialize($question['supply'])) >= $this->setting['apend_question_num']) && $this->message("您已超过问题最大补充次数" . $this->setting['apend_question_num'] . ",发布失败！", 'BACK');
            $_ENV['question']->add_supply($qid, $question['supply'], $content, $status); //添加问题补充
            $viewurl = urlmap('question/view/' . $qid, 2);
            if (0 == $status) {
                $this->message('补充问题成功！为了确保问答的质量，我们会对您的提问内容进行审核。请耐心等待......', 'BACK');
            } else {
                $this->message('补充问题成功！', $viewurl);
            }
        }
        include template("supply");
    }

    /* 追加悬赏 */

    function onaddscore() {
        $qid = intval($this->post['qid']);
        $score = abs($this->post['score']);
        if ($this->user['credit2'] < $score) {
            $this->message("财富值不足!", 'BACK');
        }
        $_ENV['question']->update_score($qid, $score);
        $this->credit($this->user['uid'], 0, -$score, 0, 'offer');
        $viewurl = urlmap('question/view/' . $qid, 2);
        $this->message('追加悬赏成功！', $viewurl);
    }

    /* 修改回答 */

    function oneditanswer() {
        $navtitle = '修改回答';
        $aid = $this->get[2] ? $this->get[2] : $this->post['aid'];
        $answer = $_ENV['answer']->get($aid);
        (!$answer) && $this->message("回答不存在或已被删除！", "STOP");
        $question = $_ENV['question']->get($answer['qid']);
        $navlist = $_ENV['category']->get_navigation($question['cid'], true);
        if (isset($this->post['submit'])) {
            $content = $this->post['content'];
            $viewurl = urlmap('question/view/' . $question['id'], 2);

            //检查审核和内容外部URL过滤
            $status = intval(2 != (2 & $this->setting['verify_question']));
            $allow = $this->setting['allow_outer'];
            if (3 != $allow && has_outer($content)) {
                0 == $allow && $this->message("内容包含外部链接，发布失败!", $viewurl);
                1 == $allow && $status = 0;
                2 == $allow && $content = filter_outer($content);
            }
            //检查违禁词
            $contentarray = checkwords($content);
            1 == $contentarray[0] && $status = 0;
            2 == $contentarray[0] && $this->message("内容包含非法关键词，发布失败!", $viewurl);
            $content = $contentarray[1];

            $_ENV['answer']->update_content($aid, $content, $status);

            if (0 == $status) {
                $this->message('修改回答成功！为了确保问答的质量，我们会对您的回答内容进行审核。请耐心等待......', $viewurl);
            } else {
                $this->message('修改回答成功！', $viewurl);
            }
        }
        include template("editanswer");
    }

    /* 搜索问题 */

    function onsearch() {
        $qstatus = $status = $this->get[3] ? $this->get[3] : 1;
        (1 == $status) && ($qstatus = "1,2,6,9");
        (2 == $status) && ($qstatus = "2,6");
        $word = trim($this->post['word']) ? trim($this->post['word']) : urldecode($this->get[2]);
        $word = str_replace(array("\\","'"," ","/","&"),"", $word);
        $word = strip_tags($word);
        $word = htmlspecialchars($word);
        $word = taddslashes($word, 1);
        (!$word) && $this->message("搜索关键词不能为空!", 'BACK');
        $navtitle = $word . '-搜索问题';
        @$page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        if (preg_match("/^tag:(.+)/", $word, $tagarr)) {
            $tag = $tagarr[1];
            $rownum = $_ENV['question']->rownum_by_tag($tag, $qstatus);
            $questionlist = $_ENV['question']->list_by_tag($tag, $qstatus, $startindex, $pagesize);
        } else {
            $questionlist = $_ENV['question']->search_title($word, $qstatus, 0, $startindex, $pagesize);
            $rownum = $_ENV['question']->search_title_num($word, $qstatus);
        }
        $related_words = $_ENV['question']->get_related_words();
        $hot_words = $_ENV['question']->get_hot_words();
        $corrected_words = $_ENV['question']->get_corrected_word($word);
        $departstr = page($rownum, $pagesize, $page, "question/search/$word/$status");
        include template('search');
    }

    /* 提问自动搜索已经解决的问题 */

    function onajaxsearch() {
        $title = $this->get[2];
        $questionlist = $_ENV['question']->search_title($title, 2, 1, 0, 5);
        include template('ajaxsearch');
    }

    /* 顶指定问题 */

    function onajaxgood() {
        $qid = $this->get[2];
        $tgood = tcookie('good_' . $qid);
        !empty($tgood) && exit('-1');
        $_ENV['question']->update_goods($qid);
        tcookie('good_' . $qid, $qid);
        exit('1');
    }

    function ondelete() {
        $_ENV['question']->remove(intval($this->get[2]));
        $this->message('问题删除成功！');
    }

    //问题推荐
    function onrecommend() {
        $qid = intval($this->get[2]);
        $_ENV['question']->change_recommend($qid, 6, 2);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $this->message('问题推荐成功!', $viewurl);
    }

    //编辑问题
    function onedit() {
        $navtitle = '编辑问题';
        $qid = $this->get[2] ? $this->get[2] : $this->post['qid'];
        $question = $_ENV['question']->get($qid);
        if (!$question)
            $this->message("问题不存在或已被删除!", "STOP");
        $navlist = $_ENV['category']->get_navigation($question['cid'], true);
        if (isset($this->post['submit'])) {
            $viewurl = urlmap('question/view/' . $qid, 2);
            $title = trim($this->post['title']);
            (!trim($title)) && $this->message('问题标题不能为空!', $viewurl);
            $_ENV['question']->update_content($qid, $title, $this->post['content']);
            $this->message('问题编辑成功!', $viewurl);
        }
        include template("editquestion");
    }

    //编辑标签
    function onedittag() {
        $tag = trim($this->post['qtags']);
        $qid = intval($this->post['qid']);
        $viewurl = urlmap("question/view/$qid", 2);
        $message = $tag ? "标签修改成功!" : "标签不能为空!";
        $taglist = explode(" ", $tag);
        $taglist && $_ENV['tag']->multi_add(array_unique($taglist), $qid);
        $this->message($message, $viewurl);
    }

    //移动分类
    function onmovecategory() {
        if (intval($this->post['category'])) {
            $cid = intval($this->post['category']);
            $cid1 = 0;
            $cid2 = 0;
            $cid3 = 0;
            $qid = $this->post['qid'];
            $viewurl = urlmap('question/view/' . $qid, 2);
            $category = $this->cache->load('category');
            if ($category[$cid]['grade'] == 1) {
                $cid1 = $cid;
            } else if ($category[$cid]['grade'] == 2) {
                $cid2 = $cid;
                $cid1 = $category[$cid]['pid'];
            } else if ($category[$cid]['grade'] == 3) {
                $cid3 = $cid;
                $cid2 = $category[$cid]['pid'];
                $cid1 = $category[$cid2]['pid'];
            } else {
                $this->message('分类不存在，请更下缓存!', $viewurl);
            }
            $_ENV['question']->update_category($qid, $cid, $cid1, $cid2, $cid3);
            $this->message('问题分类修改成功!', $viewurl);
        }
    }

    //设为未解决
    function onnosolve() {
        $qid = intval($this->get[2]);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $_ENV['question']->change_to_nosolve($qid);
        $this->message('问题状态设置成功!', $viewurl);
    }

    //前台删除问题回答
    function ondeleteanswer() {
        $qid = intval($this->get[3]);
        $aid = intval($this->get[2]);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $_ENV['answer']->remove_by_qid($aid, $qid);
        $this->message("回答删除成功!", $viewurl);
    }

    //前台审核回答
    function onverifyanswer() {
        $qid = intval($this->get[3]);
        $aid = intval($this->get[2]);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $_ENV['answer']->change_to_verify($aid);
        $this->message("回答审核完成!", $viewurl);
    }

    //问题关注
    function onattentto() {
        $qid = intval($this->post['qid']);
        if (!$qid) {
            exit('error');
        }
        $is_followed = $_ENV['question']->is_followed($qid, $this->user['uid']);
        if ($is_followed) {
            $_ENV['user']->unfollow($qid, $this->user['uid']);
        } else {
            $_ENV['user']->follow($qid, $this->user['uid'], $this->user['username']);
            $question = taddslashes($_ENV['question']->get($qid), 1);
            $msgfrom = $this->setting['site_name'] . '管理员';
            $username = addslashes($this->user['username']);
            $this->load("message");
            $viewurl = url('question/view/' . $qid, 1);
            $_ENV['message']->add($msgfrom, 0, $question['authorid'], $username . "刚刚关注了您的问题", '<a target="_blank" href="' . url('user/space/' . $this->user['uid'], 1) . '">' . $username . '</a> 刚刚关注了您的问题' . $question['title'] . '"<br /> <a href="' . $viewurl . '">点击查看</a>');
            $_ENV['doing']->add($this->user['uid'], $this->user['username'], 4, $qid);
        }
        exit('ok');
    }

    function onfollow() {
        $qid = intval($this->get[2]);
        $question = taddslashes($_ENV['question']->get($qid), 1);
        if (!$question) {
            $this->message("问题不存在!");
            exit;
        }
        $page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $followerlist = $_ENV['question']->get_follower($qid, $startindex, $pagesize);
        $rownum = $this->db->fetch_total('question_attention', " qid=$qid ");
        $departstr = page($rownum, $pagesize, $page, "question/follow/$qid");
        include template("question_follower");
    }

}

?>