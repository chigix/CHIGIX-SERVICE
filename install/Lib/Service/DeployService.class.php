<?php

/**
 * 部署服务类
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class DeployService extends ChigiService {

    public $onSubmit = array(
        'projectPath' => array('require', '对不起，请先选择目标项目'),
        ''
    );

    public function onSubmit($data) {
        //
    }

}

?>
