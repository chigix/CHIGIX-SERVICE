<?php
/**
 * 别名定义
 * @author 千木郷 chigix@zoho.com
 */
$chigiActionPath =EXTEND_PATH . 'Chigi/ChigiAction.class.php';
$chigiTempPath = CORE_PATH.'Template/ThinkTemplate.class.php';
if (defined('APP_DEBUG')) {
    if (APP_DEBUG === true) {
        $chigiActionPath = EXTEND_PATH . 'Chigi/ChigiActionDebug.class.php';
        $chigiTempPath = EXTEND_PATH.'Chigi/ChigiTemplate.class.php';
    }
}
return array(
//加载千木调试模板引擎
'ThinkTemplate' => $chigiTempPath,
'ChigiAction' => $chigiActionPath,
'ChigiApi' => EXTEND_PATH . 'Chigi/ChigiApi.class.php',
'ChigiService' => EXTEND_PATH . 'Chigi/ChigiService.class.php',
);
?>