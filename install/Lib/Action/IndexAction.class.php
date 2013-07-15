<?php

// 本类由系统自动生成，仅供测试用途
class IndexAction extends ChigiAction {

    public function _initialize() {
        $this->assign('PublicView_Header', array(
            'pageName' => ACTION_NAME
        ));
    }

    public function index() {
        $this->display('index');
    }

    public function quick_install() {
        $this->display();
    }

    /**
     * 普通安装第一页
     */
    public function normal_install_first() {
        $chigi_path = str_replace('\\', '/', CHIGI_PATH);
        $chigi_path = substr($chigi_path, 0, -14);
        $project_list = array();
        foreach (scandir($chigi_path) as $value) {
            if (is_dir($chigi_path . $value)) {
                if (file_exists($chigi_path . $value . '/Conf/config.php')) {
                    array_push($project_list, array($value, $chigi_path . $value . '/Conf/config.php'));
                }
            }
        }
        $this->assign('project_list', $project_list);
        $this->display();
    }

    public function normal_install() {
        if (isset($_POST['project'])) {
            session('project', $_POST['project']);
        }
        $project_root_dir = substr($_SESSION['project'], 0, -15);
        $default_RC_DIR = $project_root_dir . 'Chiji/';
        $default_domain = substr(CHIGI_ROOT_URL, 0, -6)
                . cut_string_using_last('/', substr($project_root_dir, 0, -1), 'right', FALSE);
        $default_RC_URL = $default_domain . '/Chiji';
        $this->assign('IndexView_normalForm', array(
            'default_ching_pool' => $project_root_dir . 'Ching/',
            'default_RC_DIR' => $default_RC_DIR,
            'default_RC_URL' => strtolower($default_RC_URL),
            'default_domain' => $_SERVER['SERVER_NAME'],
            'redirect_link' => redirect_link('Index/normal_final'),
        ));
        $this->display();
    }

    public function normal_final() {
        $project_root_dir = substr($_SESSION['project'], 0, -15);
        // <editor-fold defaultstate="collapsed" desc="初始化整个配置参数$confArr">
        $confArr = array(
            //配置数据库
            'DB_HOST' => 'localhost',
            'DB_NAME' => '',
            'DB_USER' => 'root',
            'DB_PWD' => '',
            'DB_PREFIX' => '',
            //设置主题名（样式库名）
            'DEFAULT_THEME' => $_POST['default_theme'],
            'SHOW_PAGE_TRACE' => 'true',
            'TOKEN_ON' => 'true',
            'TOKEN_NAME' => '__hash__',
            'TOKEN_TYPE' => 'md5',
            'TOKEN_RESET' => 'true',
            //千木服务配置
            'CHIGI_HOST' => $_POST['domain'],
            "CHIGI_AUTH" => empty($_POST['app_code']) ? 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx' : $_POST['app_code'], //应用连接密钥（32位）
            "CHIGI_SUCCESSDIRECT" => $_POST['success_redirect'],
            "CHIGI_ERRORDIRECT" => $_POST['error_redirect'],
            'COM_POST_ON' => 'true',
            //千路前端配置
            'CHIJI' => array(
                'LESS_COMPRESS' => $_POST['less_compress'], //LESS是否压缩
                'JS_DEBUG' => strtolower($_POST['js_debug']), //true会启动SourceMap
                'RC_DIR' => $_POST['CHIJI_RC_DIR'],
            ),
            //模板引擎编译配置
            "TMPL_PARSE_STRING" => array(
                '__CHIJI__' => $_POST['CHIJI_RC_URL'], //最后不带斜杠
            ),
            //URL模式配置
            'URL_MODEL' => 1,
            'URL_CGI_FIX' => 'false',
            'URL_HTML_SUFFIX' => 'html',
            'URL_CASE_INSENSITIVE' => 'true',
            //CHING参数设置
            'CHINGSET' => array(
                'TYPE' => 'File',
                'DIR' => $_POST['ching_pool'],
                'EXPIRE' => $_POST['ching_expire'],
                'DOMAIN' => $_POST['ching_domain'], //设置ching会话SID的作用域名
            ),
        );
        // </editor-fold>
        // <editor-fold defaultstate="collapsed" desc="检查参数正确性">
        $samples_dir = CHIGI_ROOT_PATH . 'samples/';
        recurse_copy($samples_dir . 'Chiji/', $confArr['CHIJI']['RC_DIR']);
        if (!file_exists($confArr['CHINGSET']['DIR'])) {
            mkdir($confArr['CHINGSET']['DIR'], 0666, true);
        }
        // </editor-fold>
        // <editor-fold defaultstate="collapsed" desc="写入conf.php">
        $conf = '<?php' . PHP_EOL . 'return ' . arrayeval($confArr) . ';' . PHP_EOL . '?>';
        $conf = str_replace("'true'", 'true', $conf);
        $conf = str_replace("'false'", 'false', $conf);
        $conf = str_replace("'null'", 'null', $conf);
        file_put_contents($project_root_dir . 'Conf/config.php', $conf);
        // </editor-fold>
        // <editor-fold defaultstate="collapsed" desc="写入alias.php">
        $aliasStr = $str = <<<EOD
<?php
/**
 * 别名定义
 * @author 千木郷 chigix@zoho.com
 */
import('Chigi.Inc.Alias');
return chigi_alias(array());
?>.
EOD;
        file_put_contents($project_root_dir . 'Conf/alias.php', $aliasStr);
        // </editor-fold>
        echo "true";
    }

}