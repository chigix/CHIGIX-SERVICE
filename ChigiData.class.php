<?php

/**
 * 千木数据对象原型
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class ChigiData {

    public $__data;
    private $__view = 'table';
    private $__ext = 'Strap';
    private $__output = "BANKAI";

    public function __construct($dataSource, $dataExt = 'Strap') {
        if (is_object($dataSource) && get_class($dataSource) == "ChigiReturn") {
            //按千木返回标准对象处理
            $this->__data = $dataSource;
            $this->__ext = $dataExt;
        } elseif (is_array($dataSource)) {
            if (array_key_exists("status", $dataSource) && array_key_exists("info", $dataSource)) {
                //按千木返回标准数组处理
                $this->__data = isset($dataSource['data']) ? $dataSource['data'] : null;
            } else {
                //按普通数据数组处理
                $this->__data = $dataSource;
            }
        } else {
            //非数组非对象的处理方式
            $this->__data = $dataSource;
        }
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
                $pageName = MODULE_NAME . 'MODULE';
            }

            // 数据格式转换
            $cols = array_keys($this->__data[0]);
            $cols = array_merge(array("__key"), $cols);

            // 数据可视化渲染
            $result = qp('<table id="' . $pageName . '_' . $name . '"><thead><tr></tr></thead><tbody></tbody></table>', '#' . $pageName . '_' . $name . '');
            $table = $result->find('table');
            $table->addClass('table');
            $table->addClass('table-bordered');
            $table->addClass('table-hover');
            $table->addClass('table-striped');
            foreach ($cols as $value) {
                $result->find('thead tr')->append("<th>$value</th>");
            }
            $tbody = $table->find('tbody');
            $tbody->append('<volist name="' . $name . '" id="vo" key="__key"></volist>');
            $volist = $tbody->find('volist');
            $newLine = qp('<tr></tr>', 'tr');
            foreach ($cols as $value) {
                if ($value == "__key") {
                    $newLine->append('<td>{$__key}</td>');
                } else {
                    $newLine->append('<td>{$vo["' . $value . '"]}</td>');
                }
            }
            $newLine->appendTo($volist);

            // 输出渲染结果
            if (file_put_contents(THEME_PATH . "$pageName/" . $name . ".html", $result->html())) {
                trace(THEME_PATH . "$pageName/" . $name . ".html", $name . "MODULE模板文件渲染完毕");
            } else {
                trace($name . "MODULE模板文件渲染失败");
            }
        }
        //$this->__output = $result->html();
        return $this->__data;
    }

    public function __toString() {
        return $this->__output;
    }

}

?>
