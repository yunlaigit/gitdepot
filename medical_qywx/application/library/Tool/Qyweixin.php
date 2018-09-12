<?php 
/**
*	微信企业号
*	@author luoyunlai 826132167@qq.com
*	@creatime	2017-11-23
**/
class Tool_Qyweixin{
	public $corpId			= "";//企业ID
	public $corpSecret		= "";//管理组的凭证秘钥
	public $parameters		= array();
	public $token			= "";
	public $jsApiTicket = NULL;
	public $jsApiTime   = NULL;

	public function __construct(){
		$tWeixinarr = Yaf_Registry::get("config")->weixin->default->toArray();
		$this->corpId = $tWeixinarr['corpId'];
		$this->corpSecret = $tWeixinarr['corpSecret'];
		$this->token = $tWeixinarr['token'];
	}

	/**
	 * 对内容进行json编码，并且保持汉字不会被编码
	 * @param $value 被编码的对象
	 * @return 编码结果字符串
	 */
	public function json_encode_ex($value) {
		if (version_compare(PHP_VERSION, '5.4.0', '<')) {
			$str = json_encode($value);
			$str = preg_replace_callback("#\\\u([0-9a-f]{4})#i", function($matchs) {
				return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
			}, $str);
			return $str;
		} else {
			return json_encode($value, JSON_UNESCAPED_UNICODE);
		}
	}

	/****************************************************
	 *  微信提交API方法，返回微信指定JSON
	 ****************************************************/

	public function qywxHttpsRequest($url,$data = null){
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
			if (!empty($data)){
					curl_setopt($curl, CURLOPT_POST, 1);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			}
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($curl);
			curl_close($curl);
			return json_decode($output, TRUE);
	}

	// /**
	//  * 发起httpPOST请求
	//  * @param $url 请求的URL
	//  * @param $parameters 请求的参数，以数组形式传递
	//  */
	public function httpPostRequest($url, $parameters = array()) {
		if (empty($url)) {
			return FALSE;
		}

		// 初始化CURL
		$ch = curl_init();
		// 设置要请求的URL
		curl_setopt($ch, CURLOPT_URL, $url);
		// 设置不显示头部信息
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		// 设置不将请求结果直接输出在标准输出里，而是返回
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		// 设置本地不检测SSL证书
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		//设置post方式提交
		curl_setopt($ch, CURLOPT_POST, TRUE);
		// 设置请求参数
		if (!empty($parameters)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->json_encode_ex($parameters));
		}
		// 执行请求动作，并获取结果
		$result = curl_exec($ch);
		if ($error = curl_error($ch)) {
			die($error);
		}
		// 关闭CURL
		curl_close($ch);

