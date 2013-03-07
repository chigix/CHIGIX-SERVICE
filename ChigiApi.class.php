<?php

/**
 * 千木架构Api类定义抽象
 *
 */
abstract class ChigiApi extends Action {

    protected $appHost;
    public $appHostIp; //连接本API的应用所在服务器IP
    protected $time;

    public function __construct($appHost = null) {
        if ($appHost === null) {
            _404();
        }
        if (is_array($this->appHost)) {
            //连接SugarService转换成APPHOST
        }
        $this->appHostIp = getClientIp();
        $this->appHost = $appHost;
        $this->time = time();
        parent::__construct();
    }

}

?>