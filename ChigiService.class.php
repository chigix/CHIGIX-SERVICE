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

    public function __construct() {
        import($this->apiAction);
        ApiAction::$appHost = C('CHIGI_AUTH');
        $this->apiAction = new ApiAction();
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
            $this->successRedirect = session("CHIGI_ERRORDIRECT");
            session("CHIGI_ERRORDIRECT", NULL);
        } else {
            $this->successRedirect = C("CHIGI_ERRORDIRECT");
        }
    }

}

?>
