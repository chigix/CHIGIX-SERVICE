<?php

/**
 * 全局Alert服务类
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class AlertService {

    public $messageSuccess = null;
    public $messageError = null;
    public $option = "info";
    public $message = "";

    /**
     * 推送一个alert，根据ReturnService实例
     * @param mixed $param 仅接受ReturnService的实例、统一返回规范的数组、符合操作码规范的三位数整型
     * @return \AlertService
     */
    public function push($param) {
        if (is_array($param)) {
            if (($param['status'] >= 200) && ($param['status'] < 300)) {
                $this->option = "alert-success";
                if ($this->getMsg("Success") === null) {
                    $this->message = $param['info'];
                } else {
                    $this->message = $this->getMsg("Success");
                }
                return $this;
            } else {
                $this->option = "alert-error";
                if ($this->getMsg("Error") === null) {
                    $this->message = $param['info'];
                } else {
                    $this->message = $this->getMsg("Error");
                }
                return $this;
            }
        } elseif (is_object($param)) {
            if (($param->getCode() >= 200) && ($param->getCode() < 300)) {
                $this->option = "alert-success";
                if (get_class($param) == 'ReturnService') {
                    $this->message = $param->getMsg("Success");
                } else {
                    if ($this->getMsg("Success") === null) {
                        $this->message = $param->getInfo();
                    } else {
                        $this->message = $this->getMsg("Success");
                    }
                }
                return $this;
            } else {
                $this->option = "alert-error";
                if (get_class($param) == "ReturnService") {
                    $this->message = $param->getMsg("Error");
                } else {
                    if ($this->getMsg("Error") === null) {
                        $this->message = $param->getInfo();
                    } else {
                        $this->message = $this->getMsg("Error");
                    }
                }
                return $this;
            }
        } elseif (is_int($param)) {
            if (($param >= 200) && ($param < 300)) {
                $this->option = "alert-success";
                $this->message = $this->getMsg("Success");
                return $this;
            } else {
                $this->option = "alert-error";
                $this->message = $this->getMsg("Error");
                return $this;
            }
        } else {
            return $this;
        }
    }

    /**
     * 推送一个Alert，手动设置Alert参数
     *
     * @param string $message
     * @param string $option 默认"alert-error"
     */
    public function pushSet($message , $option = 'alert-error') {
        $this->message = $message;
        $this->option = $option;
        return $this;
    }
    /**
     * 启动alert前端推送
     *
     * @return \AlertService
     */
    public function alert() {
        if (empty($this->message)) {
            return;
        }
        ching("chijiAlertOn", true);
        ching("chijiAlert", array(
            "option" => $this->option,
            "message" => $this->message
        ));
    }

}

?>
