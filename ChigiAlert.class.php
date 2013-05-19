<?php

/**
 * 千木 Alert 工具类
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class ChigiAlert {

    public $messageSuccess = null;
    public $messageError = null;
    public $option = "alert-info"; //设置Alert类型：alert-error、alert-success、alert-info
    public $message = "";

    /**
     * 构造方法，支持无参、单参、双参调用
     *
     * 用法示例：
     * new ChigiAlert();
     * new ChigiAlert($ChigiReturnObj);
     * new ChigiAlert(array('status)=>200));
     * new ChigiAlert("此处为ALERT内容");
     * new ChigiAlert("此处为ALERT内容",'alert-info');
     * @return \ChigiAlert
     */
    public function __construct() {
        $args = func_get_args();
        switch (count($args)) {
            case 1:
                //单个参数传给push
                return $this->push($args[0]);
                break;
            case 2:
                //双参传给pushset
                return $this->pushSet($args[0], $args[1]);
                break;
            default:
                return $this;
                break;
        }
    }

    /**
     * 推送一个alert，根据ReturnService实例
     * @param ChigiReturn|RETA|int $param 仅接受ReturnService的实例、RETA数组、符合操作码规范的三位数整型
     * @return \ChigiAlert
     */
    public function push($param) {
        if (is_array($param) && ($param['info'] != null)) {
            //单参Return标准数组传入
            $this->option = ($param['status'] - 200) < 100 ? "alert-success" : "alert-error";
            $this->message = isset($param['data']) ? $param['data'] : $param['info'];
            return $this;
        } elseif (is_object($param) && get_class($param) == 'ChigiReturn') {
            $str = $param->getCode()< 300 ? "success" : "error";
            $info = $param->getInfo();
            $this->option = "alert-$str";
            $this->message = $info === null ? $this->getMsg($str) : $info;
            return $this;
        } elseif (is_string($param)) {
            //单参字符串数据传入
            return $this->pushSet($param);
        } else {
            //不符合以上传入标准
            return $this;
        }
    }

    /**
     * 推送一个Alert，手动设置Alert参数
     *
     * @param string $message
     * @param string $option 默认"alert-error"
     * @return \ChigiAlert
     */
    public function pushSet($message, $option = 'alert-info') {
        $this->message = $message;
        $this->option = $option;
        return $this;
    }

    /**
     * 启动alert前端推送
     */
    public function alert() {
        if (empty($this->message)) {
            //无信息内容，不启动推送
            ching("chijiAlertOn", null);
            ching("chijiAlert", null);
        } else {
            //推送已有信息内容
            ching("chijiAlertOn", true);
            ching("chijiAlert", array(
                "option" => $this->option,
                "message" => $this->message
            ));
        }
    }

}

?>
