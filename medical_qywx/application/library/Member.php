<?php
class Member{
	//会员处理
	/* public function __construct(){
		$this->
	} */
	static $type = array(
		1=>array('type'=>1, 'credit'=>'', 'description'=>"登陆积分"),
		2=>array('type'=>2, 'credit'=>'', 'description'=>'充值积分'),
		3=>array('type'=>3, 'credit'=>50, 'description'=>'邀请好友积分'),
		4=>array('type'=>4, 'credit'=>'', 'description'=>'邀请好友满一百积分'),
		5=>array('type'=>5, 'credit'=>'', 'description'=>'YBC交易积分'),
		6=>array('type'=>6, 'credit'=>200, 'description'=>'实名注册'),
		7=>array('type'=>7, 'credit'=>50, 'description'=>'设置交易密码'),
		8=>array('type'=>8, 'credit'=>50, 'description'=>'谷歌验证'),
		9=>array('type'=>9, 'credit'=>50, 'description'=>'首次充值'),
		10=>array('type'=>10, 'credit'=>100, 'description'=>'首次充值1000元以上'),
		11=>array('type'=>11, 'credit'=>100, 'description'=>'首次融资1000元以上'),
		12=>array('type'=>12, 'credit'=>100, 'description'=>'首次融币50个以上'),
		13=>array('type'=>13, 'credit'=>'', 'description'=>'每日融资奖励'),
		14=>array('type'=>14, 'credit'=>'', 'description'=>'每日实盘账户净资产奖励'),
		15=>array('type'=>15, 'credit'=>'', 'description'=>'BTC交易积分'),
		99=>array('type'=>99, 'credit'=>'', 'description'=>'官方赠送积分')
	);
	//登陆
	static function UserloginAddlog($id){
		$userlogin = new UserLoginModel();
		$user = new UserModel();
		$udata = $user -> getById($id);
		$data = array('uid'=>$id, 'updated'=>time(), 'updateip'=>Tool_Fnc::realip(), 'fqy'=>1);
		if($fqy = Member::LoginToday($id)){
			if($fqy['s'] == 1){
				$data['fqy'] = $fqy['fqy']+1;
			}elseif($fqy['s'] == 0){
				$data['fqy'] = $fqy['fqy'];
			}
		}
		$userlogin->insert($data);//添加登陆日志
		$type = self::$type;
		if($fqy){
			if($fqy['s'] == 1){
				if($data['fqy'] <= 10){
					$credit = $data['fqy']*10;
				}else{
					$credit = 100;
				}
				self::AddCredit($id, $credit);
				self::AddLevelLog($id, 1, $credit, $data['fqy']);
			}elseif($fqy['s'] == 0){//同一天
				//$credit = 10;
			}
		}else{//第一次
			$credit = 10;
			self::AddCredit($id, $credit);
			self::AddLevelLog($id, 1, $credit, $data['fqy']);
		}
	}
	//充值
	static function RmbInAddlog($id, $money){
		if($money < 5000){
			$credit = floor($money/100);
		}else if($money >= 5000 && $money < 20000){
			$credit = floor($money/80);
		}else if($money >= 20000 && $money < 50000){
			$credit = floor($money/50);
		}else if($money >= 50000){
			$credit = floor($money/25);
		}
		if($credit>=1){
			self::AddLevelLog($id, 2, $credit);
			self::AddCredit($id, $credit);
		}
	}
	//邀请好友
	static function FriendsAddLog($id){
		$type = self::$type;
		$credit = $type[3]['credit'];
		self::AddLevelLog($id,3, $credit);
		self::AddCredit($id, $credit);
	}
	//添加积分记录
	static function AddLevelLog($id, $tp, $credit, $t=0){
        return true;
		
	}
	//添加积分
	static function AddCredit($id, $credit){
		$user = new UserModel();
		//$data = array('uid'=>$id, 'credit' => "credit+{$credit}");
		$data = "update {$user->table} set credit=credit+{$credit} where uid={$id}";
		$user->exec($data);
	}
	//添加积分和记录
	static function AddUserCredit($id, $tp, $credit=0){
		$type = self::$type;
		if($type[$tp]['credit'] != ''){
			$credit = $type[$tp]['credit'];
		}
		self::AddLevelLog($id, $tp, $credit);
		self::AddCredit($id, $credit);
		return true;
	}
	//判断登陆次数
	static function LoginToday($id){
		$userlogin = new UserLoginModel();
		$fqy = $userlogin->fRow("select fqy, updated from {$userlogin->table} where uid={$id} order by id desc");
		$time = strtotime(date('Y-m-d', time())." 00:00:00");
		if(!empty($fqy)){
			if($time>$fqy['updated'] && $time-3600*24 < $fqy['updated']){//连续天数
				$fqy['s'] = 1;
				return $fqy;
			}elseif($time<$fqy['updated'] && $time+3600*24 > $fqy['updated']){//同一天
				$fqy['s'] = 0;
				return $fqy;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	static function GetLog($uid,$type){
		$userlevellog = new UserLevelLogModel();
		$data = array();
		$data = $userlevellog->query("select * from {$userlevellog->table} where type={$type} and uid={$uid}");
		return $data;
	}
}
