<?php
/**
 * 千木控制器扩展
 *
 * @author Richard Lea <chigix@zoho.com>
 * @version 3.1.2 <ThinkPHP控制器扩展，为千木Service架构提供接口，所有千木特定接口，均以public function__chigiXXXX()形式定义>
 */
abstract class ChigiAction extends Action  {
	public function __construct() {
		require_once("functions.php");
		parent::__construct();
	}
	public function __destruct(){
		parent::__destruct();
	}
	public function __chigiFetch(){
		$this->fetch();
	}
	public function __chigiShow($content=""){
		$this->show($content);
	}
	public function __chigiDisplay(){
		$this->display();
	}

//-----------------------------------------------------
//---原生方法继承   --------------------------
//-----------------------------------------------------
    protected function show($content,$charset='',$contentType='',$prefix='') {
        parent::show($content,$charset,$contentType,$prefix);
    }
    protected function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        parent::display($templateFile,$charset,$contentType,$content,$prefix);
    }
    protected function fetch($templateFile='',$content='',$prefix='') {
        parent::fetch($templateFile,$content,$prefix);
    }
}
?>