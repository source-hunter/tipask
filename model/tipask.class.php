<?php

!defined('IN_TIPASK') && exit('Access Denied');
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

require TIPASK_ROOT . '/lib/db.class.php';
require TIPASK_ROOT . '/lib/global.func.php';
require TIPASK_ROOT . '/lib/cache.class.php';
require TIPASK_ROOT . '/model/base.class.php';

class tipask {

    var $get = array();
    var $post = array();
    var $vars = array();

    function tipask() {
        $this->init_request();
        $this->load_control();
    }

    function init_request() {
        if (!file_exists(TIPASK_ROOT . '/data/install.lock')) {
            header('location:install/index.php');
            exit();
        }
        require TIPASK_ROOT . '/config.php';
        header('Content-type: text/html; charset=' . TIPASK_CHARSET);
        $querystring = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        $pos = strrpos($querystring, '.');
        if ($pos !== false) {
            $querystring = substr($querystring, 0, $pos);
        }
        /* 处理简短url */
        $pos = strpos($querystring, '-');
        ($pos !== false) && $querystring = urlmap($querystring);
        $andpos = strpos($querystring, "&");
        $andpos && $querystring = substr($querystring, 0, $andpos);
        $this->get = explode('/', $querystring);
        if (empty($this->get[0])) {
            $this->get[0] = 'index';
        }
        if (empty($this->get[1])) {
            $this->get[1] = 'default';
        }
        if (count($this->get) < 2) {
            exit(' Access Denied !');
        }
        unset($GLOBALS, $_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);

        $this->get = taddslashes($this->get, 1);
        $this->post = taddslashes(array_merge($_GET, $_POST));
        checkattack($this->post, 'post');
        checkattack($this->get, 'get');
        unset($_POST);
    }

    function load_control() {
        $controlfile = TIPASK_ROOT . '/control/' . $this->get[0] . '.php';
        $isadmin = ('admin' == substr($this->get[0], 0, 5));
        $isadmin && $controlfile = TIPASK_ROOT . '/control/admin/' . substr($this->get[0], 6) . '.php';
        if (false === include($controlfile)) {
            $this->notfound('control file "' . $controlfile . '" not found!');
        }
    }

    function run() {
        $controlname = $this->get[0] . 'control';
        $control = new $controlname($this->get, $this->post);
        $method = 'on' . $this->get[1];
        if (method_exists($control, $method)) {
            $regular = $this->get[0] . '/' . $this->get[1];
            $isajax = (0 === strpos($this->get[1], 'ajax'));
            if ($control->checkable($regular) || $isajax) {
                $control->$method();
            } else {
                $control->message('您无权进行当前操作，原因如下：<br/> 您所在的用户组(' . $control->user['grouptitle'] . ')无法进行此操作。', 'user/login');
            }
        } else {
            $this->notfound('method "' . $method . '" not found!');
        }
    }

    function notfound($error) {
        @header('HTTP/1.0 404 Not Found');
        exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\"><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p> $error </p></body></html>");
    }

}

?>