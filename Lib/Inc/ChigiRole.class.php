<?php

/**
 * 千木角色处理根类
 *
 * @author Richard Lea  <chigix@zoho.com>
 */
class ChigiRole {

    /**
     * 角色ID
     * @var int
     */
    public $id;

    /**
     * 角色所在服务对象
     *
     * @var ChigiService
     */
    public $roleService;

    /**
     * 角色名称（英文）
     * @var string
     */
    public $roleName;

    /**
     * 角色标题（任意语种）
     * @var string
     */
    public $roleTitle;

    /**
     * 角色所属父角色列表<br>
     * 其中，每个元素都是 ChigiRole 角色对象，一维数组数组
     * @var array
     */
    public $belongs_to = array();

    /**
     * 全局注册角色管理器
     * $Manager['ServiceID']['DataId']
     * @var array
     */
    public static $Manager = array();

    /**
     * Access Control List
     * @var array
     */
    protected $ACL = array(
        'PAGE' => array(),
        'VIEW' => array(),
        'FILTER' => array(),
    );

    /**
     * 权限分配表名
     * @var Model
     */
    protected $ACL_TBL = "ChigiAccessCtrl";

    /**
     * 服务拼装关联记录表
     *
     * @var Model
     */
    protected $COUPLE_TBL = "ChigiCouple";

    /**
     * 千木角色构造器
     * @param int $id 角色ID
     * @param ChigiService $role_service 所属服务类
     * @param string $role_name 角色名称
     * @param string $role_title 角色标题
     */
    private function __construct($id, $role_service, $role_name, $role_title) {
        //指明权限列表存放数据表模型
        $this->ACL_TBL = C("ACL_TBL") ? M(C("ACL_TBL")) : M($this->ACL_TBL);
        // 指明拼装列表存放数据表模型
        $this->COUPLE_TBL = C("COUPLE_TBL") ? M(C("COUPLE_TBL")) : M($this->COUPLE_TBL);
        $this->id = (int) $id;
        $this->roleService = $role_service;
        $this->roleName = $role_name;
        $this->roleTitle = $role_title;
    }

    /**
     * 千木角色获取器
     * @param int $id 角色ID
     * @param ChigiService $role_service 所属服务类
     * @param string $role_name 角色名称，若不给定，则默认通过其服务获取name值
     * @param string $role_title 角色标题，若不给定，则默认通过其服务获取title值
     * @return ChigiRole
     */
    public static function instance($id, $role_service, $role_name = null, $role_title = null) {
        if (isset(self::$Manager[$role_service->serviceID][$id])) {
            return self::$Manager[$role_service->serviceID][$id];
        } else {
            if (empty($role_name) || empty($role_title)) {
                $role_service->bind('id', $id);
                $role_info = $role_service->request(null, 'chigiRoleName');
                $role_name = $role_info->name;
                $role_title = $role_info->title;
            }
            $role = new ChigiRole($id, $role_service, $role_name, $role_title);
            self::$Manager[$role_service->serviceID][$id] = $role;
            return $role;
        }
    }

