Developing Specification
===============================

# Developing Specification

- [Developing Specification](#developing-specification-1)
	- [RETA:Return Values Formatting](#retareturn-values-formatting)
	- [On: Form Both-sides Standard](#on-form-both-sides-standard)
		- [Public Interface](#public-interface)
		- [Override Interface](#override-interface)
		- [Defination Specification in the service developing](#defination-specification-in-the-service-developing)
		- [AUTO REQUEST](#auto-request)
	- [RESTful Interface](#restful-interface)
	- [Under: Method for Environment Check](#under-method-for-environment-check)
	- [CHING-SESSION Mechanism](#ching-session-mechanism)
		- [CHING会话部署 与 参数配置](#ching--)
		- [The Ching() Function](#the-ching-function)
		- [Ching-session Initialization](#ching-session-initialization)
		- [Service registration on Ching](#service-registration-on-ching)
		- [Ching-session Timeout](#ching-session-timeout)
	- [Naming rules for template assigning](#naming-rules-for-template-assigning)
	- [Naming rules for template files](#naming-rules-for-template-files)

## RETA:Return Values Formatting

在千木架构中，建议所有的方法或函数返回值均采用数组，示例格式如下：

		// 1.8.5起，支持简化写法：
		$return = chigi_reta(220,"获取文章id=13成功",
			array(
				"title" => "示例标题",
				"content" => "此处示例内容",
			),
		);

		// 旧版完整写法
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

从1.7.0开始，服务类不再允许通过HTTP访问，目标服务类的指定均通过给on操作传参完成，而两种不同的接收接口则对应两种不同的传参方式，提升开发体验。

开发上，以上两种接口的主要区别就是在于控制器中是否需要定义一个新的on操作，而service中仍旧是需要定义对应的on业务逻辑处理与请求发送。

service 中可以不定义on业务逻辑处理的唯一允许条件就是仅针对简单POST数据请求，则可以交由架构自己进行 'AUTO REQUEST'.

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

表单提交地址为： `{:redirect_link('Index/onxxx/',array('iframe'=>$_GET['iframe']))}` ，对应在Index控制器中定义的 `onxxx` 操作。

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

		public $onTest = array();...//建立声明此与on方法名一样的属性，以作为表单数据源对象使用
		// ↑详见后面相隔的 Data Source Support 一节

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

### Definition Specification in the service developing

Only business logic is in need, in the service layer, for the 'on' methods, and the return value would be catched in the infrasture so as to do some judgement on the result and redirecting then.

The support returns value/type are:

* **TRUE**: Success redirecting.
* **FALSE**: Error redirecting.
* **1**: Success redirecting.
* **0**: Error redirecting.
* **-1**: Debug mode without redirecting.
* **array('debug'=>$result)**: Debug mode without redirecting and with the addition of the information for the target `$result` data
* **RETA**: redirecting upon the ChigiCode in the 'status' element.
* **ChigiReturn**: redirecting upon the ChigiCode in the object.

And in the invoking, no matter public interface or private interface, the writing must be completed such as 'onAddArticle' rather than 'addArticle' or 'AddArticle'.

Since 1.8.9, you have been allowed to watch the `$result` data's detail in the Debug mode:

	// ↓Service Layer
	public function toBeDebuged(){
		$result = $this->request('target');
		return array('debug'=>$result);
	}

### Data Source Support

Since 1.9.3, this structure begin to support data source programming, especially for form developing.

Specificly, the Data Source is designed for the form and extending the on interface. So it's very easy to define just using a property with a same name of the target on method.

The reference of the definition of the data source in the service:

	// ↓Service Layer
	public $onTest = array(
			'field_name'=>array('validate_rule','err_msg' [,'auto_fill_rule', 'auto_fill_contents']),
			'email'=>array('email','对不起，邮箱地址不正确','string','NO EMAIL'),
		);

	public function onTest(){
		...// Referenced to the section of 'Override Interface'
	}

### AUTO REQUEST

Since 1.8.0, the auto-request has been supported in this infrastructure. 

Orienting the simple business logic defined in the API mostly with only POST datas required, developers is allowed not to write the corresponding method starting with 'on' in the service layer.

Of course, there is nothing required in both action and service when using the public interface of `on` .

However, it would be automatically invoked in this infrasture so that a specification, in the API layer, was in need for this super 'on' dealing.

For the easiest using, the only specification is the return value shoule be RETA in the API layer. That's all!

For instance, I want to add an article just submitted, you could only define the business logic to process datas in the API:

	public function requestAddArticle($data){
		$id = $this->dm('Article')->add($data);
		return array(
				'status' => 211,
				'info' => 'Article added succefully',
				'data' => $id
			);
	}

Then you could define the on routers in the action if it's sure that the form could be submitted in 15 mins.

	public function add_article(){
		ching("CHIGI_SUCCESSDIRECT", 'Profile/index');
        ching("CHIGI_ERRORDIRECT", 'Login/index');
        ching("CHIGI_TAG", array(
            'SERVICE' => 'Article',
            'METHOD' => 'onAddArticle'
        ));
	}

Originally, it is same to define a method in the service manually:

	public function onAddArticle(){
		return $this->request($_POST, 'addApp');
	}

## RESTful Interface

从 1.8.5 起，千木架构正式支持 RESTful 的 WebApp 开发，配合千路前端引擎提供的 Backbone、jQuery、underScore 的封装，可以非常便捷地部署REST接口。

REST接口在URL表现上，与普通的 `Action/Method` 形式一样，但是后面不得跟有伪静态后缀，可以使用 `rest_link('Action/interface')` 来生成目标REST接口的URL地址。

部署上，REST接口与控制器操作一样，均写在控制器中，其代码表现是一个控制器中的属性，命名没有限制，可以与控制器中现有操作名重叠，不会冲突。

该属性类型为一关联数组，定义CURD四种不同请求的REST配置，其书写有一定的规则要求，如下所示：

	class AjaxAction extends ChigiAction{
		protected $test = array(
			'CREATE' => array('ServiceName','MethodName'),
			'UPDATE' => array('ServiceName','MethodName'),
			'READ'   => array('ServiceName','MethodName'),
			'DELETE' => array('ServiceName','MethodName'),
		);
	}

	//访问该接口时，生成URL地址的rest_link写法：
	rest_link('Ajax/test');

另外，REST 接口定义时，可以根据前端请求类型需求，按需定义，并不一定要 CURD 四大请求类型全部写完整。

所有的实体业务逻辑均是指向到具体的服务类中，所以在这里其实可以和上面的表单接口on的业务逻辑进行复用，免去再针对REST接口进行单独开发。

在服务类中定义业务实体方法时，其书写规则与服务类中on接口方法的书写是一样的，亦可以直接使用 "on" 开头的方法名，以完成 Auto Request。

唯一的区别就是在传参上， on 接口方法一般是针对表单使用，故一般可直接获取 `$_POST` ，而REST接口的数据必须在服务类方法中定义好预置参数，千木架构会自动将前端推送过来的数据传入，仅支持一个参数，一般即为前端推送的Model数据，而在URL中的id值，千木架构则会自动将其推入绑定数据层，直接通过 `$this->bind('rest_id')` 即可获取。

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

## Naming rules for template files

由于从1.8.5开始，千木架构原生支持SourceMap，所以大规模JS调试变得会十分方便。

但为保证SourceMap的使用不会出错，请遵守相应的命名及目录部署规则：

1. 保证主题路径（一般即 Default/）下仅能有一层子文件夹。
2. 模板文件名中不得出现冒号（`:`）及下划线（`_`），命名方式可采用驼峰式。