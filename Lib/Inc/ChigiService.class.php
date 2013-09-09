<?php
/**
 * 千木服务根类
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class ChigiService {

    /**
     * 执行成功后跳转页面→指向Index模块中的操作
     *
     * @var String
     */
    protected $successRedirect = "";

    /**
     * 执行失败后跳转页面→指向Index模块中的操作
     *
     * @var String
     */
    protected $errorRedirect = "";

    /**
     * 成功跳转的提示信息
     *
     * @var string
     */
    protected $successAlert = "";

    /**
     * 失败跳转的提示信息
     *
     * @var string
     */
    protected $errorAlert = "";

    /**
     * 地址栏传参
     *
     * @var Array
     */
    protected $addrParams = array();

    /**
     * api地址参数，实例化后会直接变成目标对象
     *
     * @var String API地址，示例："Article.Action.ArticleApi"
     */
    public $apiAction = "";

    /**
     * 32位服务标识码
     *
     * @var string 例：'SugarService'的md5
     */
    public $serviceID = "";

    /**
     * 数据抽象绑定
     *
     * @var array
     */
    protected $__bindings = array();

    /**
     * 权限分配记录表
     * @var Model
     */
    protected $ACL_TBL = "ChigiAccessCtrl";

    /**
     * Access Control List
     *
     * @var array
     */
    protected $ACL = array(
        'PAGE' => array(),
        'VIEW' => array(),
        'FILTER' => array(),
        // 当前服务类全局环境角色对象
        'ROLE' => null
    );

    public function __construct() {
        //初始化绑定api类
        if (!empty($this->apiAction)) {
            $this->request();
            $apiName = cut_string_using_last('.', $this->apiAction, 'right', false);
            $this->apiAction = new $apiName(C('CHIGI_AUTH'));
        }
        isset($_GET['iframe']) ? $this->setDirect($_GET['iframe']) : $this->setDirect();

        //指明权限列表存放数据表
        $this->ACL_TBL = C('ACL_TBL') ? M(C('ACL_TBL')) : M($this->ACL_TBL);
        $this->ACL += array(
            'PAGE' => array(),
            'VIEW' => array(),
            'FILTER' => array(),
            'ROLE' => null
        );
        // 权限列表中，字符串定义的则转化为数组，包含住本权限声明定义所在的服务对象，
        // 布尔值写死在程序中的，则保留布尔值，不允许被覆盖
        // 直接写了数组的，则不转换，相当于作一个默认值
        // <editor-fold defaultstate="collapsed" desc="权限列表规范化">
        foreach ($this->ACL['PAGE'] as $pageName => $accessStatus) {
            if (is_string($accessStatus)) {
                $this->ACL['PAGE'][$pageName] = array($accessStatus, $this);
            }
        }
        foreach ($this->ACL['VIEW'] as $package => $viewArr) {
            if (is_array($viewArr)) {
                foreach ($viewArr as $viewItem => $accessStatus) {
                    $this->ACL['VIEW'][$package . '_' . $viewItem] =
                            is_string($accessStatus) ?
                            array($accessStatus, $this) : $accessStatus;
                }
                unset($this->ACL['VIEW'][$package]);
            }
        }
        // </editor-fold>
        $this->ACL['FILTER']['SELF'] = array();
        //初始化当前服务32位标识码（本地使用）
        $this->serviceID = md5(get_class($this));
        if (!CHING::$COOKIE_STATUS)
            $this->addAddrParams("sid", CHING::$CID);
        if (method_exists($this, '_initialize'))
            $this->_initialize();
    }

    /**
     * 获取当前服务类中指定的数据ID作为角色对象返回
     *
     * @param int $id
     * @return \ChigiRole
     */
    public function getRole($id) {
        $this->bind('id', $id);
        $role_info = $this->request(null, 'chigiRoleName');
        $role = ChigiRole::instance($id, $this, $role_info->name, $role_info->title);
        return $role;
    }

    /**
     * 获取当前服务的所有角色（数据）
     * @param array $data 指定范围集，开发者空参使用，主供拼装数据使用，开发者无需考虑
     * @return array 一维数值数组（元素为ChigiRole）
     */
    public function getAllRoles($data = null) {
        $result = $this->request($data, 'getAllDatas')->__;
        $arr = array();
        foreach ($result as $data) {
            $new_role = ChigiRole::instance($data['id'], $this, $data['name'], $data['title']);
            if (!in_array($new_role, $arr)) {
                array_push($arr, $new_role);
            }
        }
        return $arr;
    }

    /**
     * 为当前服务类的权限机制指定角色对象
     *
     * @param \ChigiRole $role
     * @param \ChigiService
     */
    public function setRole($role) {
        $this->ACL['ROLE'] = $role;
        return $this;
    }

    /**
     * 获取当前服务类全局环境唯一注册对象
     * @return ChigiRole
     */
    public function getCurrentRole() {
        if ($this->ACL['ROLE']) {
            return $this->ACL['ROLE'];
        } else {
            $args = func_get_args();
            $this->ACL['ROLE'] = $this->getRole($args[0]);
            return $this->ACL['ROLE'];
        }
    }

    /**
     * 根据给出的$me_role 角色，来获取其以上的 N 级父元素
     * @param ChigiRole $me_role 作为往上查询的根据的底层角色
     * @param int $max_level N的最大值，默认为5，0即不往上查询
     * @return array 一维数值型角色数组，键值表示其往上查询的级别
     */
    public function getParents($me_role, $max_level = 5) {
        if (get_class($this) !== get_class($me_role->roleService)) {
            trace('[0]目标角色所属服务非本服务：' . get_class($this), '', 'NOTIC');
        }
        $this->bind('id', $me_role->id);
        $parents = $this->request($max_level, 'chigiParentsFetch')->__;
        foreach ($parents as $key => $value) {
            /* @var $value array */
            $parents[$key] = ChigiRole::instance($value['id'], $this, $value['name'], $value['title']);
        }
        return $parents;
    }

    /**
     * 获取当前角色权限
     *
     * @param string $level 'PAGE'/'VIEW'/'FILTER'/'ALL'
     * @return array
     */
    public function getACL($level = 'ALL') {
        if ($level === 'ALL') {
            return array(
                'PAGE' => $this->ACL['PAGE'],
                'VIEW' => $this->ACL['VIEW'],
                'FILTER' => $this->ACL['FILTER'],
            );
        } else {
            return $this->ACL[$level];
        }
    }

    /**
     * 检查PAGE级权限统一方法
     *
     * @param string $pageName PAGE资源名称，声明于 $resource 服务类中
     * @param ChigiRole $role 要查询的目标角色
     * @param ChigiService $resource PAGE资源所在的服务类
     * @return bool
     */
    public function chigiCheckPage($pageName, $role, $resource) {
        $condition = array(
            'role_service' => $role->roleService->serviceID,
            'role_id' => $role->id,
            'node_service' => $resource->serviceID,
            'node_name' => md5($pageName),
            'node_level' => 'PAGE',
        );
        $result = $this->ACL_TBL->where($condition)->find();
        return $result ? TRUE : FALSE;
    }

    /**
     * 检查VIEW级权限统一方法
     * @param string $viewName VIEW资源名称，声明于 $resource 服务类中
     * @param ChigiRole $role 要查询的目标角色
     * @param ChigiService $resource VIEW资源所在的服务类
     * @return bool
     */
    public function chigiCheckView($viewName, $role, $resource) {
        $condition = array(
            'role_service' => $role->roleService->serviceID,
            'role_id' => $role->id,
            'node_service' => $resource->serviceID,
            'node_name' => md5($viewName),
            'node_level' => 'VIEW',
        );
        $result = $this->ACL_TBL->where($condition)->find();
        return $result ? TRUE : FALSE;
    }

    /**
     * 为目标跳转地址添加地址参数
     * @param string $key
     * @param string $value
     * @return \ChigiService
     */
    public function addAddrParams($key, $value) {
        $this->addrParams[$key] = $value;
        return $this;
    }

    public function setDirect($successAdd = null, $errorAdd = null) {
        $this->setSuc($successAdd);
        $this->setErr($errorAdd);
        return $this;
    }

    public function setSuc($addr = null) {
        if ($addr !== null) {
            $this->successRedirect = $addr;
        } elseif (ching("CHIGI_SUCCESSDIRECT") !== null) {
            $this->successRedirect = ching("CHIGI_SUCCESSDIRECT");
            ching("CHIGI_SUCCESSDIRECT", NULL);
        } elseif ($this->successRedirect != "") {
            ;
        } else {
            $this->successRedirect = C("CHIGI_SUCCESSDIRECT");
        }
        return $this;
    }

    public function setErr($addr = null) {
        if ($addr !== null) {
            $this->errorRedirect = $addr;
        } elseif (ching("CHIGI_ERRORDIRECT") !== null) {
            $this->errorRedirect = ching("CHIGI_ERRORDIRECT");
            ching("CHIGI_ERRORDIRECT", NULL);
        } elseif ($this->errorRedirect != "") {
            ;
        } else {
            $this->errorRedirect = C("CHIGI_ERRORDIRECT");
        }
        return $this;
    }

    /**
     * 返回成功页面URL
     *
     * @param string $alertMsg 指定Alert内容
     * @return string
     */
    public function successDirectLink($alertMsg = "") {
        if (!empty($alertMsg)) {
            $alert = new ChigiAlert($alertMsg, 'alert-success');
            $alert->alert();
        } elseif (!empty($this->successAlert)) {
            $alert = new ChigiAlert($this->successAlert, 'alert-success');
            $alert->alert();
        }
        return redirect_link($this->successRedirect, $this->addrParams);
    }

    /**
     * 跳转至执行成功页面
     *
     * @param string $alertMsg 指定Alert内容
     */
    public function successDirectHeader($alertMsg = "") {
        redirect($this->successDirectLink($alertMsg), 0);
    }

    /**
     * 返回失败页面URL
     *
     * @param string $alertMsg 指定Alert内容
     * @return string
     */
    public function errorDirectLink($alertMsg = "") {
        if (!empty($alertMsg)) {
            $alert = new ChigiAlert($alertMsg, 'alert-error');
            $alert->alert();
        } elseif (!empty($this->errorAlert)) {
            $alert = new ChigiAlert($this->errorAlert, 'alert-error');
            $alert->alert();
        }
        return redirect_link($this->errorRedirect, $this->addrParams);
    }

    /**
     * 跳转至执行失败页面
     *
     * @param string $alertMsg 指定Alert内容
     */
    public function errorDirectHeader($alertMsg = "") {
        redirect($this->errorDirectLink($alertMsg), 0);
    }

    /**
     * Alert推送操作【支持链写】
     *
     * @param string $message
     * @param string $option
     * @return \ChigiService
     */
    public function pushAlert($message = "", $option = "alert-error") {
        if (empty($message)) {
            return $this;
        }
        $serviceAlert = service("Alert");
        $serviceAlert->pushSet($message, $option);
        return $this;
    }

    /**
     * 设置跳转成功提示信息
     *
     * @param string $msg
     */
    protected function setSucAlert($msg = "") {
        $this->successAlert = $msg;
        return $this;
    }

    /**
     * 设置跳转失败提示信息
     *
     * @param string $msg
     */
    protected function setErrAlert($msg = "") {
        $this->errorAlert = $msg;
        return $this;
    }

    /**
     * 环境保障操作【链写】
     *
     * 使用示例：
     * $service->under('Login')->setDirect('Login/index')->pushAlert("对不起，请先登录")->check();
     *
     * @param string $method
     * @return \underCheck
     */
    public function under($method) {
        $method = 'under' . $method;
        $result = $this->$method();
        $underObj = new underCheck($result);
        return $underObj;
    }

    /**
     * API请求
     *
     * @param array|string|int $data
     * @param string $method
     * @return \ChigiReturn
     */
    public function request($data = array(), $method = '') {
        static $api = null;
        if (!empty($method)) {
            $toSend = array(
                'data' => $data,
                'user_agent' => array(
                    'ip' => getClientIp(),
                    'bot' => CHING::$BOT,
                    '__' => $_SERVER['HTTP_USER_AGENT']
                ),
                'bindings' => $this->__bindings
            );
            $response = $api->response($toSend, $method);
            $this->__bindings = $response['bindings'];
            $result = new ChigiReturn($response['data']);
            return $result;
        } elseif (is_null($api)) {
            //初始化API
            import($this->apiAction);
            $apiName = cut_string_using_last('.', $this->apiAction, 'right', false);
            $api = new $apiName(C('CHIGI_AUTH'));
        } else {
            throw_exception(get_class($this) . "API不正确，请检查地址");
        }
    }

    /**
     * 原型key-value数据绑定
     *
     * @param string $key
     * @param string $value
     * @return mixed 上次的目标key值
     */
    public function bind() {
        $argNum = func_num_args();
        $arg = func_get_args();
        switch ($argNum) {
            case 1:
                return isset($this->__bindings[$arg[0]]) ? $this->__bindings[$arg[0]] : null;
                break;
            case 2:
                $temp = isset($this->__bindings[$arg[0]]) ? $this->__bindings[$arg[0]] : null;
                $this->__bindings[$arg[0]] = $arg[1];
                return $temp;
                break;
            default:
                return;
                break;
        }
    }

}

