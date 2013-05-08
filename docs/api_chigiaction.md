API_ChigiAction
===============================

# $this->on($serviceName = null, $methodName = null, $successDirect = null, $errorDirect = null);

* 描述：表单提交统一接收操作
* 说明：本操作可以直接暴露于HTTP下提交运行，则所有的参数会自动从ching会话中获取，关于此部分详细的规范见[公共接收接口规范](#-8)一节。
* 参数：

		$serviceName    指定负责处理表单的服务名
		$methodName     指定具体的表单处理业务逻辑操作名（实为定义于服务类中的以“on”开头的方法）
		$successDirect  指定数据处理成功后的跳转页面，进行页面的输出工作（on操作期间仅负责数据处理，无任何输出）
		$errorDirect    指定处理失败后的跳转页面

* **关于数据**：on方法会直接接收并包装POST数据，而后传入指定的服务与其中的操作，自动执行，而至于具体的操作中的逻辑流程则定义在服务类中对应的以 “on” 开头的方法中。

[返回目录](#contents)