<?php
/**
 * api 私钥 基础类
 */
abstract class Ctrl_Apiatk extends Yaf_Controller_Abstract{
	public $tUid = 0; #认证成功之后，会员id
	public $tMemname = '';
	public function init(){
        $p = $_REQUEST;
		$pAtk = empty($p['atk'])?'':trim($p['atk']);
		if(!Tool_Validate::az09($pAtk)){Tool_Fnc::ajaxMsg('token 格式不正确');}
		if(!$tUid = $this->checktoken($pAtk)){
			Tool_Fnc::ajaxMsg('token 认证失败',-1);
		}
		
		$this->tUid = $tUid;
	}
    //创建TOKEN
    protected function creattoken($mid){
		$tMO = new UserModel;
        return $tMO->creattoken($mid); 
    }  

	//token 认证
	private function checktoken($pToken){
		if(empty($pToken)){return false;}
		$tMO = new SupportworkertokenModel;
		$tRow = $tMO->field('id,token,sw_id,endtime')->where('token = \'' .$pToken.'\'')->fRow();
		if(empty($tRow['id'])){
			return false;
		}
		$tTime = time();
		
		//if(($tTime-$tRow['endtime']) > 0){ return false;}
		
		return $tRow['sw_id'];
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

