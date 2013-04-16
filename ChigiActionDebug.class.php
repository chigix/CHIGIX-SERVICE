<?php

/**
 * 千木控制器扩展
 *
 * @author Richard Lea <chigix@zoho.com>
 * @version 3.1.2 <ThinkPHP控制器扩展，为千木Service架构提供接口，所有千木特定接口，均以public function__chigiXXXX()形式定义>
 */
abstract class ChigiAction extends Action {

    private $cacheChing;

    public function __construct() {
        // <editor-fold defaultstate="collapsed" desc="地址栏参数处理">
        foreach ($_GET as $key => $value) {
            if (in_array($key, array('_URL_', C('TOKEN_NAME')))) {
                continue;
            }
            $_GET[$key] = base64_decode($value);
        }
        // </editor-fold>
        // <editor-fold defaultstate="collapsed" desc="客户端SID处理">
        if (isset($_COOKIE['sid'])) {
            define("CHING", $_COOKIE['sid']);
        } elseif (isset($_GET['sid'])) {
            define("CHING", $_GET['sid']);
        } elseif (isset($_POST['sid'])) {
            define("CHING", $_POST['sid']);
        } else {
            //当前浏览器上无sid记录
            //↓则生成一条新的游客记录
            $cid = md5(getClientIp() . microtime());
            cookie("sid", $cid, array('domain' => C("CHINGSET.DOMAIN")));
            define("CHING", $cid);
        }
        // </editor-fold>
        // <editor-fold defaultstate="collapsed" desc="CHING会话初始化">
        $this->cacheChing = cache_ching();
        $content = $this->cacheChing->get(CHING);
        //Ching会话初始化
        if ($content === false) {
            $this->cacheChing->set(CHING, array());
            $content = array();
        }
        C("CHING", $content);
        // </editor-fold>
        $this->__chigiEmptyRedirection();
        parent::__construct();
    }

    //目标操作不在控制器中，进行自动跳转
    protected function __chigiEmptyRedirection() {
        if (method_exists($this, ACTION_NAME)) {
            //如果目标操作直接在当前控制器中
            return;
        } elseif (startsWith(ACTION_NAME, 'on')) {
            //on表单提交接收操作
            return($this->on());
        } else {
            // <editor-fold defaultstate="collapsed" desc="查询全局页面定义">
            $result = array();
            $isInt = intval(ACTION_NAME);
            $pageName = ACTION_NAME;
            if ($isInt > 0) {
                $result = M('ChigiPage')->field('pagename,domain,protocol')->find($isInt);
                $pageName = $result['pagename'];
            } else {
                $result = M('ChigiPage')->field('domain,protocol')->where(array('pagename' => ACTION_NAME, 'status' => 1))->find();
            }
            //查询结果处理，正确则进行跳转
            if ($result === null) {
                return;
            } else {
                if (isset($_GET['_URL_'])) {
                    unset($_GET['_URL_']);
                }
                if (isset($_GET['method'])) {
                    unset($_GET['method']);
                }
                return(redirectHeader($result['protocol'] . '://' . $result['domain'] . U('/' . $pageName . '/'), $_GET));
            }
            // </editor-fold>
        }
    }

