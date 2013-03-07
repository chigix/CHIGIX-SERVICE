<?php

/**
 * 千木控制器扩展
 *
 * @author Richard Lea <chigix@zoho.com>
 * @version 3.1.2 <ThinkPHP控制器扩展，为千木Service架构提供接口，所有千木特定接口，均以public function__chigiXXXX()形式定义>
 */
abstract class ChigiAction extends Action {

    public $cacheChing;

    public function __construct() {
        require_once("functions.php");
        if (isset($_COOKIE['sid'])) {
            define("CHING", $_COOKIE['sid']);
        } elseif (isset($_GET['sid'])) {
            define("CHING", $_GET['sid']);
        } elseif (isset($_POST['sid'])) {
            define("CHING", $_POST['sid']);
        } else {
            //当前浏览器上无sid记录
            //↓则生成一条新的游客记录
            $cid = md5(getClientIp() . microtime());
            cookie("sid", $cid, array('domain' => C("SIDDOMAIN")));
            define("CHING", $cid);
        }
        $this->cacheChing = Cache::getInstance("File", array("temp" => dirname($_SERVER['SCRIPT_FILENAME']) . '/' . THINK_PATH . '../Ching/'));
        $content = $this->cacheChing->get(CHING);
        //Ching会话初始化
        if ($content === false) {
            $this->cacheChing->set(CHING, array());
        }
        C("CHING", $content);
        parent::__construct();
    }

    public function __destruct() {
        $this->cacheChing->set(CHING, C("CHING"), 900); //缓存仅存在15分钟
        parent::__destruct();
    }

    public function __chigiFetch() {
        $this->fetch();
    }

    public function __chigiShow($content = "") {
        $this->show($content);
    }

    public function __chigiDisplay() {
        $this->display();
    }

//-----------------------------------------------------
//---原生方法继承   --------------------------
//-----------------------------------------------------
    protected function show($content, $charset = '', $contentType = '', $prefix = '') {
        parent::show($content, $charset, $contentType, $prefix);
    }

    protected function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {
        parent::display($templateFile, $charset, $contentType, $content, $prefix);
    }

    protected function fetch($templateFile = '', $content = '', $prefix = '') {
        parent::fetch($templateFile, $content, $prefix);
    }

}

?>