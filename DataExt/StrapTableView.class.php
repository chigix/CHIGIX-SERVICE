<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StrapView
 *
 * @author Administrator
 */
class StrapTableView {

    public $html;
    public function __construct($data,$name,$pageName) {
        //数据格式转换 与 数组维数判断
        //一维线性数组
        $cols = array('__key','__value');
        if (isset($data[0]) && is_array($data[0])) {
            //当前数组维数大于2
            $cols = array_merge(array("__key"), array_keys($data[0]));
        }
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

        //制作行内容
        if (count($cols)>2) {
            $tbody->append('<volist name="' . $pageName . '_' . $name . '[\'' . $name . '\']" id="vo" key="__key"></volist>');
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
        }  else {
            foreach ($data as $key => $value) {
                $newLine = qp('<tr></tr>', 'tr');
                $newLine->append('<td>' . $key . '</td>');
                $newLine->append('<td>{$' . $pageName . '_' . $name . '[\'' . $name . '\'][\'' . $key . '\']}</td>');
                $newLine->appendTo($tbody);
            }
        }
        $result->append('<script type="text/javascript">var ' . $pageName . '_' . $name . '={:json_encode($' . $pageName . '_' . $name . ')};</script>');
        $this->html = $result->html();
    }

}

?>
