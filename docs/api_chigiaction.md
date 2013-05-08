The ChigiAction Class
===============================

# ChigiAction::on;

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

* **关于数据**：on方法会直接接收并包装POST数据，而后传入指定的服务与其中的操作，自动执行，而至于具体的操作中的逻辑流程则定义在服务类中对应的以 “on” 开头的方法中。

[返回目录](#contents)