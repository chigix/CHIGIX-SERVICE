<?php

function keywordSpaceClip($targetString) {
    $array = explode(' ', $targetString);
    foreach ($array as $key => $value) {
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
    } else {
        $traceInfo = debug_backtrace();
        throw_exception('Service ' . $serviceName . ' not found ' . $traceInfo[0]['file'] . ' 第 ' . $traceInfo[0]['line'] . ' 行.');
    }
}

/**
 * 指定Api模块地址连接函数
 *
 * @param String $address Api模块地址
 */
function apiConnect(&$address) {
    import($address);
    ApiAction::$appHost = C('CHIGI_AUTH');
    $address = new ApiAction();
}

/**
 * 在Chigi架构下任意地方强制显示PageTrace
 *
 * 在部署模式下时请把所有的chigiTrace调用撤去，避免影响性能，相应的提示会输出在系统每天的LOG记录中。
 */
function chigiTrace() {
    if (!APP_DEBUG) {
        $debug = debug_backtrace();
        Log::write('撤销' . $debug[0]['file'] . "文件第" . $debug[0]['line'] . "行chigiTrace函数的调用！", Log::ALERT);
    }
    B('ShowPageTrace');
}

/**
 * 判断参数是否等效于true
 * 根据参数的操作码直接转换成布尔，方便在条件中使用
 * @param mixed $param
 * @return boolean
 */
function chigiValid($param) {
    if (is_array($param)) {
        if (($param['status'] >= 200) && ($param['status'] < 300)) {
            return true;
        } else {
            return false;
        }
    } elseif (is_object($param)) {
        if (($param->code >= 200) && ($param->code < 300)) {
            return true;
        } else {
            return false;
        }
    } elseif (is_int($param)) {
        if (($param >= 200) && ($param < 300)) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

/**
 * 判断参数是否等效于false
 * 根据参数的操作码，将所有5xx编码的参数均转换为FALSE，方便在条件中使用
 * @param mixed $param
 * @return boolean
 */
function chigiErrorstate($param) {
    if (is_array($param)) {
        if (($param['status'] >= 500) && ($param['status'] < 600)) {
            return true;
        } else {
            return false;
        }
    } elseif (is_object($param)) {
        if (($param->code >= 500) && ($param->code < 600)) {
            return true;
        } else {
            return false;
        }
    } elseif (is_int($param)) {
        if (($param >= 500) && ($param < 600)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * 客户端真实IP探测
 *
 * @return String
 */
function getClientIp() {
    $cip = getenv('HTTP_CLIENT_IP');
    $xip = getenv('HTTP_X_FORWARDED_FOR');
    $rip = getenv('REMOTE_ADDR');
    $srip = $_SERVER['REMOTE_ADDR'];
    $onlineip = "";
    //优先cip以尽可能获取最真实的客户端IP
    if ($cip && strcasecmp($cip, 'unknown')) {
        $onlineip = $cip;
    } elseif ($xip && strcasecmp($xip, 'unknown')) {
        $onlineip = $xip;
    } elseif ($rip && strcasecmp($rip, 'unknown')) {
        $onlineip = $rip;
    } elseif ($srip && strcasecmp($srip, 'unknown')) {
        $onlineip = $srip;
    }
    $match = "";
    preg_match("/[\d\.]{7,15}/", $onlineip, $match);
    return $match[0] ? $match[0] : 'unknown';
}

/**
 * 返回个位数
 *
 * @param Integer $int
 * @return Integer
 */
function getNumOnes($int) {
    $i = $int % 10;
    return $i;
}

/**
 * 返回十位数
 *
 * @param Integer $int
 * @return Integer
 */
function getNumTens($int) {
    $two=($int/10)%10;//十位
    return $two;
}

/**
 * 返回百位数
 *
 * @param type $int
 * @return type
 */
function getNumHundreds($int) {
    $three=($int/100)%10;//百位
    return $three;
}
?>