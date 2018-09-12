<?php
Yaf_loader::import("SDK/getui/IGt.Push.php");
class Tool_Getui extends IGeTui{
	public $tAppkey = '';
	public $tAppid = '';
	public $tMastersecret = '';
	public $tHost = '';
	public $tTaskid = '';

	function __construct(){
		# 配置
		if(!$tConf = Yaf_Registry::get("config")->getui->default){
			exit('redis config error: default');
		}
		$tConf = $tConf->toArray();
		$this->tAppkey = $tConf['appkey'];
		$this->tAppid = $tConf['appid'];
		$this->tMastersecret = $tConf['mastersecret'];
		$this->tHost = $tConf['host'];
		$this->tTaskid = $tConf['taskid'];

		parent::__construct($this->tHost,$this->tAppkey,$this->tMastersecret);
	}

	//ios单个推送
	public function pushAPN($pDevicetoken){
        $template = new IGtAPNTemplate();
		$template->set_pushInfo("", 1, "推送的要不要", "", "", "", "", "");
 
        $message = new IGtSingleMessage();

        $message->set_data($template);
        $ret = $this->pushAPNMessageToSingle($this->tAppid, $pDevicetoken, $message);
        var_dump($ret);
	}
	private function pushMessageToSingledemo($pCid){

		// 1.TransmissionTemplate:
		// 2.LinkTemplate:
		// 3.NotificationTemplate
		// 4.NotyPopLoadTemplate

		$template = $this->IGtNotyPopLoadTemplateDemo();
		//$template = IGtLinkTemplateDemo();
		//$template = $this->IGtNotificationTemplateDemo();
		//$template = IGtTransmissionTemplateDemo();
		//
		$message = new IGtSingleMessage();
		$message->set_isOffline(true);//
		$message->set_offlineExpireTime(5);//
		$message->set_data($template);//
		//
		$target = new IGtTarget();
		$target->set_appId($this->tAppid);
		$target->set_clientId($pCid);
		$rep = $this->pushMessageToSingle($message,$target);
		var_dump($rep);
		echo ("<br><br>");
	}
	private function IGtNotificationTemplateDemo(){
		$template =  new IGtNotificationTemplate();
        $template->set_appId(APPID);//应用appid
        $template->set_appkey(APPKEY);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $template->set_transmissionContent("测试离线");//透传内容
        $template->set_title(title);//通知栏标题
        $template->set_text(nr);//通知栏内容
        $template->set_logo("");//通知栏logo
        $template->set_isRing(true);//是否响铃
        $template->set_isVibrate(true);//是否震动
        $template->set_isClearable(true);//通知栏是否可清除
        // iOS推送需要设置的pushInfo字段
        //$template ->set_pushInfo($actionLocKey,$badge,$message,$sound,$payload,$locKey,$locArgs,$launchImage);
        $template ->set_pushInfo("test",1,"","","","","","");
        return $template;
	}
	private function IGtNotyPopLoadTemplateDemo(){
		$template =  new IGtNotyPopLoadTemplate();
		$template ->set_appId(APPID);   //应用appid
		$template ->set_appkey(APPKEY); //应用appkey
		//通知栏
		$template ->set_notyTitle("个推");                 //通知栏标题
		$template ->set_notyContent("个推最新版点击下载"); //通知栏内容
		$template ->set_notyIcon("");                      //通知栏logo
		$template ->set_isBelled(true);                    //是否响铃
		$template ->set_isVibrationed(true);               //是否震动
		$template ->set_isCleared(true);                   //通知栏是否可清除
		//弹框
		$template ->set_popTitle("拍医拍");   //弹框标题
		$template ->set_popContent("新版拍医拍发布了"); //弹框内容
		$template ->set_popImage("");           //弹框图片
		$template ->set_popButton1("下载");     //左键
		$template ->set_popButton2("取消");     //右键
		//下载
		$template ->set_loadIcon("");           //弹框图片
		$template ->set_loadTitle("地震速报下载");
		$template ->set_loadUrl("http://dizhensubao.igexin.com/dl/com.ceic.apk");
		$template ->set_isAutoInstall(true);
		$template ->set_isActived(true);
		return $template;
	}

}
