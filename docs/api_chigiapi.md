API_ChigiApi
===============================

# $this->appHost;

* 描述：当前运行的应用的注册32位密钥
* 说明：此密钥由Service通过项目配置参数获取并传递给api类

# $this->appHostIp;

* 描述：当前运行的应用自身的服务器IP
* 说明：此IP须与应用自身注册IP对应，否则将无法通过应用安全检测

# $this->time;

* 描述：当前Api被实例化时的时间戳

# $this->dm("TableName");

*	功能：返回目标数据模型，做到按需连接数据库查询，避免产生无用资源
*	参数：String，例如 `SugarMembers` 则指向 `sugar_members` 表
*	注意：使用dm函数获取的目标数据模型必须在当前Api子类中有对应的属性，且属性中写明要连接的数据模型地址，例如：

		public $dmTableName = "Project://TableName";  //设置目标数据模型地址，必须为public，否则报错
		public function test(){
			$this->dm("TableName");  //获取目标数据模型
		}

*	版本：1.4.0+

