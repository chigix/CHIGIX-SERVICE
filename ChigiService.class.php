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
     * api地址参数，实例化后会直接变成目标对象
     *
     * @var String
     */
    public $apiAction = "";

    public function __construct() {
        import($this->apiAction);
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
    }

    public function addAddrParams($key, $value) {
        $this->addrParams[$key] = $value;
    }

    /**
     * 跳转至执行成功页面
     */
    public function successDirectHeader() {
        redirectHeader($this->successRedirect, $this->addrParams);
    }

    /**
     * 跳转至执行失败页面
     */
    public function errorDirectHeader() {
        redirectHeader($this->errorRedirect, $this->addrParams);
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
            $this->addAddrParams('iframe', $_GET['iframe']);
        } else {
            $this->addAddrParams('iframe', (is_ssl()?'https://':'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
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
