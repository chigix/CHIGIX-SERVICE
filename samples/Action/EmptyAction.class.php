<?php
/**
 * 模块跳转控制器
 * @version 1.2.0
 * @author Richard Lea <chigix@zoho.com>
 */
class EmptyAction extends ChigiAction {

    public $actionName; //模块名
    public $methodName; //操作名

    public function _initialize() {
        $this->actionName = $_GET['_URL_'][0];
        $this->methodName = $_GET['_URL_'][1];
    }

    public function index() {
        if (endsWith($this->actionName, 'Service')) {
            _404();
        } else {
            _404();
        }
    }

    public function _empty($name) {
        //服务类库调用
        if (endsWith($this->actionName, 'Service')) {
            //服务类调用安全令牌检测：
            if (!M()->autoCheckToken($_POST)) {
                _404();
            }
            unset($_POST[C("TOKEN_NAME")]);
            if ($_SESSION['verify'] !== null) {
                if ($_SESSION['verify'] != md5($_POST['verify'])) {
                    $this->error("验证码错误");
                }
                unset($_POST['verify']);
            }
            $serviceName = $this->actionName;
            $methodName = $this->methodName;
            import('@.Service.' . $serviceName);
            $service = new $serviceName();
            $service->$methodName();
        } else {
            _404();
        }
    }

}

?>