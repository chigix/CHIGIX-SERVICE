<?php

/**
 * 千木数据抽象类
 * 用于智能化处理各种函数的返回值问题，
 * 并提供数据均衡化解决方案
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class ChigiReturn {

    private $__data = null;  //返回携带实体数据
    private $__info;  //官方返回信息
    private $__code; //操作码
    // message→直接供ChigiAlert产生Alert用的信息，默认初始化使用info，
    // 仅当状态码第三位为6时，使用data
    private $__messageSuccess = "HELLO~~";
    private $__messageError = "你的操作好像有问题 →_→";
    private $__view = 'table';
    private $__output = "";

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
            return isset($this->__data[$name]) ? $this->__data[$name] : null;
        }
    }

    public function __toString() {
        if ($this->isValid()) {
            return $this->__messageSuccess;
        } else {
            return $this->__messageError;
        }
    }

    /**
     * 用于判断当前数据是否为Valid(状态码为2开头)
     *
     * @return boolean
     */
    public function isValid() {
        return chigiValid($this->__code);
    }

    /**
     * 判断当前数据是否为Error（状态码为5开头）
     *
     * @return boolean
     */
    public function isError() {
        return chigiErrorstate($this->__code);
    }

    /**
     * 返回当前数据的3位十进制状态码
     *
     * @return int
     */
    public function getCode() {
        return $this->__code;
    }

    /**
     * 返回当前数据的官方信息
     *
     * @return string
     */
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
     * @param string $name 仅能填Success或Error（不区分大小写），
     *                      可不填跳过，则默认根据ChigiCode自动判断要修改的目标信息
     * @param string $str
     */
    public function setMsg() {
        $args = func_get_args();
        $args_num = func_num_args();
        /* @var $name string Success/Error */
        $name = "";
        /* @var $str string 给出要设置的Msg内容 */
        $str = "";
        if ($args_num === 1) {
            // 根据ChigiCode判断要修改的目标Msg
            $name = $this->isValid() ? 'Success' : 'Error';
            $str = $args[0];
        } else {
            $name = ucfirst($args[0]);
            $str = $args[1];
        }
        $name = "__message" . $name;
        $this->$name = $str;
    }

    /**
     * 设定并返回数据视图内容
     *
     * @param string $type 设定数据视图要输出的目标类型
     * @param string $name 设定assign时的名称设定，与assign对应
     * @param bool $isLock 是否加锁，默认false即不加锁，加锁后可以开发者进行自行更改
     * @param string $pageName 指定页面名称，若不指定则默认使用当前模块名
     * @return string 数据视图层渲染结果HTML代码
     */
    public function view($type, $name, $isLock = false, $pageName = null) {
        if (APP_DEBUG && !$isLock) {
            //编译模式
            $this->__view = $type;
            if ($pageName === null) {
                $pageName = MODULE_NAME . 'View';
            } else {
                $pageName .= 'View';
            }

            // 数据格式转换
            $class = $type . 'View';
            require_once 'DataExt/' . $class . '.class.php';
            $result = new $class($this->__data, $name, $pageName);
            // 输出渲染结果
            if (file_put_contents(THEME_PATH . "$pageName/" . $name . ".html", $result->html)) {
                trace(THEME_PATH . "$pageName/" . $name . ".html", $name . "MODULE模板文件渲染完毕");
            } else {
                throw_exception($name . "MODULE模板文件渲染失败");
            }
        }
        //$this->__output = $result->html();
        return $this->__data;
    }

    /**
     * 生成整个数据抽象的JSON格式
     *
     * @return string
     */
    public function toJsonAll() {
        return json_encode(array(
                    'status' => $this->__code,
                    'info' => $this->__info,
                    'data' => $this->__data,
                ));
    }

    /**
     * 生成数据部分的JSON格式
     *
     * @return string
     */
    public function toJsonData() {
        return json_encode($this->__data);
    }

    /**
     * 返回RETA数组形式
     *
     * @return array
     */
    public function toReta() {
        return array(
            "status" => $this->__code,
            "info" => $this->__info,
            "data" => $this->__data
        );
    }

}

?>
