<?php

$version = (float) substr(phpversion(), 0, 3);
if ($version >= 5.3) {
    //用户项目入口
    define('APP_NAME', 'Installation');
    define('APP_PATH', './');

    define('APP_DEBUG', true); //您可以注释掉这行，关闭调试模式
    define('THINK_PATH', '../../Core/'); //定义正确的框架文件路径
    require_once THINK_PATH . 'ThinkPHP.php';
} else {
    exit("对不起，您的PHP版本略低，千木架构对PHP最低版本要求5.3");
}
exit;
?>
