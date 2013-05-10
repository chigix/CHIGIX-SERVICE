Deployment
===============================

## Components Arrangement

### Controller

1.	IndexAction	——首页，为保证URL的统一，请保证Index控制器下有且仅有一个index方法
2.	.....Action ——其他页面，一个页面就一个控制器，且直接使用index方法，或可在控制器内跳转修改index。
3.	AjaxAction	——ajax模块，主负责所有页面上异步交互的执行操作

[INDEX](#index)		
[CONTENTS](./index.md#contents)

### Model

由于一切业务逻辑均服务化，所以对于一些主要的业务逻辑均已封装于已有的服务中，而对于已有服务提供的模型则无需作任何部署，直接连接到对应的Service类即可，在千木服务化架构下会替开发者完成所有本服务下的业务逻辑。

通过服务层的抽象与分离，可以更深层地降低应用本身与数据库之间的耦合性，让应用的开发从繁杂的数据模型交互中解放，变成简单易行的服务类的拼装与调用。

[INDEX](#index)		
[CONTENTS](./index.md#contents)

### Template

由于在千木架构下，所有的模板都是直接采用与当前控制器及操作一一对应，故在控制器中只需 `$this->display()` 即可完成前端的输出，而不用开发者再作其他的工作。当然，这需要一个非常严格的模板目录部署机制，以做到 `display` 方法输出时可以直接定位及渲染模板。

从1.7版本开始，调试模式下集成前端渲染增强，每个页面均由一个主页面渲染入口及一堆模块页面组成，在主页面中声明需要加载的子页面，千木架构会自动进行块状加载及渲染，并通过ThinkTemplate模板引擎进行编译，以支持部署模式下的ThinkTemplate模板编译缓存的高效性。

模板目录Tpl下的文件部署如下：

		|-Tpl/Default               主题目录
		|        ├Index/            Index控制器下的页面渲染入口（即主模板文件）
		|        	├index.html     index页面主模板文件
		|        	├method.html    其他页面主模板文件
		|        ├IndexMODULE/            Index控制器下的模块页面（供块状渲染）
		|        	├indexStarter.html     index页面初始模块
		|        	├pageLoginForm.html    page页面的登录表单
		|        ├Public    公用模板文件
		|        ├Utils     可移植服务接口模板文件
		|

[INDEX](#index)		
[CONTENTS](./index.md#contents)

### Widget

		|-Widget/				Widget扩展目录
		|     ├DemoServiceWidget.class.php    DemoService对应Widget类
		|     ├DemoService/                   DemoService对应Widget类调用模板目录
		|              ├method1.html          Widget类下method1操作对应模板文件
		|              ├demoMethod.html       DemoServiceWidget类下的demoMethod操作对应模板文件
		|     ├OtherServiceWidget.class.php   其他同样型的Widget类部署
		|

[INDEX](#index)		
[CONTENTS](./index.md#contents)

## URL

从1.7.0开始，提供更为自由与友好的URL识别与跳转机制，在继承1.5.0提供的URL跳转池基础上，新增URL版本统一机制，以提供更好的SEO地址优化。

通过URL的部署，让开发者在WEB编程中无需再思考URL的定位问题，所有的模块化控制均统一到以控制器为单位的解决方案上，开发者仅需明确跳转或调用的控制器即可，URL的格式及版本统一在架构的封装中一并提供解决与实现。

### URL General Standard

一般性地址，非html结尾的URL均带斜杠，且所有index操作均隐藏 并以斜杠结尾的URL 作为该页面的指定URL格式：

		http://www.chigix.com/
		http://www.chigix.com/on.html
		http://www.chigix.com/login/
		http://www.chigix.com/login/secpage.html
		http://www.chigix.com/profile/request.html?arg1=var1&arg2=var2

SEO专用地址建议：

		【普通静态页地址】
		http://www.chigix.com/news/933.html

		【带分页】
		http://www.chigix.com/article-668-1.html

		【分类型】
		http://www.chigix.com/houduan/php/qian-mu-fu-wu-jia-gou.html

[INDEX](#index)		
[CONTENTS](./index.md#contents)

### URL Redirecting POOL

从千木服务架构1.5.0起，提供全局URL跳转机制，可将某项目下的控制器注册为全局控制器，从而只需将需要整合在一起的网站项目共同连接到同一URL跳转池中，即可轻松组建站群。

而现1.7.0中的URL跳转池是创建在数据库中的，ChigiAction会自动连接该数据库并进行跳转检测，从而实现全局统一页面跳转。

而开发者使用该功能则必须保证所注册的全局控制器仅能存在一个项目中，不能出现多个项目共有，否则会出现局部不跳转。

而对于已有项目的控制器重名问题，例如Index控制器，则可以在操作名前加两个下划线，以避开重名。

由于ThinkPHP在刚开始运行首先检测是否存在对应的控制器，故如需使用控制器名的跳转，可在当前项目下放置一个EmptyAction空类即可，当然亦可在里面写入自定义的逻辑，通过放置一个空模块，便可让ThinkPHP跳转对应模块类的存在检查而直接进入千木架构的URL跳转。

[INDEX](#index)		
[CONTENTS](./index.md#contents)

## BEFORE ONLINE

App目录部署如下：

		|-Core/
		|-App/
		|   |-Common/
		|   |-Conf/
		|   |-Lang/
		|   |-Lib/
		|   |-Runtime/
		|   |-Tpl/
		|   |-.htaccess  ——直接将域名指向到App目录中，不同级别的域名则部署方式相同，无需改动PHP
		|   |-index.php  ——因为作为根目录，所以入口文件放置于目标域名的根目录下
		|
		...
		...
		|-Chiji/   ——建议直接建一个新域名指向该目录，所有静态资源文件均通过该域名来获取
		|    |-App/
		|    |   |-img/
		|    |   |-css/
		|    |   |-js/
		|    |
		|    |-Img/  ——作为图片服务的资源管理仓库
		|
		|

[INDEX](#index)		
[CONTENTS](./index.md#contents)