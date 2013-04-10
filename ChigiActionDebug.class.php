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
        if ( isset($_SESSION['verify'])) {
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
        parent::display($templateFile, $charset, $contentType, $content, $prefix);
    }

    protected function fetch($templateFile = '', $content = '', $prefix = '') {
        parent::fetch($templateFile, $content, $prefix);
    }

}

?>