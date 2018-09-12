<?php
    class Weixin_WeixinController extends Ctrl_Token{
    	function post($url, $data){
		//初始化curl
		$ch = curl_init($url);
		//设置请求参数
		curl_setopt($ch, CURLOPT_POST, 1);//设置当前的请求方式为post
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//设置要发送的参数
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//发送请求
		$res = curl_exec($ch);
		//返回结果
		return $res;
	}
        public function indexAction(){
         $res=new Ctrl_Token;
		//设置接口地址
         // $url='https://qyapi.weixin.qq.com/cgi-bin/menu/create?access_token=ACCESS_TOKEN&agentid=AGENTID';
		$url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$res->getToken();
			$data=' {
		     "button":[
		      {
		           "name":"互动",
		           "sub_button":[
		           {	
		              "type":"media_id",
		               "name":"精华问答",
		               "media_id":"T-ff_ALdW0wkt820Jsrk2ac41ijoMu0X8KU6Viw0DpY"
		            },
		            {
		               "type":"view",
		               "name":"我要提问",
		               "url":"http://www.baidu.com/"
		            },
		       
		            ]
		       },
				
		       ]
		 }';
		 

	 $res =$this->post($url, $data);
	 var_dump($res);die;
	
        }
    public  function uploadAction(){
    	echo '4';die;
    }
    }
