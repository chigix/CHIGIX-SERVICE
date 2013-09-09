<?php

/**
 * 千木表单对象
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class ChigiForm {

    public $action;
    public $__chigiDataRecorder = array();
    //使用时：$__chigiServiceBelongs->$__chigiDataSourceName; 即可获取数据源属性
    /**
     * 当前表单对象数据源对应所在服务
     * @var ChigiService
     */
    public $__chigiServiceBelongs;

    /**
     * 当前表单对象所指向的目标数据源名称
     * @var string
     */
    public $__chigiDataSourceName = "";

    /**
     * 千木表单对象构造器
     * @param string $data_source 数据源表达式，例："Deploy:onSubmit"
     * @param string $dest_addr 目标地址，例："Index/onxxx"
     */
    public function __construct($data_source, $dest_addr) {
        /* @var $data_source_split array array('Service','Method') */
        $data_source_split = explode(':', $data_source);
        $service = service($data_source_split[0]);
        if (APP_DEBUG) {
            if (2 !== count($data_source_split)) {
                throw_exception("数据源声明格式错误，详见下面的【错误位置】");
            } else {
                if (!method_exists($service, $data_source_split[1])) {
                    throw_exception("数据源对应方法不存在：$data_source_split[0]::$data_source_split[1]()");
                }
                if (!property_exists($service, $data_source_split[1])) {
                    throw_exception("数据源在目标服务中不存在：$data_source_split[0]::$$data_source_split[1]");
                }
            }
        }
        // 将数据源入口信息填充入表单对象
        $this->__chigiServiceBelongs = $service;
        $this->__chigiDataSourceName = $data_source_split[1];
    }

    public function __get($name) {
        switch ($name) {
            case 'recorder':
                $str = "";
                foreach ($this->__chigiDataRecorder as $key => $value) {
                    $str .= "<input type=\"hidden\" name=\"$key\" value=\"$value\"";
                }
                return $str;
                break;
            default:
                break;
        }
    }

    /**
     * 设置目标 action 地址，
     * 并根据所给出的$addr是否为on开头，自动进行公共接口的ching会话部署
     * @param string $addr
     * @return ChigiForm
     */
    public function setAction($addr) {
        $addr_split = explode('/', $addr);
        if (2 === count($addr_split) && 'on' === substr($addr_split[1], 0, 2)) {
            // 目标控制器中确有on开头的on操作包装，则启用自定义on接口
            $this->action = redirect_link($addr);
        } else {
            // 将启用on公共自动接口，该地址将作为目标成功地址
            ching("CHIGI_SUCCESSDIRECT", $addr); //设置成功跳转地址
            ching("CHIGI_ERRORDIRECT", $_SERVER['HTTP_REFERER']); //设置失败跳转地址
            ching("CHIGI_TAG", array(//设置表单"CHIGI_TAG"目标数组
                "SERVICE" => substr(get_class($this->__chigiServiceBelongs), 0, -7),
                "METHOD" => $this->__chigiDataSourceName
            ));
            $this->action = redirect_link('/on/');
        }
        return $this;
    }

}

?>
