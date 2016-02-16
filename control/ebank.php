<?php

!defined('IN_TIPASK') && exit('Access Denied');

class ebankcontrol extends base {

    function ebankcontrol(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('ebank');
    }

    /* 支付宝回调 */

    function onaliapyback() {
        if (!$this->setting['recharge_open']) {
            $this->message("财富充值服务已关闭，如有问题，请联系管理员!", "STOP");
        }
        exit;
        if ($_GET['trade_status'] == 'TRADE_SUCCESS') {
            $credit2 = $_GET['total_fee'] * $this->setting['recharge_rate'];
            $this->credit($this->user['uid'], 0, $credit2, 0, "支付宝充值");
            $this->message("充值成功", "user/score");
        } else {
            $this->message("服务器繁忙，请稍后再试!", 'STOP');
        }
    }

    /* 支付宝转账 */

    function onaliapytransfer() {
        if (isset($this->post['submit'])) {
            $recharge_money = intval($this->post['money']);
            if (!$this->user['uid']) {
                $this->message("您无权执行该操作!", "STOP");
                exit;
            }
            if (!$this->setting['recharge_open']) {
                $this->message("财富充值服务已关闭，如有问题，请联系管理员!", "STOP");
            }
            if ($recharge_money <= 0 || $recharge_money > 20000) {
                $this->message("输入充值金额不正确!充值金额必须为整数，且单次充值不超过20000元!", "STOP");
                exit;
            }
            $_ENV['ebank']->aliapytransfer($recharge_money);
        }
    }

}

?>