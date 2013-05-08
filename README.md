千木服务架构
=======================

For ThinkPHP 3.1.0 +

Version 1.7.5

Author 千木郷（李颖豪） chigix@zoho.com

Facebook: http://facebook.com/chigix

Weibo: http://weibo.com/chigix

# CONTENTS

- [Introduction](#introduction)
	- [About Version](#about-version)
	- [License](#license)
- [Overview](#overview)
- [Getting Started](#getting-started)
	- [Requirement](#requirement)
	- [Installation](#installation)
		- [GET ME](#get-me)
		- [SETUP alias.php](#setup-aliasphp)
		- [SETUP config.php](#setup-configphp)
		- [Directory Specification](#directory-specification)
	- [Deployment of Components](#deployment-of-components)
		- [Controller](#controller)
		- [Model](#model)
		- [Template](#template)
		- [Widget](#widget)
	- [URL](#url)
		- [URL General Standard](#url-general-standard)
		- [URL Redirecting POOL](#url-redirecting-pool)
	- [Deployment for ONLINE](#deployment-for-online)
- [Developing Specification](./docs/developing_specification.md)
- [API——ChigiAction](./docs/api_chigiaction.md)
- [API——ChigiApi](./docs/api_chigiapi.md)
- [API——ChigiService](./docs/api_chigiservice.md)
- [Functions](./docs/functions.md)
- [MISC](./docs/misc.md)

# Introduction

本架构旨在为业务逻辑层，构建一个快速敏捷的开发模式，使得常用逻辑服务化封装，并直接可拼装，解放代码与数据库设计。

同时高度分离逻辑处理与界面输出，彻底提升开发者代码的可移植性，借助于架构的服务层抽象，可以轻松地剥离出具有可移植性的代码，从而简化逻辑层的代码量与复杂度。

在架构设计上是基于经典的MVC架构进行一定的演变，使框架结构化的开发更加灵活与轻便，以松耦合、高内聚作为开发整体思想。

MVC架构是在软件开发中已占据不可动摇的地位，其架构模式的经典性与效力性无可质疑，而现在亦有大量优秀的成型的MVC框架，所以本架构不希望重复制造已存在的轮子，MVC架构使用现有的已足够。而本架构是基于MVC模式之上，提供一个项目部署与开发的整体方案——服务化方案。使得开发者不需再拘泥于MVC架构，更无需为架构而架构，一切的讨论与开发都由服务而来，一切的资源亦都是服务，有点类似于云计算中的一切皆服务思想（XaaS）。

## About Version

现本架构尚于起步阶段，1.0版本定位于拥有完整的服务体系架构，可实现在服务体系下模块与模块间接交互，并尽可能多地开发基本服务，以使基于本架构能更便捷地开发出流行的web应用。

1.0版核心点还是在于单体应用的开发，而不在大规模集群和分布式应用的开发。

2.0版本的主要路线是开始由单体应用框架开始转移向集群分布式系统的定位，主要任务是提供暴露于外部WEB网络下的API接口，以实现模块与模块之间的分布式通信。

## License

本项目遵循 **Apache2开源协议** 发布。Apache License是著名的非盈利开源组织 Apache 采用的协议。该协议和 BSD 类似，鼓励代码共享和尊重原作者的著作权，同样允许代码修改，再作为 **开源或商业软件** 发布。需要满足如下条件：

1. 需要给代码的用户一份 Apache License
2. 如果你修改了代码，需要在被修改的文件中说明
3. 在延伸的代码中（修改和有源代码衍生的代码中）需要带有原来代码中的协议、商标、专利声明和其他原来作者规定需要包含的说明
4. 如果再发布的产品中包含一个 Notice 文件，则在 Notice 文件中需要带有 Apache License 。当然允许第三方在 Notice 中增加自己的许可，但不可以表现为对 Apache License 构成更改。

具体的协议参考：http://www.apache.org/licenses/LICENSE-2.0

# Overview

通过本项目，开发者可以有一种更灵活更轻便的方式来部署和使用MVC架构，而开发在本架构中亦可进行如下两种开发：

1.	服务开发

	由于本框架是将所有的分层、架构均归为一个服务，所以架构本身只是一个解决方案，而部署具体的业务逻辑、开源业务逻辑则只需要将业务逻辑、应用逻辑本身当作服务来开发即可，而服务的作用就是实现系统的模块化拼装，服务与服务之间完全无依赖，在服务内只完成属于服务自己的业务逻辑和应用逻辑，而搭建的系统则是为服务之间建立起依赖关系。

2.	应用开发

	其实就是开发一个具体的项目，具体的系统，而基于服务架构的开发，只需要将自己系统所需的几个服务进行一下依赖的定义，完成服务组装，即可完成一套大型系统的开发项目。

本项目中已重构了框架中原生的Action控制器类，并为调试期间的工作提供了为本架构服务的模板引擎，虽然不会影响实际部署时的实现，但是毕竟是专为调试期间提供的服务，所以在部署模式时建议切换回ThinkPHP原生模式。

本架构仅仅是一个MVC开发的部署方案，也就说本质仍旧是依赖于原生的MVC框架，所以在千木服务架构与MVC架构之间是无缝切换的。而千木服务更大程度上是从调试阶段来考虑的。实际部署时，若不切换回原始的MVC框架，会有一定的性能影响。

# Getting Started

![CHIGIX-SERVICE INFRASTRUCTURE](./docs/img/infrastructure.jpg)

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
