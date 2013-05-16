<?php

/**
 * 返回值工具类
 * 用于智能化处理各种函数的返回值问题
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class ChigiReturn {

    private $__data = null;  //返回携带实体数据
    private $__info;  //官方返回信息
    private $__code; //操作码
    private $__messageSuccess = "HELLO~~";
    private $__messageError = "你的操作好像有问题 →_→";

    /**
     * 初始化返回值服务类，导入各种包
     */
    public function __construct($returnValue = null) {
        if (is_int($returnValue)) {
            //传入为ChigiCode
            $this->__code = $returnValue;
            if ($this->isValid())
                $this->__code = 251;
            if ($this->isError())
                $this->__code = 501;
            if ($this->__code != $returnValue) {
                $this->__messageError = "$returnValue : There is no data within this return";
                $this->__messageSuccess = "$returnValue : There is no data within this return";
            }
        } elseif (is_array($returnValue) && isset($returnValue['status'])) {
            //传入为RETA数组
            $this->__code = $returnValue['status'];
            $this->__info = isset($returnValue['info']) ? $returnValue['info'] : "$returnValue : There is no info within this return";
            $this->__data = isset($returnValue['data']) ? $returnValue['data'] : null;
            if ($this->isValid()) {
                // 2xx
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
                //4xx 或 5xx
                $this->__messageError = $this->__info;
                switch (getNumOnes($this->__code)) {
                    case 6:
                        $this->__messageError = (String) $this->__data;
                        break;

                    default:
                        break;
                }
            }
        } elseif (is_object($returnValue) && get_class($returnValue) == 'ChigiReturn') {
            //传入为ChigiReturn
            $this->__code = $returnValue->getCode();
            $this->__data = $returnValue->__;
            $this->__info = $returnValue->getInfo();
            $this->__messageError = $returnValue->getMsg('Error');
            $this->__messageSuccess = $returnValue->getMsg('Success');
        } else {
            // 其他类型传入，包含无参构造
            $this->__data = $returnValue;
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
    public static function get($returnValue) {
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
     * @param string $name 仅能填Success或Error（不区分大小写）
     * 若不填，则根据ChigiCode，自动判断获取Success或Error的信息
     * @return string
     */
    public function getMsg($name = "") {
        if (empty($name)) {
            $name = $this->isValid() ? 'Success' : 'Error';
        }
        $name = "__message" . ucfirst($name);
        return $this->$name;
    }

    /**
     * 设置message
     *
     * @param string $name 仅能填Success或Error
     * @param string $str
     */
    public function setMsg($name, $str) {
        $name = "__message" . $name;
        $this->$name = $str;
    }

}

?>
