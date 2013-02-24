<?php

class EmptyAction extends ChigiAction {

    public $actionName; //模块名
    public $methodName; //操作名

    public function _initialize() {
        $this->actionName = $_GET['_URL_'][0];
        $this->methodName = $_GET['_URL_'][1];
    }

    public function index() {
        if ($this->endsWith($this->actionName, 'Service')) {
            _404();
        }  else {
            _404();
        }
    }

    public function _empty($name) {
        //服务类库调用
        if ($this->endsWith($this->actionName, 'Service')) {
            //服务类调用安全检测：
            if (!M()->autoCheckToken($_POST)) {
                _404();
            }
            unset($_POST[C(TOKEN_NAME)]);
            $serviceName = $this->actionName;
            $methodName = $this->methodName;
            import('@.Service.' . $serviceName);
            $service = new $serviceName();
            $service->$methodName();
        } else {
            _404();
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
    public function endsWith($haystack, $needle, $case = false) {
        if ($case) {
            return (strcmp(substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0);
        }
        return (strcasecmp(substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0);
    }

}

?>