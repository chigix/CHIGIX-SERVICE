<?php
	function keywordSpaceClip($targetString){
		$array = explode(' ' , $targetString);
		foreach ($array as $key=>$value) {
			$array[$key] = array('keyword' => $value);
		}
		return $array;
	}
	
	/**
	 * 指定Service连接函数
	 *
	 * @param String $serviceName Service名称，如“Article”即可
	 * @return Object
	 */
	function service($serviceName) {
		$service = $serviceName . 'Service';
		if (import('@.Service.' . $service)) {
			return (new $service());
		}else {
			$traceInfo = debug_backtrace();
			throw_exception('Service ' . $serviceName .' not found ' . $traceInfo[0]['file'] . ' 第 ' . $traceInfo[0]['line'] . ' 行.');
		}
	}
	
	/**
	 * 指定Api模块地址连接函数
	 *
	 * @param String $address Api模块地址
	 */
	function apiConnect(&$address){
		import($address);
		ApiAction::$appHost = CHIGI_AUTH;
		$address = new ApiAction();
	}
	/**
	 * 在Chigi架构下任意地方强制显示PageTrace
	 *
	 * 在部署模式下时请把所有的chigiTrace调用撤去，避免影响性能，相应的提示会输出在系统每天的LOG记录中。
	 */
	function chigiTrace(){
		if (!APP_DEBUG) {
			$debug = debug_backtrace();
			Log::write('撤销' . $debug[0]['file'] . "文件第" . $debug[0]['line'] . "行chigiTrace函数的调用！" , Log::ALERT);
		}
		B('ShowPageTrace');
	}
	
	/**
	 * 生成唯一CHIGI_AUTH信息字符串
	 *
	 * 可标示当前用户的当前应用
	 * @param String $username
	 * @param String $password
	 * @return unknown
	 */
	function chigiAuth($username , $password) {
		return to_guid_string(array($username, $password, APP_NAME));
	}
?>