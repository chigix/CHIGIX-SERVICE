<?php

/**
 * 千木架构Api类定义抽象
 *
 * ★所有类不得自定义构造方法，但必须全部有定义的_ChigiApiInit()方法，否则报错。
 */
abstract class ChigiApiAction extends Action {

    static public $appHost;
    public $appHostIp; //连接本API的应用所在服务器IP

    /**
     * 千木架构Api类定义初始化
     *
     * 负责安全访问，避免异步HTTP访问与攻击
     * 无正确POST令牌信息均返回404错误。
     */
    public function _initialize() {
        if (self::$appHost === null) {
            _404();
        }
        if (is_array(self::$appHost)) {
            //连接SugarService转换成APPHOST
        }
        $this->appHostIp = getClientIp();
        $this->_ChigiApiInit();
    }

}

?>