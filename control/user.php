<?php

!defined('IN_TIPASK') && exit('Access Denied');

class usercontrol extends base {

    function usercontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('user');
        $this->load('question');
        $this->load('answer');
        $this->load("favorite");
    }

    function ondefault() {
        $this->onscore();
    }

    function oncode() {
        ob_clean();
        $code = random(4);
        $_ENV['user']->save_code(strtolower($code));
        makecode($code);
    }

    function onregister() {
        if ($this->user['uid']) {
            header("Location:" . SITE_URL);
        }
        $navtitle = '注册新用户';
        if (!$this->setting['allow_register']) {
            $this->message("系统注册功能暂时处于关闭状态!", 'STOP');
        }
        if (isset($this->base->setting['max_register_num']) && $this->base->setting['max_register_num'] && !$_ENV['user']->is_allowed_register()) {
            $this->message("您的当前的IP已经超过当日最大注册数目，如有疑问请联系管理员!", 'STOP');
            exit;
        }
        $forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL;
        $this->setting['passport_open'] && !$this->setting['passport_type'] && $_ENV['user']->passport_client(); //通行证处理
        if (isset($this->post['submit'])) {
            $username = trim($this->post['username']);
            $password = trim($this->post['password']);
            $email = $this->post['email'];
            if ('' == $username || '' == $password) {
                $this->message("用户名或密码不能为空!", 'user/register');
            } else if (!preg_match("/^[a-z'0-9]+([._-][a-z'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$/", $email)) {
                $this->message("邮件地址不合法!", 'user/register');
            } else if ($this->db->fetch_total('user', " email='$email' ")) {
                $this->message("此邮件地址已经注册!", 'user/register');
            } else if (!$_ENV['user']->check_usernamecensor($username)) {
                $this->message("邮件地址被禁止注册!", 'user/register');
            }
            $this->setting['code_register'] && $this->checkcode(); //检查验证码
            $user = $_ENV['user']->get_by_username($username);
            $user && $this->message("用户名 $username 已经存在!", 'user/register');
            //ucenter注册成功，则不会继续执行后面的代码。
            if ($this->setting["ucenter_open"]) {
                $this->load('ucenter');
                $_ENV['ucenter']->register();
            }
            $uid = $_ENV['user']->add($username, $password, $email);
            $_ENV['user']->refresh($uid);
            $this->credit($this->user['uid'], $this->setting['credit1_register'], $this->setting['credit2_register']); //注册增加积分
            //通行证处理
            $forward = isset($this->post['forward']) ? $this->post['forward'] : SITE_URL;
            $this->setting['passport_open'] && $this->setting['passport_type'] && $_ENV['user']->passport_server($forward);
            //发送邮件通知
            $subject = "恭喜您在" . $this->setting['site_name'] . "注册成功！";
            $message = '<p>现在您可以登录<a swaped="true" target="_blank" href="' . SITE_URL . '">' . $this->setting['site_name'] . '</a>自由的提问和回答问题。祝您使用愉快。</p>';
            sendmail($this->user, $subject, $message);
            $this->message('恭喜，注册成功！');
        }
        include template('register');
    }

    function onlogin() {
        if ($this->user['uid']) {
            header("Location:" . SITE_URL);
        }
        $navtitle = '用户登录';
        $this->setting['passport_open'] && !$this->setting['passport_type'] && $_ENV['user']->passport_client(); //通行证处理
        if (isset($this->post['submit'])) {
            $username = trim($this->post['username']);
            $password = md5($this->post['password']);
            $cookietime = intval($this->post['cookietime']);
            $forward = isset($this->post['forward']) ? $this->post['forward'] : SITE_URL;
            //ucenter登录成功，则不会继续执行后面的代码。
            if ($this->setting["ucenter_open"]) {
                $this->load('ucenter');
                $_ENV['ucenter']->login($username, $password);
            }
            $this->setting['code_login'] && $this->checkcode(); //检查验证码
            $user = $_ENV['user']->get_by_username($username);
            if (is_array($user) && ($password == $user['password'])) {
                $_ENV['user']->refresh($user['uid'], 1, $cookietime);
                $this->setting['passport_open'] && $this->setting['passport_type'] && $_ENV['user']->passport_server($forward);
                $this->credit($this->user['uid'], $this->setting['credit1_login'], $this->setting['credit2_login']); //登录增加积分
                header("Location:" . $forward);
            } else {
                $this->message('用户名或密码错误！', 'user/login');
            }
        } else {
            $forward = (isset($_SERVER['HTTP_REFERER']) && false !== strpos($group['regulars'], 'question/answer')) ? $_SERVER['HTTP_REFERER'] : SITE_URL;
            include template('login');
        }
    }

    /* 用于ajax登录 */

    function onajaxlogin() {
        $username = $this->post['username'];
        if (TIPASK_CHARSET == 'GBK') {
            require_once(TIPASK_ROOT . '/lib/iconv.func.php');
            $username = utf8_to_gbk($username);
        }
        $password = md5($this->post['password']);
        $user = $_ENV['user']->get_by_username($username);
        if (is_array($user) && ($password == $user['password'])) {
            exit('1');
        }
        exit('-1');
    }

    /* 用于ajax检测用户名是否存在 */

    function onajaxusername() {
        $username = $this->post['username'];
        if (TIPASK_CHARSET == 'GBK') {
            require_once(TIPASK_ROOT . '/lib/iconv.func.php');
            $username = utf8_to_gbk($username);
        }
        $user = $_ENV['user']->get_by_username($username);
        if (is_array($user)
        )
            exit('-1');
        $usernamecensor = $_ENV['user']->check_usernamecensor($username);
        if (FALSE == $usernamecensor)
            exit('-2');
        exit('1');
    }

    /* 用于ajax检测用户名是否存在 */

    function onajaxemail() {
        $email = $this->post['email'];
        $user = $_ENV['user']->get_by_email($email);
        if (is_array($user)
        )
            exit('-1');
        $emailaccess = $_ENV['user']->check_emailaccess($email);
        if (FALSE == $emailaccess
        )
            exit('-2');
        exit('1');
    }

    /* 用于ajax检测验证码是否匹配 */

    function onajaxcode() {
        $code = strtolower(trim($this->get[2]));
        if ($code == $_ENV['user']->get_code()) {
            exit('1');
        }
        exit('0');
    }

    /* 退出系统 */

    function onlogout() {
        $navtitle = '登出系统';
        //ucenter退出成功，则不会继续执行后面的代码。
        if ($this->setting["ucenter_open"]) {
            $this->load('ucenter');
            $_ENV['ucenter']->logout();
        }
        $forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL;
        $this->setting['passport_open'] && !$this->setting['passport_type'] && $_ENV['user']->passport_client(); //通行证处理
        $_ENV['user']->logout();
        $this->setting['passport_open'] && $this->setting['passport_type'] && $_ENV['user']->passport_server($forward); //通行证处理
        $this->message('成功退出！');
    }

    /* 找回密码 */

    function ongetpass() {
        $navtitle = '找回密码';
        if (isset($this->post['submit'])) {
            $email = $this->post['email'];
            $name = $this->post['username'];
            $this->checkcode(); //检查验证码
            $touser = $_ENV['user']->get_by_name_email($name, $email);
            if ($touser) {
                $authstr = authcode($touser['username'], "ENCODE");
                $_ENV['user']->update_authstr($touser['uid'], $authstr);
                $getpassurl = SITE_URL . '?user/resetpass/' . urlencode($authstr);
                $subject = "找回您在" . $this->setting['site_name'] . "的密码";
                $message = '<p>如果是您在<a swaped="true" target="_blank" href="' . SITE_URL . '">' . $this->setting['site_name'] . '</a>的密码丢失，请点击下面的链接找回：</p><p><a swaped="true" target="_blank" href="' . $getpassurl . '">' . $getpassurl . '</a></p><p>如果直接点击无法打开，请复制链接地址，在新的浏览器窗口里打开。</p>';
                sendmail($touser, $subject, $message);
                $this->message("找回密码的邮件已经发送到你的邮箱，请查收!", 'BACK');
            }
            $this->message("用户名或邮箱填写错误，请核实!", 'BACK');
        }
        include template('getpass');
    }

    /* 重置密码 */

    function onresetpass() {
        $navtitle = '重置密码';
        @$authstr = $this->get[2] ? $this->get[2] : $this->post['authstr'];
        if (empty($authstr))
            $this->message("非法提交，缺少参数!", 'BACK');
        $authstr = urldecode($authstr);
        $username = authcode($authstr, 'DECODE');
        $theuser = $_ENV['user']->get_by_username($username);
        if (!$theuser || ($authstr != $theuser['authstr']))
            $this->message("本网址已过期，请重新使用找回密码的功能!", 'BACK');
        if (isset($this->post['submit'])) {
            $password = $this->post['password'];
            $repassword = $this->post['repassword'];
            if (strlen($password) < 6) {
                $this->message("密码长度不能少于6位!", 'BACK');
            }
            if ($password != $repassword) {
                $this->message("两次密码输入不一致!", 'BACK');
            }
            $_ENV['user']->uppass($theuser['uid'], $password);
            $_ENV['user']->update_authstr($theuser['uid'], '');
            $this->message("重置密码成功，请使用新密码登录!");
        }
        include template('resetpass');
    }

    function onask() {
        $navtitle = '我的问题';
        $status = intval($this->get[2]);
        @$page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize; //每页面显示$pagesize条
        $questionlist = $_ENV['question']->list_by_uid($this->user['uid'], $status, $startindex, $pagesize);
        $questiontotal = intval($this->db->fetch_total('question', 'authorid=' . $this->user['uid'] . $_ENV['question']->statustable[$status]));
        $departstr = page($questiontotal, $pagesize, $page, "user/ask/$status"); //得到分页字符串
        include template('myask');
    }

    function onrecommend() {
        $this->load('message');
        $navtitle = '为我推荐的问题';
        @$page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $user_categorys = array_per_fields($this->user['category'], 'cid');
        $_ENV['message']->read_user_recommend($this->user['uid'], $user_categorys);
        $questionlist = $_ENV['message']->list_user_recommend($this->user['uid'], $user_categorys, $startindex, $pagesize);
        $questiontotal = $_ENV['message']->rownum_user_recommend($this->user['uid'], $user_categorys);
        $departstr = page($questiontotal, $pagesize, $page, "user/recommend");
        include template('myrecommend');
    }

    function onspace_ask() {
        $navtitle = 'TA的提问';
        $uid = intval($this->get[2]);
        $member = $_ENV['user']->get_by_uid($uid, 0);
        $status = $this->get[3] ? $this->get[3] : 1;
        //升级进度
        $membergroup = $this->usergroup[$member['groupid']];
        @$page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize; //每页面显示$pagesize条
        $questionlist = $_ENV['question']->list_by_uid($uid, $status, $startindex, $pagesize);
        $questiontotal = $this->db->fetch_total('question', 'authorid=' . $uid . $_ENV['question']->statustable[$status]);
        $departstr = page($questiontotal, $pagesize, $page, "user/space_ask/$uid/$status"); //得到分页字符串
        include template('space_ask');
    }

    function onanswer() {
        $navtitle = '我的回答';
        $status = intval($this->get[2]);
        @$page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize; //每页面显示$pagesize条
        $answerlist = $_ENV['answer']->list_by_uid($this->user['uid'], $status, $startindex, $pagesize);
        $answersize = intval($this->db->fetch_total('answer', 'authorid=' . $this->user['uid'] . $_ENV['answer']->statustable[$status]));
        $departstr = page($answersize, $pagesize, $page, "user/answer/$status"); //得到分页字符串
        include template('myanswer');
    }

    function onspace_answer() {
        $navtitle = 'TA的回答';
        $uid = intval($this->get[2]);
        $status = $this->get[3] ? $this->get[3] : 'all';
        $member = $_ENV['user']->get_by_uid($uid, 0);
        //升级进度
        $membergroup = $this->usergroup[$member['groupid']];
        @$page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize; //每页面显示$pagesize条
        $answerlist = $_ENV['answer']->list_by_uid($uid, $status, $startindex, $pagesize);
        $answersize = intval($this->db->fetch_total('answer', 'authorid=' . $uid . $_ENV['answer']->statustable[$status]));
        $departstr = page($answersize, $pagesize, $page, "user/space_answer/$uid/$status"); //得到分页字符串
        include template('space_answer');
    }

    function onfollower() {
        $navtitle = '关注者';
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $followerlist = $_ENV['user']->get_follower($this->user['uid'], $startindex, $pagesize);
        $rownum = $this->db->fetch_total('user_attention', " followerid=" . $this->user['uid']);
        $departstr = page($rownum, $pagesize, $page, "user/follower");
        include template("myfollower");
    }

    function onattention() {
        $navtitle = '已关注';
        $attentiontype = ($this->get[2] == 'question') ? 'question' : '';
        if ($attentiontype) {
            $page = max(1, intval($this->get[3]));
            $pagesize = $this->setting['list_default'];
            $startindex = ($page - 1) * $pagesize;
            $questionlist = $_ENV['user']->get_attention_question($this->user['uid'], $startindex, $pagesize);
            $rownum = $_ENV['user']->rownum_attention_question($this->user['uid']);
            $departstr = page($rownum, $pagesize, $page, "user/attention/$attentiontype");
            include template("myattention_question");
        } else {
            $page = max(1, intval($this->get[2]));
            $pagesize = $this->setting['list_default'];
            $startindex = ($page - 1) * $pagesize;
            $attentionlist = $_ENV['user']->get_attention($this->user['uid'], $startindex, $pagesize);
            $rownum = $this->db->fetch_total('user_attention', " uid=" . $this->user['uid']);
            $departstr = page($rownum, $pagesize, $page, "user/attention");
            include template("myattention");
        }
    }

    function onscore() {
        $navtitle = '我的积分';
        if ($this->setting['outextcredits']) {
            $outextcredits = unserialize($this->setting['outextcredits']);
        }
        $higherneeds = intval($this->user['creditshigher'] - $this->user['credit1']);
        $adoptpercent = $_ENV['user']->adoptpercent($this->user);
        $highergroupid = $this->user['groupid'] + 1;
        isset($this->usergroup[$highergroupid]) && $nextgroup = $this->usergroup[$highergroupid];
        $credit_detail = $_ENV['user']->credit_detail($this->user['uid']);
        $detail1 = $credit_detail[0];
        $detail2 = $credit_detail[1];
        include template('myscore');
    }

    function onlevel() {
        $navtitle = '我的等级';
        $usergroup = $this->usergroup;
        include template("mylevel");
    }

    function onexchange() {
        $navtitle = '积分兑换';
        if ($this->setting['outextcredits']) {
            $outextcredits = unserialize($this->setting['outextcredits']);
        } else {
            $this->message("系统没有开启积分兑换!", 'BACK');
        }
        $exchangeamount = $this->post['exchangeamount']; //先要兑换的积分数
        $outextindex = $this->post['outextindex']; //读取相应积分配置
        $outextcredit = $outextcredits[$outextindex];
        $creditsrc = $outextcredit['creditsrc']; //积分兑换的源积分编号
        $appiddesc = $outextcredit['appiddesc']; //积分兑换的目标应用程序 ID
        $creditdesc = $outextcredit['creditdesc']; //积分兑换的目标积分编号
        $ratio = $outextcredit['ratio']; //积分兑换比率
        $needamount = $exchangeamount / $ratio; //需要扣除的积分数

        if ($needamount <= 0) {
            $this->message("兑换的积分必需大于0 !", 'BACK');
        }
        if (1 == $creditsrc) {
            $titlecredit = '经验值';
            if ($this->user['credit1'] < $needamount) {
                $this->message("{$titlecredit}不足!", 'BACK');
            }
            $this->credit($this->user['uid'], -$needamount, 0, 0, 'exchange'); //扣除本系统积分
        } else {
            $titlecredit = '财富值';
            if ($this->user['credit2'] < $needamount) {
                $this->message("{$titlecredit}不足!", 'BACK');
            }
            $this->credit($this->user['uid'], 0, -$needamount, 0, 'exchange'); //扣除本系统积分
        }
        $this->load('ucenter');
        $_ENV['ucenter']->exchange($this->user['uid'], $creditsrc, $creditdesc, $appiddesc, $exchangeamount);
        $this->message("积分兑换成功!  你在“{$this->setting[site_name]}”的{$titlecredit}减少了{$needamount}。");
    }

    /* 个人中心修改资料 */

    function onprofile() {
        $navtitle = '个人资料';
        if (isset($this->post['submit'])) {
            $gender = $this->post['gender'];
            $bday = $this->post['birthyear'] . '-' . $this->post['birthmonth'] . '-' . $this->post['birthday'];
            $phone = $this->post['phone'];
            $qq = $this->post['qq'];
            $msn = $this->post['msn'];
            $messagenotify = isset($this->post['messagenotify']) ? 1 : 0;
            $mailnotify = isset($this->post['mailnotify']) ? 2 : 0;
            $isnotify = $messagenotify + $mailnotify;
            $introduction = htmlspecialchars($this->post['introduction']);
            $signature = htmlspecialchars($this->post['signature']);
            if (($this->post['email'] != $this->user['email']) && (!preg_match("/^[a-z'0-9]+([._-][a-z'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$/", $this->post['email']) || $this->db->fetch_total('user', " email='" . $this->post['email'] . "' "))) {
                $this->message("邮件格式不正确或已被占用!", 'user/profile');
            }
            $_ENV['user']->update($this->user['uid'], $gender, $bday, $phone, $qq, $msn, $introduction, $signature, $isnotify);
            isset($this->post['email']) && $_ENV['user']->update_email($this->post['email'], $this->user['uid']);
            $this->message("个人资料更新成功", 'user/profile');
        }
        include template('profile');
    }

    function onuppass() {
        $this->load("ucenter");
        $navtitle = "修改密码";
        if (isset($this->post['submit'])) {
            if (trim($this->post['newpwd']) == '') {
                $this->message("新密码不能为空！", 'user/uppass');
            } else if (trim($this->post['newpwd']) != trim($this->post['confirmpwd'])) {
                $this->message("两次输入不一致", 'user/uppass');
            } else if (trim($this->post['oldpwd']) == trim($this->post['newpwd'])) {
                $this->message('新密码不能跟当前密码重复!', 'user/uppass');
            } else if (md5(trim($this->post['oldpwd'])) == $this->user['password']) {
                $_ENV['user']->uppass($this->user['uid'], trim($this->post['newpwd']));
                $this->message("密码修改成功,请重新登录系统!", 'user/login');
            } else {
                $this->message("旧密码错误！", 'user/uppass');
            }
        }
        include template('uppass');
    }

    // 1提问  2回答
    function onspace() {
        $navtitle = "个人空间";
        $userid = intval($this->get[2]);
        $member = $_ENV['user']->get_by_uid($userid, 2);
        if ($member) {
            $this->load('doing');
            $membergroup = $this->usergroup[$member['groupid']];
            $adoptpercent = $_ENV['user']->adoptpercent($member);
            $page = max(1, intval($this->get[3]));
            $pagesize = 8;
            $startindex = ($page - 1) * $pagesize;
            $doinglist = $_ENV['doing']->list_by_type("my", $userid, $startindex, $pagesize);
            $rownum = $_ENV['doing']->rownum_by_type("my", $userid);
            $departstr = page($rownum, $pagesize, $page, "user/space/$userid");
            $navtitle = $member['username'] . $navtitle;
            include template('space');
        } else {
            $this->message("抱歉，该用户个人空间不存在！", 'BACK');
        }
    }

    // 0总排行、1上周排行 、2上月排行
    //user/scorelist/1/
    function onscorelist() {
        $navtitle = "经验排行榜";
        $type = isset($this->get[2]) ? $this->get[2] : 0;
        $userlist = $_ENV['user']->list_by_credit($type, 100);
        $usercount = count($userlist);
        include template('scorelist');
    }

    function onactivelist() {
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $userlist = $_ENV['user']->get_active_list($startindex, $pagesize);
        $answertop = $_ENV['user']->get_answer_top();
        $rownum = $this->db->fetch_total('user', " 1=1 ");
        $departstr = page($rownum, $pagesize, $page, "user/activelist");
        include template("activelist");
    }

    function oneditimg() {
        if (isset($_FILES["userimage"])) {
            $uid = intval($this->get[2]);
            $avatardir = "/data/avatar/";
            $extname = extname($_FILES["userimage"]["name"]);
            if (!isimage($extname))
                exit('type_error');
            $upload_tmp_file = TIPASK_ROOT . '/data/tmp/user_avatar_' . $uid . '.' . $extname;
            $uid = abs($uid);
            $uid = sprintf("%09d", $uid);
            $dir1 = $avatardir . substr($uid, 0, 3);
            $dir2 = $dir1 . '/' . substr($uid, 3, 2);
            $dir3 = $dir2 . '/' . substr($uid, 5, 2);
            (!is_dir(TIPASK_ROOT . $dir1)) && forcemkdir(TIPASK_ROOT . $dir1);
            (!is_dir(TIPASK_ROOT . $dir2)) && forcemkdir(TIPASK_ROOT . $dir2);
            (!is_dir(TIPASK_ROOT . $dir3)) && forcemkdir(TIPASK_ROOT . $dir3);
            $smallimg = $dir3 . "/small_" . $uid . '.' . $extname;
            if (move_uploaded_file($_FILES["userimage"]["tmp_name"], $upload_tmp_file)) {
                $avatar_dir = glob(TIPASK_ROOT . $dir3 . "/small_{$uid}.*");
                foreach ($avatar_dir as $imgfile) {
                    if (strtolower($extname) != extname($imgfile))
                        unlink($imgfile);
                }
                if (image_resize($upload_tmp_file, TIPASK_ROOT . $smallimg, 80, 80))
                    echo 'ok';
            }
        } else {
            if ($this->setting["ucenter_open"]) {
                $this->load('ucenter');
                $imgstr = $_ENV['ucenter']->set_avatar($this->user['uid']);
            }
            include template("editimg");
        }
    }

    function onmycategory() {
        $this->load("category");
        $categoryjs = $_ENV['category']->get_js();
        $qqlogin = $_ENV['user']->get_login_auth($this->user['uid'], 'qq');
        $sinalogin = $_ENV['user']->get_login_auth($this->user['uid'], 'sina');
        include template("mycategory");
    }

    //解除绑定
    function onunchainauth() {
        $type = ($this->get[2] == 'qq') ? 'qq' : 'sina';
        $_ENV['user']->remove_login_auth($this->user['uid'], $type);
        $this->message($type . "绑定解除成功!", 'user/mycategory');
    }

    function onajaxcategory() {
        $cid = intval($this->post['cid']);
        if ($cid && $this->user['uid']) {
            foreach ($this->user['category'] as $category) {
                if ($category['cid'] == $cid) {
                    exit;
                }
            }
            $_ENV['user']->add_category($cid, $this->user['uid']);
        }
    }

    function onajaxdeletecategory() {
        $cid = intval($this->post['cid']);
        if ($cid && $this->user['uid']) {
            $_ENV['user']->remove_category($cid, $this->user['uid']);
        }
    }

    function onajaxpoplogin() {
        $forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL;
        include template("poplogin");
    }

    /* 用户查看下详细信息 */

    function onajaxuserinfo() {
        $uid = intval($this->get[2]);
        if ($uid) {
            $userinfo = $_ENV['user']->get_by_uid($uid, 1);
            $is_followed = $_ENV['user']->is_followed($userinfo['uid'], $this->user['uid']);
            $userinfo_group = $this->usergroup[$userinfo['groupid']];
            include template("usercard");
        }
    }

    function onajaxloadmessage() {
        $uid = $this->user['uid'];
        if ($uid == 0) {
            return;
        }
        $user_categorys = array_per_fields($this->user['category'], 'cid');
        $message = array();
        $this->load('message');
        $message['msg_system'] = $this->db->fetch_total('message', " new=1 AND touid=$uid AND fromuid<>$uid AND fromuid=0 AND status<>2");
        $message['msg_personal'] = $this->db->fetch_total('message', " new=1 AND touid=$uid AND fromuid<>$uid AND fromuid<>0 AND status<>2");
        $message['message_recommand'] = $_ENV['message']->rownum_user_recommend($uid, $user_categorys, 'notread');
        echo tjson_encode($message);
        exit;
    }

    //积分充值
    function onrecharge() {
        header("Location:" . SITE_URL);
        exit;
        include template("recharge");
    }

    //关注用户
    function onattentto() {
        $uid = intval($this->post['uid']);
        if (!$uid) {
            exit('error');
        }
        $is_followed = $_ENV['user']->is_followed($uid, $this->user['uid']);
        if ($is_followed) {
            $_ENV['user']->unfollow($uid, $this->user['uid'], 'user');
        } else {
            $_ENV['user']->follow($uid, $this->user['uid'], $this->user['username'], 'user');
            $msgfrom = $this->setting['site_name'] . '管理员';
            $username = addslashes($this->user['username']);
            $this->load("message");
            $_ENV['message']->add($msgfrom, 0, $uid, $username . "刚刚关注了您", '<a target="_blank" href="' . url('user/space/' . $this->user['uid'], 1) . '">' . $username . '</a> 刚刚关注了您!<br /> <a href="' . url('user/follower', 1) . '">点击查看</a>');
        }
        exit('ok');
    }

}

?>