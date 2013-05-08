API_ChigiService
===============================

# $this->apiAction;

* 描述：指向当前Service服务类所对应的api接口对象
* 使用：

		//安装配置
		public $apiAction = 'Sugar.Action.SugarApi';  //以SugarService为例

		//使用示例
		$this->apiAction->onlineip = getClientIp(); //属性访问
		$this->apiAction->requestCurrentUser($property); //操作访问调用

# $this->addAddrParams( string $key, mixed $value);

* 功能：为当前服务的跳转地址添加活动参数，即地址栏“?”后面的部分
* 参数：
				$key：参数名
				$value：参数值，仅支持字符串和数值两种基本类型
* 返回值：无

# $this->successDirectHeader();

* 功能：手动执行成功页面跳转
* 参数：无
* 返回值：无

# $this->errorDirectHeader();

* 功能：手动执行失败页面跳转
* 参数：无
* 返回值：无

# $this->setDirect( string $successAdd = null, string $errorAdd = null);

* 描述：设置当前Service操作的跳转地址。
* 使用：无返回，仅用于在Service类中设置跳转地址，而该地址可通过控制器传入，其中技巧不作赘述。
* 说明：所有的ChigiService子类初始化时会自动拥有初始跳转地址，其地址获取优先级为：参数传入→ching→项目配置参数
* ching配合：支持 `ching("CHIGI_ERRORDIRECT")` 和 `ching("CHIGI_SUCCESSDIRECT")` 。
* 项目配置： `C("CHIGI_ERRORDIRECT")` 和 `C("CHIGI_SUCCESSDIRECT")` 。

# $this->under($method);

* 描述：环境保障操作，执行指定的$method操作以检测当前环境是否达标，若不达标则会跳转，达标则继续往下执行。
* 使用：采用链式书写→ `$service->under('Login')->setDirect('/login/')->pushAlert("对不起，请先登录")->check();`
* **书写注意**：$method首字母需大写。
* 说明：执行链中项目均为可选，整个链必须做到 `under()` 开头到 `check()` 结尾才正确。
* ching配合：pushAlert会通过AlertService将message写入到 `ching("chijiAlert")` 中。
* under机制参见[环境保障规范](#under)

[返回目录](#contents)