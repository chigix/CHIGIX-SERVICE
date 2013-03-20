<?php

/*
 * 返回值服务类
 * 用于智能化处理各种函数的返回值问题。
 */

/**
 * 返回值服务类
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class ReturnService {

    public $data;  //返回携带实体数据
    public $info;  //官方返回信息
    public $code; //操作码
    public $isValid;
    public $isError;
    public $messageSuccess = "HELLO~~";
    public $messageError = "你的操作好像有问题 →_→";

    /**
     * 初始化返回值服务类，导入各种包
     */
    public function __construct($returnValue = null) {
        if ($returnValue == null) {
            // 在控制器中初始化
        } else {
            // 处理包装返回值，来自$this->get()的实例化
            $this->code = $returnValue['status'];
            $this->info = $returnValue['info'];
            $this->data = $returnValue['data'];
            $this->isValid = chigiValid($this->code);
            $this->isError = chigiErrorstate($this->code);
            if ($this->isValid) {
                $this->messageSuccess = $this->info;
                switch (getNumOnes($this->code)) {
                    case 4:
                        if (count($this->data) == 3) {
                            cookie($this->data[0], $this->data[1], $this->data[2]);
                        } else {
                            cookie($this->data[0], $this->data[1]);
                        }
                        break;
                    case 5:
                        ching($this->data[0], $this->data[1]);
                        break;
                    case 6:
                        $this->messageSuccess = (String)($this->data);
                        break;
                    default:
                        break;
                }
            } else {
                $this->messageError = $this->info;
                switch (getNumOnes($this->code)) {
                    case 6:
                        $this->messageError = (String)$this->data;
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

    /**
     * 返回值数据输出，用于模板中直接使用
     * @return String
     */
    public function __toString() {
        if ($this->isValid) {
            return $this->messageSuccess;
        } else {
            return $this->messageError;
        }
    }

}

?>
