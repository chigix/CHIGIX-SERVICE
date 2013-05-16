Getting Started
================================

# Getting Started

- [Getting Started](#getting-started-1)
	- [Requirement](#requirement)
	- [Installation](#installation)
		- [GET ME](#get-me)
		- [SETUP alias.php](#setup-aliasphp)
		- [SETUP config.php](#setup-configphp)
		- [Directory Specification](#directory-specification)

![CHIGIX-SERVICE INFRASTRUCTURE](./img/infrastructure.jpg)

所有的源代码资源可参考sample目录下的文件，可以直接复制，参照里面的注释和格式进行修改即可。

1.7.0版本开始，前端渲染增强已完全集成入千木服务架构（低版本中依赖于千路前端服务ChijiService），所有的开发更加接近原生的ThinkPHP的书写规范，使开发者在ThinkPHP的原生环境下，无需对业务逻辑代码进行任何的调整与修改即可平滑过渡进入千木服务架构平台之上。并且可以直接连接基于本架构所开发的各种强劲的服务，以简化业务逻辑的开发与模块拼装。

在控制器中，前端渲染增强可以说是本架构的一大亮点，将WEB开发中一个一直以来都非常头痛的问题当作一个服务来解决，开发者在前端与后端之间的对接通道仍保留 `$this->display()` 不变，本架构会自动为开发者安排好所有的前端资源部署，而开发者在前端调用时，所有的资源只需从目录：`webRoot\Chiji\ProjectName\` 中获取即可。

在控制器中需要连接服务才能使用目标服务提供的方法和业务逻辑，而开发者几乎什么都不用做就可以开发出一个具有规模的系统。

连接服务亦非常简单，只需一句 `service("ServiceName")` ，从1.7.0开始服务的连接单例化，故开发者可以随意调用service函数而无需担心服务类的内存开销。

**所有的service只能在客户端应用及客户端service中使用，不能在服务端Api及Model中使用。**

## Requirement

1. Lessc 编译库，已在Samples中附带，请移到ORG扩展目录下即可
2. JSxs 编译库，已在Samples中附带，请移到ORG扩展目录下即可
3. PHP 5.3

Friendly, the Lessc Compiler[1] and the JSxs Compiler[2] have been provided in the sample files of this project. You can directly drag the /Chigi/samples/ORG/Chiji directory to the ORG originally in the ThinkPHP Extend Folder(/ThinkPHP/Extend/Library/ORG/).

## Installation

This is just an extension for ThinkPHP 3.1.0+. Hence all the code upon that version could use CHIGIX-SERVICE Infrastructure directly. It may be considered, with the fail to meet the fundamental requirements , to do some altering following the updating instructions in the ThinkPHP Official Document ,  after witch it could migrate to this structure.

### GET ME

本项目托管于GITHUB仓库上，遵循APACHE 2 开源协议，欢迎加入。

https://github.com/chigix/CHIGIX-SERVICE

RELEASES：

[Version_1.7.1](https://github.com/chigix/CHIGIX-SERVICE/archive/V_1.7.1.zip)
[Version_1.7.5 SPECIAL](https://github.com/chigix/CHIGIX-SERVICE/archive/V_1.7.5.zip)

Put the sources downloaded into the ThinkPHP Extension Directory, default as `webRoot/ThinkPHP/Extend/` . And then, just feel free to enjoy it.

### SETUP alias.php

若要使用千木服务架构来设计应用，仅需在项目的别名配置（alias.php）中加入如下两行即可（可直接拷贝，无需改动）：

		require(EXTEND_PATH . 'Chigi/alias.php');
		return chigi_alias();

由于从1.7.0开始，架构内部完全集成前端渲染增强，故需要再安装前端渲染增强插件，可以在Samples/ORG/下找到Chiji目录，将该目录直接复制到ThinkPHP的扩展ORG目录下即可。该扩展包中集成了phpLess和JSxs，可与千木架构完美配合。

至此整个项目便可以完全使用千木服务架构来进行开发。

千木架构在ThinkPHP上的安装基于alias别名控制文件，但是不影响开发者定义自己的别名文件，该函数中支持直接放入数组：

		return chigi_alias(array(
			'Content' => EXTEND_PATH . 'Example/Content.class.php',
			'Comment' => EXTEND_PATH . 'Example/Comment.class.php',
			'User' => EXTEND_PATH . 'Example/User.class.php',
			));
		//↓上面的别名定义与原生架构完美兼容：
		import('Content');
		$obj = new Content();  //与ThinkPHP自身的用法无区别★

### SETUP config.php

		//设置主题名（样式库名）
		'DEFAULT_THEME' => 'Default',
		'SHOW_PAGE_TRACE' => true,
		'TOKEN_ON' => true,
		'TOKEN_NAME' => '__hash__',
		'TOKEN_TYPE' => 'md5',
		'TOKEN_RESET' => true,

		//千木服务配置
		'CHIGI_HOST' => 'sugar.five.com', //需指明当前项目的标准域名形式
		"CHIGI_AUTH" => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",  //应用连接密钥（32位）
		"CHIGI_SUCCESSDIRECT" => "Login/index",
		"CHIGI_ERRORDIRECT" => "Login/index",
		'COM_POST_ON' => true, //是否启用POST通信，不启用则无法接收来自POST的数据，并且API服务上接到POST会返回404
		
		//千路前端配置
		'CHIJI' => array(
		    'LESS_COMPRESS' => 'lessjs', //lessjs|compressed，LESS是否压缩
		    'JS_DEBUG' => true, //FALSE则会JS压缩
		    'RC_DIR' => '/var/Chiji/',//前端统一资源目录，请务必最后带上斜杠，建议使用绝对路径
		),
		
		//模板引擎编译配置
		"TMPL_PARSE_STRING" => array(
			//前端资源获取统一URL路径，与后端的 `CHIJI.RC_DIR` 对应
		    '__CHIJI__' => 'http://xxxxxx',  //最后不带斜杠
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
		    'DIR' => '/var/Chigi/Ching/',
		    'EXPIRE' => 900,  //ching会话操作时效，默认为15分钟
		    'DOMAIN' => "host.com", //设置ching会话SID的作用域名
		    //↑【注意】：若像localhost之类的不带点的本地域名，请将此值填为null，否则将无法注入COOKIE
		),

### Directory Specification

1. Make a directory reference to `CHIJI.RC_DIR` and drag the resources from `samples\Chiji` into it.
2. Make a directory reference to `CHINGSET.DIR` when the `CHINGSET.TYPE` was 'File';
3. Make sure the folder `Extend/Library/ORG/Chiji/` is ready.

@todo-1.8:

Develop an installation checking script by a lockfile in the conf folder. The script will walk through the entire configure array to check every item valid.

## Double-side Mechanism between Service and API layers

In the service layer, you must send datas via `$this->request()` method to have a communication with API layer.

Physically, the keys within the associative array via `request()` are:

	* `'data'`
	* `'user_agent'`
		* `'ip'`——The client IP address
		* `'bot'`——The Browser Engine information or the Spider crawler name
		* `'__'`——The whole string of the $_SERVER['USER_AGENT']
	* `'bindings'`
		An associative array loaded to the API automatically. So developers could use `$this->bind()` to have operations on it in API layer.


The keys within the associative array via `response()` are:

	* `'data'`
		It would be returned to the Service Layer.
	* `'bindings'`
		An associative array loaded to the Service automatically. So developers could use `$this->bind()` to have operations on it in the Service layer.