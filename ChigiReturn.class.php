<?php

/*
 * 返回值工具类
 * 用于智能化处理各种函数的返回值问题
 */

/**
 * 返回值服务类
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class ChigiReturn {

    private $__data;  //返回携带实体数据
    private $__info;  //官方返回信息
    private $__code; //操作码
    private $__messageSuccess = "HELLO~~";
    private $__messageError = "你的操作好像有问题 →_→";

    /**
     * 初始化返回值服务类，导入各种包
     */
    public function __construct($returnValue = null) {
        if ($returnValue == null) {
            // 在控制器中初始化
        } else {
            // 处理包装返回值，来自$this->get()的实例化
            $this->__code = $returnValue['status'];
            $this->__info = $returnValue['info'];
            $this->__data = $returnValue['data'];
            if ($this->isValid()) {
                $this->__messageSuccess = $this->__info;
                switch (getNumOnes($this->__code)) {
                    case 4:
                        if (count($this->__data) == 3) {
                            cookie($this->__data[0], $this->__data[1], $this->__data[2]);
                        } else {
                            cookie($this->__data[0], $this->__data[1]);
                        }
                        break;
                    case 5:
                        ching($this->__data[0], $this->__data[1]);
                        break;
                    case 6:
                        $this->__messageSuccess = (String) ($this->__data);
                        break;
                    default:
                        break;
                }
            } else {
                $this->__messageError = $this->__info;
                switch (getNumOnes($this->__code)) {
                    case 6:
                        $this->__messageError = (String) $this->__data;
                        break;

                    default:
                        break;
                }
            }
        }
    }

    /**
     * 将返回值包装成本类实体对象
     *
     * 若传入参数不是数组，则直接返回参数本身。
     * 若传入数组具有“status”元素，则包装成本类实体对象
     * @param mixed $returnValue
     * @return /ReturnService
     */
    public function get($returnValue) {
        if ($returnValue['status'] !== null) {
            return new self($returnValue);
        } else {
            return $returnValue;
        }
    }

    //魔术方法系列
    public function __get($name) {
        if ($name == '__') {
            return $this->__data;
        } else {
            return $this->__data[$name];
        }
    }

    public function __toString() {
        if ($this->isValid()) {
            return $this->__messageSuccess;
        } else {
            return $this->__messageError;
        }
    }

    public function isValid() {
        return chigiValid($this->__code);
    }

    public function isError() {
        return chigiErrorstate($this->__code);
    }

    public function getCode() {
        return $this->__code;
    }

    public function getInfo() {
        return $this->__info;
    }

    /**
     * 返回当前返回的总体概览
     *
     * @return array
     */
    public function getReturn() {
        return array(
            "status" => $this->__code,
            "info" => $this->__info,
            "data" => $this->__data
        );
    }

    /**
     * 获取成功/失败信息
     *
     * @param string $name 仅能填Success或Error
     * @return string
     */
    public function getMsg($name) {
        $name = "__message" . $name;
        return $this->$name;
    }

    /**
     * 设置message
     *
     * @param string $name 仅能填Success或Error
     * @param string $str
     */
    public function setMsg($name , $str) {
        $name = "__message" . $name;
        $this->$name = $str;
    }

}

?>
