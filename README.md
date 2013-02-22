千木服务架构
=======================

For ThinkPHP 3.1.0 +

Version 1.0.0

Author 千木郷（李颖豪） chigix@zoho.com

Facebook: http://facebook.com/chigix

Weibo: http://weibo.com/chigix

# Introduction

本架构旨在提供一个WEB快速开发的全新架构模式，基于经典的MVC架构进行一定的演变，使框架结构化的开发更加灵活与轻便，以松耦合、高内聚作为开发整体思想。

MVC架构是在软件开发中已占据不可动摇的地位，其架构模式的经典性与效力性无可质疑，而现在亦有大量优秀的成型的MVC框架，所以本架构不希望重复制造已存在的轮子，MVC架构使用现有的已足够。而本架构是基于MVC模式之上，提供一个项目部署与开发的整体方案——服务化方案。使得开发者不需再拘泥于MVC架构，更无需为架构而架构，一切的讨论与开发都由服务而来，一切的资源亦都是服务，有点类似于云计算中的一切皆服务思想（XaaS）。

# Overview

通过本项目，开发者可以有一种更灵活更轻便的方式来部署和使用MVC架构，而开发在本架构中亦可进行如下两种开发：

1.	服务开发

	由于本框架是将所有的分层、架构均归为一个服务，所以架构本身只是一个解决方案，而部署具体的业务逻辑、开源业务逻辑则只需要将业务逻辑、应用逻辑本身当作服务来开发即可，而服务的作用就是实现系统的模块化拼装，服务与服务之间完全无依赖，在服务内只完成属于服务自己的业务逻辑和应用逻辑，而搭建的系统则是为服务之间建立起依赖关系。

2.	应用开发

	其实就是开发一个具体的项目，具体的系统，而基于服务架构的开发，只需要将自己系统所需的几个服务进行一下依赖的定义，完成服务组装，即可完成一套大型系统的开发项目。

本项目中已重构了框架中原生的Action控制器类，并为调试期间的工作提供了为本架构服务的模板引擎，虽然不会影响实际部署时的实现，但是毕竟是专为调试期间提供的服务，所以在部署模式时建议切换回ThinkPHP原生模式。

本架构仅仅是一个MVC开发的部署方案，也就说本质仍旧是依赖于原生的MVC框架，所以在千木服务架构与MVC架构之间是无缝切换的。而千木服务更大程度上是从调试阶段来考虑的。实际部署时，若不切换回原始的MVC框架，会有一定的性能影响。

# Getting Started

所有的源代码资源可参考sample目录下的文件，可以直接复制，参照里面的注释和格式进行修改即可。

