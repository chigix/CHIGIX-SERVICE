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
     * 推送一个alert
     * @param mixed $param 仅接受ReturnService的实例、统一返回规范的数组、符合操作码规范的三位数整型
     * @return void
     */
    public function push($param) {
        if (is_array($param)) {
            if (($param['status'] >= 200) && ($param['status'] < 300)) {
                $this->option = "alert-success";
                if ($this->messageSuccess === null) {
                    $this->message = $param['info'];
                } else {
                    $this->message = $this->messageSuccess;
                }
                return;
            } else {
                $this->option = "alert-error";
                if ($this->messageError === null) {
                    $this->message = $param['info'];
                } else {
                    $this->message = $this->messageError;
                }
                return;
            }
        } elseif (is_object($param)) {
            if (($param->code >= 200) && ($param->code < 300)) {
                $this->option = "alert-success";
                if (get_class($param) == 'ReturnService') {
                    $this->message = $param->messageSuccess;
                } else {
                    if ($this->messageSuccess === null) {
                        $this->message = $param->info;
                    } else {
                        $this->message = $this->messageSuccess;
                    }
                }
                return;
            } else {
                $this->option = "alert-error";
                if (get_class($param) == "ReturnService") {
                    $this->message = $param->messageError;
                } else {
                    if ($this->messageError === null) {
                        $this->message = $param->info;
                    } else {
                        $this->message = $this->messageError;
                    }
                }
                return;
            }
        } elseif (is_int($param)) {
            if (($param >= 200) && ($param < 300)) {
                $this->option = "alert-success";
                $this->message = $this->messageSuccess;
                return;
            } else {
                $this->option = "alert-error";
                $this->message = $this->messageError;
                return;
            }
        } else {
            return;
        }
    }

    /**
     * 启动alert前端推送
     */
    public function alert() {
        if ($this->message == "") {
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
