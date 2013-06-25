<?php

$version = (float) substr(phpversion(), 0, 3);
$rc_url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$pos = strpos($rc_url, 'chigi/install');
$chigi_root_url = substr($rc_url, 0, $pos);
define('CHIGI_ROOT_URL', $chigi_root_url . 'chigi/');
// 当前config.php文件的路径
$rc_dir = dirname(__FILE__);
$rc_dir = str_replace('\\', '/', $rc_dir);
$pos = strpos($rc_dir, 'Chigi/install');
$chigi_root_path = substr($rc_dir, 0, $pos);
define('CHIGI_ROOT_PATH',$chigi_root_path . 'Chigi/');
if ($version >= 5.3) {
    //用户项目入口
    define('APP_NAME', 'Installation');
    define('APP_PATH', './');

    //define('APP_DEBUG', true); //您可以注释掉这行，关闭调试模式
    define('THINK_PATH', '../../Core/'); //定义正确的框架文件路径
    require_once THINK_PATH . 'ThinkPHP.php';
} else {
    exit("对不起，您的PHP版本略低，千木架构对PHP最低版本要求5.3");
}
exit;
?>
