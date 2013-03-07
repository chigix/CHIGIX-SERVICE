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
function arrayGetElement($array, $key) {
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
 * @param string $name 要获取或新设置的ching名，
 * 获取时支持点号来表示要获取的目标数组元素，形如ching("Sugar.start.ele1")
 * 设置数组请直接将数组放入value参数中
 * @param mixed $value
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
                $arr = explode('.', func_get_arg(0));
                if (isset($data[$arr[0]])) {
                    $result = $data;
                    foreach ($arr as $value) {
                        if (isset($result[$value])) {
                            $result = $result[$value];
                        }  else {
                            return null;
                        }
                    }
                    return $result;
                }  else {
                    return null;
                }
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

/**
 * 进行地址跳转
 *
 * @param string $addr 支持HTTP地址或U生成地址
 * @param string $params 地址参数，会自动根据当前COOKIE状态添加SID的显式传递
 */
function redirectHeader($addr, $params = array()) {
    if (!isset($_COOKIE['sid'])) {
        $params['sid'] = CHING;
    }
    $paramString = "";
    if ($params != array()) {
        $paramString = "?";
        foreach ($params as $value) {
            $paramString .= $value;
        }
    }
    if (!startsWith($addr, 'http://')) {
        $addr = U($addr);
    }
    header("location:" . $addr . $paramString);
}

/**
 * 检测目标字符串$haystack是否以$needle开头
 *
 * @param String $haystack
 * @param String $needle
 * @param Boolean $case
 * @return Boolean
 */
function startsWith($haystack, $needle, $case = false) {
    if ($case) {
        return (strcmp(substr($haystack, 0, strlen($needle)), $needle) === 0);
    }
    return (strcasecmp(substr($haystack, 0, strlen($needle)), $needle) === 0);
}

/**
 * 检测目标字符串$haystack是否以$needle结尾
 *
 * @param String $haystack
 * @param String $needle
 * @param Boolean $case
 * @return Boolean
 */
function endsWith($haystack, $needle, $case = false) {
    if ($case) {
        return (strcmp(substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0);
    }
    return (strcasecmp(substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0);
}

function getToken() {
    $tokenName = C('TOKEN_NAME');
    // 标识当前页面唯一性
    $tokenKey = md5($_SERVER['REQUEST_URI']);
    $tokenAray = session($tokenName);
    //获取令牌
    $tokenValue = $tokenAray[$tokenKey];
    return $tokenKey . '_' . $tokenValue;
}

/**
 * 查找字符在指定字符串中从后面开始的第一次出现的位置，并进行自定义切割字符串
 *
 * @param string $character 目标要搜索的标记字符
 * @param string $string 要进行切割的母字符串
 * @param string $side 要切割出标记字符左边的内容还是右边的内容
 * 支持参数: left  right.
 * @param bool $keep_character 返回字符串中是否保留标记字符
 * 支持参数: true  false.
 * @return string
 */
function cut_string_using_last($character, $string, $side, $keep_character=true) {
    $offset = ($keep_character ? 1 : 0);
    $whole_length = strlen($string);
    $right_length = (strlen(strrchr($string, $character)) - 1);
    $left_length = ($whole_length - $right_length - 1);
    switch($side) {
        case 'left':
            $piece = substr($string, 0, ($left_length + $offset));
            break;
        case 'right':
            $start = (0 - ($right_length + $offset));
            $piece = substr($string, $start);
            break;
        default:
            $piece = false;
            break;
    }
    return($piece);
}

?>