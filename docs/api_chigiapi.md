The ChigiApi Class
===============================

# ChigiApi::$appHost

* 描述：当前运行的应用的注册32位密钥
* 说明：此密钥由Service通过项目配置参数获取并传递给api类

# ChigiApi::$appHostIp

* Description:

	The IP address of the current App server under link.

* Note:

	This is designed out of security that it won't make any link until this IP address is same to that registered.

# ChigiApi::$time

* Description:

	The timestamp for the current instance of the related Api.

# ChigiApi::dm

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