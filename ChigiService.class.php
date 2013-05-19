<?php

/**
 * 千木服务根类
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class ChigiService {

    /**
     * 执行成功后跳转页面→指向Index模块中的操作
     *
     * @var String
     */
    protected $successRedirect = "";

    /**
     * 执行失败后跳转页面→指向Index模块中的操作
     *
     * @var String
     */
    protected $errorRedirect = "";

    /**
     * 成功跳转的提示信息
     *
     * @var string
     */
    protected $successAlert = "";

    /**
     * 失败跳转的提示信息
     *
     * @var string
     */
    protected $errorAlert = "";

    /**
     * 地址栏传参
     *
     * @var Array
     */
    protected $addrParams = array();

    /**
     * api地址参数，实例化后会直接变成目标对象
     *
     * @var String API地址，示例："Article.Action.ArticleApi"
     */
    public $apiAction = "";

    /**
     * 数据抽象绑定
     *
     * @var array
     */
    protected $__bindings = array();

    public function __construct() {
        $this->request();
        $apiName = cut_string_using_last('.', $this->apiAction, 'right', false);
        $this->apiAction = new $apiName(C('CHIGI_AUTH'));
        isset($_GET['iframe']) ? $this->setDirect($_GET['iframe']) : $this->setDirect();
        if (!CHING::$COOKIE_STATUS)
            $this->addAddrParams("sid", CHING::$CID);
        if (method_exists($this, '_initialize'))
            $this->_initialize();
    }

    public function setDirect($successAdd = null, $errorAdd = null) {
        $this->setSuc($successAdd);
        $this->setErr($errorAdd);
        return $this;
    }

    public function setSuc($addr = null) {
        if ($addr !== null) {
            $this->successRedirect = $addr;
        } elseif (ching("CHIGI_SUCCESSDIRECT") !== null) {
            $this->successRedirect = ching("CHIGI_SUCCESSDIRECT");
            ching("CHIGI_SUCCESSDIRECT", NULL);
        } elseif ($this->successRedirect != "") {
            ;
        } else {
            $this->successRedirect = C("CHIGI_SUCCESSDIRECT");
        }
        return $this;
    }

    public function setErr($addr = null) {
        if ($addr !== null) {
            $this->errorRedirect = $addr;
        } elseif (ching("CHIGI_ERRORDIRECT") !== null) {
            $this->errorRedirect = ching("CHIGI_ERRORDIRECT");
            ching("CHIGI_ERRORDIRECT", NULL);
        } elseif ($this->errorRedirect != "") {
            ;
        } else {
            $this->errorRedirect = C("CHIGI_ERRORDIRECT");
        }
        return $this;
    }

    /**
     * 设置跳转成功提示信息
     *
     * @param string $msg
     */
    protected function setSucAlert($msg = "") {
        $this->successAlert = $msg;
        return $this;
    }

    /**
     * 设置跳转失败提示信息
     *
     * @param string $msg
     */
    protected function setErrAlert($msg = "") {
        $this->errorAlert = $msg;
        return $this;
    }

    public function addAddrParams($key, $value) {
        $this->addrParams[$key] = $value;
        return $this;
    }

    /**
     * 跳转至执行成功页面
     */
    public function successDirectHeader($alertMsg = "") {
        if (!empty($alertMsg)) {
            $alert = new ChigiAlert($alertMsg, 'alert-success');
            $alert->alert();
        } elseif (!empty($this->successAlert)) {
            $alert = new ChigiAlert($this->successAlert, 'alert-success');
            $alert->alert();
        }
        redirectHeader($this->successRedirect, $this->addrParams);
    }

    /**
     * 跳转至执行失败页面
     */
    public function errorDirectHeader($alertMsg = "") {
        if (!empty($alertMsg)) {
            $alert = new ChigiAlert($alertMsg, 'alert-error');
            $alert->alert();
        } elseif (!empty($this->errorAlert)) {
            $alert = new ChigiAlert($this->errorAlert, 'alert-error');
            $alert->alert();
        }
        redirectHeader($this->errorRedirect, $this->addrParams);
    }

    /**
     * 环境保障操作【链写】
     *
     * 使用示例：
     * $service->under('Login')->setDirect('Login/index')->pushAlert("对不起，请先登录")->check();
     *
     * @param string $method
     * @return \underCheck
     */
    public function under($method) {
        $method = 'under' . $method;
        $result = $this->$method();
        $underObj = new underCheck($result);
        return $underObj;
    }

    /**
     * Alert推送操作【支持链写】
     *
     * @param string $message
     * @param string $option
     * @return \ChigiService
     */
    public function pushAlert($message = "", $option = "alert-error") {
        if (empty($message)) {
            return $this;
        }
        $serviceAlert = service("Alert");
        $serviceAlert->pushSet($message, $option);
        return $this;
    }

    /**
     * API请求
     *
     * @param array|string|int $data
     * @param string $method
     * @return \ChigiReturn
     */
    public function request($data = array(), $method = '') {
        static $api = null;
        if (!empty($method)) {
            $toSend = array(
                'data' => $data,
                'user_agent' => array(
                    'ip' => getClientIp(),
                    'bot' => CHING::$BOT,
                    '__' => $_SERVER['HTTP_USER_AGENT']
                ),
                'bindings' => $this->__bindings
            );
            $response = $api->response($toSend, $method);
            $this->__bindings = $response['bindings'];
            $result = new ChigiReturn($response['data']);
            return $result;
        } elseif (is_null($api)) {
            //初始化API
            import($this->apiAction);
            $apiName = cut_string_using_last('.', $this->apiAction, 'right', false);
            $api = new $apiName(C('CHIGI_AUTH'));
        } else {
            throw_exception(get_class($this) . "API不正确，请检查地址");
        }
    }

    /**
     * 原型key-value数据绑定
     *
     * @return mixed 上次的目标key值
     */
    protected function bind() {
        $argNum = func_num_args();
        $arg = func_get_args();
        switch ($argNum) {
            case 1:
                return isset($this->__bindings[$arg[0]]) ? $this->__bindings[$arg[0]] : null;
                break;
            case 2:
                $temp = isset($this->__bindings[$arg[0]]) ? $this->__bindings[$arg[0]] : null;
                $this->__bindings[$arg[0]] = $arg[1];
                return $temp;
                break;
            default:
                return;
                break;
        }
    }

}

