<?php

/**
 * CHIJI RENDERER for front-end
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class Chiji {

    //前端渲染模块记录
    public $moduleList = array();
    //额外添加JS模块
    public $jsListAddition = array();

    public function __construct() {
        define('CHIGITEMPLATE_OK', true);
    }

    /**
     * 检测模板文件是否存在，并返回模板文件内容
     * 针对不存在的模板会根据@指令来生成，并返回模板文件内容
     *
     * @param string $pagePath 例：./Tpl/Default/Todo/index.html
     * @return string
     */
    public function chijiTempCheck($pagePath) {
        static $count = 0;
        $count++;
        //例：/Default/Action/pageName.html  →$pagePath
        //例：/Default/Action  →用来存放主页面
        $dirPath = cut_string_using_last('/', $pagePath, 'left', false);
        //例：/Default/ActionMODULE  →用来存放MODULE块页面
        $modulePath = $dirPath . 'MODULE';
        if ($count > 1 && !file_exists($modulePath)) {
            //第二次检查，主针对被 include 的页面模块
            //若MODULEMODULE结尾的目录不存在，则去掉最后的一个'MODULE'
            $modulePath = substr($modulePath, 0, -6);
        }
        //例：LeftMenu
        $pageName = cut_string_using_last('.', cut_string_using_last('/', $pagePath, 'right', false), 'left', false);
        // 例：AppsMODULE
        $packageName = cut_string_using_last('/', cut_string_using_last('/', $pagePath, 'left', false), 'right', false);
        //$pageName = cut_string_using_first('.', cut_string_using_last('/', $templateName, 'right', false), 'left', false);
        /* @var $pageData string */
        $pageData = file_get_contents($pagePath);
        if ($pageData == '@todo') {
            //写入主模板的HTML
            $pageData = file_get_contents(CHIGI_PATH . 'html/index.html');
            $pageData = $this->chijiKwordReplace($pageData, $pagePath);
            $layout = file_get_contents(CHIGI_PATH . 'html/indexLayout.html');
            $layout = $this->chijiKwordReplace($layout, $pagePath);
            file_put_contents($pagePath, $pageData);
            if (!file_exists($modulePath)) {
                mkdir($modulePath);
            }
            file_put_contents($modulePath . '/' . $pageName . 'Layout.html', $layout);
            return;
        } elseif ($pageData == '@module') {
            //写入module模板的HTML
            $pageData = file_get_contents(CHIGI_PATH . 'html/module.html');
            $pageData = $this->chijiKwordReplace($pageData, $pagePath);
            file_put_contents($pagePath, $pageData);
            // 例：LeftMenu
            //$pageName = cut_string_using_first('.', cut_string_using_last('/', $templateName, 'right', false), 'left', false);
        }
        if ('MODULE' == substr($packageName, -6)) {
            return $pageData . '<script type="text/javascript">(function(){if("undefined" == typeof ' . $packageName . '_' . $pageName . '|| ' . $packageName . '_' . $pageName . ' instanceof HTMLElement){console.log(\'【var ' . $packageName . '_' . $pageName . '】\',\'未定义\');}else{console.log(\'【var ' . $packageName . '_' . $pageName . '】\',' . $packageName . '_' . $pageName . ');}})();</script>';
        }
    }

    /**
     * 模板生成关键词替换
     *
     * @param string $data 要被替换的内容
     * @param string $pagePath 当前模板路径，例：./Tpl/Default/Todo/test.html
     * @return string 替换结果
     */
    public function chijiKwordReplace($data, $pagePath) {
        // index
        $pageName = cut_string_using_first('.', cut_string_using_last('/', $pagePath, 'right', false), 'left', false);
        // AppsMODULE
        $packageName = cut_string_using_last('/', cut_string_using_last('/', $pagePath, 'left', false), 'right', false);
        $replace = array(
            '{PAGENAME}' => $pageName, //index
            '{APPNAME}' => APP_NAME,
            '{PACKAGENAME}' => $packageName, //PasswdReset
            '{IPACKAGENAME}' => parse_name($packageName), //passwd_reset
        );
        foreach ($replace as $key => $value) {
            $data = str_replace($key, $value, $data);
        }
        return $data;
    }

    /**
     * 前端JS、CSS资源部署生成器
     *
     * @param string $pagePath 当前模板文件路径：./Tpl/Default/Todo/index.html
     */
    public function chijiJCGenerator($pagePath) {
        static $count = 0;
        $count++;
        if ($count > 1) {
            return;
        }
        if (!C('CHIJI.RC_DIR')) {
            throw_exception("对不起，当前项目尚未配置前端资源部署目录");
        }
        $resourceDir = C('CHIJI.RC_DIR');
        $pageName = cut_string_using_first('.', cut_string_using_last('/', $pagePath, 'right', false), 'left', false);
        $packageName = cut_string_using_last('/', cut_string_using_last('/', $pagePath, 'left', false), 'right', false);
        //开始模块前端动态编译
        //↓用于存放生成的Less模块导入文件列表 并写入初始的index.less
        $lessFile = file_exists(cut_string_using_last('.', $pagePath, 'left', true) . 'less') ? file_get_contents(cut_string_using_last('.', $pagePath, 'left', true) . 'less') : "";
        // <editor-fold defaultstate="collapsed" desc="Trace模板Module加载列表">
        trace("【模板Module加载列表】#############");
        foreach ($this->moduleList as $key => $value) {
            trace('→' . $key . "：" . $value);
        }
        trace("[模板Module加载列表]================");
        // </editor-fold>
        //★Less编译
        // <editor-fold defaultstate="collapsed" desc="Less编译">
        import('ORG.Chiji.Lessc');
        $less = new lessc;
        //##处理module编译顺序列表并生成LESS的导入文件列表(String)
        foreach ($this->moduleList as $value) {
            $class = str_replace(array(':', '#'), array('/', '.'), $value);
            $class_strut = explode('/', $class);
            $lessFileItem = $class_strut[1] . '.less';
            $importDirItem = THEME_PATH . $class_strut[0] . '/';
            $less->addImportDir($importDirItem);
            if (file_exists($importDirItem . $lessFileItem)) {
                $lessFile .= '@import ' . $lessFileItem . ';' . PHP_EOL;
            }
        }
        if ($less->importDir != '') {
            $less->importDir = array_unique($less->importDir); //合并重复项目
        }
        //##编译及写入page-CSS文件
        if (C('CHIJI.LESS_COMPRESS')) {
            $less->setFormatter(C('CHIJI.LESS_COMPRESS'));
        } else {
            $less->setFormatter("compressed");
        }

        $dataToWrite = $less->compile($lessFile);
        if (file_put_contents($resourceDir . '/css/' . $packageName . '-' . $pageName . '.css', $dataToWrite)) {
            trace('Chiji/css/' . parse_name($packageName) . '-' . $pageName . '.css', "页面CSS渲染完毕");
        } else {
            if (empty($dataToWrite)) {
                trace("页面CSS无内容");
            } else {
                trace("页面CSS渲染失败，请查看错误信息");
            }
        }
        // </editor-fold>
        //★JavaScript模块编译
        // <editor-fold defaultstate="collapsed" desc="JavaScript模块编译">
        //##处理module编译顺序列表并生成JS合并
        $jsCombinedString = file_exists(cut_string_using_last('.', $pagePath, 'left', true) . 'js') ? file_get_contents(cut_string_using_last('.', $pagePath, 'left', true) . 'js') : "";
        $page_strut = substr($pagePath, strpos($pagePath, THEME_PATH) + strlen(THEME_PATH));
        $page_strut = cut_string_using_last('.', $page_strut, 'left', false);
        $page_strut = str_replace('/', ':', $page_strut);
        //例：Todo:index
        chigiThis($page_strut);
        $jsCombinedString = $this->jsCompiler($jsCombinedString, $page_strut);
        //用来存放每个模板模块推送的变量
        $CGArray = array();
        foreach ($this->moduleList as $key => $value) {
            $class = str_replace(array(':', '#'), array('/', '.'), $value);
            $class_strut = explode('/', $class);
            $jsFileItem = $class_strut[1];
            $importDirItem = THEME_PATH . $class_strut[0] . '/';
            $CGString = '"' . str_replace('/', '_', $class) . '":' . str_replace('/', '_', $class);
            array_push($CGArray, $CGString);
            if (file_exists($importDirItem . $jsFileItem . '.js')) {
                $jsCombinedString .= $this->jsCompiler(file_get_contents($importDirItem . $jsFileItem . '.js'), $value) . PHP_EOL;
            }
            if (C('CHIJI.JS_DEBUG') && file_exists($importDirItem . $jsFileItem . '-test.js')) {
                $jsCombinedString .= file_get_contents($importDirItem . $jsFileItem . '-test.js') . PHP_EOL;
            }
        }
        $jsCombinedString .= 'define("CGA",[],function(){return {' . implode(',', $CGArray) . '};});';
        //##JS代码压缩
        if (!C("CHIJI.JS_DEBUG")) {
            import('ORG.Chiji.JsCompress');
            $jsCombinedString = chijiJsCompress($jsCombinedString);
        }
        if (file_put_contents($resourceDir . '/js/' . $packageName . '-' . $pageName . '.js', $jsCombinedString)) {
            trace('Chiji/js/' . parse_name($packageName) . '-' . $pageName . '.js', "页面JS渲染完毕");
        } else {
            if (empty($jsCombinedString)) {
                trace("页面JS无内容");
            } else {
                trace("页面JS渲染失败，请查看错误信息");
            }
        }
        // </editor-fold>
    }

    /**
     * JS模板编译器
     *
     * @param string $newer JS模板文件内容
     * @param string $value 当前模块名：TestMODULE_TestView
     * @return string 编译结果内容
     */
    public function jsCompiler($newer, $value) {
        //JS超级接口编译
        /* @var $detpos integer */
        $detpos = strpos($newer, '@require:');
        static $count = 0;
        $count++;
        // <editor-fold defaultstate="collapsed" desc="针对 require 的模块化编译">
        if ($detpos < 5 && is_int($detpos)) {
            $eol = strpos($newer, PHP_EOL);
            $detpos += 9;
            $arr = array(
                'jquery' => '$',
                'backbone' => 'Backbone',
                'underscore' => '_'
            );
            /* @var $arrk array */
            $arrk = explode(',', substr($newer, $detpos, $eol - $detpos));
            $arrv = array();
            foreach ($arrk as $subkey => $subvalue) {
                $subvalue = trim($subvalue);
                if (substr($subvalue, 0, 6) == 'order!') {
                    $subvalue = substr($subvalue, 6);
                }
                if (isset($arr[$subvalue])) {
                    array_push($arrv, $arr[$subvalue]);
                } elseif (substr($subvalue, 0, 10) == 'chigiThis(') {
                    $param = array();
                    $subvalue = preg_match('/chigiThis\(.*(["\'\(](.*)[\'"\)])*\)/U', $subvalue, $param);
                    $subvalue = chigiThis($param[2]);
                    $arrk[$subkey] = $subvalue;
                    array_push($arrv, ucfirst($subvalue));
                } else {
                    array_push($arrv, ucfirst($subvalue));
                }
            }
            $newer = trim(substr($newer, $eol));
            $newer = 'define("chigiThis",["' . implode('","', $arrk) . '"],function(' . implode(',', $arrv) . '){' . PHP_EOL . $newer . PHP_EOL . '});';
        };
        // </editor-fold>
        $newer = preg_replace('/chigiThis\(.*(["\'\(].*[\'"\)])*\)/U', '{:$0}', $newer);
        $newer = preg_replace_callback('/\{\:(.+(["\'].*[\'"].*)*)\}/U', create_function('$matches', 'return(eval(\'return \' . $matches[1] . \';\'));'), $newer);
        //编译 chigiThis 关键字
        if ($count == 1) {
            //主入口
            $newer = str_replace('chigiThis', 'app/' . strtolower(str_replace(':', '-', $value)), $newer);
        }  else {
            $newer = str_replace('chigiThis', str_replace(':', '_', $value), $newer);
        }
        return $newer . PHP_EOL;
    }

    /**
     * moduleList 堆入
     *
     * @param string $file
     */
    public function moduleListPush($file) {
        array_push($this->moduleList, $file);
    }

}

?>