    /**
     * 表单提交统一接收操作
     *
     * @param string $serviceName
     * @param string $methodName
     * @param string $successDirect
     * @param string $errorDirect
     * @return void
     */
    public function on($serviceName = null, $methodName = null, $successDirect = null, $errorDirect = null) {
        //对表单进行安全令牌验证：
        if (!M()->autoCheckToken($_POST)) {
            _404();
        }
        unset($_POST[C("TOKEN_NAME")]);
        if (isset($_SESSION['verify'])) {
            if ($_SESSION['verify'] != md5($_POST['verify'])) {
                $this->error("验证码错误");
            }
            unset($_POST['verify']);
        }
        //对于15分钟内简单表单，无需再单独定义表单接收操作
        if ($serviceName === null) {
            if (ching('CHIGI_TAG') === null) {
                //操作超时
                $serviceAlert = service("Alert");
                $serviceAlert->push(array(
                    'status' => 401,
                    'info' => "对不起，操作超时"
                ));
                $serviceAlert->alert();
                return(redirectHeader($_SERVER['HTTP_REFERER']));
            } else {
                //本操作暴露于HTTP下执行
                $serviceName = ching("CHIGI_TAG.SERVICE");
                $methodName = ching("CHIGI_TAG.METHOD");
            }
        }
        $serviceName .= 'Service';
        import('@.Service.' . $serviceName);
        $service = new $serviceName();
        //优先捕获iframe
        if ($_GET['iframe']) {
            $successDirect = $_GET['iframe'];
        }
        $service->setDirect($successDirect, $errorDirect);
        $result = $service->$methodName();
        // <editor-fold defaultstate="collapsed" desc="将非int型的$result根据返回值规范变换为-1,0,1">
        if (is_object($result)) {
            if ($result->isValid()) {
                $result = 1;
            } else {
                $result = 0;
            }
        }
        if (is_array($result)) {
            if (getNumHundreds($result['status']) == 2) {
                $result = 1;
            } else {
                $result = 0;
            }
        }
        // </editor-fold>
        switch ($result) {
            case false:
            case 0:
                return($service->errorDirectHeader());
                break;
            case true:
            case 1:
                return($service->successDirectHeader());
                break;
            case -1:
                //DEBUG，不跳转
                echo '<h1>ON操作调试模式</h1><br/><h2>Result返回结果：</h2><br/>';
                dump($result);
                echo '<h2>当前Service状态：</h2></br>';
                dump($service);
                B('ShowPageTrace');
                return;
                break;
            default:
                //非DEBUG，不跳转，直接返回
                //主用于兼容向下兼容旧版本的on接口写法
                return;
                break;
        }
        //return();
    }

    public function __destruct() {
        $this->cacheChing->set(CHING, ching(), C("CHINGSET.EXPIRE")); //缓存仅存在15分钟
        parent::__destruct();
    }

    public function __chigiFetch() {
        $this->fetch();
    }

    public function __chigiShow($content = "") {
        $this->show($content);
    }

    public function __chigiDisplay() {
        $this->display();
    }

//-----------------------------------------------------
//---原生方法继承   --------------------------
//-----------------------------------------------------
    protected function show($content, $charset = '', $contentType = '', $prefix = '') {
        parent::show($content, $charset, $contentType, $prefix);
    }