/**
 * 环境保障链式操作类
 *
 */
class underCheck {

    /**
     * 当前环境是否达标，true为达标；false为不达标，将进行跳转。
     *
     * @var boolean
     */
    private $under_status = false;
    private $addr = '';
    private $params = array();
    private $alert = null;

    /**
     * 构造传入返回标准结果数组
     *
     * @param array $result
     */
    public function __construct($result) {
        if (isset($_GET['iframe'])) {
            $this->addAddrParams('iframe', $_GET['iframe']);
        } else {
            $this->addAddrParams('iframe', (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        }
        if (is_int($result)) {
            $result == 1 ? $this->under_status = true : $this->under_status = false;
        } elseif (is_bool($result)) {
            $this->under_status = $result;
        } elseif (is_array($result)) {
            getNumHundreds($result['status']) == 2 ? $this->under_status = true : $this->under_status = false;
            if (getNumTens($result['status']) == 2) {
                $this->pushAlert($result['data']);
            }
        } else {
            throw_exception("underCheck传参错误");
        }
    }

    /**
     * 设置目标跳转地址
     *
     * @param string $addr
     * @return \underCheck
     */
    public function setDirect($addr) {
        $this->addr = $addr;
        return $this;
    }

    /**
     * 移除指定URL参数
     *
     * @param string $name
     * @return \underCheck
     */
    public function rmAddrParam($name) {
        $this->params[$name] = null;
        return $this;
    }

    /**
     * 添加地址栏参数
     *
     * @param type $key
     * @param type $value
     * @return \underCheck
     */
    public function addAddrParams($key, $value) {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * Alert推送操作【支持链写】
     *
     * @param string $message
     * @param string $option
     * @return \underCheck
     */
    public function pushAlert($message = "") {
        if (empty($message) || $this->under_status == true) {
            return $this;
        }
        $this->alert = $message;
        return $this;
    }

    /**
     * 手动检测环境，若不达标则直接跳转
     */
    public function check() {
        if ($this->under_status == false) {
            if (!empty($this->alert) && !$this->under_status == true) {
                $alert = new ChigiAlert($this->alert, 'alert-error');
                $alert->alert();
            }
            redirectHeader($this->addr, $this->params);
        }
    }

}

?>
