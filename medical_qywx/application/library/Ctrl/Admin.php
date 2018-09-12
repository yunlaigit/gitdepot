<?php
/**
 * 管理类
 */
class Ctrl_Admin extends Ctrl_Base {
	# 管理员
	protected $_auth = 5;
	protected $disableAction = array();	//禁用方法
	protected $disableController = false;	//禁用控制器
	protected $disableMethodPost = array();	//禁用POST提交控制器

	public function init(){
        // if(!isset($_SESSION['admin'])){echo '<script>top.location.href="/admin_login/quit";</script>';exit();}
	}
	/**
	 * Ajax 保存字段
	 */
	public function ajaxsaveAction($table) {
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			$this->_table2obj($table);
			$table->update($_POST);
		}
		exit;
	}

}
