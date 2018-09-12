<?php
/**
 * api 基础类
 */
abstract class Ctrl_Apibusiness extends Yaf_Controller_Abstract{

	public function init(){
		
	}
    //创建TOKEN
    protected function creattoken($mid){
		$tUMO = new UserModel;
        return $tUMO->creattoken($mid); 
    }  
	//token 认证
    protected function checktoken($pToken){
		if(empty($pToken)){return false;}
		$tMO = new UsertokenModel;
		$tRow = $tMO->field('id,token,uid,endtime')->where('token = \'' .$pToken.'\'')->fRow();
		if(empty($tRow['id'])){
			return false;
		}
		$tTime = time();
		
		#if(($tTime-$tRow['endtime']) > 0){ return false;}
		
		return $tRow['uid'];
	}
	/**
	 * 注册变量到模板
	 * @param str|array $pKey
	 * @param mixed $pVal
	 */
	protected function assign($pKey, $pVal = ''){
		if(is_array($pKey)){
			$this->_view->assign($pKey);
			return $pKey;
		}
		$this->_view->assign($pKey, $pVal);
		return $pVal;
	}
	# 验证码
	protected function valiCaptcha(){
		if(!isset($_POST['captcha'], $_SESSION['captcha']) || (strtolower($_SESSION['captcha']) != strtolower($_POST['captcha']))){
			#$this->assign('captchamsg', '验证码错误');
            Tool_Fnc::ajaxMsg('验证码错误');
			return false;
		}
		unset($_SESSION['captcha']);
		return true;
	}
}