在控制器中，`servcie("Chiji");` 是 `$this->display();` 的替代方案，Chiji（千路前端服务）可以说是本架构的一大亮点，将WEB开发中一个一直以来都非常头痛的问题当作一个服务来解决，开发者在前端与后端之间仅需要通过连接“千路前端”服务即可，而连接代码也仅此一句，无需作任何变动，亦无需考虑任何参数，直接连接完千路前端服务后，本架构中集成的千路前端服务会自动为开发者安排好所有的前端资源部署，而开发者在前端调用时，所有的资源只需从目录：`webRoot\Chiji\ProjectName\` 中获取即可。

在控制器中需要连接服务才能使用目标服务提供的方法和业务逻辑，而开发者几乎什么都不用做就可以开发出一个具有规模的系统。

连接服务亦非常简单，只需一句 `service("ServiceName")` 

## 架构部署

### 控制器部署

1.	IndexAction	——所有直接显示的网页及显式URL访问的操作均在Index中，每个页面有各自对应的操作方法。
2.	EmptyAction	——空模块，主负责Service在URL访问下的安全包装
3.	AjaxAction	——ajax模块，主负责所有页面上异步交互的执行操作

### 模型部署

由于一切业务逻辑均服务化，所以对于一些主要的业务逻辑均已封装于已有的服务中，而对于已有服务提供的模型则无需作任何部署，直接连接到对应的Service类即可，在千木服务化架构下会替开发者完成所有本服务下的业务逻辑。

### 模板部署

由于在千木架构下，所有的模板都是直接采用与当前控制器及操作一一对应，故在控制器中只需 `$this->display()` 即可完成前端的输出，而不用开发者再作其他的工作。当然，这需要一个非常严格的模板目录部署机制，以做到 `display` 方法输出时可以直接定位及渲染模板。另外千路前端服务中会自动渲染CSS和JS并完成智能合并及压缩。详见千路前端服务ChijiService类的使用API。

模板目录Tpl下的文件部署如下：

		|-Tpl/Default				主题目录
		|        ├Index/			Index控制器下的页面渲染入口（即主模板文件）
		|        	├index.html		index页面主模板文件
		|        	├method.html	其他页面主模板文件
		|        ├Main/				Index:index操作的页面模块文件
		|        	├starter-module.html		Index:index页面的HTML起始模块
		|        	├starter-module.less		Index:index页面的全局样式定义
		|        	├starter-module.js			Index:index页面的起始脚本模块（通常用作全局脚本）
		|        ├Public	公用模板文件
		|        ├Utils		可移植服务接口模板文件（由对应的Widget来导入）
		|

### Widget部署

暂无想法~~~欢迎建议与交流

# 开发规范

## 返回值统一规范

在千木架构中，建议所有的方法或函数返回值均采用数组，示例格式如下：

		$return = array(
			'status' => 220,
			'info' => "获取文章id=13成功",
			'data' => array(
				"title" => "示例标题",
				"content" => "此处示例内容",
			),
		);

建议返回值格式须由 `status` 、 `info` 、`data` 三个元素组成：

1.	status：返回值的操作码，一个3位数的INT值，一切要包含的意义尽在这三位数中。
2.	info：返回官方型的返回信息，供调用本函数的开发者看，可以写一些建议，也可以写操作状态，是一个字符串。
3.	data：真正要返回的实体数据，即整个函数真正要返回的内容在这里

采用建议的返回值规范格式可以很方便地通过返回值处理服务ReturnService来接收返回值并进行智能处理和包装，方便开发与模块间开发的规范统一。提升开发体验。

关于status操作码，可参考最后所附的ChigiCode。

# API——ChigiAction

# ChigiCode

## 第一位数说明

*	`0` ——保留数值，留空
*	`2` ——函数执行正常，返回成功信息
*	`4` ——函数本体执行正常，返回业务逻辑上的失败信息
*	`5` ——函数本体执行失败，即应用逻辑上出错，主要有 `脚本错误` 和 `数据库查询错误` 两种

## 第二位数说明

*	`0` ——函数执行中断，无返回讨论
*	`1` ——数值型返回
*	`2` ——字符串返回
*	`3` ——数组型返回
*	`4` ——布尔型返回
*	`5` ——无返回，返回数据为null
*	`6` ——

## 第三位数说明

*	`1` ——一般返回数据，无附加说明
*	`2` ——抛出_404()
*	`3` ——返回数据库查询错误信息

## 具体代码说明

*	`000` ——留空，表示未定义
*	`211` ——执行正常，返回为数值，一般返回数据，无附加说明
*	`221` ——执行正常，返回为字符串，一般返回数据，无附加说明
*	`231` ——执行正常，返回为数组，一般返回数据，无附加说明
*	`241` ——执行正常，返回为对象，一般返回数据，无附加说明
*	`251` ——执行正常，返回为布尔，一般返回数据，无附加说明
*	`261` ——执行正常，没有返回，返回数据为null
*	`402` ——业务失败，业务逻辑上中断脚本运行，并抛出_404()
*	`411` ——业务失败，返回数值，一般返回数据，无附加说明
*	`421` ——业务失败，返回字符串，一般返回数据，无附加说明
*	`431` ——业务失败，返回数组，一般返回数据，无附加说明
*	`441` ——业务失败，返回布尔值，一般返回数据，无附加说明
*	`451` ——业务失败，void返回，返回数据为null
*	`501` ——函数执行中断，应用逻辑错误，不作任何附加处理
*	`502` ——函数执行中断，抛出_404()
*	`523` ——数据库查询出错，返回数据库查询错误信息内容