The ChigiAction Class
===============================

## ChigiAction::on;

The public general interface for fetch the data from submited form.

* Description：

		$this->on($serviceName = null, $methodName = null, $successDirect = null, $errorDirect = null);

	本操作可以直接暴露于HTTP下提交运行，则所有的参数会自动从ching会话中获取，关于此部分详细的规范见[公共接收接口规范](#-8)一节。

* Parameters：

	Param                   |Desc
	------------------------|-----------------------------
	$serviceName            |To give a service name to accept the data from this form table.
	$methodName             |To give a method name in the $serviceName to accept the data from this form table.
	$successDirect          |To modify the success direct address ( allowed for ActionName )
	$errorDirect            |To modify the error direct address ( allowed for ActionName )

* **INVOKE**：

on方法会直接调用定义在 `$serviceName` 服务类中的 `$methodName` 方法，而通过 service 层的方法可以获取到客户端送过来的 `$_POST` 数组数据，则在 service 层的对应方法中再发送 request 请求便可将POST数据传递给API进行数据处理与包装。在控制器与 service 中此类方法名须以 `on` 开头。

* **AUTO REQUEST**:

从1.8.0版本开始，on方法支持自动发送请求，即主要针对像简单地将POST数据转发给API的 service 操作则可以免定义，而直接由千木架构自身发送请求，以运行API中定义的相关业务逻辑。即面向一些纯粹转发POST数据的表单接收方法则可以在控制器与service中均免定义。

当然，此功能不与on 私有接口设置冲突，若仍需设置on私有接口，则开发者可以继续将接口定义在控制器中，而service中如不定义对应的on开头方法，则架构仍旧可以自动向API发送数据请求。

[INDEX](#index)		
[CONTENTS](../README.md#contents)