/**
 * 环境保障链式操作类
 *
 */
class underCheck {

    /**
     * 当前环境是否达标，true为达标；false为不达标，将进行跳转。
     *
     * @var boolean
     */
    private $under_status = false;
    private $addr = '';
    private $params = array();
    private $alert = null;

    /**
     * 构造传入返回标准结果数组
     *
     * @param array $result
     */
    public function __construct($result) {
        if (isset($_GET['iframe'])) {
            $this->addAddrParams('iframe', $_GET['iframe']);
        } else {
            $this->addAddrParams('iframe', (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        }
        if (is_int($result)) {
            $result == 1 ? $this->under_status = true : $this->under_status = false;
        } elseif (is_bool($result)) {
            $this->under_status = $result;
        } elseif (is_array($result)) {
            getNumHundreds($result['status']) == 2 ? $this->under_status = true : $this->under_status = false;
            if (getNumTens($result['status']) == 2) {
                $this->pushAlert($result['data']);
            }
        } else {
            throw_exception("underCheck传参错误");
        }
    }

    /**
     * 设置目标跳转地址
     *
     * @param string $addr
     * @return \underCheck
     */
    public function setDirect($addr) {
        $this->addr = $addr;
        return $this;
    }

    /**
     * 移除指定URL参数
     *
     * @param string $name
     * @return \underCheck
     */
    public function rmAddrParam($name) {
        $this->params[$name] = null;
        return $this;
    }

    /**
     * 添加地址栏参数
     *
     * @param type $key
     * @param type $value
     * @return \underCheck
     */
    public function addAddrParams($key, $value) {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * Alert推送操作【支持链写】
     *
     * @param string $message
     * @param string $option
     * @return \underCheck
     */
    public function pushAlert($message = "") {
        if (empty($message) || $this->under_status == true) {
            return $this;
        }
        $this->alert = $message;
        return $this;
    }

    /**
     * 手动检测环境，若不达标则直接跳转
     */
    public function check() {
        if ($this->under_status == false) {
            if (!empty($this->alert) && !$this->under_status == true) {
                $alert = new ChigiAlert($this->alert, 'alert-error');
                $alert->alert();
            }
            redirectHeader($this->addr, $this->params);
        }
    }

}

?>
