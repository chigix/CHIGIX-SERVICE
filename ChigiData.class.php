<?php

/**
 * 千木数据对象原型
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class ChigiData {

    private $__data;
    private $__view = 'table';
    private $__ext = 'Strap';
    private $__output = "";
    public function __construct($dataSource,$dataExt = 'Strap') {
        $this->__data = $dataSource;
        $this->__ext = $dataExt;
        import(CHIGI_PATH . 'DataExt/' . $dataExt . 'View');
    }

    /**
     * 已实例化对象重新获取数据原型
     *
     * @param mixed $dataSource
     */
    public function get($dataSource) {
        $this->__data = $dataSource;
    }
    /**
     * 设定并返回数据视图内容
     *
     * @param string $type 设定数据视图要输出的目标类型
     * @param String $dataExt 临时改变目标渲染驱动类
     * @return string 数据视图层渲染结果
     */
    public function view($type , $dataExt = null) {
        $this->__view = $type;
        $renderResult = "";
        $this->__output = $renderResult;
        return $renderResult;
    }
    public function __toString() {
        return $this->__output;
    }
}

?>
