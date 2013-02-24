<?php
 return array (
  //配置数据库
  'DB_HOST' => 'localhost',
  'DB_NAME' => '',
  'DB_USER' => '',
  'DB_PWD' => '',
  'DB_PREFIX' => '',
  //设置主题名（样式库名）
  'DEFAULT_THEME'=> 'Default',
  'SHOW_PAGE_TRACE' => true,
  'TOKEN_ON' => true,
  'TOKEN_NAME'=>'__hash__',
  'TOKEN_TYPE' => 'md5',
  'TOKEN_RESET' => true,
  //自定义配置属性
  'COM_POST_ON' => true,  //是否启用POST通信，不启用则无法接收来自POST的数据，并且API服务上接到POST会返回404
  //千路前端配置
  'CHIJI' => array(
  	'LESS_COMPRESS' => 'lessjs', //LESS是否压缩
  	'JS_DEBUG' => true,  //FALSE则会JS压缩
  ),
  "TMPL_PARSE_STRING" => array(
    '__CHIJI__' => 'http://chiji.five.com',
  ),
  //模板引擎编译配置
  'TMPL_VAR_IDENTIFY' => "obj",
  //URL模式开启
  'URL_MODEL' => 2,
  "URL_ROUTER_ON" => true,
  "URL_ROUTE_RULES" => array(
      //"xx" => "Index/xx",
  ),
);