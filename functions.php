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
        if (($param->getCode() >= 500) && ($param->getCode() < 600)) {
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
        if (($param->getCode() >= 200) && ($param->getCode() < 300)) {
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
 * @return \ChigiService
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
 * 支持点号来表示要获取的目标数组元素，形如ching("Sugar.start.ele1")
 * ching("Sugar.start.ele1" , 1234); //给元素$_CHING["Sugar"]["start"]["ele1"]设置为1234，同时返回设置前的那次的值。
 * @param mixed $value
 * @return mixed
 */
function ching() {
    if (defined("CHING")) {
        $data = C("CHING");
        $args = func_get_args();
        $argNum = count($args);
        switch ($argNum) {
            case 0:
                //返回当前所有ching会话
                return $data;
                break;
            case 1:
                if (is_null($args[0])) {  // 清空ching
                    return C("CHING", array());
                }
                //获取目标ching会话内容，支持数组元素筛选
                return getNestedVar($data, $args[0]);
                break;
            case 2:
                //设置ching会话值，支持数组筛选
                $temp = getNestedVar($data, $args[0]);
                set_value($data, $args[0], $args[1]);
                // <editor-fold defaultstate="collapsed" desc="过滤当前数据索引路径上的null数组">
                if ($args[1] === null) {
                    $pathString = $args[0];
                    $lastKey = "";
                    $valTmp = getNestedVar($data, $pathString);
                    while ($pathString != "") {
                        if (empty($valTmp)) {
                            $lastKey = cut_string_using_last('.', $pathString, 'right', false);
                            $pathString = cut_string_using_last('.', $pathString, 'left', false);
                            if ($lastKey == $pathString) {
                                unset($data[$lastKey]);
                                break;
                            } else {
                                $valTmp = getNestedVar($data, $pathString);
                                if ($lastKey != "") {
                                    unset($valTmp[$lastKey]);
                                    set_value($data, $pathString, $valTmp);
                                }
                            }
                        } else {
                            break;
                        }
                    }
                }
                // </editor-fold>
                C("CHING", $data); //缓存仅存在15分钟
                return $temp;
                break;
            default:
                break;
        }
    } else {
        return null;
    }
}

/**
 * 通过点号设置目标数组
 *
 * 使用：
 * $foo = array();
 * set_value($foo, 'bar.color', 'black');
 * print_r($foo);
 * 输出：
 * array(
 *     'bar' => array(
 *         'clock' => 'black'
 *     )
 * )
 *
 * @param array $root 目标数组变量
 * @param string $compositeKey 点号索引路径
 * @param mixed $value 目标要赋入的值
 * @return type
 */
function set_value(&$root, $compositeKey, $value) {
    $keys = explode('.', $compositeKey);
    while (count($keys) > 1) {
        $key = array_shift($keys);
        if (!isset($root[$key])) {
            $root[$key] = array();
        }
        $root = &$root[$key];
    }
    $key = reset($keys);
    $root[$key] = $value;
}

/**
 * 通过点号字符串获取目标数组元素
 * 使用：getNestedVar($arr, 'AA.BB.CC');  //返回$arr['AA']['BB']['CC'];
 *
 * @param array $context 目标数组
 * @param string $name 点号索引字符串
 * @return mixed 若不存在，则默认返回null
 */
function getNestedVar(&$context, $name) {
    $pieces = explode('.', $name);
    foreach ($pieces as $piece) {
        if (!is_array($context) || !array_key_exists($piece, $context)) {
            // error occurred
            return null;
        }
        $context = &$context[$piece];
    }
    return $context;
}

/**
 * 进行地址跳转
 *
 * @param string $addr 支持HTTP地址或U生成地址
 * @param string $params 地址参数，会自动根据当前COOKIE状态添加SID的显式传递
 * 地址参数写法："key"=>"value"  →  ?key=value
 */
function redirectHeader($addr, $params = array()) {
    exit(header("location:" . redirect_link($addr, $params)));
}

/**
 * 生成带参复杂地址链接
 *
 * @param string $addr 支持HTTP地址或U生成地址
 * @param string $params 地址参数，会自动根据当前COOKIE状态添加SID的显式传递
 * 地址参数写法："key"=>"value"  →  ?key=value
 */
function redirect_link($addr, $params = array()) {
    if (!isset($_COOKIE['sid'])) {
        $params['sid'] = CHING;
    }
    $paramString = "";
    if ($params != array()) {
        foreach ($params as $key => $val) {
            if ($val === NULL) {
                continue;
            }
            if (is_array($val))
                $val = implode(',', $val);
            $val = base64_encode($val);
            $paramString .= '/' . $key . '/' . $val;
        }
    }
    $paramString = cut_string_using_first('/', $paramString, 'right', false);
    if (startsWith($addr, 'http%3A%2F%2F')) {
        $addr = rawurldecode($addr);
    } elseif (!startsWith($addr, 'http://')) {
        $addr = U($addr);
    }
    if (endsWith($addr, '/') === false) {
        //斜杠不存在
        return($addr . '/' . $paramString);
    } else {
        return($addr . $paramString);
    }
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
 * 若字符串中无对应的切割字符标记，则返回原字符串
 *
 * @param string $character 目标要搜索的标记字符
 * @param string $string 要进行切割的母字符串
 * @param string $side 要保留并返回切割字符左边的内容还是右边的内容
 * 支持参数: left  right.
 * @param bool $keep_character 返回字符串中是否保留标记字符
 * 支持参数: true  false.
 * @return string
 */
function cut_string_using_last($character, $string, $side, $keep_character = true) {
    $offset = ($keep_character ? 1 : 0);
    $whole_length = strlen($string);
    $right_length = (strlen(strrchr($string, $character)) - 1);
    if ($right_length == -1) {
        return $string;
    }
    $left_length = ($whole_length - $right_length - 1);
    switch ($side) {
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

/**
 * 查找字符在指定字符串中从前面开始的第一次出现的位置，并进行自定义切割字符串
 *
 * @param string $character 目标要搜索的标记字符
 * @param string $string 要进行切割的母字符串
 * @param string $side 要切割出标记字符左边的内容还是右边的内容
 * 支持参数: left  right.
 * @param bool $keep_character 返回字符串中是否保留标记字符
 * 支持参数: true  false.
 * @return string
 */
function cut_string_using_first($character, $string, $side, $keep_character = true) {
    $offset = ($keep_character ? 1 : 0);
    $whole_length = strlen($string);
    $right_length = (strlen(strstr($string, $character)) - 1);
    $left_length = ($whole_length - $right_length - 1);
    switch ($side) {
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

/**
 * ching会话缓存初始化
 *
 * @return object
 */
function cache_ching() {
    $type = C('CHINGSET.TYPE');
    $expire = C('CHINGSET.EXPIRE');
    if ($expire === null) {
        $expire = 900; //设定默认超时时间
    }
    switch ($type) {
        case 'Apc':
            //采用Apc缓存机制存储
            return(Cache::getInstance('Apc', array("expire" => $expire)));
            break;
        case 'Xcache':
            //采用Xcache缓存机制存储
            return(Cache::getInstance('Xcache', array("expire" => $expire)));
            break;
        default:
            //默认采用文件存储
            $dir = C('CHINGSET.DIR');
            if ($dir === null) {
                $dir = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . THINK_PATH . '../Ching/';
            }
            return(Cache::getInstance('File', array("temp" => $dir, "expire" => $expire)));
            break;
    }
}

?>