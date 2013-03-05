<?php

/**
 * Implode an array with the key and value pair giving
 * a glue, a separator between pairs and the array
 * to implode.
 * @param string $glue The glue between key and value
 * @param string $separator Separator between pairs
 * @param array $array The array to implode
 * @return string The imploded array
 */
function arrayImplode($glue, $separator, $array) {
    if (!is_array($array))
        return $array;
    $string = array();
    foreach ($array as $key => $val) {
        if (is_array($val))
            $val = implode(',', $val);
        $string[] = "{$key}{$glue}{$val}";
    }
    return implode($separator, $string);
}

/**
 * 指定Api模块地址连接函数（将被废弃，不建议使用）
 *
 * @param String $address Api模块地址
 */
function apiConnect(&$address) {
    import($address);
    $address = new ApiAction(C('CHIGI_AUTH'));
}

/**
 * 获取目标数组的指定key键对应的值
 *
 * @param Array $array
 * @param mixed $key
 * @return mixed 指定的key对应的值
 */
function arrayGetElement($array , $key) {
    return $array[$key];
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
 * 返回百位数
 *
 * @param type $int
 * @return type
 */
function getNumHundreds($int) {
    $three = ($int / 100) % 10; //百位
    return $three;
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
    $two = ($int / 10) % 10; //十位
    return $two;
}

/**
 * 以空格作为分隔符的关键词数组生成
 *
 * @param String $targetString
 * @return Array
 */
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
 * ching会话机制
 * 可用于代替session，并可以通过配置文件指定缓存方式，亦可当作session的包装函数使用
 *
 * @param type $name
 * @param type $value
 * @return mixed
 */
function ching() {
    if (defined("CHING")) {
        $data = C("CHING")->get(CHING);
        $argNum = func_num_args();
        switch ($argNum) {
            case 0:
                return $data;
                break;
            case 1:
                return isset($data[func_get_arg(0)]) ? $data[func_get_arg(0)] : null;
                break;
            case 2:
                $data[func_get_arg(0)] = func_get_arg(1);
                C("CHING")->set(CHING, $data, 900); //缓存仅存在15分钟
            default:
                break;
        }
    } else {
        return null;
    }
}

?>