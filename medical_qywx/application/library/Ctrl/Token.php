<?php 

	class Ctrl_Token extends Ctrl_Base{
		//应用的参数
		public $appID = 'wx815a05389f6d8815';
		public $appsecret = 'b512db03f314e1f7e0bad35702546440';
		//存储token信息的文件路径
		public $tokenFile = './token.txt';
		public $tokenFileLifeTime = 7200;//有效时间

		function get($url){
		//初始化curl
		$ch = curl_init($url);
		//设置请求参数
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//发送
		$res = curl_exec($ch);
		//返回执行的结果
		return $res;
	}
		//返回token字符串
		public function getToken(){
			//检测缓存是否已经过期
			if(!$this->checkFileExists() || $this->checkFileExpire()){
				//请求微信服务器获取token字符串
				$token = $this->requestToken();
				return $token;
			}
			//如果文件存在并且没有过期
			return $this->readToken();
		}

		//检测token文件是否存在
		public function checkFileExists(){
			return file_exists($this->tokenFile);
		}

		//请求微信服务器获取token字符串
		public function requestToken(){
			//获取token的接口地址
			
			$url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appID.'&secret='.$this->appsecret;
			//发起get请求
			$res = $this->get($url);
			//获取token字符串
			$data = json_decode($res, true);
			//判断
			if(!empty($data['access_token'])){
				//获取成功的话
				$this->writeToken($data['access_token']);
				return $data['access_token'];
			}else{//执行失败  返回请求的结果字符串
				echo '获取token失败,错误代码为'.$res;die;
			}
		}

		//将token字符串写入文件中
		public function writeToken($token){
			file_put_contents($this->tokenFile, $token);
		}

		//检测token文件是否过期  最后修改时间  有效时间   当前时间
		public function checkFileExpire(){
			// 获取最后的修改事件
			$mtime = filemtime($this->tokenFile);
			//如果两个时间之和小于当前时间 意味着文件已经过期
			if($mtime + $this->tokenFileLifeTime < time()){
				return true;
			}else{
				return false;
			}
			// return filemtime($this->tokenFile) + $this->tokenFileLifeTime < time();
		}

		//从文件中直接读取token
		public function readToken(){
			return file_get_contents($this->tokenFile);
		}


	}
	


 ?>