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
     * 地址栏传参
     *
     * @var Array
     */
    protected $addrParams = array();

    /**
     * 当前客户端中是否有cookie("sid")
     *
     * @var type
     */
    public $cookie_status = 1;

    /**
     * api地址参数，实例化后会直接变成目标对象
     *
     * @var String
     */
    public $apiAction = "";

    public function __construct() {
        $this->cookie_status = isset($_COOKIE['sid']) ? 1 : 0;
        import($this->apiAction);
        $apiName = cut_string_using_last('.', $this->apiAction, 'right', false);
        $this->apiAction = new $apiName(C('CHIGI_AUTH'));
        isset($_GET['iframe']) ? $this->setDirect($_GET['iframe']) : $this->setDirect();
        if (method_exists($this, '_initialize'))
            $this->_initialize();
    }

    public function setDirect($successAdd = null, $errorAdd = null) {
        if ($successAdd !== null) {
            $this->successRedirect = $successAdd;
        } elseif (ching("CHIGI_SUCCESSDIRECT") !== null) {
            $this->successRedirect = ching("CHIGI_SUCCESSDIRECT");
            ching("CHIGI_SUCCESSDIRECT", NULL);
        } elseif ($this->successRedirect != "") {
            ;
        } else {
            $this->successRedirect = C("CHIGI_SUCCESSDIRECT");
        }
        if ($errorAdd !== null) {
            $this->errorRedirect = $errorAdd;
        } elseif (ching("CHIGI_ERRORDIRECT") !== null) {
            $this->errorRedirect = ching("CHIGI_ERRORDIRECT");
            ching("CHIGI_ERRORDIRECT", NULL);
        } elseif ($this->errorRedirect != "") {
            ;
        } else {
            $this->errorRedirect = C("CHIGI_ERRORDIRECT");
        }
    }

    public function addAddrParams($key, $value) {
        $this->addrParams[$key] = base64_encode($value);
    }

    /**
     * 跳转至执行成功页面
     */
    public function successDirectHeader() {
        if ($this->cookie_status == 0) {
            $this->addAddrParams("sid", CHING);
        }
        if (startsWith($this->successRedirect, 'http://')) {
            if (endsWith($this->successRedirect, '/') === false) {
                exit(header('location:' . $this->successRedirect . (($this->addrParams == array()) ? '' : '/' . arrayImplode('/', '/', $this->addrParams))));
            } else {
                exit(header('location:' . $this->successRedirect . (($this->addrParams == array()) ? '' : arrayImplode('/', '/', $this->addrParams))));
            }
        } elseif (startsWith($this->successRedirect, '/index.php/')) {
            if (endsWith($this->successRedirect, '/') === false) {
                exit(header('location:' . $this->successRedirect . (($this->addrParams == array()) ? '' : '/' . arrayImplode('/', '/', $this->addrParams))));
            } else {
                exit(header('location:' . $this->successRedirect . (($this->addrParams == array()) ? '' : arrayImplode('/', '/', $this->addrParams))));
            }
        } else {
            exit(header('location:' . U($this->successRedirect) . (($this->addrParams == array()) ? '' : arrayImplode('/', '/', $this->addrParams))));
        }
    }

    /**
     * 跳转至执行失败页面
     */
    public function errorDirectHeader() {
        if ($this->cookie_status == 0) {
            $this->addAddrParams('sid', CHING);
        }
        if (startsWith($this->errorRedirect, 'http://')) {
            if (endsWith($this->errorRedirect, '?') === false) {
                exit(header('location:' . $this->errorRedirect . (($this->addrParams == array()) ? '' : '/' . arrayImplode('/', '/', $this->addrParams))));
            } else {
                exit(header('location:' . $this->errorRedirect . (($this->addrParams == array()) ? '' : arrayImplode('/', '/', $this->addrParams))));
            }
        } elseif (startsWith($this->errorRedirect, '/index.php/')) {
            if (endsWith($this->errorRedirect, '?') === false) {
                exit(header('location:' . $this->errorRedirect . (($this->addrParams == array()) ? '' : '/' . arrayImplode('/', '/', $this->addrParams))));
            } else {
                header('location:' . $this->errorRedirect . (($this->addrParams == array()) ? '' : arrayImplode('/', '/', $this->addrParams)));
                exit;
            }
        } else {
            exit(header('location:' . U($this->errorRedirect) . (($this->addrParams == array()) ? '' : arrayImplode('/', '/', $this->addrParams))));
        }
    }

    /**
     * 环境保障操作【链写】
     *
     * 使用示例：
     * $service->under('Login')->setDirect('/login/')->pushAlert("对不起，请先登录")->check();
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

    /**
     * 构造传入返回标准结果数组
     *
     * @param array $result
     */
    public function __construct($result) {
        if (isset($_GET['iframe'])) {
            $this->addAddrParams('iframe' , $_GET['iframe']);
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
        }  else {
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
        $option = "alert-error";
        $serviceAlert = service("Alert");
        $serviceAlert->pushSet($message, $option)->alert();
        return $this;
    }

    /**
     * 手动检测环境，若不达标则直接跳转
     */
    public function check() {
        if ($this->under_status == false) {
            redirectHeader($this->addr, $this->params);
        }
    }

}

?>
