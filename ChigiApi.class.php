<?php

/**
 * 千木架构Api类定义抽象
 *
 */
abstract class ChigiApi extends Action {

    protected $appHost;
    public $appHostIp; //连接本API的应用所在服务器IP
    protected $time;
    protected $user_agent = array(); //客户端信息
    protected $__bindings = array(); //数据抽象绑定

    /**
     * 原型key-value数据绑定
     *
     * @return mixed
     */

    protected function bind() {
        $argNum = func_num_args();
        $arg = func_get_args();
        switch ($argNum) {
            case 1:
                return isset($this->__bindings[$arg[0]]) ? $this->__bindings[$arg[0]] : null;
                break;
            case 2:
                $temp = isset($this->__bindings[$arg[0]]) ? $this->__bindings[$arg[0]] : null;
                $this->__bindings[$arg[0]] = $arg[1];
                return $temp;
                break;
            default:
                return;
                break;
        }
    }

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

    /**
     * 响应来自Service的request，并返回请求结果数据
     *
     * @param array $data
     * @param string $method
     * @return array 返回数组信息，包含 data数组 和 bindings同步信息
     */
    public function response($data, $method) {
        $method = 'request' . ucfirst($method);
        if (!method_exists($this, $method)) {
            $trace = debug_backtrace();
            throw_exception(get_class($this) . '中方法' . $method . '不存在，请检查→_→' . $trace[1]['file'] . ':' . $trace[1]['line']);
        }
        $this->user_agent = $data['user_agent'];
        if (method_exists($this, '_initResponse'))
            $this->_initResponse();
        $result = array();
        $result['data'] = $this->$method($data['data']);//请求所返回的真正可操作数据
        $result['bindings'] = $this->__bindings;//请求函数会自动将绑定数据进行更新，Service中无需手动操作
        return $result;
    }

}

?>