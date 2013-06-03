<?php

/**
 * CHIJI RENDERER for front-end
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class Chiji {

    /**
     * 前端渲染模块记录
     * 0 => string 'TodoView:TodoApp'
     *
     * @var array
     */
    public $moduleList = array();
    //额外添加JS模块
    public $jsListAddition = array();
    //已经编译过的JS模块
    public $jsList = array();

    public function __construct() {
        define('CHIGITEMPLATE_OK', true);
    }

    /**
     * 检测模板文件是否存在，并返回模板文件内容
     * 针对不存在的模板会根据@指令来生成，并返回模板文件内容
     *
     * @param string $page_path 例：./Tpl/Default/Todo/index.html
     * @return string
     */
    public function chijiTempCheck($page_path) {
        static $count = 0;
        $count++;
        //例：/Default/Action  →用来存放主页面
        // <editor-fold defaultstate="collapsed" desc="$dir_path 确定逻辑，以兼容 include 子视图模块">
        $dir_path = cut_string_using_last('/', $page_path, 'left', false);
        if ($count > 1) {
            //针对被 include 的页面检查，精准定位dirPath
            //例：/Default/TodoView  →$dirPath 当前的值
            $dir_path_arr = preg_split("/(?=[A-Z])/", $dir_path);
            array_pop($dir_path_arr);
            $dir_path = implode('', $dir_path_arr);
        }
        // </editor-fold>
        //存放子模板视图的目录：例：/Default/TodoView
        $view_path = $dir_path . 'View';
        //存放Require 集合的目录：例：/Default/TodoCollection
        $collection_path = $dir_path . 'Collection';
        //存放Require 模型的目录：例：/Default/TodoModel
        $model_path = $dir_path . 'Model';
        //例：LeftMenu
        $page_name = cut_string_using_last('.', cut_string_using_last('/', $page_path, 'right', false), 'left', false);
        // 例：AppsView 或 AppsCollection 或 AppsModel
        $package_name = cut_string_using_last('/', cut_string_using_last('/', $page_path, 'left', false), 'right', false);
        /* @var $pageData string */
        $page_data = file_get_contents($page_path);
        if (trim($page_data) == '@todo') {
            //写入主模板的HTML
            /* @var $page_data string */
            $page_data = file_get_contents(CHIGI_PATH . 'html/index.html');
            $page_data = $this->chijiKwordReplace($page_data, $page_path);
            $layout = file_get_contents(CHIGI_PATH . 'html/indexLayout.html');
            $layout = $this->chijiKwordReplace($layout, $page_path);
            file_put_contents($page_path, $page_data);
            file_put_contents($dir_path . '/' . $page_name . 'Layout.html', $layout);
            if (!file_exists($view_path)) {
                mkdir($view_path);
            }
            return;
        } elseif (trim($page_data) == '@view') {
            //写入module模板的HTML
            $page_data = file_get_contents(CHIGI_PATH . 'html/view.html');
            $page_data = $this->chijiKwordReplace($page_data, $page_path);
            file_put_contents($page_path, $page_data);
            // 例：LeftMenu
            //$pageName = cut_string_using_first('.', cut_string_using_last('/', $templateName, 'right', false), 'left', false);
        }
        if ('View' == substr($package_name, -4)) {
            return $page_data . '<script type="text/javascript">(function(){if("undefined" == typeof ' . $package_name . '_' . $page_name . '|| ' . $package_name . '_' . $page_name . ' instanceof HTMLElement){console.log(\'【var ' . $package_name . '_' . $page_name . '】\',\'未定义\');}else{console.log(\'【var ' . $package_name . '_' . $page_name . '】\',' . $package_name . '_' . $page_name . ');}})();</script>';
        }
    }

    /**
     * 模板生成关键词替换
     *
     * @param string $data 要被替换的内容
     * @param string $page_path 当前模板路径，例：./Tpl/Default/Todo/test.html
     * @return string 替换结果
     */
    public function chijiKwordReplace($data, $page_path) {
        // index
        $page_name = cut_string_using_first('.', cut_string_using_last('/', $page_path, 'right', false), 'left', false);
        // AppsView 或 AppsModel 或 AppsCollection
        $package_name = cut_string_using_last('/', cut_string_using_last('/', $page_path, 'left', false), 'right', false);
        $replace = array(
            '{PAGENAME}' => $page_name, //index
            '{IPAGENAME}' => parse_name($page_name), //index
            '{APPNAME}' => APP_NAME,
            '{PACKAGENAME}' => $package_name, //PasswdReset
            '{IPACKAGENAME}' => parse_name($package_name), //passwd_reset
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
        if (file_put_contents($resourceDir . '/css/' . parse_name($packageName) . '-' . parse_name($pageName) . '.css', $dataToWrite)) {
            trace('Chiji/css/' . parse_name($packageName) . '-' . parse_name($pageName) . '.css', "页面CSS渲染完毕");
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
        //针对随HTML的JS模块进行编译
        foreach ($this->moduleList as $key => $value) {
            if (in_array($value, $this->jsList)) {
                //避免模块重复编译
                $this->jsListPush($value);
                $this->jsListPass($value);
                continue;
            }
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
        //针对已编译模块中发现的依赖模块，进行编译
        while (count($this->jsListAddition) !== 0) {
            foreach ($this->jsListAddition as $value) {
                if (in_array($value, $this->jsList)) {
                    //避免模块重复编译
                    $this->jsListPass($value);
                    $this->jsListPush($value);
                    continue;
                }
                $module_path = str_replace('_', '/', $value);
                $module_path = THEME_PATH . $module_path . '.js';
                if (file_exists($module_path)) {
                    $jsCombinedString .= $this->jsCompiler(file_get_contents($module_path), $value) . PHP_EOL;
                } else {
                    trace($module_path, 'JS模块地址不存在', 'NOTIC');
                }
                $this->jsListPass($value);
            }
        }
        $jsCombinedString .= 'define("CGA",[],function(){return {' . implode(',', $CGArray) . '};});';
        //##JS代码压缩
        if (!C("CHIJI.JS_DEBUG")) {
            import('ORG.Chiji.JsCompress');
            $jsCombinedString = chijiJsCompress($jsCombinedString);
        }
        if (file_put_contents($resourceDir . '/js/' . parse_name($packageName) . '-' . parse_name($pageName) . '.js', $jsCombinedString)) {
            trace('Chiji/js/' . parse_name($packageName) . '-' . parse_name($pageName) . '.js', "页面JS渲染完毕");
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
     * @param string $value 当前模块名：TodoView:TodoApp
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
                    // 已定义的在$arr 中的特殊JS类库
                    array_push($arrv, $arr[$subvalue]);
                } elseif (substr($subvalue, 0, 10) == 'chigiThis(') {
                    //千路前端模块
                    $param = array();
                    $subvalue = preg_match('/chigiThis\(.*(["\'\(](.*)[\'"\)])*\)/U', $subvalue, $param);
                    $subvalue = chigiThis($param[2]);
                    $arrk[$subkey] = $subvalue;
                    array_push($arrv, ucfirst($subvalue));
                    $this->jsListAdd($subvalue);
                } else {
                    //其他普通模块
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
        } else {
            $newer = str_replace('chigiThis', str_replace(':', '_', $value), $newer);
        }
        $this->jsListPush(str_replace(':', '_', $value));
        $this->jsListPass(str_replace(':', '_', $value));
        return $newer . PHP_EOL;
    }

    /**
     * moduleList 堆入
     *
     * @param string $file 例：【TodoView:TodoApp】
     */
    public function moduleListPush($file) {
        array_push($this->moduleList, $file);
    }

    /**
     * 已编译JS模块 堆入
     *
     * @param string $moduleIdentifier 模块标识名：TestView_TestView
     */
    public function jsListPush($moduleIdentifier) {
        array_push($this->jsList, $moduleIdentifier);
        $this->jsList = array_unique($this->jsList);
    }

    /**
     * 新加入待编译的JS模块 堆入
     *
     * @param string $moduleIdentifier 模块标识名：TestView_TestView
     */
    public function jsListAdd($moduleIdentifier) {
        if (in_array($moduleIdentifier, $this->jsList)) {
            //检查目标模块是否已编译过
            return;
        }
        array_push($this->jsListAddition, $moduleIdentifier);
        $this->jsListAddition = array_unique($this->jsListAddition);
    }

    /**
     * 将指定模块从待编译区移动至已编译区
     *
     * @param string $moduleIdentifier 模块标识名：TestView_TestView
     */
    public function jsListPass($moduleIdentifier) {
        array_unshift($this->jsListAddition, $moduleIdentifier);
        $this->jsListAddition = array_unique($this->jsListAddition);
        array_shift($this->jsListAddition);
        array_push($this->jsList, $moduleIdentifier);
    }

}

?>
