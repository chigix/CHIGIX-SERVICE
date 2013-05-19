Developing Specification
===============================

# Developing Specification

- [RETA:Return Values Formatting](#retareturn-values-formatting)
- [On: Form Both-sides Standard](#on-form-both-sides-standard)
	- [Public Interface](#public-interface)
	- [Override Interface](#override-interface)
- [URL Params Via GET](#url-params-via-get)
- [Communication Standard via POST & REQUEST](#communication-standard-via-post--request)
- [Under: Method for Environment Check](#under-method-for-environment-check)
- [CHING-SESSION Mechanism](#ching-session-mechanism)
	- [CHING会话部署 与 参数配置](#ching--)
	- [The Ching() Function](#the-ching-function)
	- [Ching-session Initialization](#ching-session-initialization)
	- [Service registration on Ching](#service-registration-on-ching)
	- [Ching-session Timeout](#ching-session-timeout)
- [Naming rules for template assigning](#naming-rules-for-template-assigning)

## RETA:Return Values Formatting

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

关于以上数组格式是整个千木服务架构专门设计用来统一返回值问题的，因此针对如上格式的数组称为 RETA 数组。

采用RETA数组可以很方便地通过返回值处理服务ReturnService来接收返回值并进行智能处理和包装，方便开发与模块间开发的规范统一。提升开发体验。

关于status操作码，可参考最后所附的ChigiCode。

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## On: Form Both-sides Standard

表单能让服务器接收来自客户端的大量复杂数据，为保证服务器的安全性，需要对表单提交进行多重安全检测过滤。

对于表单提交，从1.5.5版本开始，千木服务架构专门提供了一个on万能操作，用于接收所有的表单数据并送至指定服务类进行处理和输出跳转。

通过on操作可以避开数据处理和结果输出之间的耦合，而关于on操作的使用则有如下两种接口方案：

1. 公共接收接口：on操作直接暴露在HTTP下接收提交过来的表单，通过通用表单接收规范，千木架构会自动将数据送至对应服务类。
2. 自定义接收接口：在控制器中定义表单的接收方法，手动调用on操作来指定目标服务类。

以上两种方法仅仅是on操作的调用方式不同，而on操作本身就是负责将数据送至服务类进行处理，数据存储和业务逻辑当然则是由Api基于数据模型完成，而数据处理完毕后的输出与跳转机制则是定义在服务类中。

on操作与under操作的逻辑部分均由相应的服务类提供，而on操作应用层封装于控制器层，可通过在控制器中包装调用或直接接收外部表单HTTP提交；而under操作封装于Service类，通过在控制器中进行环境检测（undercheck）调用。

从1.5.5开始，服务类不再允许暴露于HTTP下，而改由on操作包装调用，目标服务类的指定均通过给on操作传参完成，而两种不同的接收接口则对应两种不同的传参方式，提升开发体验。

从1.7.0开始，服务类不再允许通过HTTP访问。

### Public Interface

表单直接提交至 `{:redirect_link('/on/',array('iframe'=>$_GET['iframe']))}` 即可，无需做任何变动。

控制器中在输出表单的操作中定义ching内容，on操作会自动处理ching会话：

1. 控制器中

		// <editor-fold defaultstate="collapsed" desc="设置公共on接口操作">
        ching("CHIGI_SUCCESSDIRECT", 'Profile/index');//设置成功跳转地址
        ching("CHIGI_ERRORDIRECT", 'Profile/index');//设置失败跳转地址
        ching("CHIGI_TAG", array(    //设置表单"CHIGI_TAG"目标数组
            "SERVICE" => "Sugar", //指定向Sugar服务
            "METHOD" => "login"
        ));
        // </editor-fold>

2. 表单设计上

		<!-- 目标跳转地址，一般与成功跳转地址相同 -->
		<input type="hidden" name="iframe" value="/index.php/profile">

		<!-- 表单令牌 -->
		<input type="hidden" name="__hash__" value="970e9c4e7b4ce03d57694536a82b46a0_11dbf80f6813d6b4b024f27346dff35b">

		<!-- 表单验证码（可选） -->
		<input type="text" name="verify" value="">
		<img src="__APP__/Public/verify" >

公用表单接收操作方案主要是减免自定义表单接收操作的定义，提升开发体验。但是相应地就有一定的弊端，因为调用服务类所用的传参方式完全基于ching会话，所以采用on操作会存在超时问题。

根据千木架构的底层定义，一旦操作超时，系统会自动跳回上一页面，并提示“操作超时”。该时效即ching会话配置 `CHINGSET.EXPIRE` 中定义的时间。

[INDEX](#index)		
[CONTENTS](../README.md#contents)

### Override Interface

表单提交地址为： `{:redirect_link('/onxxx/',array('iframe'=>$_GET['iframe']))}` ，对应在Index控制器中定义的 `onxxx` 操作。

控制器中操作定义：必须以on开头：

		public function onxxx(){
			$serviceName = "Article";
			$methodName = "add";
			$successDirect = '/profile/';
			$errorDirect = '/login/';
			//手动调用on方法
			return($this->on($serviceName,$methodName,$successDirect,$errorDirect));
		}

服务类中定义：必须以on开头：

		public function onTest(){
			if(DEBUG) return -1;
			if(FALSE) return 0;
			if(TRUE) return 1;

			//↓直接返回ReturnService对象
			return service("Return")->get(...);

			//↓支持返回 RETA 数组
			return array(
					"status" => 201,
					"info" => "BANKAI"
				);
		}

由于这种手动传参调用的方式不依赖ching会话，所以服务器的资源开销会小一些，同时不存在表单提交的时效问题，可以应用在文章提交之类的表单页需要长时间停留的业务上。

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## URL Params Via GET

所有参数均基于PATH-INFO，且均以标准的 `key/value` 型书写，其中key则直接可从 `$_GET` 中获取，而部分value需经过 `base64_encode` 函数加密写入，获取时由 `base64_decode` 函数解密使用。（仅有本架构占用的一些特殊的GET变量需要进行如上转换，不影响开发者的一般使用）

建议使用架构自提供的 `redirectHeader()` `redirect_link()` 和继承ChigiService的服务子类中的 `addAddrParams()` 方法来自动生成URL。

本架构默认占用的GET变量名：

1. $_GET['iframe']，全局化的GET变量，如果地址栏中没有设定，则会在架构中自动补充为NULL

而获取时ChigiAction根类已自动将所有的$_GET参数（除ThinkPHP的URL索引外）全部进行了 `base64_decode` 解码。

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## Communication Standard via POST & REQUEST

POST认为是来自表单的参数传递，故所有的POST请求中均需有表单令牌验证。

REQUEST由于包含了GET的信息，但却不与GET一起统一进行BASE64的解密，所以不建议使用REQUEST。

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## Under: Method for Environment Check

under机制就是一种业务级的环境保障，即先手动检测当前环境是否符合要求，若不符合则跳转到一个新的页面，若符合则往下执行。

而在本架构中，从1.6开始由服务类开始提供under操作，多种服务类之间可以拼装，环境检测亦可以拼装，不同的环境检测由不同的服务类提供调用，增加开发体验。

开发上，控制器中通过 `under` 操作调用，服务类中则提供对应的以 `under` 开头的方法。

通过调用根类提供的under()操作，直接便捷地执行对应服务类中的环境检测逻辑：

**控制器中**：

		$serviceSugar->under('Login')->setDirect('/login/')->rmAddrParam('iframe')->pushAlert("对不起，请先登录")->check();

则在SugarService **服务类中** 有对应的 `underLogin()` 方法以封装检测逻辑，支持Integer、Boolean、Array三种返回类型：

		/**
		 * @return integer
		 */
		public function underLogin(){
			return isset(ching("uid"))? 1 : 0;
		}

		/**
		 * @return boolean
		 */
		public function underLogin(){
			return isset(ching("uid"))? true : false;
		}

		/**
		 * @return array 需符合本架构的返回值规范
		 */
		public function underLogin(){
			if(isset(ching("uid"))){
				return array(
					"status" => 201,
					"info" => "LOGINED"
				);
			}else{
				return array(
					"status" => 401,
					"info" => "UNLOGINED"
				);
			}
		}

on操作与under操作的逻辑部分均由相应的服务类提供，而on操作应用层封装于控制器层，可通过在控制器中包装调用或直接接收外部表单HTTP提交；而under操作封装于Service类，通过在控制器中进行环境检测（undercheck）调用。

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## CHING-SESSION Mechanism

从1.2版本开始，提供CHING会话机制，该会话机制旨在分布式的会话实现，并作为原生SESSION机制的增强替代方案。

CHING会话机制提供开发者与SESSION几乎完全一样的使用方法，但是其内部的实现机制上直接提供了跨子域、无COOKIE跨页、多站会话共享等问题的解决。

现1.7.0+版本中的CHING会话机制物理实现上仅处于物理文件读写、APC、Xcache，Memcache等其他缓存支持将在2.0+版本中提供支持。

### CHING会话部署 与 参数配置

		/WebRoot/Core/  ——放置MVC框架内核
				/Ching/ ——放置CHING会话文件

一般网站按上述部署即可，在config.php中加入下面的设置项

		'CHINGSET' => array(
				'TYPE' => 'File',     //底层缓存方式
				'DIR' => dirname($_SERVER['SCRIPT_FILENAME']) . '/' . THINK_PATH . '../Ching/',  //缓存目录（仅针对File缓存方式有效）
				'EXPIRE' => 60,      //缓存时效，超时该缓存中的内容将不可读，并且尝试读取的操作将返回false
				'DOMAIN' => "five.com", //设置ching会话SID的作用域名
		);

SID是CHING会话的暴露标识，用以让浏览器在页面切换之间可以继续上一页面的访问情况，关于SID则比session_id更加灵活与安全，SID可以通过COOKIE、GET、POST三种方式传递给服务器，为了安全起见，可以直接将CHING会话与用户挂钩。

从1.2.1版本开始，本框架会自动为所有访问者创建SID，包括游客，开发者则无需考虑SID的任何有关实现，只需直接操作用户会话即可。同时游客SID与SugarService下的用户加密SID完全兼容配合，不同类型的SID可以在SugarService下直接进行检测识别。

### The Ching() Function

ching会话在使用上与session完全一样，仅是普通的键值型数据的临时存储。

基本使用如下：

1.	`ching("newName",$newValue);`              设置新的ching会话值
2.	`ching("newName.se.ele1",$newValue);`      设置新的ching会话值，数组索引直接设置元素值(1.7.0+)
3.	`ching()`                                  获取当前全局ching会话内容（数组）
4.	`ching("name");`                           ching会话取值
5.	`ching("Array.Element1.Ele2");`            ching会话数组取值（1.3.0+）
6.	`ching("name",null);`                      删除指定ching
7.	`ching(null);`                             清空当前ching（1.3.5+）

### Ching-session Initialization

通过CHING实例化 `CHING::getInstance()` 即可返回一个初始化完毕的全新的CHING会话对象。

该对象实例来自CHING类，直接通过调用其内部的 `get()` 方法和 `set()` 方法来进行CHING会话数据存储。

`CHING::getInstance()` 使用时可传入一个指定的ChingID参数（一般就直接空参调用，CHING会话系统会自动生成一个ChingID），所有可配置参数均来自项目配置文件中的CHINGSET配置项。

缓存机制上，目前ching会话配置仅支持 Apc、Xcache和File（文件存储）三种底层缓存实现，对于一般的非分布式架构网站则足矣，而对于分布式大规模网站的ching会话则更需求于Memcache之类的缓存机制，此类支持将在 2.0+ 版本中提供实现。

### Service registration on Ching

所有的服务可以向ching会话进行存储，但为避免服务与服务之间的ching会话产生冲突，在此制定服务注册规范：

各自服务均需以服务本身名称进行ching会话名注册，可参考下面ArticleService的会话注册：

		$content = array(
			"key" => "val",
		);
		ching("ArticleService" , $content);

### Ching-session Timeout

CHING会话目前默认时效为15分钟，开发者亦可通过CHINGSET配置项，在配置文件中自定义时效( 1.4.0+ )。

**注意** 任何会话机制在底层都是有缓存方式在支持，所以规定会话的时效性十分重要，可以为服务器避免不必要的开销，另外可以直接根据文件的最后修改时间删除超过15分钟的文件，进行会话垃圾清理。

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## Naming rules for template assigning

|assign                                                      | 模板引擎调用                            |
|------------------------------------------------------------|-----------------------------------------|
|`$this->assign("PackageName_ELeName_Var");`                 | `{$PackageName_EleNameMODULE_var}`      |
|`$this->assign("Public_Header_" . ACTION_NAME, "active");`  | `{$Public_Header_index}`                |


|文件                                        | include标签                                      |
|--------------------------------------------|--------------------------------------------------|
|`/Theme/AlertService/TopAlert.html`         | `<include file="AlertService:TopALert" />`       |
|`/Theme/AppsMODULE/indexDisplayList.html`   | `<include file="AppsMODULE:indexDisplayList" />` |

[INDEX](#index)		
[CONTENTS](../README.md#contents)
