<?php
/**
 * api 基础类
 */
abstract class Ctrl_Api extends Yaf_Controller_Abstract{

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
}

