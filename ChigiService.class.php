<?php

/**
 * 千木服务根类
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class ChigiService {

    /**
     * 指定对应Api地址，方法中调用时则为该模块对象实例
     *
     * @var Object | String
     */
    protected $apiAction = 'Sugar.Action.ApiAction';

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
    public function __construct() {
        import($this->apiAction);
        $this->apiAction = new ApiAction(C('CHIGI_AUTH'));
        $this->setDirect();//初始化默认跳转地址
        if (method_exists($this, '_initialize'))
            $this->_initialize();
    }

    public function setDirect($successAdd = null, $errorAdd = null) {
        if ($successAdd !== null) {
            $this->successRedirect = $successAdd;
        } elseif (session("CHIGI_SUCCESSDIRECT") !== null) {
            $this->successRedirect = session("CHIGI_SUCCESSDIRECT");
            session("CHIGI_SUCCESSDIRECT", NULL);
        } else {
            $this->successRedirect = C("CHIGI_SUCCESSDIRECT");
        }
        if ($errorAdd !== null) {
            $this->errorRedirect = $errorAdd;
        } elseif (session("CHIGI_ERRORDIRECT") !== null) {
            $this->errorRedirect = session("CHIGI_ERRORDIRECT");
            session("CHIGI_ERRORDIRECT", NULL);
        } else {
            $this->errorRedirect = C("CHIGI_ERRORDIRECT");
        }
    }

    public function addAddrParams($key,$value) {
        $this->addrParams[$key] = $value;
    }

    /**
     * 跳转至执行成功页面
     */
    public function successDirectHeader() {
        header('location:' . U($this->successRedirect) . (($this->addrParams == array())? '' : '?' . arrayImplode('=', '&', $this->addrParams)));
    }
    /**
     * 跳转至执行失败页面
     */
    public function errorDirectHeader() {
        header('location:' . U($this->errorRedirect) . (($this->addrParams == array())? '' : '?' . arrayImplode('=', '&', $this->addrParams)));
    }
}

?>
