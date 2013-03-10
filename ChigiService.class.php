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
        isset($_GET['iframe']) ? $this->setDirect(rawurldecode($_GET['iframe'])) : $this->setDirect();
        if (method_exists($this, '_initialize'))
            $this->_initialize();
    }

    public function setDirect($successAdd = null, $errorAdd = null) {
        if ($successAdd !== null) {
            $this->successRedirect = $successAdd;
        } elseif (ching("CHIGI_SUCCESSDIRECT") !== null) {
            $this->successRedirect = ching("CHIGI_SUCCESSDIRECT");
            ching("CHIGI_SUCCESSDIRECT", NULL);
        } else {
            $this->successRedirect = C("CHIGI_SUCCESSDIRECT");
        }
        if ($errorAdd !== null) {
            $this->errorRedirect = $errorAdd;
        } elseif (ching("CHIGI_ERRORDIRECT") !== null) {
            $this->errorRedirect = ching("CHIGI_ERRORDIRECT");
            ching("CHIGI_ERRORDIRECT", NULL);
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
                return (header('location:' . $this->successRedirect . (($this->addrParams == array()) ? '' : '/' . arrayImplode('/', '/', $this->addrParams))));
            } else {
                return (header('location:' . $this->successRedirect . (($this->addrParams == array()) ? '' : arrayImplode('/', '/', $this->addrParams))));
            }
        } elseif (startsWith($this->successRedirect, '/index.php/')) {
            if (endsWith($this->successRedirect, '/') === false) {
                return (header('location:' . $this->successRedirect . (($this->addrParams == array()) ? '' : '/' . arrayImplode('/', '/', $this->addrParams))));
            } else {
                return (header('location:' . $this->successRedirect . (($this->addrParams == array()) ? '' : arrayImplode('/', '/', $this->addrParams))));
            }
        } else {
            return (header('location:' . U($this->successRedirect) . (($this->addrParams == array()) ? '' : arrayImplode('/', '/', $this->addrParams))));
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
                return (header('location:' . $this->errorRedirect . (($this->addrParams == array()) ? '' : '/' . arrayImplode('/', '/', $this->addrParams))));
            } else {
                return (header('location:' . $this->errorRedirect . (($this->addrParams == array()) ? '' : arrayImplode('/', '/', $this->addrParams))));
            }
        } elseif (startsWith($this->errorRedirect, '/index.php/')) {
            if (endsWith($this->errorRedirect, '?') === false) {
                return (header('location:' . $this->errorRedirect . (($this->addrParams == array()) ? '' : '/' . arrayImplode('/', '/', $this->addrParams))));
            } else {
                return (header('location:' . $this->errorRedirect . (($this->addrParams == array()) ? '' : arrayImplode('/', '/', $this->addrParams))));
            }
        } else {
            return (header('location:' . U($this->errorRedirect) . (($this->addrParams == array()) ? '' : arrayImplode('/', '/', $this->addrParams))));
        }
    }

}

?>