    /**
     * 获取当前角色权限
     * @param string $level 'PAGE'/'VIEW'/'FILTER'/'ALL'
     * @return array
     */
    public function getACL($level = 'ALL') {
        if ('ALL' === $level) {
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
     * 获取本角色指定 $data 资源的权限信息
     * @param string $level 'PAGE'/'VIEW'/'FILTER'
     * @param string $data 要查询的目标权限名称
     * @param ChigiRole $fromRole 本方法递归时调用，外部调用时无需此参数
     * @return boolean
     */
    public function getAccessStatus($level, $data, $fromRole = null) {
        if (!is_string($data)) {
            $tracks = debug_backtrace();
            throw_exception('getAccessStatus方法的 $data 传值类型有误，请检查：' . $tracks[0]['file'] . ':' . $tracks[0]['line']);
        }
        /* @var $fromRecord ChigiRole */
        static $fromRecord = null;
        if (is_null($fromRole)) {
            // 首次运行，将 $fromRecord 还原为当前对象，以开始随历史更新记录
            $fromRecord = $this;
        }
        $fromRole = is_null($fromRole) ? $this : $fromRole;
        if (isset($this->ACL[$level][$data])) {
            // 当前角色中已有目标权限记录信息
            if (
                    is_bool($this->ACL[$level][$data]) //布尔则不允许覆盖
                    || $this->ACL[$level][$data][0] === TRUE //数组第一个值为TRUE则跳过
            ) {
                return is_array($this->ACL[$level][$data]) ? $this->ACL[$level][$data][0] : $this->ACL[$level][$data];
            } elseif (is_array($this->ACL[$level][$data]) && is_string($this->ACL[$level][$data][0])) {
                $fromRecord = $this;
                $method_name = $this->ACL[$level][$data][0];
                $this->ACL[$level][$data][0] = $fromRole->roleService->$method_name($data, $fromRole, $this->ACL[$level][$data][1]);
                if (!$this->ACL[$level][$data][0]) {
                    $this->ACL[$level][$data] = -1;
                    return $this->getAccessStatus($level, $data, $this);
                }
                return $this->ACL[$level][$data];
            } elseif ($this->ACL[$level][$data] === -1) {
                // -1 是硬要求从父角色开始查询，一般针对情况如下：
                // 当前角色所属服务中无此权限的定义声明 或 当前角色中此权限为 非绝对 FALSE
                // 则从父角色中寻找权限值
                $this->ACL[$level][$data] = FALSE;
                foreach ($this->belongs_to as $p_role) {
                    /* @var $p_role ChigiRole */
                    $result_from_p_role = $p_role->getAccessStatus($level, $data, $this);
                    if (
                            is_bool($result_from_p_role) //布尔则不允许覆盖
                            || $result_from_p_role[0] === TRUE //数组第一个值为TRUE则跳过
                    ) {
                        $this->ACL[$level][$data] = is_array($result_from_p_role) ? $result_from_p_role[0] : $result_from_p_role;
                        return $this->ACL[$level][$data];
                    }
                }
                // 若能执行至此，说明循环结束，且所有父级元素的最终结果没有TRUE
                // 则下面作最后一次本角色中的判断：
                $serviceACL = $fromRecord->roleService->getACL($level);
                $method_name = $serviceACL[$data][0];
                $this->ACL[$level][$data] = $fromRecord->roleService->$method_name($data, $this, $serviceACL[$data][1]);
                if ('ChigiService' === get_parent_class($this)) {
                    // 若为基本服务类角色，则直接将此次权限结果以绝对布尔存入当前角色中
                    $this->ACL[$level][$data] =
                            is_array($this->ACL[$level][$data]) ?
                            $this->ACL[$level][$data][0] : $this->ACL[$level][$data];
                }
                return $this->ACL[$level][$data];
            } else {
                unset($this->ACL[$level][$data]);
                $this->ACL[$level][$data] = $this->getAccessStatus($level, $data, $this);
                return $this->ACL[$level][$data];
            }
        } else {
            // 当前角色中尚无目标PAGE权限记录，需从服务中获取完毕后，重新进行判定
            $serviceACL = $this->roleService->getACL($level);
            if (isset($serviceACL[$data])) {
                // 当前角色所属服务类中有定义声明此权限
                $this->ACL[$level][$data] = $serviceACL[$data];
            } else {
                // 当前角色所属服务类中无定义声明此权限
                // 则从父角色所在服务中寻找此权限声明
                $this->ACL[$level][$data] = -1;
            }
            return $this->getAccessStatus($level, $data, $this);
        }
        // 或当前角色中的目标PAGE权限记录为非绝对 FALSE
    }

    /**
     * 获取本角色指定 $pageName 资源的权限信息
     * @param string $pageName
     * @return boolean
     */
    public function getPageAccessStatus($pageName) {
        $result = $this->getAccessStatus('PAGE', $pageName);
        if (is_array($result)) {
            // 若为数组表示，则获取首元素中的布尔值
            $result = $result[0];
        }
        return $result ? TRUE : FALSE;
    }

    /**
     * 获取本角色指定 $packageName:$viewName 的资源的权限信息
     * @param string $packageName
     * @param string $viewName
     * @return boolean
     */
    public function getViewAccessStatus($packageName, $viewName) {
        $result = $this->getAccessStatus('VIEW', $packageName . '_' . $viewName);
        if (is_array($result)) {
            // 若为数组表示，则获取首元素中的布尔值
            $result = $result[0];
        }
        return $result ? TRUE : FALSE;
    }

    /**
     * 将当前角色中的权限控制列表推送到 $targetRole 角色中
     * @param ChigiRole $targetRole
     */
    public function passACL($targetRole) {
        $targetRole->pushACL($this->ACL, $this);
        return $this;
    }

    /**
     * 推入权限列表到当前角色
     * @param array $acl 完整的三级关联权限数组
     * @param ChigiRole $fromRole 权限列表所来自的角色
     * @return ChigiRole
     */
    public function pushACL($acl, $fromRole) {
        if (isset($acl['PAGE'])) {
            $this->pushACLPage($acl['PAGE']);
        }
        if (isset($acl['VIEW'])) {
            $this->pushACLView($acl['VIEW']);
        }
        if (isset($acl['FILTER'])) {
            $this->pushACLFilter($acl['FILTER']);
        }
        return $this;
    }

    /**
     * 推VIEW级权限列表到当前角色
     * @param array $viewCl VIEW级二维关联权限列表
     * @param ChigiRole $fromRole 权限列表所来自的角色
     * @return ChigiRole
     */
    public function pushACLView($viewCl, $fromRole) {
        foreach ($viewCl as $package => $pageArr) {
            foreach ($pageArr as $viewItem => $accessStatus) {
                /* @var $accessStatus array */
                // 关于$accessStatus的形式及定义是由每个服务类在构造时处理的结果，
                // 详见服务根类关于ACL列表单项信息的处理算法 及 结果形式
                if (isset($this->ACL['VIEW'][$package][$viewItem])) {
                    // 已有此条权限记录信息的
                    if (
                            is_bool($this->ACL['VIEW'][$package][$viewItem]) //布尔则不允许覆盖
                            || $this->ACL['VIEW'][$package][$viewItem][0] === TRUE //数组第一个值为TRUE则跳过
                    ) {
                        continue;
                    }
                }
                if (is_bool($accessStatus)) {
                    // 本角色中当前没有此权限记录，而推送来的权限是绝对布尔值，则直接使用，不作覆盖
                    $this->ACL['VIEW'][$package][$viewItem] = $accessStatus;
                    continue;
                }
                // 尚无此条权限记录信息的，或者此推送来的数据权限记录信息也仍为字符串
                // 则需要先进行处理成相对布尔，供下一个if语句接收，以合并入当前角色中
                // $accessStatus[0] 为相对布尔值，此值可覆盖；
                // $accessStatus[1] 为资源所在服务对象，以便传入chigiCheckView第三参数
                if (is_array($accessStatus) && is_string($accessStatus[0])) {
                    // 被推入的数据中，亦仍无详细明确的权限 bool 信息
                    $accessStatus[0] = $fromRole->roleService->$accessStatus[0]("$package:$viewItem", $fromRole, $accessStatus[1]);
                }
                if (is_array($accessStatus) && is_bool($accessStatus[0])) {
                    // 现被推入的数据中，已有明确的 bool 信息，则合并入当前角色中
                    $this->ACL['VIEW'][$package][$viewItem] = $accessStatus;
                }
            }
        }
        return $this;
    }

    /**
     * 推送PAGE级权限列表到当前角色
     * @param array $pageCl PAGE级一维关联权限列表
     * @param ChigiRole $fromRole 权限列表所来自的角色
     * @return ChigiRole
     */
    public function pushACLPage($pageCl, $fromRole) {
        foreach ($pageCl['PAGE'] as $pageName => $accessStatus) {
            /* @var $accessStatus array */
            // 关于$accessStatus的形式及定义是由每个服务类在构造时处理的结果，
            // 详见服务根类关于ACL列表单项信息的处理算法 及 结果形式
            if (isset($this->ACL['PAGE'][$pageName])) {
                // 已经有此条权限记录信息的
                if (
                        is_bool($this->ACL['PAGE'][$pageName]) //布尔则不允许覆盖
                        || $this->ACL['PAGE'][$pageName][0] === TRUE //数组第一个值为TRUE则跳过
                ) {
                    continue;
                }
            }
            if (is_bool($accessStatus)) {
                // 本角色中当前没有此权限记录，而推送来的权限是绝对布尔值，则直接使用，不作覆盖
                $this->ACL['PAGE'][$pageName] = $accessStatus;
                continue;
            }
            // 尚无此条权限记录信息的，或者此推送来的数据权限记录信息也仍为字符串
            // 则需要先进行处理成相对布尔，供下一个if语句接收，以合并入当前角色中
            // $accessStatus[0] 为相对布尔值，此值可覆盖；
            // $accessStatus[1] 为资源所在服务对象，以便传入chigiCheckView第三参数
            if (is_array($accessStatus) && is_string($accessStatus[0])) {
                // 被推入的数据中，亦仍无详细明确的权限 bool 信息
                $accessStatus[0] = $fromRole->roleService->$accessStatus[0]($pageName, $fromRole, $accessStatus[1]);
            }
            if (is_array($accessStatus) && is_bool($accessStatus[0])) {
                // 现被推入的数据中，已有明确的 bool 信息，则合并入当前角色中
                $this->ACL['PAGE'][$pageName] = $accessStatus;
            }
        }
        return $this;
    }

    /**
     * 推送DATA级权限列表到当前角色
     * @param array $dataCl
     * @param ChigiRole $fromRole 权限列表所来自的角色
     * @return ChigiRole
     */
    public function pushACLFilter($dataCl, $fromRole) {
        //
        return $this;
    }

    /**
     * 将指定权限列表写入指定等级中
     *
     * @param string $level 'PAGE'/'VIEW'/'FILTER'
     * @param array $list 要写入的权限列表
     */
    public function setACL($level, $list) {
        $this->ACL[$level] = $list;
    }

    /**
     * 获取当前服务类 View 级输出权限
     *
     * @return array
     */
    public function getViewAccessList($resource = null) {
        $this->refreshBelongs();
        $acl = $this->roleService->getACL('VIEW');
        if ($acl) {
            foreach ($acl as $key => $value) {
                if (!$value) {
                    continue;
                }
                $tmp_item = is_bool($value) ? $value : $this->getAccessStatus('VIEW', $key);
                $acl[$key] = is_array($tmp_item) ? $tmp_item[0] : $tmp_item;
            }
        }
        if (!empty($this->belongs_to)) {
            foreach ($this->belongs_to as $role) {
                /* @var $role ChigiRole */
                $parent_acl = $role->getViewAccessList();
                // <editor-fold defaultstate="collapsed" desc="父级权限向当前$acl合并">
                if (!empty($parent_acl)) {
                    foreach ($role->getViewAccessList() as $key => $value) {
                        if (isset($acl[$key]) && $acl[$key] === TRUE) {
                            //跳过
                        } else {
                            $acl[$key] = $value;
                        }
                    }
                }
                // </editor-fold>
            }
        }
        return $acl;
    }

    /**
     * 获取分配模式下的权限控制列表
     * @param ChigiRole $resource 供递归使用
     */
    public function getAssignAccessList($resource = null) {
        $acl = $this->roleService->getACL();
        return $acl;
    }

    /**
     * 页面级访问权限检查，具有自动祖先级权限继承支持
     * @param string $pageName
     * @param string $errorAdd
     * @param string $errAlert
     * @return boolean
     */
    public function checkPageAccess($pageName, $errorAdd = null, $errAlert = null) {
        $result = $this->getAccessStatus('PAGE', $pageName);
        if (is_array($result)) {
            // 若为数组表示，则获取首元素中的布尔值
            $result = $result[0];
        }
        if (!$result) {
            redirect($this->roleService->errorDirectLink($errAlert), 0);
        }
        return $result;
    }

    /**
     * 将父级角色对象 加入 当前对象 belongs_to 列表中
     *
     * @param ChigiRole $service
     * @return ChigiRole
     */
    public function addToBelongs($role) {
        if (!in_array($role, $this->belongs_to)) {
            array_push($this->belongs_to, $role);
        }
        return $this;
    }

    /**
     * 返回本角色的所有父角色数组
     * @return array
     */
    public function getBelongs() {
        return $this->belongs_to;
    }

    /**
     * 通过指定服务，获取所有属性该服务的父角色数组
     * @param string|ChigiService $service 指定服务对象，<br>可以用服务名称，不要带上 Service后缀
     * @return array
     */
    public function getBelongsByService($service) {
        if (is_string($service)) {
            // 将字符串转换成 service 对象
            $service = service($service);
        }
        $parents = array();
        foreach ($this->belongs_to as $value) {
            /* @var $value ChigiRole */
            if ($value->roleService->serviceID == $service->serviceID) {
                $parents[] = $value;
            }
        }
        return $parents;
    }

    /**
     * 刷新当前角色的父级所属角色列表
     * @return \ChigiRole
     */
    public function refreshBelongs() {
        $linked_serviced = service('ID_ALL');
        // <editor-fold defaultstate="collapsed" desc="跨服务中查询父角色">
        $condition = array(
            'resource_service' => $this->roleService->serviceID,
            'resource_id' => $this->id
        );
        $select_result = $this->COUPLE_TBL->where($condition)->field('couple_service,main_id')->select();
        if ($select_result) {
            $select_result = array_unique($select_result);
            foreach ($select_result as $value) {
                // $value=array('main_service'=>'servieID','main_id'=>123);
                if (isset($linked_serviced[$value['couple_service']])) {
                    // 目前已连接此服务，尝试从该服务中获取角色
                    /* @var $temp_role ChigiRole */
                    $temp_role = $linked_serviced[$value['couple_service']]->getRole($value['main_id']);
                    $this->addToBelongs($temp_role);
                } else {
                    // 当前还没有连接此服务，跳过
                    continue;
                }
            }
        }
        // </editor-fold>
        // <editor-fold defaultstate="collapsed" desc="自服务中查询父角色">
        $parent_roles = $this->roleService->getParents($this, 1);
        if (!empty($parent_roles)) {
            // 角色数组非空，即确实查到了有父一级角色
            $this->addToBelongs($parent_roles[0]);
        }
        // </editor-fold>
        return $this;
    }

}

?>
