Functions
===============================

# string arrayImplode( string $glue, string $separator, array $array);

* 功能：将关联数组合并成一个字符串，弥补PHP原生的implode函数仅能处理数值数组的不足。
* 参数：

		$glue       键值之间的连接，形如 `{$key}{$glue}{$value}`
		$separator  数组元素与元素之间的整体分隔符
		$array      要进行合并的目标数组（关联数组）

# void redirectHeader($addr, $params = array() , $domain = null);

* 功能：直接进行地址跳转
* 参数：

		$addr    主地址，可以是http开头的独立地址，若调用项目内部操作页面，则需使用U();
						 如果是http开头的独立地址，则允许自带有地址参数，$params中的参数本函数会自动处理添加
		$params  地址参数，例如array("iframe"=>U('Action/Module'))则会生成 ?iframe=index.php/......这样的地址
		$domain  指定域名，例如"http://www.chigix.com"，传入的域名必须完整包含协议名，且结尾没有斜杠，若为空则自动使用当前域名

* 关于iframe：iframe参数是本架构特别定义的一个地址栏参数，用于显式指示目标跳转页面，主用于避免ching会话超时问题

	iframe采用rawurlencode/rawurldecode进行编解码。

	本函数仅用于生成地址并直接跳转，故在主地址和iframe参数中可以直接使用U函数生成地址，然后 `redirect_link()` 函数中可以继续使用 `$_GET` 来获取iframe参数。关于 `redirect_link()` 的参数转发中，详见相应的函数说明。

# string redirect_link($addr, $params = array() , $domain = null);

* 功能：生成带参复杂地址链接，并以字符串返回
* 参数：

		$addr    主地址，可以是http开头的独立地址，若调用项目内部操作页面，则需使用U();
						 如果是http开头的独立地址，则允许自带有地址参数，$params中的参数本函数会自动处理添加
		$params  地址参数，例如array("iframe"=>U('Action/Module'))则会生成 ?iframe=index.php/......这样的地址
		$domain  指定域名，例如"http://www.chigix.com"，传入的域名必须完整包含协议名，且结尾没有斜杠，若为空则自动使用当前域名

* 关于iframe：iframe参数是本架构特别定义的一个地址栏参数，用于显式指示目标跳转页面，主用于避免ching会话超时问题

	iframe采用rawurlencode/rawurldecode进行编解码。

	典型示例：

		redirect_link($addr,array("iframe"=>U('Action/Module')));  //用U函数时直接在里面使用
		redirect_link($addr,array("iframe"=>$_GET['iframe']));     //从iframe中获取地址参数再传入时无需再使用U函数
		redirect_link('/on/');   //生成：http://www.chigix.com/on.html
		redirect_link('Login/index');   //生成：http://www.chigix.com/login/
		redirect_link('Index/index');   //生成：http://www.chigix.com

[返回目录](#contents)
