<?php
return array(
    //设置主题名（样式库名）
    'DEFAULT_THEME' => 'Default',
    'SHOW_PAGE_TRACE' => true,
    'TOKEN_ON' => true,
    'TOKEN_NAME' => '__hash__',
    'TOKEN_TYPE' => 'md5',
    'TOKEN_RESET' => true,
    //千木服务配置
    'CHIGI_HOST' => $_SERVER['SERVER_NAME'], //需指明当前项目的标准域名形式
    "CHIGI_AUTH" => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx", //应用连接密钥（32位）
    "CHIGI_SUCCESSDIRECT" => "Login/index",
    "CHIGI_ERRORDIRECT" => "Login/index",
    'COM_POST_ON' => true, //是否启用POST通信，不启用则无法接收来自POST的数据，并且API服务上接到POST会返回404
    //千路前端配置
    'CHIJI' => array(
        'LESS_COMPRESS' => 'lessjs', //lessjs|compressed，LESS是否压缩
        'JS_DEBUG' => false, //FALSE则会JS压缩
        'RC_DIR' => CHIGI_ROOT_PATH . 'install/Chiji/', //前端统一资源目录，请务必最后带上斜杠，建议使用绝对路径
    ),
    //模板引擎编译配置
    "TMPL_PARSE_STRING" => array(
        //前端资源获取统一URL路径，与后端的 `CHIJI.RC_DIR` 对应
        '__CHIJI__' => CHIGI_ROOT_URL . 'install/chiji', //最后不带斜杠
    ),
    'TMPL_VAR_IDENTIFY' => "obj",
    //URL模式配置
    'URL_MODEL' => 1,
    'URL_HTML_SUFFIX' => 'html',
    'URL_CASE_INSENSITIVE' => true,
    //CHING参数设置
    'CHINGSET' => array(
        //↓ching会话所采用的底层缓存机制
        'TYPE' => 'File',
        //↓ching会话文件存储位置，可实现会话共享，仅对File有效
        'DIR' => dirname(__FILE__) . '/../Ching/',
        'EXPIRE' => 900, //ching会话操作时效，默认为15分钟
        'DOMAIN' => null, //设置ching会话SID的作用域名
    //↑【注意】：若像localhost之类的不带点的本地域名，请将此值填为null，否则将无法注入COOKIE
    ),
);
?>