<?php

/**
 * 别名定义
 * @author Richard Lea <chigix@zoho.com>
 */
defined('CHIGI_PATH') or define('CHIGI_PATH', dirname(__FILE__) . '/');
require 'QueryPath/qp.php';
require 'functions.php';
require 'Ching.class.php';
CHING::getInstance();

// <editor-fold defaultstate="collapsed" desc="地址栏参数处理">
if (isset($_GET['iframe'])) {
    $_GET['iframe'] = base64_decode($value);
} else {
    $_GET['iframe'] = null;
}

// </editor-fold>
function chigi_alias($configarr = array()) {
    $chigiActionPath = CHIGI_PATH . 'ChigiAction.class.php';
    $chigiTempPath = CORE_PATH . 'Template/ThinkTemplate.class.php';
    if (APP_DEBUG) {
        //$chigiActionPath = CHIGI_PATH . 'ChigiActionDebug.class.php';
        $chigiTempPath = CHIGI_PATH . 'ChigiTemplate.class.php';
    }
    $orig = array(
        'ThinkTemplate' => $chigiTempPath,
        'ChigiAction' => $chigiActionPath,
        'ChigiApi' => CHIGI_PATH . 'ChigiApi.class.php',
        'ChigiService' => CHIGI_PATH . 'ChigiService.class.php',
        'ChigiData' => CHIGI_PATH . 'ChigiData.class.php',
        'ChigiReturn' => CHIGI_PATH . 'ChigiReturn.class.php',
    );
    return array_merge($orig, $configarr);
}

?>