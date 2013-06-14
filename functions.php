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
 * 支持前端的 chigiThis 函数编译
 *
 * @param string $path 目标模块路径：Test/LeftMenu → TestView_LeftMenu
 * @param string $follow 尾随字符串参数
 * @return string 产生统一模块名字符串
 */
function chigiThis() {
    //例：Todo:index
    static $currentThis = "";
    //例：Todo
    $package_name = cut_string_using_first(':', $currentThis, 'left', false);
    $arg_num = func_num_args();
    $args = func_get_args();
    switch ($arg_num) {
        case 0:
            return str_replace(':', '_', $currentThis);
            break;
        case 1:
            if (empty($currentThis)) {
                //当前第一次运行，初始化整个chigiThis 指针
                $currentThis = $args[0];
            } elseif ($args[0] == null) {
                //清空当前静态变量
                $currentThis = "";
            } else {
                if (!strpos($args[0], '/')) {
                    //当前 View 下的模块
                    $package_name = cut_string_using_first(':', $currentThis, 'left', false);
                    return $package_name . 'View_' . $args[0];
                } else {
                    //传入$args[0]→ 【TodoView/TestApp】→编译成：【TodoView_TestApp】
                    $package_name = cut_string_using_first('/', $args[0], 'left', false);
                    $page_name = cut_string_using_first('/', $args[0], 'right', false);
                    return $package_name . '_' . $page_name;
                }
            }
            break;
        case 2:
            //尾随参数
            return chigiThis($args[0]) . ucfirst($args[1]);
            break;
        default:
            break;
    }
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
    static $services = array(); //静态，模拟单例模式
    if (isset($services[$serviceName])) {
        return $services[$serviceName];
    } elseif (import('@.Service.' . $service)) {
        $services[$serviceName] = new $service();
        return $services[$serviceName];
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
    /* @var $chingObj CHING */
    static $chingObj = NULL;
    $chingObj = CHING::getInstance();
    if (isset(CHING::$CID)) {
        $args = func_get_args();
        $argNum = count($args);
        switch ($argNum) {
            case 0:
                //返回当前所有ching会话
                return $chingObj->getAll();
                break;
            case 1:
                if (is_null($args[0])) {  // 清空ching
                    $chingObj->delete();
                    return null;
                }
                //获取目标ching会话内容，支持数组元素筛选
                return $chingObj->get($args[0]);
                break;
            case 2:
                //设置ching会话值，支持数组筛选
                return $chingObj->set($args[0], $args[1]);
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
 * @param string $domain 指定域名，若为空则默认使用当前域名，所传入域名须包含完整的协议，且结尾没有斜杠
 * @param boolean $sidShow 是否显示SID
 */
function redirectHeader($addr, $params = array(), $domain = null, $sidShow = true) {
    redirect(redirect_link($addr, $params, $domain), 0); //redirect函数中已封装了exit
}

/**
 * 生成带参复杂地址链接
 *
 * @param string $addr 支持HTTP地址或U生成地址
 * @param string $params 地址参数，会自动根据当前COOKIE状态添加SID的显式传递
 * 地址参数写法："key"=>"value"  →  ?key=value
 * @param string $domain 指定域名，若为空则默认使用当前域名，所传入域名须包含完整的协议，且结尾没有斜杠
 * @param boolean $sidShow 是否显示SID
 */
function redirect_link($addr, $params = array(), $domain = null, $sidShow = true) {
    if (strpos($addr, '://') > 0) {
        //若传入参数为完整的URL地址
        return $addr;
    }
    if ((!CHING::$COOKIE_STATUS) && $sidShow) {
        //当前未检测到客户端中COOKIE支持，且允许显示SID
        $params['sid'] = CHING::$CID;
    }
    if (isset($params[C('VAR_URL_PARAMS')])) {
        //滤去可能来自GET中的 _URL_ 项
        unset($params[C('VAR_URL_PARAMS')]);
    }
    $paramString = http_build_query($params);
    if (substr($addr, -5) == 'index') {
        if (substr($addr, 0, 5) == 'Index') {
            //定位为Index/index，即总域名（注：千木架构规范，Index控制器下仅能存在一个操作）
            $addr = is_null($domain) ? ((is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . __APP__) : ($domain . __APP__);
            $addr .= '/';
        } else {
            //定位地址中有可省略的index，故结尾不留.html
            $addr = is_null($domain) ? substr(U($addr, '', false, false, true), 0, -5) : $domain . substr(U($addr, '', false, false, false), 0, -5);
        }
    } else {
        //定位地址无任何可省略成分，最终需生成完整的.html格式URL
        $addr = is_null($domain) ? U($addr, '', true, false, true) : $domain . U($addr, '', true, false, false);
    }
    return($addr . (empty($paramString) ? '' : '?') . $paramString);
}

/**
 * 生成带参复杂地址链接REST接口地址
 *
 * @param string $addr 支持HTTP地址或U生成地址
 * @param string $params 地址参数，会自动根据当前COOKIE状态添加SID的显式传递
 * 地址参数写法："key"=>"value"  →  ?key=value
 * @param string $domain 指定域名，若为空则默认使用当前域名，所传入域名须包含完整的协议，且结尾没有斜杠
 * @param boolean $sidShow 是否显示SID
 */
function rest_link($addr, $params = array(), $domain = null, $sidShow = true) {
    if (strpos($addr, '://') > 0) {
        //若传入参数为完整的URL地址
        return $addr;
    }
    if ((!CHING::$COOKIE_STATUS) && $sidShow) {
        //当前未检测到客户端中COOKIE支持，且允许显示SID
        $params['sid'] = CHING::$CID;
    }
    if (isset($params[C('VAR_URL_PARAMS')])) {
        //滤去可能来自GET中的 _URL_ 项
        unset($params[C('VAR_URL_PARAMS')]);
    }
    $paramString = http_build_query($params);
    //定位地址无任何可省略成分，最终需生成无后缀URL
    $addr = is_null($domain) ? U($addr . '_chigix_restful', '', FALSE, false, true) : $domain . U($addr . '_chigix_restful', '', FALSE, false, false);
    return($addr . (empty($paramString) ? '' : '?') . $paramString);
}

/**
 * RETA数组包装类
 *
 * @param int $code
 * @param string $info
 * @param mixed $data
 * @return RETA
 */
function chigi_reta($code, $info, $data = null) {
    return array(
        "status" => $code,
        "info" => $info,
        "data" => $data
    );
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

?>