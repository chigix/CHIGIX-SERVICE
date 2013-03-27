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

    /**
     * 数据模型获取包装函数（避免无用SQL查询）
     *
     * @param string $model
     * @return /Model
     */
    public function dm($model) {
        $property = "dm" . $model;
        if (!property_exists($this, $property)) {
            //属性不存在
            throw_exception($model . "模型未在当前类中定义");
        }
        if (is_string($this->$property)) {
            //当前是第一次调用，需要先初始化数据模型
            $this->$property = D($this->$property);
        }
        return $this->$property;
    }
}

?>