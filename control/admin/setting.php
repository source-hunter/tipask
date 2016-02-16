<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_settingcontrol extends base {

    function admin_settingcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('setting');
    }

    function ondefault() {
        $this->onbase();
    }

    /* 基本设置 */

    function onbase() {
        $tpllist = $_ENV['setting']->tpl_list();
        if (isset($this->post['submit'])) {
            $this->setting['site_name'] = $this->post['site_name'];
            $this->setting['register_clause'] = $this->post['register_clause'];
            $this->setting['site_icp'] = $this->post['site_icp'];
            $this->setting['verify_question'] = $this->post['verify_question'];
            $this->setting['allow_outer'] = $this->post['allow_outer'];
            $this->setting['tpl_dir'] = $this->post['tpl_dir'];
            $this->setting['question_share'] = $this->post['question_share'];
            $this->setting['site_statcode'] = $this->post['site_statcode'];
            $this->setting['index_life'] = $this->post['index_life'];
            $this->setting['sum_category_time'] = $this->post['sum_category_time'];
            $this->setting['sum_onlineuser_time'] = $this->post['sum_onlineuser_time'];
            $this->setting['list_default'] = $this->post['list_default'];
            $this->setting['rss_ttl'] = $this->post['rss_ttl'];
            $this->setting['code_register'] = intval(isset($this->post['code_register']));
            $this->setting['code_login'] = intval(isset($this->post['code_login']));
            $this->setting['code_ask'] = intval(isset($this->post['code_ask']));
            $this->setting['code_message'] = intval(isset($this->post['code_message']));
            $this->setting['notify_mail'] = intval(isset($this->post['notify_mail']));
            $this->setting['notify_message'] = intval(isset($this->post['notify_message']));
            $this->setting['allow_expert'] = intval($this->post['allow_expert']);
            $this->setting['apend_question_num'] = intval($this->post['apend_question_num']);
            $this->setting['allow_credit3'] = intval($this->post['allow_credit3']);
            $overdue_days = intval($this->post['overdue_days']);
            if ($overdue_days && $overdue_days >= 3) {
                $this->setting['overdue_days'] = $overdue_days;
                $_ENV['setting']->update($this->setting);
                $message = '站点设置更新成功！';
            } else {
                $type = "errormsg";
                $message = '问题过期时间至少为3天！';
            }
        }
        include template('setting_base', 'admin');
    }

    /* 时间设置 */

    function ontime() {
        $timeoffset = array(
            '-12' => '(标准时-12:00) 日界线西',
            '-11' => '(标准时-11:00) 中途岛、萨摩亚群岛',
            '-10' => '(标准时-10:00) 夏威夷',
            '-9' => '(标准时-9:00) 阿拉斯加',
            '-8' => '(标准时-8:00) 太平洋时间(美国和加拿大)',
            '-7' => '(标准时-7:00) 山地时间(美国和加拿大)',
            '-6' => '(标准时-6:00) 中部时间(美国和加拿大)、墨西哥城',
            '-5' => '(标准时-5:00) 东部时间(美国和加拿大)、波哥大',
            '-4' => '(标准时-4:00) 大西洋时间(加拿大)、加拉加斯',
            '-3.5' => '(标准时-3:30) 纽芬兰',
            '-3' => '(标准时-3:00) 巴西、布宜诺斯艾利斯、乔治敦',
            '-2' => '(标准时-2:00) 中大西洋',
            '-1' => '(标准时-1:00) 亚速尔群岛、佛得角群岛',
            '0' => '(格林尼治标准时) 西欧时间、伦敦、卡萨布兰卡',
            '1' => '(标准时+1:00) 中欧时间、安哥拉、利比亚',
            '2' => '(标准时+2:00) 东欧时间、开罗，雅典',
            '3' => '(标准时+3:00) 巴格达、科威特、莫斯科',
            '3.5' => '(标准时+3:30) 德黑兰',
            '4' => '(标准时+4:00) 阿布扎比、马斯喀特、巴库',
            '4.5' => '(标准时+4:30) 喀布尔',
            '5' => '(标准时+5:00) 叶卡捷琳堡、伊斯兰堡、卡拉奇',
            '5.5' => '(标准时+5:30) 孟买、加尔各答、新德里',
            '6' => '(标准时+6:00) 阿拉木图、 达卡、新亚伯利亚',
            '7' => '(标准时+7:00) 曼谷、河内、雅加达',
            '8' => '(标准时+8:00)北京、重庆、香港、新加坡',
            '9' => '(标准时+9:00) 东京、汉城、大阪、雅库茨克',
            '9.5' => '(标准时+9:30) 阿德莱德、达尔文',
            '10' => '(标准时+10:00) 悉尼、关岛',
            '11' => '(标准时+11:00) 马加丹、索罗门群岛',
            '12' => '(标准时+12:00) 奥克兰、惠灵顿、堪察加半岛');
        if (isset($this->post['submit'])) {
            $this->setting['time_offset'] = $this->post['time_offset'];
            $this->setting['time_diff'] = $this->post['time_diff'];
            $this->setting['date_format'] = $this->post['date_format'];
            $this->setting['time_format'] = $this->post['time_format'];
            $this->setting['time_friendly'] = $this->post['time_friendly'];
            $_ENV['setting']->update($this->setting);
            $message = '时间设置更新成功！';
        }
        include template('setting_time', 'admin');
    }

    /* 列表显示 */

    function onlist() {
        if (isset($this->post['submit'])) {
            foreach ($this->post as $key => $value) {
                if ('list' == substr($key, 0, 4)) {
                    $this->setting[$key] = $value;
                }
            }
            $this->setting['index_life'] = intval($this->post['index_life']);
            $this->setting['hot_words'] = $_ENV['setting']->get_hot_words($this->setting['list_hot_words']);
            $_ENV['setting']->update($this->setting);
            $message = '列表显示更新成功！';
        }
        include template('setting_list', 'admin');
    }

    /* 注册设置 */

    function onregister() {
        if (isset($this->post['submit'])) {
            $this->setting['allow_register'] = $this->post['allow_register'];
            $this->setting['max_register_num'] = $this->post['max_register_num'];
            $this->setting['access_email'] = $this->post['access_email'];
            $this->setting['censor_email'] = $this->post['censor_email'];
            $this->setting['censor_username'] = $this->post['censor_username'];
            $_ENV['setting']->update($this->setting);
            $message = '注册设置更新成功！';
        }
        include template('setting_register', 'admin');
    }

    /* 邮件设置 */

    function onmail() {
        if (isset($this->post['submit'])) {
            foreach ($this->post as $key => $value) {
                if ('mail' == substr($key, 0, 4)) {
                    $this->setting[$key] = $value;
                }
            }
            $_ENV['setting']->update($this->setting);
            $message = '邮件设置更新成功！';
        }
        include template('setting_mail', 'admin');
    }

    /* 积分设置 */

    function oncredit() {
        if (isset($this->post['submit'])) {
            foreach ($this->post as $key => $value) {
                if ('credit' == substr($key, 0, 6)) {
                    $this->setting[$key] = $value;
                }
            }
            $_ENV['setting']->update($this->setting);
            $message = '积分设置更新成功！';
        }
        include template('setting_credit', 'admin');
    }

    /* 缓存设置 */

    function oncache() {
        $tplchecked = $datachecked = false;
        if (isset($this->post['submit'])) {
            if (isset($this->post['type'])) {
                if (in_array('tpl', $this->post['type'])) {
                    $tplchecked = true;
                    cleardir(TIPASK_ROOT . '/data/view');
                }
                if (in_array('data', $this->post['type'])) {
                    $datachecked = true;
                    cleardir(TIPASK_ROOT . '/data/cache');
                }
                $message = '缓存更新成功！';
            } else {
                $tplchecked = $datachecked = false;
                $message = '没有选择缓存类型！';
                $type = 'errormsg';
            }
        }
        include template('setting_cache', 'admin');
    }

    /* 通行证设置 */

    function onpassport() {
        if (isset($this->post['submit'])) {
            foreach ($this->post as $key => $value) {
                if ('passport' == substr($key, 0, 8)) {
                    $this->setting[$key] = $value;
                }
            }
            $this->setting['passport_credit1'] = intval(isset($this->post['passport_credit1']));
            $this->setting['passport_credit2'] = intval(isset($this->post['passport_credit2']));
            $_ENV['setting']->update($this->setting);
            $message = '通行证设置更新成功！';
        }
        include template('setting_passport', 'admin');
    }

    /* UCenter设置 */

    function onucenter() {
        if (isset($this->post['submit'])) {
            $this->setting['ucenter_open'] = intval(isset($this->post['ucenter_open']));
            $_ENV['setting']->update($this->setting);
            if ($this->post['ucenter_config']){
                $ucconfig = "<?php\n";
                $ucconfig.=tstripslashes($this->post['ucenter_config']);
                writetofile(TIPASK_ROOT . '/data/ucconfig.inc.php',$ucconfig);
            }
            //连接ucenter服务端，生成uc配置文件
            $message = 'UCenter设置完成！';
        }
        include template('setting_ucenter', 'admin');
    }

    /* SEO设置 */

    function onseo() {
        if (isset($this->post['submit'])) {
            foreach ($this->post as $key => $value) {
                if ('seo' == substr($key, 0, 3)) {
                    $this->setting[$key] = $value;
                }
            }
            $this->setting['seo_prefix'] = ($this->post['seo_on']) ? '' : '?';
            $_ENV['setting']->update($this->setting);
            $message = 'SEO设置更新成功！';
        }
        include template('setting_seo', 'admin');
    }

    /* 消息模板 */

    function onmsgtpl() {
        if (isset($this->post['submit'])) {
            $msgtpl = array();
            for ($i = 1; $i <= 4; $i++) {
                $message['title'] = $this->post['title' . $i];
                $message['content'] = $this->post['content' . $i];
                $msgtpl[] = $message;
            }
            $this->setting['msgtpl'] = serialize($msgtpl);
            $_ENV['setting']->update($this->setting);
            unset($type);
            $message = '消息模板设置成功!';
        }
        $msgtpl = unserialize($this->setting['msgtpl']);
        include template('setting_msgtpl', 'admin');
    }

    /* 生成htm页面 */

    function onhtm() {
        $minqid = $this->get[2];
        $maxqid = $this->get[3];
        $qid = $this->get[4];
        $this->load('question');
        $question = $_ENV['question']->get($qid);
        if ($question && 0 != $question['status'] && 9 != $question['status']) {
            $this->write_question($question);
        }
        $nextqid = $qid + 1;
        $finish = $qid - $minqid + 1;
        include template('makehtm', 'admin');
    }

    /* 防采集设置 */

    function onstopcopy() {
        if (isset($this->post['submit'])) {
            foreach ($this->post as $key => $value) {
                if ('stopcopy' == substr($key, 0, 8)) {
                    $this->setting[$key] = strtolower($value);
                }
            }
            $_ENV['setting']->update($this->setting);
            $message = '防采集设置更新成功！';
        }
        include template('setting_stopcopy', 'admin');
    }

    /* 更新问答统计 */

    function oncounter() {
        if (isset($this->post['submit'])) {
            foreach ($this->post as $key => $value) {
                if ('counter' == substr($key, 0, 7)) {
                    $this->setting[$key] = strtolower($value);
                }
            }
            $_ENV['setting']->update_counter();
            $_ENV['setting']->update($this->setting);
            $message = '问答统计更新成功！';
        }
        include template('setting_counter', 'admin');
    }

    /*     * 广告管理* */

    function onad() {
        if (isset($this->post['submit'])) {
            $this->setting['ads'] = taddslashes(serialize($this->post['ad']), 1);
            $_ENV['setting']->update($this->setting);
            $type = 'correctmsg';
            $message = '广告修改成功!';
            $this->setting = $this->cache->load('setting');
        }
        $adlist = tstripslashes(unserialize($this->setting['ads']));
        include template('setting_ad', 'admin');
    }

    /**
     * 搜索设置
     */
    function onsearch() {
        if (isset($this->post['submit'])) {
            $this->setting['search_placeholder'] = $this->post['search_placeholder'];
            $this->setting['xunsearch_open'] = $this->post['xunsearch_open'];
            $this->setting['xunsearch_sdk_file'] = $this->post['xunsearch_sdk_file'];
            if ($this->setting['xunsearch_open'] && !file_exists($this->setting['xunsearch_sdk_file'])) {
                $type = 'errormsg';
                $message = 'SDK文件不存在，请核实!';
            } else {
                $type = 'correctmsg';
                $message = '搜索设置成功!';
            }
            $_ENV['setting']->update($this->setting);
        }
        include template('setting_search', 'admin');
    }

    /**
     * 生产全文检索
     */
    function onmakewords() {
        $this->load("question");
        $_ENV['question']->make_words();
    }

    /* qq互联设置 */

    function onqqlogin() {
        if (isset($this->post['submit'])) {
            $this->setting['qqlogin_open'] = $this->post['qqlogin_open'];
            $this->setting['qqlogin_appid'] = trim($this->post['qqlogin_appid']);
            $this->setting['qqlogin_key'] = trim($this->post['qqlogin_key']);
            $this->setting['qqlogin_avatar'] = trim($this->post['qqlogin_avatar']);
            $_ENV['setting']->update($this->setting);
            $this->setting = $this->cache->load('setting');
            $logininc = array();
            $logininc['appid'] = $this->setting['qqlogin_appid'];
            $logininc['appkey'] = $this->setting['qqlogin_key'];
            $logininc['callback'] = SITE_URL . 'plugin/qqlogin/callback.php';
            $logininc['scope'] = "get_user_info";
            $logininc['errorReport'] = "true";
            $logininc['storageType'] = "file";
            $loginincstr = "<?php die('forbidden'); ?>\n" . json_encode($logininc);
            $loginincstr = str_replace("\\", "", $loginincstr);
            writetofile(TIPASK_ROOT . "/plugin/qqlogin/API/comm/inc.php", $loginincstr);
            $message = 'qq互联参数保存成功！';
        }
        include template("setting_qqlogin", "admin");
    }

    /* sina互联设置 */

    function onsinalogin() {
        if (isset($this->post['submit'])) {
            $this->setting['sinalogin_open'] = $this->post['sinalogin_open'];
            $this->setting['sinalogin_appid'] = trim($this->post['sinalogin_appid']);
            $this->setting['sinalogin_key'] = trim($this->post['sinalogin_key']);
            $this->setting['sinalogin_avatar'] = trim($this->post['sinalogin_avatar']);
            $_ENV['setting']->update($this->setting);
            $this->setting = $this->cache->load('setting');
            $config = "<?php \r\ndefine('WB_AKEY',  '" . $this->setting['sinalogin_appid'] . "');\r\n";
            $config .= "define('WB_SKEY',  '" . $this->setting['sinalogin_key'] . "');\r\n";
            $config .= "define('WB_CALLBACK_URL',  '" . SITE_URL . 'plugin/sinalogin/callback.php' . "');\r\n";
            writetofile(TIPASK_ROOT . '/plugin/sinalogin/config.php', $config);
            $message = 'sina互联参数保存成功！';
        }
        include template("setting_sinalogin", "admin");
    }

    /* 财富充值设置 */

    function onebank() {
        if (isset($this->post['submit'])) {
            $aliapy_config = array();
            $this->setting['recharge_open'] = $this->post['recharge_open'];
            $this->setting['recharge_rate'] = trim($this->post['recharge_rate']);
            $aliapy_config['seller_email'] = $this->setting['alipay_seller_email'] = $this->post['alipay_seller_email'];
            $aliapy_config['partner'] = $this->setting['alipay_partner'] = trim($this->post['alipay_partner']);
            $aliapy_config['key'] = $this->setting['alipay_key'] = trim($this->post['alipay_key']);
            $aliapy_config['sign_type'] = 'MD5';
            $aliapy_config['input_charset'] = strtolower(TIPASK_CHARSET);
            $aliapy_config['transport'] = 'http';
            $aliapy_config['return_url'] = SITE_URL . "index.php?ebank/aliapyback";
            $aliapy_config['notify_url'] = "";
            $_ENV['setting']->update($this->setting);
            $strdata = "<?php\nreturn " . var_export($aliapy_config, true) . ";\n?>";
            writetofile(TIPASK_ROOT . "/data/alipay.config.php", $strdata);
        }
        include template("setting_ebank", "admin");
    }

}

?>