<?php

/**
 * CHING会话对象，单例模式
 *
 * @author Administrator
 * @todo 增加判断服务类机制，以避免服务类使用CHING会话
 */
class CHING {

    private static $__instance;
    public static $CID;
    private $__cache;
    private $__data = array();

    public static function getInstance($cid = null) {
        if ((!isset(self::$__instance)) || $cid != null) {
            self::$__instance = new CHING($cid);
        }
        return self::$__instance;
    }

    private function __construct($cid) {
        // <editor-fold defaultstate="collapsed" desc="客户端SID处理">
        if (!is_null($cid)) {
            //指定CID，不使用自动生成的新CID
            self::$CID = $cid;
        } elseif (isset($_COOKIE['sid'])) {
            self::$CID = $_COOKIE['sid'];
        } elseif (isset($_GET['sid'])) {
            self::$CID = $_GET['sid'];
        } elseif (isset($_POST['sid'])) {
            self::$CID = $_POST['sid'];
        } else {
            //当前浏览器上无sid记录
            //↓则生成一条新的游客记录
            $cid = md5(getClientIp() . microtime());
            self::$CID = $cid;
        }
        cookie("sid", self::$CID, array('domain' => C("CHINGSET.DOMAIN")));
        // </editor-fold>
        // <editor-fold defaultstate="collapsed" desc="CHING会话初始化">
        $this->__cache = $this->cache_ching();
        $content = $this->__cache->get(self::$CID);
        if ($content === false) {
            //当前第一次访问或上次会话已失效，初始化一个新的Ching会话
            $this->__cache->set(self::$CID, array());
            $content = array();
        }
        $this->__data = $content;
        // </editor-fold>
    }

    /**
     * 获取元素值
     * 支持点号来表示要获取的目标数组元素，形如ching("Sugar.start.ele1")
     *
     * @param string $name
     * @return mixed
     */
    public function get($name) {
        return getNestedVar($this->__data, $name);
    }

    /**
     * 获取所有元素
     *
     * @return array
     */
    public function getAll() {
        return $this->___data;
    }

    /**
     * 设置当前项目的CHING 值
     * 返回之前设置
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function set($name, $value) {
        $temp = $this->get($name);
        set_value($this->__data, $name, $value);
        if (is_null($value)) {
            $this->delete($name);
        }
        return $temp;
    }

    /**
     * 删除指定的CHING 值
     *
     * @param string $pathString
     */
    public function delete($pathString = null) {
        if (is_null($pathString)) {
            //全部清空
            $this->__data = array();
        } else {
            $lastKey = "";
            $valTmp = getNestedVar($this->__data, $pathString);
            while ($pathString != "") {
                if (empty($valTmp)) {
                    $lastKey = cut_string_using_last('.', $pathString, 'right', false);
                    $pathString = cut_string_using_last('.', $pathString, 'left', false);
                    if ($lastKey == $pathString) {
                        unset($this->__data[$lastKey]);
                        break;
                    } else {
                        $valTmp = getNestedVar($data, $pathString);
                        if ($lastKey != "") {
                            unset($valTmp[$lastKey]);
                            set_value($this->__data, $pathString, $valTmp);
                        }
                    }
                } else {
                    break;
                }
            }
        }
    }

    public function __destruct() {
        $this->__cache->set(self::$CID, $this->__data, C("CHINGSET.EXPIRE")); //缓存仅存在15分钟
    }

    public function __clone() {
        trigger_error("CLONE is not ALLOWED", E_USER_ERROR);
    }

    /**
     * ching会话缓存初始化
     * 返回一个Cache 实例
     *
     * @return /Cache
     */
    public function cache_ching() {
        $type = C('CHINGSET.TYPE');
        $expire = C('CHINGSET.EXPIRE');
        if ($expire === null) {
            $expire = 900; //设定默认超时时间
        }
        switch ($type) {
            case 'Apc':
                //采用Apc缓存机制存储
                return(Cache::getInstance('Apc', array("expire" => $expire)));
                break;
            case 'Xcache':
                //采用Xcache缓存机制存储
                return(Cache::getInstance('Xcache', array("expire" => $expire)));
                break;
            default:
                //默认采用文件存储
                $dir = C('CHINGSET.DIR');
                if ($dir === null) {
                    $dir = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . THINK_PATH . '../Ching/';
                }
                if (!file_exists($dir)) {
                    throw_exception("对不起，Ching会话目录部署不可访问，请检查CHINGSET配置项");
                }
                return(Cache::getInstance('File', array("temp" => $dir, "expire" => $expire)));
                break;
        }
    }

}

?>