		return json_decode($result, TRUE);
	}

	/**
	 * 使用POST请求上传文件
	 */
	public function uploadFileByPost($url, $data) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SAFE_UPLOAD, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($ch);
		if ($error = curl_error($ch)) {
			die($error);
		}
		curl_close($ch);

		return json_decode($result, TRUE);
	}

	/****************************************************
	 *  微信带证书提交数据 - 微信红包使用
	 ****************************************************/

	public function qywxHttpsRequestPem($url, $vars, $second=30,$aHeader=array()){
			$ch = curl_init();
			//超时时间
			curl_setopt($ch,CURLOPT_TIMEOUT,$second);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
			//这里设置代理，如果有的话
			//curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
			//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);

			//以下两种方式需选择一种

			//第一种方法，cert 与 key 分别属于两个.pem文件
			//默认格式为PEM，可以注释
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/apiclient_cert.pem');
			//默认格式为PEM，可以注释
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY,getcwd().'/apiclient_key.pem');

			curl_setopt($ch,CURLOPT_CAINFO,'PEM');
			curl_setopt($ch,CURLOPT_CAINFO,getcwd().'/rootca.pem');

			//第二种方式，两个文件合成一个.pem文件
			//curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');

			if( count($aHeader) >= 1 ){
					curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
			}

			curl_setopt($ch,CURLOPT_POST, 1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
			$data = curl_exec($ch);
			if($data){
					curl_close($ch);
					return $data;
			}
			else { 
					$error = curl_errno($ch);
					echo "call faild, errorCode:$error\n"; 
					curl_close($ch);
					return false;
			}
	}

	/****************************************************
	 *  微信获取AccessToken 返回指定微信公众号的at信息
	 ****************************************************/

	public function qywxAccessToken($corpId = NULL , $corpSecret = NULL){
		$corpId					= is_null($corpId) ? $this->corpId : $corpId;
		$corpSecret				= is_null($corpSecret) ? $this->corpSecret : $corpSecret;
		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".$corpId."&corpsecret=".$corpSecret;

		$tKey = md5($url);
		$access_token = Cache_File::get($tKey,7000);
		if(!empty($access_token)){
			return $access_token;
		}
		$result 				= $this->qywxHttpsRequest($url);
		$access_token			= $result["access_token"];
		Cache_File::set($tKey,$access_token);
		return $access_token;
	}

	/****************************************************
	 *  微信根据code获取成员信息(USERID)(企业成员授权返回USERID、
	 *	USER_TICKET非企业成员授权返回OPENID)
	 ****************************************************/

	public function qywxGetuserinfo($qywxAccessToken = null,$code){
		$qywxAccessToken 	= is_null($qywxAccessToken)?$this->qywxAccessToken():$qywxAccessToken;
		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=".$qywxAccessToken."&code=".$code;
		$result 			= $this->qywxHttpsRequest($url);
		return $result;
	}

	/****************************************************
	 *  微信根据user_ticket获取成员信息
	 ****************************************************/

	public function qywxGetuserdetail($code){
		$qywxAccessToken	= $this->qywxAccessToken();
		$userTicket 		= $this->qywxGetuserinfo($code);
		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/user/getuserdetail?access_token=".$qywxAccessToken;
		$data				= array(
						'user_ticket' => $userTicket['user_ticket'],
		);
		$result 			= $this->httpPostRequest($url, $data);
		return $result;
	}

	/****************************************************
	 *  微信userid转换openid(微信红包使用)
	 ****************************************************/

	public function qywxConvertToOpenid($code){
		$qywxAccessToken	= $this->qywxAccessToken();
		$userInfo			= $this->qywxGetuserinfo($code);
		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_openid?access_token=".$qywxAccessToken;
		$data				= array(
						'userid' => $userInfo['UserId'],
		);
		$result				= $this->httpPostRequest($url, $data);
		return $result;
	}

	/****************************************************
	 *  微信openid转换userid(微信红包使用)
	 ****************************************************/

	public function qywxConvertToUserid($code){
		$qywxAccessToken	= $this->qywxAccessToken();
		$userInfo			= $this->qywxGetuserinfo($code);
		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_userid?access_token=".$qywxAccessToken;
		$openId 			= $this->qywxConvertToOpenid($code);

		$data				= array(
						'userid' => $openId['openid'],
		);
		$result				= $this->httpPostRequest($url, $data);
		return $result;
	}

	/****************************************************
	 *  微信根据授权码(code)获取企业号登录用户信息(第三方授权登录使用)
	 ****************************************************/

	public function qywxGetlogininfo($code){
		$qywxAccessToken	= $this->qywxAccessToken();
		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/service/get_login_info?access_token=".$qywxAccessToken;
		$data				= array(
						'auth_code' => $code,
		);
		$result 			= $this->httpPostRequest($url, $data);
		return $result;
	}

	/****************************************************
	 *  微信根据授权码(code)获取企业号官网url(单点登录使用)
	 ****************************************************/

	public function qywxGetloginurl($code){
		$qywxAccessToken	= $this->qywxAccessToken();

		$loginInfo 			= $this->qywxGetlogininfo($code);

		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/service/get_login_url?access_token=".$qywxAccessToken;

		$data 				= array(
						'login_ticket' => $loginInfo['redirect_login_info']['login_ticket'],//通过get_login_info得到的login_ticket, 10小时有效
						'target' 	   => 'agent_setting',//登录跳转到企业号后台的目标页面，目前有：agent_setting、send_msg、contact 
		);
		$result 			= $this->httpPostRequest($url, $data);
		return $result;
	}

	/****************************************************
	 *  微信获取应用列表
	 ****************************************************/

	public function qywxAgentlist(){
		$qywxAccessToken 	= $this->qywxAccessToken();

		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/agent/list?access_token=".$qywxAccessToken;

		$result 			= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信获取应用信息
	 ****************************************************/

	public function qywxGetagentinfo($agentId){
		$qywxAccessToken 	= $this->qywxAccessToken();

		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/agent/get?access_token=".$qywxAccessToken."&agentid=".$agentId;

		$result				= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号设置企业号应用
	 ****************************************************/

	public function qywxSetagent($mediaId, $name, $description, $redirect_domain, $home_url, $chat_extension_url, $agentId = '1', $report_loction_flag = '0', $isreportuser = '0', $isreportenter = '0'){

		$qywxAccessToken 	= $this->qywxAccessToken();

		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/agent/set?access_token=".$qywxAccessToken;

		$data 				= array(
						'mediaid' 				=> $mediaId,
						'name' 					=> $name,
						'description' 			=> $description,
						'redirect_domain' 		=> $redirect_domain,
						'home_url' 				=> $home_url,
						'chat_extension_url' 	=> $chat_extension_url,
						'agentid'				=> $agentId,
						'report_loction_flag'	=> $report_loction_flag,
						'isreportenter'			=> $isreportenter,
						'isreportuser'			=> $isreportuser,
		);

		$result 			= $this->httpPostRequest($url, $data);
		return $result;
	}

	/****************************************************
	 *  微信企业号创建应用菜单
	 ****************************************************/

	public function qywxCreatemenu($agentId, $jsonData){
		$qywxAccessToken 	= $this->qywxAccessToken();

		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/menu/create?access_token=".$qywxAccessToken."&agentid=".$agentId;
		$result 			= $this->httpPostRequest($url, $jsonData);

		return $result;
	}

	/****************************************************
	 *  微信企业号删除应用菜单
	 ****************************************************/

	public function qywxDeletemenu($agentId){
		$qywxAccessToken 	= $this->qywxAccessToken();

		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/menu/delete?access_token=".$qywxAccessToken."&agentid=".$agentId;

		$result 			= $this->httpPostRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取应用菜单列表
	 ****************************************************/

	public function qywxGetmenulist($agentId){
		$qywxAccessToken 	= $this->qywxAccessToken();

		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/menu/get?access_token=".$qywxAccessToken."&agentid=".$agentId;

		$result 			= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号二次验证成功后关注企业号接口
	 ****************************************************/

	public function qywxAuthsucc($UserId){
		$qywxAccessToken 	= $this->qywxAccessToken();

		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/user/authsucc?access_token=".$qywxAccessToken."&userid=".$UserId;

		$result 			= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号创建部门
	 ****************************************************/

	public function qywxCreatedDepartemnt($name, $parentId = '1'){
		$qywxAccessToken 	= $this->qywxAccessToken();

		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/department/create?access_token=".$qywxAccessToken;
		$data 				= array(
						'name' => $name,
						'parentid' => $parentId
		);

		$result 			= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号更新部门
	 ****************************************************/

	public function qywxUpdatedDepartemnt($name, $parentId = '1'){
		$qywxAccessToken 	= $this->qywxAccessToken();

		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/department/update?access_token=".$qywxAccessToken;
		$data 				= array(
						'name' => $name,
						'parentid' => $parentId
		);

		$result 			= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号删除部门
	 ****************************************************/

	public function qywxDeleteDepartemnt($pId){
		$qywxAccessToken 	= $this->qywxAccessToken();

		$url 				= "https://qyapi.weixin.qq.com/cgi-bin/department/delete?access_token=".$qywxAccessToken."&id=".$pId;

		$result 			= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取部门列表
	 ****************************************************/

	public function qywxDepartmentList($pId = null,$qywxAccessToken = null){
		$qywxAccessToken 		= is_null($qywxAccessToken)?$this->qywxAccessToken():$qywxAccessToken;

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token=".$qywxAccessToken."&id=".$pId;

		$result 				= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号创建成员
	 ****************************************************/

	public function qywxCreateuser($data, $qywxAccessToken = null){
		// $qywxAccessToken 		= $this->qywxAccessToken();
		$qywxAccessToken 		= is_null($qywxAccessToken)?$this->qywxAccessToken():$qywxAccessToken;

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/user/create?access_token=".$qywxAccessToken;

		// $data 					= array(
		// 					'userid' 		 => $UserId,
		// 					'name' 			 => $name,
		// 					'department'	 => $department,
		// 					'position'		 => $position,
		// 					'mobile'		 => $mobile,
		// 					'gender'		 => $gender,
		// 					'email'			 => $email,
		// 					'weixinid'		 => $weixinid,
		// 					'avatar_mediaid' => $avatar_mediaid,
		// 					'extattr'		 => $extattr
		// );

		$result 				= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号更新成员
	 ****************************************************/

	public function qywxUpdateuser($data,$qywxAccessToken = null){
		// $qywxAccessToken 		= $this->qywxAccessToken();
		$qywxAccessToken 		= is_null($qywxAccessToken)?$this->qywxAccessToken():$qywxAccessToken;
		

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/user/update?access_token=".$qywxAccessToken;

		// $data 					= array(
		// 					'userid' 		 => $UserId,
		// 					'name' 			 => $name,
		// 					'department'	 => $department,
		// 					'position'		 => $position,
		// 					'mobile'		 => $mobile,
		// 					'gender'		 => $gender,
		// 					'email'			 => $email,
		// 					'weixinid'		 => $weixinid,
		// 					'avatar_mediaid' => $avatar_mediaid,
		// 					'extattr'		 => $extattr
		// );

		$result 				= $this->httpPostRequest($url, $data);

		return $result;
	}

		/****************************************************
	 *  微信企业号删除成员
	 ****************************************************/

	public function qywxDeleteuser($UserId){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/user/delete?access_token=".$qywxAccessToken."&userid=".$UserId;

		$result 				= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号批量删除成员
	 ****************************************************/

	public function qywxBatchdeleteuser($UserIdList){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/user/batchdelete?access_token=".$qywxAccessToken;

		$data 					= array(
							'useridlist' 		 => $UserIdList,
		);

		$result 				= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取成员
	 ****************************************************/

	public function qywxBatchgetuser($UserId,$qywxAccessToken = null){
		$qywxAccessToken 		= is_null($qywxAccessToken)?$this->qywxAccessToken():$qywxAccessToken;

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=".$qywxAccessToken."&userid=".$UserId;

		$result 				= $this->httpPostRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取部门成员
	 ****************************************************/

	public function qywxUserSimplelist($departmentId,$qywxAccessToken = null){
		$qywxAccessToken 		= is_null($qywxAccessToken)?$this->qywxAccessToken():$qywxAccessToken;

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=".$qywxAccessToken."&department_id=".$departmentId."&fetch_child=1&status=0";

		$result					= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取部门成员详情
	 ****************************************************/

	public function qywxUserlist($departmentId,$qywxAccessToken = null){
		// $qywxAccessToken 		= $this->qywxAccessToken();
		$qywxAccessToken 		= is_null($qywxAccessToken)?$this->qywxAccessToken():$qywxAccessToken;

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token=".$qywxAccessToken."&department_id=".$departmentId."&fetch_child=1&status=0";

		$result					= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号创建标签
	 ****************************************************/

	public function qywxCreatetag($data,$qywxAccessToken = null){
		// $qywxAccessToken 		= $this->qywxAccessToken();
		$qywxAccessToken 		= is_null($qywxAccessToken)?$this->qywxAccessToken():$qywxAccessToken;

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/tag/create?access_token=".$qywxAccessToken;

		// $data 					= array('tagname' => $tagname);

		$result					= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号更新标签名字
	 ****************************************************/

	public function qywxUpdatetag($data, $qywxAccessToken = null){
		// $qywxAccessToken 		= $this->qywxAccessToken();
		$qywxAccessToken 		= is_null($qywxAccessToken)?$this->qywxAccessToken():$qywxAccessToken;

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/tag/update?access_token=".$qywxAccessToken;

		// $data 					= array(
		// 				'tagid' 	=> $tagId,
		// 				'tagname' 	=> $tagname
		// );

		$result 				= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号删除标签
	 ****************************************************/

	public function qywxDeletetag($tagId,$qywxAccessToken = null){
		// $qywxAccessToken 		= $this->qywxAccessToken();
		$qywxAccessToken 		= is_null($qywxAccessToken)?$this->qywxAccessToken():$qywxAccessToken;

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/tag/delete?access_token=".$qywxAccessToken."&tagid=".$tagId;

		$result					= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取标签成员
	 ****************************************************/

	public function qywxGettag($tagId){
		$qywxAccessToken   		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/tag/get?access_token=".$qywxAccessToken."&tagid=".$tagId;

		$result					= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号增加标签成员
	 ****************************************************/

	public function qywxAddtagusers($tagId){
		$qywxAccessToken		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/tag/addtagusers?access_token=".$qywxAccessToken;

		$data 					= array(
						'tagid' => $tagId,
		);

		$result					= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号删除标签成员
	 ****************************************************/

	public function qywxDeltagusers($tagId){
		$qywxAccessToken		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/tag/deltagusers?access_token=".$qywxAccessToken;

		$data 					= array(
						'tagid' => $tagId,
		);

		$result 				= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取标签列表
	 ****************************************************/	

	public function qywxTaglist($qywxAccessToken = null){
		// $qywxAccessToken 		= $this->qywxAccessToken();
		$qywxAccessToken 		= is_null($qywxAccessToken)?$this->qywxAccessToken():$qywxAccessToken;

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/tag/list?access_token=".$qywxAccessToken;

		$result 				= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号增量更新成员
	 ****************************************************/

	 public function qywxBatchSyncuser($mediaId){
	 	$qywxAccessToken 		= $this->qywxAccessToken();

	 	$url 					= "https://qyapi.weixin.qq.com/cgi-bin/batch/syncuser?access_token=".$qywxAccessToken;

	 	$data 					= array(
	 					'media_id' => $mediaId,
	 	);

	 	$result					= $this->httpPostRequest($url, $data);

	 	return $result;
	 }	

	/****************************************************
	 *  微信企业号全量覆盖成员
	 ****************************************************/	

	public function qywxBatchReplaceuser($mediaId){
		$qywxAccessToken		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/batch/replaceuser?access_token=".$qywxAccessToken;

		$data 					= array(
						'media_id' => $mediaId,
		);

		$result 				= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号全量覆盖部门
	 ****************************************************/	

	public function qywxBatchReplaceparty($mediaId){
		$qywxAccessToken		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/batch/replaceparty?access_token=".$qywxAccessToken;

		$data 					= array(
						'media_id' => $mediaId,
		);

		$result 				= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取异步任务结果
	 ****************************************************/

	public function qywxBatchGetresult($jobId){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/batch/getresult?access_token=".$qywxAccessToken."&jobid=".$jobId;

		$result 				= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号上传临时素材
	 ****************************************************/

	public function qywxUploadmedia($type, $media){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/media/upload?access_token=".$qywxAccessToken;

		$data 					= array(
						'type' 	=> $type,
						'media' => $media,
		);

		$result 				= httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取临时素材
	 ****************************************************/

	public function qywxGetmedia($mediaId){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/media/get?access_token=".$qywxAccessToken."&media_id=".$mediaId;

		$result 				= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号消息发送
	 ****************************************************/

	public function qywxSendmessage($jsonData, $qywxAccessToken = null){
		// $qywxAccessToken 		= $this->qywxAccessToken();
		$qywxAccessToken 	= is_null($qywxAccessToken)?$this->qywxAccessToken():$qywxAccessToken;

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$qywxAccessToken;

		$result					= $this->httpPostRequest($url, $jsonData);

		return $result;
	}

	/****************************************************
	 *  微信企业号创建会话
	 ****************************************************/

	public function qywxCreatechat($chatId, $name, $owner, $userList){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/chat/create?access_token=".$qywxAccessToken;

		$data 					= array(
						'chatid' 	=> $chatId,
						'name'	 	=> $name,
						'owner'	 	=> $owner,
						'userlist'	=> $userList
		);

		$result					= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取会话
	 ****************************************************/

	public function qywxGetchat($chatId){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/chat/get?access_token=".$qywxAccessToken."&chatid=".$chatId;

		$result					= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号修改会话信息
	 ****************************************************/

	public function qywxUpdatechat($chatId, $UserId){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/chat/update?access_token=".$qywxAccessToken;

		$data 					= array(
						'chatid' 	=> $chatId,
						'op_user'	 	=> $UserId,
		);

		$result					= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号退出会话
	 ****************************************************/

	public function qywxQuitchat($chatId, $UserId){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/chat/quit?access_token=".$qywxAccessToken;

		$data 					= array(
						'chatid' 	=> $chatId,
						'op_user'	 	=> $UserId,
		);

		$result					= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号清除会话未读状态
	 ****************************************************/

	public function qywxClearNotifychat($UserId, $type, $id){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/chat/clearnotify?access_token=".$qywxAccessToken;

		$data 					= array(
						'op_user' 	=> $UserId,
						'chat' 		=> array(
								'type' 	=> $type,
								'id'   	=> $id
						)
		);

		$result					= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号发送会话消息
	 ****************************************************/

	public function qywxSendChatmessage($jsonData){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/chat/send?access_token=".$qywxAccessToken;

		$result					= $this->httpPostRequest($url, $jsonData);

		return $result;
	}

	/****************************************************
	 *  微信企业号设置成员新消息免打扰
	 ****************************************************/

	public function qywxChatSetmute($jsonData){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/chat/setmute?access_token=".$qywxAccessToken;

		$result					= $this->httpPostRequest($url, $jsonData);

		return $result;
	}

	/****************************************************
	 *  微信企业号向企业号客服发送客服消息
	 ****************************************************/

	public function qywxSendkf($jsonData){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/kf/send?access_token=".$qywxAccessToken;

		$result 				= $this->httpPostRequest($jsonData);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取客服列表
	 ****************************************************/

	public function qywxKflist($type){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/kf/list?access_token=".$qywxAccessToken."&type=".$type;

		$result					= $this->qywxHttpsRequest($url);

		return $result;
	}

	/****************************************************
	 *  微信企业号摇一摇获取周边设备及用户信息
	 ****************************************************/

	public function qywxGetShakeinfo($ticket){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/shakearound/getshakeinfo?access_token=".$qywxAccessToken;

		$data 					= array(
							'ticket' => $ticket
		);

		$result					= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号上传卡券LOGO
	 ****************************************************/

	public function qywxMediaUploadimg($media){
		$qywxAccessToken		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/media/uploadimg?access_token=".$qywxAccessToken."&type=card_logo";

		$result 				= $this->httpPostRequest($url, $media);

		return $result;
	}

	/****************************************************
	 *  微信企业号创建卡券
	 ****************************************************/

	public function qywxCreatecard($jsonData){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/card/create?access_token=".$qywxAccessToken;

		$result 				= $this->httpPostRequest($url, $jsonData);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取卡券详情
	 ****************************************************/

	public function qywxGetcard($cardId){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/card/get?access_token=".$qywxAccessToken;

		$data 					= array('card_id' => $cardId);

		$result					= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取卡券摘要列表
	 ****************************************************/

	public function qywxCardBatchget($offset, $count){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/card/batchget?access_token=".$qywxAccessToken;

		$data 					= array(
						'offset' => $offset,
						'count'  => $count
		);

		$result 				= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号修改卡券库存
	 ****************************************************/

	public function qywxCardModifystock($cardId, $reduce_stock_value){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/card/modifystock?access_token=".$qywxAccessToken;

		$data                	= array(
						'card_id' 				=> $cardId,
						'reduce_stock_value'	=> $reduce_stock_value
		);

		$result 				= $this->httpPostRequest($data);

		return $result;
	}

	/****************************************************
	 *  微信企业号删除卡券
	 ****************************************************/

	public function qywxCarddeletestock($cardId){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/card/delete?access_token=".$qywxAccessToken;

		$data                	= array(
						'card_id' 				=> $cardId,
		);

		$result 				= $this->httpPostRequest($data);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取卡券图文消息内容
	 ****************************************************/

	public function qywxGetCardmpnews($agentId, $cardId){
		$qywxAccessToken		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/card/mpnews/gethtml?access_token=".$qywxAccessToken;

		$data 					= array(
						'agentid' => $agentId,
						'card_id' => $cardId
		);

		$result					= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号创建卡券二维码
	 ****************************************************/

	public function qywxCardCreateqrcode($jsonData){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/card/qrcode/create?access_token=".$qywxAccessToken;

		$result					= $this->httpPostRequest($jsonData);

		return $result;
	}

	/****************************************************
	 *  微信企业号查询卡券code
	 ****************************************************/

	public function qywxCardGetcode($code){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/card/code/get?access_token=".$qywxAccessToken;

		$data 					= array(
						'code' => $code
		);

		$result 				= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号核销卡券code
	 ****************************************************/

	public function qywxCardConsumecode($code){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/card/code/consume?access_token=".$qywxAccessToken;

		$data 					= array(
						'code' => $code
		);

		$result 				= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取应用套件令牌
	 ****************************************************/

	public function qywxServiceGetsuitetoken($suite_id, $suite_secret, $suite_ticket){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/service/get_suite_token";

		$tKey 					= md5($url);

		$access_token 			= Cache_File::get($tKey, 3600);

		if(!empty($access_token)){
			return $access_token;
		}

		$data 					= array(
						'suite_id' 		=> $suite_id,
						'suite_secret' 	=> $suite_secret,
						'suite_ticket'	=> $suite_ticket
		);

		$result 				= $this->httpPostRequest($url, $result);

		$access_token 			= $result['suite_access_token'];

		Cache_File::set($tKey, $access_token);

		return $access_token;
	}

	/****************************************************
	 *  微信企业号获取预授权码
	 ****************************************************/

	public function qywxServiceGetpreauthcode($suite_id, $suite_secret, $suite_ticket){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$Suitetoken 			= $this->qywxServiceGetsuitetoken($suite_id, $suite_secret, $suite_ticket);

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/service/get_pre_auth_code?suite_access_token=".$Suitetoken['suite_access_token'];

		$data 					= array('suite_id' => $suite_id);

		$result					= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号设置授权配置
	 ****************************************************/

	public function qywxServiceSetsessioninfo($suite_id, $suite_secret, $suite_ticket, $jsonData){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$Suitetoken 			= $this->qywxServiceGetsuitetoken($suite_id, $suite_secret, $suite_ticket);

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/service/set_session_info?suite_access_token=".$Suitetoken['suite_access_token'];

		$result					= $this->httpPostRequest($url, $jsonData);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取永久预授权码
	 ****************************************************/

	public function qywxServiceGetpermanentcode($suite_id, $suite_secret, $suite_ticket, $auth_code){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$Suitetoken 			= $this->qywxServiceGetsuitetoken($suite_id, $suite_secret, $suite_ticket);

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/service/get_permanent_code?suite_access_token=".$Suitetoken['suite_access_token'];

		$data 					= array(
							'suite_id' 	=> $suite_id,
							'auth_code' => $auth_code
		);

		$result					= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取授权信息
	 ****************************************************/

	public function qywxServiceGetauthinfo($suite_id, $suite_secret, $suite_ticket, $auth_corpid, $permanent_code){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$Suitetoken 			= $this->qywxServiceGetsuitetoken($suite_id, $suite_secret, $suite_ticket);

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/service/get_auth_info?suite_access_token=".$Suitetoken['suite_access_token'];

		$data 					= array(
							'suite_id' 			=> $suite_id,
							'auth_corpid' 		=> $auth_corpid,
							'permanent_code'	=> $permanent_code
		);

		$result					= $this->httpPostRequest($url, $data);

		return $result;
	}

	/****************************************************
	 *  微信企业号获取access_token
	 ****************************************************/

	public function qywxServiceGetcorptoken($suite_id, $suite_secret, $suite_ticket, $auth_corpid, $permanent_code){
		$qywxAccessToken 		= $this->qywxAccessToken();

		$Suitetoken 			= $this->qywxServiceGetsuitetoken($suite_id, $suite_secret, $suite_ticket);

		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/service/get_corp_token?suite_access_token=".$Suitetoken['suite_access_token'];

		$tKey 					= md5($url);

		$access_token 			= Cache_File::get($tKey, 3600);

		if(!empty($access_token)){
			return $access_token;
		}

		$data 					= array(
							'suite_id' 			=> $suite_id,
							'auth_corpid' 		=> $auth_corpid,
							'permanent_code'	=> $permanent_code
		);

		$result					= $this->httpPostRequest($url, $data);

		$access_token 			= $result['access_token'];

		Cache_File::set($tKey, $access_token);

		return $access_token;
	}

	/****************************************************
	 *  微信企业号获取应用提供商凭证
	 ****************************************************/

	public function  qywxServiceGetprovidertoken($corpid, $provider_secret){
		$url 					= "https://qyapi.weixin.qq.com/cgi-bin/service/get_provider_token";

		$tKey 					= md5($url);
		$access_token 			= Cache_File::get($tKey, 3600);
		if(!empty($access_token)){
			return $access_token;
		}

		$data 					= array(
							'corpid' 			=> $corpid,
							'provider_secret'	=> $provider_secret
		);

		$result					= $this->httpPostRequest($url, $data);

		$access_token 			= $result['get_provider_token'];

		Cache_File::set($tKey, $access_token);

		return $result;
	}

	/****************************************************
	 *  微信获取ApiTicket 返回指定微信公众号的at信息
	 ****************************************************/

	public function qywxJsApiTicket($corpId = NULL , $corpSecret = NULL){
			$corpId          = is_null($corpId) ? $this->corpId : $corpId;
			$corpSecret      = is_null($corpSecret) ? $this->corpSecret : $corpSecret;

			// $url            = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=".$this->wxAccessToken();
			$url 			= "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=".$this->qywxAccessToken($corpId, $corpSecret);

			$result         = $this->qywxHttpsRequest($url);

			$ticket         = $result['ticket'];
			//echo $ticket . "<br />";
			return $ticket;
	}

	public function qywxVerifyJsApiTicket($corpId = NULL , $corpSecret = NULL){
		if(!empty($this->jsApiTime) && intval($this->jsApiTime) > time() && !empty($this->jsApiTicket)){
			$ticket = $this->jsApiTicket;
		}
		else{
			$ticket = $this->qywxJsApiTicket($corpId,$corpSecret);
			$this->jsApiTicket = $ticket;
			$this->jsApiTime = time() + 7200;
		}
		return $ticket;
	}
}
?>