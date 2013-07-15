<?php

/**
 * 千木服务耦合机制类
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class ChigiCouple extends ChigiService {

    /**
     * 主服务
     *
     * @var ChigiService
     */
    protected $serviceMain;

    /**
     * 资源服务
     *
     * @var ChigiService
     */
    protected $serviceResource;

    /**
     * 服务拼装关联记录表
     *
     * @var Model
     */
    protected $COUPLE_TBL = "ChigiCouple";
    protected $ACL = array(
        'PAGE' => array(),
        'VIEW' => array(),
        'FILTER' => array(),
        // 当前服务类全局环境角色
        'ROLE' => null
    );

    public function __construct() {
        $this->serviceMain = service($this->serviceMain);
        $this->serviceResource = service($this->serviceResource);
        $this->ACL += array(
            'PAGE' => array(),
            'VIEW' => array(),
            'FILTER' => array(),
            'ROLE' => null
        );
        //指明拼装关联数据存放的数据表名
        $this->COUPLE_TBL = C('COUPLE_TBL') ? M(C('COUPLE_TBL')) : M($this->COUPLE_TBL);
        parent::__construct();
    }

    /**
     * 获取当前服务类中指定的数据ID作为角色对象返回
     *
     * @param int $id 指定数据ID，拼装类中沿用主服务ID
     * @return \ChigiRole
     */
    public function getRole($id) {
        $this->serviceMain->bind('id', $id);
        $role_info = $this->serviceMain->request(null, 'chigiRoleName');
        $role = ChigiRole::instance($id, $this, $role_info->name, $role_info->title);
        return $role;
    }

    /**
     * 获取当前服务类全局环境唯一注册对象
     * @return ChigiRole
     */
    public function getCurrentRole() {
        $data = func_get_args();
        if ($this->ACL['ROLE']) {
            return $this->ACL['ROLE'];
        } else {
            $resource_role = $this->serviceResource->getRole($data[1]);
            $main_role = $this->serviceMain->getRole($data[0]);
            $this->serviceResource->setRole($resource_role);
            $this->serviceMain->setRole($main_role);
            $role = $this->getRole($data[0]);
            $this->setRole($role);
            return $role;
        }
    }

    /**
     * 根据给出的$me_role 角色，来获取其以上的 N 级父元素
     * @param ChigiRole $me_role 作为往上查询的根据的底层角色
     * @param int $max_level N的最大值，默认为5，0即不往上查询
     * @return array 一维数值型角色数组，键值表示其往上查询的级别
     */
    public function getParents($me_role, $max_level = 5) {
        $this->serviceMain->bind('id', $me_role->id);
        $parents = $this->serviceMain->request($max_level, 'chigiParentsFetch')->__;
        foreach ($parents as $key => $value) {
            /* @var $value array */
            $parents[$key] = ChigiRole::instance($value['id'], $this, $value['name'], $value['title']);
        }
        return $parents;
    }

    /**
     * 获取当前服务的所有角色（数据）
     * @param array $data 指定范围集，开发者空参使用，主供拼装数据使用，开发者无需考虑
     * @return array 一维数值数组（元素为ChigiRole）
     */
    public function getAllRoles($data = null) {
        $condition = array(
            'couple_service' => $this->serviceID,
        );
        $result = $this->COUPLE_TBL->field('main_id')->where($condition)->select();
        foreach ($result as $key => $value) {
            $result[$key] = $value['main_id'];
        }
        $result = $this->serviceMain->request($result, 'getAllDatas')->__;
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
     * API请求
     *
     * @param array|string|int $data
     * @param string $method
     * @return \ChigiReturn
     */
    public function request($data = array(), $method = '') {
        return $this->serviceMain->request($data, $method);
    }

    /**
     * 根据指定的main服务的ID，获取资源服务的对应ID数组，若指明只取一个结果，则只返回一个整型的ID
     *
     * @param int $main_id 给出作为查询依据的main服务ID
     * @param bool $limit 若为TRUE，则只返回一个整型的ID
     * @return array|int|null
     */
    public function getResourceId($main_id, $limit = false) {
        $condition = array(
            'main_id' => $main_id ? $main_id : 0,
            'main_service' => $this->serviceMain->serviceID
        );
        if ($limit) {
            $result = $this->COUPLE_TBL->where($condition)->field('resource_id')->find();
            return $result ? (int) $result['resource_id'] : NULL;
        } else {
            $result = $this->COUPLE_TBL->where($condition)->field('resource_id')->select();
            foreach ($result as $key => $value) {
                $result[$key] = (int) $value['resource_id'];
            }
            return $result ? $result : array();
        }
    }

    /**
     * 根据指定的资源服务的ID，获取main服务的对应ID数组，若指明只取一个结果，则只返回一个整型的ID
     *
     * @param int $resource_id
     * @param bool $limit
     * @return array|int|null 返回数组为数值数组
     */
    public function getMainId($resource_id, $limit = false) {
        $condition = array(
            'resource_id' => $resource_id ? $resource_id : 0,
            'resource_service' => $this->serviceResource->serviceID
        );
        if ($limit) {
            $result = $this->COUPLE_TBL->where($condition)->field('main_id')->find();
            return $result ? (int) $result['main_id'] : NULL;
        } else {
            $result = $this->COUPLE_TBL->where($condition)->field('main_id')->select();
            if ($result) {
                foreach ($result as $key => $value) {
                    $result[$key] = (int) $value['main_id'];
                }
            }
            return $result ? $result : array();
        }
    }

    /**
     * 获取当前角色权限
     *
     * @param string $level 'PAGE'/'VIEW'/'FILTER'/'ALL'
     * @return array
     */
    public function getACL($level = 'ALL') {
        if ($level === 'ALL') {
            $acl['PAGE'] = $this->getACL('PAGE');
            $acl['VIEW'] = $this->getACL('VIEW');
            $acl['FILTER'] = $this->getACL('FILTER');
        } else {
            $acl = $this->ACL[$level];
            $acl += $this->serviceResource->getACL($level);
        }
        return $acl;
    }

    public function __call($name, $arguments) {
        //调用当前拼装服务类不存在的方法，则去被拼装服务类中查询及调用
        if (method_exists($this->serviceMain, $name)) {
            return call_user_func_array(array($this->serviceMain, $name), $arguments);
        } else {
            return call_user_func_array(array($this->serviceResource, $name), $arguments);
        }
    }

}

?>