    protected function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {
        if (APP_DEBUG) {
            if ($templateFile == '') {
                $templateFile = ACTION_NAME;
            }
            $this->tempCheck($templateFile); //检测模板文件是否存在，若不存在则自动生成
            // 设置MODULE_LIST
            C('CHIJI.MODULE_LIST', array());
            // <editor-fold defaultstate="collapsed" desc="初始化视图类，摘自Action类initView方法">
            //实例化视图类
            if (!$this->view)
                $this->view = Think::instance('View');
            // 模板变量传值
            if ($this->tVar)
                $this->view->assign($this->tVar);
            // </editor-fold>
            // <editor-fold defaultstate="collapsed" desc="摘自View类display方法">
            G('viewStartTime');
            tag('view_begin', $templateFile);
            $output = $this->view->fetch($templateFile, $content, $prefix);
            // </editor-fold>
            //开始模块前端动态编译
            $lessFile = ""; //用于存放生成的Less模块导入文件列表
            if (CHIGITEMPLATE_OK !== true) {
                //本标识位于chigiTemplate.class.php构造方法中声明
                throw_exception('模板引擎加载错误，别名配置“' . APP_NAME . '/Conf/alias.php”中应定义“ThinkTemplate”配置项！！');
            }
            // <editor-fold defaultstate="collapsed" desc="Trace模板Module加载列表">

            trace("【模板Module加载列表】#############");
            foreach (C('CHIJI.MODULE_LIST') as $key => $value) {
                trace('→' . $key . "：" . $value);
            }
            trace("[模板Module加载列表]================");
            // </editor-fold>
            //★Less编译
            // <editor-fold defaultstate="collapsed" desc="Less编译">
            import('ORG.Chiji.Lessc');
            $less = new lessc;
            //##处理module编译顺序列表并生成LESS的导入文件列表(String)
            foreach (C('CHIJI.MODULE_LIST') as $value) {
                $class = str_replace(array(':', '#'), array('/', '.'), $value);
                $class_strut = explode('/', $class);
                $lessFileItem = $class_strut[1] . '.less';
                $importDirItem = THEME_PATH . $class_strut[0] . '/';
                $less->addImportDir($importDirItem);
                if (file_exists($importDirItem . $lessFileItem)) {
                    $lessFile .= '@import ' . $lessFileItem . ';' . PHP_EOL;
                }
            }
            $less->importDir = array_unique($less->importDir); //合并重复项目
            //##编译及写入page-CSS文件
            if (C('CHIJI.LESS_COMPRESS')) {
                $less->setFormatter(C('CHIJI.LESS_COMPRESS'));
            } else {
                $less->setFormatter("compressed");
            }
            if (file_put_contents('./../Chiji/' . APP_NAME . '/css/page-' . $templateFile . '.css', $less->compile($lessFile))) {
                trace('Chiji/' . APP_NAME . '/css/page-' . $templateFile . '.css', "页面CSS渲染完毕");
            } else {
                if ($less->compile($lessFile) == '') {
                    trace("页面CSS无内容");
                } else {
                    trace("页面CSS渲染失败");
                }
            }
            // </editor-fold>
            //★JavaScript模块编译
            // <editor-fold defaultstate="collapsed" desc="JavaScript模块编译">
            //##处理module编译顺序列表并生成JS合并
            $jsCombinedString = "";
            foreach (C('CHIJI.MODULE_LIST') as $value) {
                $class = str_replace(array(':', '#'), array('/', '.'), $value);
                $class_strut = explode('/', $class);
                $jsFileItem = $class_strut[1];
                $importDirItem = THEME_PATH . $class_strut[0] . '/';
                if (file_exists($importDirItem . $jsFileItem . '.js')) {
                    $jsCombinedString .= file_get_contents($importDirItem . $jsFileItem . '.js') . PHP_EOL;
                }
                if (C('CHIJI.JS_DEBUG') && file_exists($importDirItem . $jsFileItem . '-test.js')) {
                    $jsCombinedString .= file_get_contents($importDirItem . $jsFileItem . '-test.js') . PHP_EOL;
                }
            }
            //##JS代码压缩
            if (!C("CHIJI.JS_DEBUG")) {
                import('ORG.Chiji.JsCompress');
                $jsCombinedString = chijiJsCompress($jsCombinedString);
            }
            switch (file_put_contents('./../Chiji/' . APP_NAME . '/js/page-' . $templateFile . '.js', $jsCombinedString)) {
                case 0:
                    trace("页面JS无内容");
                    break;
                case false:
                    trace("页面JS渲染失败");
                    break;
                default:
                    trace('Chiji/' . APP_NAME . '/css/page-' . $templateFile . '.js', "页面JS渲染完毕");
                    break;
            }
            // </editor-fold>
            //★输出前端页面HTML代码至浏览器
            // <editor-fold defaultstate="collapsed" desc="摘自View类render方法，请视当前版本的render方法进行改动">
            $charset = C('DEFAULT_CHARSET');
            $contentType = C('TMPL_CONTENT_TYPE');
            // 网页字符编码
            header('Content-Type:' . $contentType . '; charset=' . $charset);
            header('Cache-control: ' . C('HTTP_CACHE_CONTROL'));  // 页面缓存控制
            header('X-Powered-By:ThinkPHP');
            echo $output;
            // </editor-fold>
            tag('view_end');
        }
    }

    protected function tempCheck($pageName) {
        $pagePath = THEME_PATH . 'Index/' . $pageName . '.html';
        $dirPath = THEME_PATH . ucfirst($pageName);
        $pageData = file_get_contents(CHIGI_PATH . 'html/index.html');
        $pageData = $this->keywordReplace($pageData, $pageName);
        $starterData = file_get_contents(CHIGI_PATH . 'html/StarterMODULE.html');
        $starterData = $this->keywordReplace($starterData, $pageName);
        $enderData = file_get_contents(CHIGI_PATH . 'html/EnderMODULE.html');
        $enderData = $this->keywordReplace($enderData, $pageName);
        if (file_exists($pagePath)) {
            return;
        } else {
            file_put_contents($pagePath, $pageData);
            if (!mkdir($dirPath)) {
                echo "创建目录失败";
                dump($dirPath);
                exit;
            }
            file_put_contents($dirPath . '/StarterMODULE.html', $starterData);
            file_put_contents($dirPath . '/EnderMODULE.html', $enderData);
            return;
        }
    }

    protected function keywordReplace($data, $pageName) {
        $replace = array(
            '{PAGENAME}' => $pageName,
            '{APPNAME}' => APP_NAME,
            '{PACKAGENAME}' => ucfirst($pageName),
        );
        foreach ($replace as $key => $value) {
            $data = str_replace($key, $value, $data);
        }
        return $data;
    }

    protected function fetch($templateFile = '', $content = '', $prefix = '') {
        parent::fetch($templateFile, $content, $prefix);
    }

}

?>