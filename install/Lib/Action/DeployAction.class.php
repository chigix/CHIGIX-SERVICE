<?php

/**
 * 部署文件生成模块
 *
 * @author Richard Lea <chigix@zoho.com>
 */
class DeployAction extends ChigiAction {

    public function index() {
        $chigi_path = str_replace('\\', '/', CHIGI_PATH);
        $chigi_path = substr($chigi_path, 0, -14);
        $project_list = array();
        foreach (scandir($chigi_path) as $value) {
            if (is_dir($chigi_path . $value)) {
                if (file_exists($chigi_path . $value . '/Conf/config.php')) {
                    array_push($project_list, array($value, $chigi_path . $value . '/'));
                }
            }
        }
        $form = new ChigiForm('Deploy:onSubmit', 'Deploy/config_settings');
        $form->setAction('Index/normal_install');
        $this->assign('PublicView_Header', array(
            'pageName' => ''
        ));
        $this->assign('project_list', $project_list);
        $this->assign('form', $form);
        $this->display();
    }

    public function config_settings() {

    }

}

?>
