The ChigiApi Class
===============================

## ChigiApi::$appHost

* 描述：当前运行的应用的注册32位密钥
* 说明：此密钥由Service通过项目配置参数获取并传递给api类

## ChigiApi::$appHostIp

* Description:

	The IP address of the current App server under link.

* Note:

	This is designed out of security that it won't make any link until this IP address is same to that registered.

## ChigiApi::$user_agent

* Description:

	The User_Agent information from service.

	It is an associative array consists:

	* `'ip'`    客户端浏览器访问时的IP地址信息
	* `'bot'`   客户端浏览器访问时的浏览器内核信息/蜘蛛引擎名称
	* `'__'`    整个 `$_SERVER['USER_AGENT']` 字符串内容

## ChigiApi::$time

* Description:

	The timestamp for the current instance of the related Api.

## ChigiApi::bind

* Description：

		$this->bind("Key" [,"Value"]);

	Add a pair of key-value as a Query Param to the current redirect link in this service.		
	Or get a value binded to the key associated.

* Parameters：

	Param                   |Desc
	------------------------|-----------------------------
	$key                    |The param's variable name
	$value                  |Optional, the value of the corresponding param

* Return Values:

	The last value of the key given.

* Example:

		$this->bind('uid',2);
		$this->bind('uid');

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## ChigiApi::dm

* Description：

		$this->dm("TableName");

	Automatic make an accessable singleton data model object to avoid repeatedly model building.

* Parameters：

	Param                   |Desc
	------------------------|-----------------------------
	$tableName              |The name related target data table in hump naming.

* Version: 1.4.0+
* Example:

	$this->dm('SugarMembers')->limit(2)->select();

[INDEX](#index)		
[CONTENTS](../README.md#contents)