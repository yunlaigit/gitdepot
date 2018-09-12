<?php
class Tool_Fnc{
    //天气预报
    static function weather($pCity = '北京',$pDay = 0){
       $pCity = urlencode(iconv( "UTF-8", "gb2312//IGNORE",$pCity));
       $tXML = self::httpget('http://php.weather.sina.com.cn/xml.php?city='.$pCity.'&password=DJOYnieT8234jlsK&day='.$pDay);
        
        $tData = array();
        if(!empty($tXML)){
            $tXML = simplexml_load_string($tXML);
            $tData['status1'] = $tXML->Weather->status1;#白天天气
            $tData['status2'] = $tXML->Weather->status2;#晚上天气
            $tData['power1'] = $tXML->Weather->power1;#白天温度
            $tData['power2'] = $tXML->Weather->power2;#晚上温度
        }
        return $tData; 
    }
    //天气预报 聚合
    static function weather_juhe($pCity = '北京'){
        header("Content-type: text/html; charset=utf-8");  
		$tAppkey = Yaf_Registry::get("config")->juhe->weather->appkey;
        $tRes = self::httpget('http://v.juhe.cn/weather/index?format=2&cityname='.$pCity.'&key='.$tAppkey);

        $tDatas = json_decode($tRes,true);
        #$tFuture = $tXML->result->future;
        if($tDatas['resultcode'] != '200'){return false;}
        
        $tData = array('today_weather'=>$tDatas['result']['future'][0]['weather'].','.$tDatas['result']['future'][0]['temperature'],'tomorrow_weather'=>$tDatas['result']['future'][1]['weather'].','.$tDatas['result']['future'][1]['temperature']);

        return $tData;
    }
      

    //订单编号
    static function build_order_no(){
        return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }
    static function httpget($pUrl){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 0); 
        curl_setopt($ch,CURLOPT_URL,$pUrl); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_TIMEOUT,60); 
        $file_content = curl_exec($ch);
        curl_close($ch);        

        return $file_content;

    }
    static function httppost($pUrl,$pPostdata){
        $ch = curl_init();  
        curl_setopt($ch, CURLOPT_POST, 1);  
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_URL,$pUrl);  
        curl_setopt($ch, CURLOPT_POSTFIELDS, $pPostdata);  
        $tContent = curl_exec($ch);  
        curl_close($ch);        
        return $tContent;
    }

    static function http_post_data($url, $data_string) {
        $curl_handle=curl_init();
        curl_setopt($curl_handle,CURLOPT_URL, $url);
        curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle,CURLOPT_HEADER, 0);
        curl_setopt($curl_handle,CURLOPT_POST, true);
        curl_setopt($curl_handle,CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl_handle,CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl_handle,CURLOPT_SSL_VERIFYPEER, 0);
        $response_json =curl_exec($curl_handle);
        $response =json_decode($response_json);
        curl_close($curl_handle);
        return $response;
    }
    function getHttpResponsePOST($url,  $para, $input_charset = '') {
        if (trim($input_charset) != '') {
            $url = $url."_input_charset=".$input_charset;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl,CURLOPT_POST,true); // post传输数据
        curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据
        $responseText = curl_exec($curl);
        var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);
        return $responseText;
    }
	static function httpsPOST($pUrl,$pData = null){
		$tCurl = curl_init();
		curl_setopt($tCurl,CURLOPT_URL , $pUrl);
		curl_setopt($tCurl,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($tCurl,CURLOPT_SSL_VERIFYHOST,FALSE);
		if(!empty($pData)){
			curl_setopt($tCurl,CURLOPT_POST,1);
			curl_setopt($tCurl,CURLOPT_POSTFIELDS, $pData);

		}
		curl_setopt($tCurl , CURLOPT_RETURNTRANSFER , 1);
		$tOutput = curl_exec($tCurl);
		curl_close($tCurl);
		return $tOutput;
	}
	static function httpsGET($pUrl,$pData = null){
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $pUrl);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $pData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$temp = curl_exec($ch);
		return $temp;
	}
	/**
	 * 获取以父ID为KEY的分类
	 *
	 * @param int $pPid 父ID
	 * @param int $pMeId 输出一个类别
	 */
	static function catdata($pPid = false, $pMeId = 0){
		static $datas = array();
		if(!$datas) foreach(Cache_Redis::hget('category') as $v1){
			$v1 = json_decode($v1, true);
			$datas[$v1['pid']][$v1['cid']] = $v1;
		}
		if(false === $pPid) return $datas;
		return $pMeId? $datas[$pPid][$pMeId]: $datas[$pPid];
	}

	/**
	 * 显示树状分类
	 * @param string $pBoxId 容器ID
	 * @param int $pPid 父ID (0:全部)
	 */
	static function cattree($pBoxId, $pPid = 0){
		$tDatas = self::catdata(false, 0);
		echo '<select id="yaf_', $pBoxId, '" name="', $pBoxId, '">';
		if(false !== strpos(strtolower($_SERVER['REQUEST_URI']), 'manage')){
			echo '<option value="0">顶级</option>';
		}
		self::cattreeIterate($tDatas, $pPid, 0);
		echo '</select>';
	}

	/**
	 * cattree 迭代函数
	 *
	 * @param array $datas 分类数组
	 * @param int $i 层级
	 * @param int $count 占位符个数
	 */
	static function cattreeIterate(&$datas, $i, $count){
		if(isset($datas[$i])) foreach($datas[$i] as $v1){
			echo "<option value='{$v1['cid']}'", $i == 0? " class='option'": "", ">", str_repeat('　　', $count), $v1['name'], "</option>";
			self::cattreeIterate($datas, $v1['cid'], $count + 1);
		}
	}

	/**
	 * 真实IP
	 * @return string 用户IP
	 */
	static function realip(){
		foreach(array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR') as $v1){
			if(isset($_SERVER[$v1])){
				$tIP = ($tPos = strpos($_SERVER[$v1], ','))? substr($_SERVER[$v1], 0, $tPos): $_SERVER[$v1];
				break;
			}
			if($tIP = getenv($v1)){
				$tIP = ($tPos = strpos($tIP, ','))? substr($tIP, 0, $tPos): $tIP;
				break;
			}
		}
		return $tIP;
	}

	/**
	 * 发送邮件
	 * @param $pAddress 地址
	 * @param $pSubject 标题
	 * @param $pBody 内容
	 */
	static function mailto($pAddress, $pSubject, $pBody){
		static $mail;
		if(!$mail){
			require preg_replace( '/Tool/' ,'' , dirname(__FILE__)) . 'Source/PHPMailer/PHPmailer.php';

			$tMailconfig = Yaf_Registry::get("config")->mail->default->toArray();

			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->CharSet = 'utf-8';
			$mail->SMTPAuth = true;
			$mail->Port = 25;
			$mail->Host = $tMailconfig['host'];
			$mail->From = $tMailconfig['from'];
			$mail->Username = $tMailconfig['username'];
			$mail->Password = $tMailconfig['password'];
			$mail->FromName = "拍医拍";
			$mail->IsHTML(true);
		}
		$mail->ClearAddresses();
		$mail->ClearCCs();
		$mail->ClearBCCs();
		$mail->AddAddress($pAddress);
		#$pCcAddress && $mail->AddBCC($pCcAddress);
		$mail->Subject = $pSubject;
		$mail->MsgHTML(preg_replace('/\\\\/', '', $pBody));
		if($mail->Send()){
			return 1;
		}else{
			return $mail->ErrorInfo;
		}
	}
    /**
     * 计算身份证的年龄
     */
    static function getAge($pIdCard){
        $tBirthday = strlen($pIdCard)==15 ? ('19' . substr($pIdCard, 6, 2)) : substr($pIdCard, 6, 4); 
        $tAge = date('Y')-$tBirthday;
        return $tAge;
    }

	/**
	 * 提示信息
	 * @param string $pMsg 信息
	 * @param bool $pUrl 跳转到
	 */
	static function showMsg($pMsg, $pUrl = false){
		is_array($pMsg) && $pMsg = join('\n', $pMsg);
        echo "<html>";
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        echo '<link rel="stylesheet" href="/plugins/SweetAlert/sweetalert.css" />';
        echo '<script src="/plugins/SweetAlert/sweetalert.min.js"></script>'; 
        $str = <<<HTML
<script>window.onload = function(){
swal({
    title:'权限不足',
    text:"$pMsg",
    confirmButtonColor: "#DD6B55",
},function(){
    window.history.back();
});
}</script></html>
HTML;
        if('.' == $pUrl) $pUrl = $_SERVER['REQUEST_URI']; 
        if($pMsg) die($str);  
        echo "<script>";
        if($pUrl) echo "self.location='{$pUrl}'"; 
        elseif(empty($_SERVER['HTTP_REFERER'])) echo 'window.history.back();';
        else echo "self.location='{$_SERVER['HTTP_REFERER']}';";
        echo "</script></html>";
        die;
    }
    //日期差
    static function diffBetweenTwoDays ($day1, $day2)
    {
              $second1 = strtotime($day1);
                $second2 = strtotime($day2);
                  
                if ($second1 < $second2) {
                            $tmp = $second2;
                                $second2 = $second1;
                                $second1 = $tmp;
                                  }
                  return ($second1 - $second2) / 86400;
    }

	/**
	 * AJAX返回
	 *
	 * @param string $pMsg 提示信息
	 * @param int $pStatus 返回状态
	 * @param mixed $pData 要返回的数据
	 * @param string $pStatus ajax返回类型
	 */
	static function ajaxMsg($pMsg = '', $pStatus = 0, $pData = '', $pType = 'json'){
		if($pType == 'json'){
			header('Content-type: application/json');
		}
		# 信息
		$tResult = array('status' => $pStatus, 'msg' => $pMsg, 'data' => $pData);
		# 格式
		'json' == $pType && exit(json_encode($tResult));
		'xml' == $pType && exit(xml_encode($tResult));
		'eval' == $pType && exit($pData);
	}
	/**
	 *  APP 用
	 *
	 * @param string $pMsg 提示信息
	 * @param int $pStatus 返回状态
	 * @param mixed $pData 要返回的数据
	 * @param string $pStatus ajax返回类型
	 */
	static function appMsg_pri($pMsg = '', $pStatus = 0, $pData = '', $pType = 'json'){
		if($pType == 'json'){
			header('Content-type: application/json');
		}
		# 信息
		$tResult = array('repstatus' => $pStatus, 'repmsg' => $pMsg);
		if(!empty($pData)){
			$tResult = array_merge($tResult,$pData);
		}
		# 格式
		'json' == $pType && exit(json_encode($tResult));
		'xml' == $pType && exit(xml_encode($tResult));
		'eval' == $pType && exit($pData);
	}
	/**
	 * app web 用
	 */
	static function appMsg($pMsg = '', $pStatus = 0, $pData = '', $pJsoncallback = ''){
		if(empty($_GET['jsoncallback'])){Tool_Fnc::appMsg_pri($pMsg, $pStatus, $pData);}
		header('Content-Type:text/html;Charset=utf-8');  
		$tResult = array('repstatus' => $pStatus, 'repmsg' => $pMsg);
		if(!empty($pData)){
			$tResult = array_merge($tResult,$pData);
		}

		echo $_GET['jsoncallback'] . "(".json_encode($tResult).")";
		exit;
	}

    /**
     * 邮件模版赋值
     *
     */
    static function emailTemplate($pData , $pTemplatename){
        $pDir = preg_replace('/library\/Tool/' , '' , dirname(__FILE__));
        $pTemplatedir = $pDir . 'views/email_template/' . $pTemplatename . '.phtml';
        if(!is_file($pTemplatedir)){
            return false;
        }
        $pHtml = file_get_contents($pTemplatedir);

        $pKeys = array_keys($pData); 
        if(!count($pKeys)){ return false;}

        foreach($pKeys as $pKey){
           $pHtml = preg_replace('/{'.$pKey.'}/' , $pData[$pKey] , $pHtml);  
        }
        return $pHtml;
    }
    public static function safe_string($pString,$pFlag = 0){
        $pString=trim($pString);                                                                               
        $pString=str_replace(array("union","into","load_file","outfile"),array("ＵＮＩＯＮ","ＩＮＴＯ","ＬＯＡＤ—ＦＩＬＥ","ＯＵＴＦＩＬＥ"),$pString);
		if(!$pFlag){
        	$pString=htmlspecialchars($pString,ENT_QUOTES);                                                    
		}
        if (!get_magic_quotes_gpc())$pString=addslashes($pString);   
        return $pString; 
    }
    public static function de_safe_string($pString){
        $pString = stripcslashes($pString);
        $pString = htmlspecialchars_decode($pString); 
        return $pString;
    }
	 /**
	  * 删除目录
	  */
	static function deldir($dirName){
		if ($handle = opendir("$dirName")) {  
			while(false !== ($item = readdir($handle))){  
				if($item != "." && $item != ".."){  
					if(is_dir("$dirName/$item")){  
						self::deldir("$dirName/$item");  
					}else{  
						unlink("$dirName/$item");
					}  
				}  
			}  
			closedir($handle);  
			return rmdir($dirName); 
		}   
		return false;
	 }
    public function xtprint($result,$message,$total=0,$data,$page=1){
        $printarr=array("result"=>$result,"message"=>$message,"total"=>$total,"data"=>$data,"page"=>$page);
        echo json_encode(Tool_Fnc::xtprintnull($printarr));                                                          
        exit(); 
    }
	
	public function xtprintnull($arr){
		foreach($arr as $k=>$v){
			if(is_array($v)){
            	$backarr[$k]=Tool_Fnc::xtprintnull($v);
        	}else{
				$backarr[$k]=strlen($v)<=0?" ":$v;
			}      
		}    
		return $backarr;  
	} 

    /**
     * 密码加密
     */
    static function markmd5($pPassword,$pKey = ''){
        $tMd5str = '';
        if(empty($pKey)){$tMd5str = $pPassword;}
        #else{$tMd5str = md5(md5($pPassword).md5($pKey));}
        else{$tMd5str = md5($pPassword.md5($pKey));}
        return $tMd5str;
    }
    /** 
     * 随机数
     */
    static function newrand($pLen = 4){
        return  strtoupper(substr(md5(rand()),0 ,$pLen)); 
    }
   
    /**
     * 二维数字根据key 排序
     */
    static function arraySort($arr, $keys, $type = 'desc') {
		if(!count($arr)){return $arr;}
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if ($type == 'asc') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
		$i=0;
        foreach ($keysvalue as $k => $v) {
            $new_array[$i] = $arr[$k];
			$i++;
        }
        return $new_array;
    }

	/**
     * 本周时间
     */
    static function current_week(){
        $tStart = strtotime(date('Y-m-d H:i:s', mktime(0, 0, 0,  date('m'), date('d') - date('N')+1, date('Y'))));
        $tEnd = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'),date('d') - date('N') + 7,date('Y'))));
        return array('start'=> $tStart,'end' => $tEnd);
    }

    static function before_week(){
        $tStart = strtotime(date('Y-m-d H:i:s', mktime(0, 0, 0,  date('m'), date('d') - date('N')-6, date('Y'))));
        $tEnd = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'),date('d') - date('N') ,date('Y'))));
        return array('start'=> $tStart,'end' => $tEnd);
    }

	/**
	 * 创建目录
	 */
	static function make_dir($path) {  
		$path = str_replace(array('/', '\\', '//', '\\\\'), DIRECTORY_SEPARATOR, $path);  
		$dirs = explode(DIRECTORY_SEPARATOR, $path);  
		$tmp = '';  
		foreach ($dirs as $dir) {  
        	$tmp .= $dir . DIRECTORY_SEPARATOR;  
			if (!file_exists($tmp) && !mkdir($tmp, 0777)) {  
            	return $tmp;  
			}  
		}  
		return true;  
	}

	/**
	 * 写入文件
	 */
	static function writefile($pFile,$pContent,$pType='w'){
		$f = fopen($pFile , $pType.'+');			
		if(!fwrite($f,$pContent)){
			return false;	
		}
		fclose($f);
		return true;	
	}
	/**
	 * 读文件
	 */
	static function readfile($pFile){
		$f = fopen($pFile , 'r+');			
		if(!$tStr = fread($f,filesize($pFile))){
			return false;	
		}
		fclose($f);
		return $tStr;	
	}

	/**
	 * 解压
	 */
	static function unzip($pFile,$pExtractdir){
		$zip = new ZipArchive;
		$res = $zip->open($pFile);
		if ($res === TRUE) {
			$zip->extractTo($pExtractdir);
			$zip->close();
			return true;
		}	 
		return false;
	 }
	/**
	 * 科学计数法 转正常
	 */
	static function etonumber($pNumber){
		$tNumber = sprintf('%.9f',$pNumber);	
		return $tNumber = rtrim(rtrim( $tNumber , '0') , '.');
	}

    public static function getSortedCategory($data,$pid=0,$html="|---",$level=0){
        $temp = array();
        foreach ($data as $k => $v) {
            if($v['pid'] == $pid){  
                $str = str_repeat($html, $level);
                $v['html'] = $str;
                $temp[] = $v;
                $temp = array_merge($temp,self::getSortedCategory($data,$v['id'],'|---',$level+1));              
            }           
        }                                                                                    
        return $temp;
    }    

    /**
     * 年龄
     */
    static function age($birthday ,$tCurdate = ''){
        $tCTime = time();
        if(!empty($tCurdate)){$tCTime = strtotime($tCurdate);}
        $age = date('Y', $tCTime) - date('Y', strtotime($birthday)) - 1;  
        if (date('m', $tCTime) == date('m', strtotime($birthday))){  
            if (date('d',$tCTime) > date('d', strtotime($birthday))){  
                    $age++;  
            }  
        }elseif (date('m', $tCTime) > date('m', strtotime($birthday))){  
            $age++;  
        }  
        return  $age;  
    }

    public static function is_mobile(){
        $user_agent = $_SERVER['HTTP_USER_AGENT']; // get the user agent value - this should be cleaned to ensure no nefarious input gets executed
        $accept     = $_SERVER['HTTP_ACCEPT']; // get the content accept value - this should be cleaned to ensure no nefarious input gets executed
        return false
        || (preg_match('/ipad/i',$user_agent))
        || (preg_match('/ipod/i',$user_agent)||preg_match('/iphone/i',$user_agent))
        || (preg_match('/android/i',$user_agent))
        || (preg_match('/opera mini/i',$user_agent))
        || (preg_match('/blackberry/i',$user_agent))
        || (preg_match('/(pre\/|palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/i',$user_agent))
        || (preg_match('/(iris|3g_t|windows ce|opera mobi|windows ce; smartphone;|windows ce; iemobile)/i',$user_agent))
        || (preg_match('/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|m881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|s800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|d736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |sonyericsson|samsung|240x|x320|vx10|nokia|sony cmd|motorola|up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|psp|treo)/i',$user_agent))
        || ((strpos($accept,'text/vnd.wap.wml')>0)||(strpos($accept,'application/vnd.wap.xhtml+xml')>0))
        || (isset($_SERVER['HTTP_X_WAP_PROFILE'])||isset($_SERVER['HTTP_PROFILE']))
        || (in_array(strtolower(substr($user_agent,0,4)),array('1207'=>'1207','3gso'=>'3gso','4thp'=>'4thp','501i'=>'501i','502i'=>'502i','503i'=>'503i','504i'=>'504i','505i'=>'505i','506i'=>'506i','6310'=>'6310','6590'=>'6590','770s'=>'770s','802s'=>'802s','a wa'=>'a wa','acer'=>'acer','acs-'=>'acs-','airn'=>'airn','alav'=>'alav','asus'=>'asus','attw'=>'attw','au-m'=>'au-m','aur '=>'aur ','aus '=>'aus ','abac'=>'abac','acoo'=>'acoo','aiko'=>'aiko','alco'=>'alco','alca'=>'alca','amoi'=>'amoi','anex'=>'anex','anny'=>'anny','anyw'=>'anyw','aptu'=>'aptu','arch'=>'arch','argo'=>'argo','bell'=>'bell','bird'=>'bird','bw-n'=>'bw-n','bw-u'=>'bw-u','beck'=>'beck','benq'=>'benq','bilb'=>'bilb','blac'=>'blac','c55/'=>'c55/','cdm-'=>'cdm-','chtm'=>'chtm','capi'=>'capi','cond'=>'cond','craw'=>'craw','dall'=>'dall','dbte'=>'dbte','dc-s'=>'dc-s','dica'=>'dica','ds-d'=>'ds-d','ds12'=>'ds12','dait'=>'dait','devi'=>'devi','dmob'=>'dmob','doco'=>'doco','dopo'=>'dopo','el49'=>'el49','erk0'=>'erk0','esl8'=>'esl8','ez40'=>'ez40','ez60'=>'ez60','ez70'=>'ez70','ezos'=>'ezos','ezze'=>'ezze','elai'=>'elai','emul'=>'emul','eric'=>'eric','ezwa'=>'ezwa','fake'=>'fake','fly-'=>'fly-','fly_'=>'fly_','g-mo'=>'g-mo','g1 u'=>'g1 u','g560'=>'g560','gf-5'=>'gf-5','grun'=>'grun','gene'=>'gene','go.w'=>'go.w','good'=>'good','grad'=>'grad','hcit'=>'hcit','hd-m'=>'hd-m','hd-p'=>'hd-p','hd-t'=>'hd-t','hei-'=>'hei-','hp i'=>'hp i','hpip'=>'hpip','hs-c'=>'hs-c','htc '=>'htc ','htc-'=>'htc-','htca'=>'htca','htcg'=>'htcg','htcp'=>'htcp','htcs'=>'htcs','htct'=>'htct','htc_'=>'htc_','haie'=>'haie','hita'=>'hita','huaw'=>'huaw','hutc'=>'hutc','i-20'=>'i-20','i-go'=>'i-go','i-ma'=>'i-ma','i230'=>'i230','iac'=>'iac','iac-'=>'iac-','iac/'=>'iac/','ig01'=>'ig01','im1k'=>'im1k','inno'=>'inno','iris'=>'iris','jata'=>'jata','java'=>'java','kddi'=>'kddi','kgt'=>'kgt','kgt/'=>'kgt/','kpt '=>'kpt ','kwc-'=>'kwc-','klon'=>'klon','lexi'=>'lexi','lg g'=>'lg g','lg-a'=>'lg-a','lg-b'=>'lg-b','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-f'=>'lg-f','lg-g'=>'lg-g','lg-k'=>'lg-k','lg-l'=>'lg-l','lg-m'=>'lg-m','lg-o'=>'lg-o','lg-p'=>'lg-p','lg-s'=>'lg-s','lg-t'=>'lg-t','lg-u'=>'lg-u','lg-w'=>'lg-w','lg/k'=>'lg/k','lg/l'=>'lg/l','lg/u'=>'lg/u','lg50'=>'lg50','lg54'=>'lg54','lge-'=>'lge-','lge/'=>'lge/','lynx'=>'lynx','leno'=>'leno','m1-w'=>'m1-w','m3ga'=>'m3ga','m50/'=>'m50/','maui'=>'maui','mc01'=>'mc01','mc21'=>'mc21','mcca'=>'mcca','medi'=>'medi','meri'=>'meri','mio8'=>'mio8','mioa'=>'mioa','mo01'=>'mo01','mo02'=>'mo02','mode'=>'mode','modo'=>'modo','mot '=>'mot ','mot-'=>'mot-','mt50'=>'mt50','mtp1'=>'mtp1','mtv '=>'mtv ','mate'=>'mate','maxo'=>'maxo','merc'=>'merc','mits'=>'mits','mobi'=>'mobi','motv'=>'motv','mozz'=>'mozz','n100'=>'n100','n101'=>'n101','n102'=>'n102','n202'=>'n202','n203'=>'n203','n300'=>'n300','n302'=>'n302','n500'=>'n500','n502'=>'n502','n505'=>'n505','n700'=>'n700','n701'=>'n701','n710'=>'n710','nec-'=>'nec-','nem-'=>'nem-','newg'=>'newg','neon'=>'neon','netf'=>'netf','noki'=>'noki','nzph'=>'nzph','o2 x'=>'o2 x','o2-x'=>'o2-x','opwv'=>'opwv','owg1'=>'owg1','opti'=>'opti','oran'=>'oran','p800'=>'p800','pand'=>'pand','pg-1'=>'pg-1','pg-2'=>'pg-2','pg-3'=>'pg-3','pg-6'=>'pg-6','pg-8'=>'pg-8','pg-c'=>'pg-c','pg13'=>'pg13','phil'=>'phil','pn-2'=>'pn-2','pt-g'=>'pt-g','palm'=>'palm','pana'=>'pana','pire'=>'pire','pock'=>'pock','pose'=>'pose','psio'=>'psio','qa-a'=>'qa-a','qc-2'=>'qc-2','qc-3'=>'qc-3','qc-5'=>'qc-5','qc-7'=>'qc-7','qc07'=>'qc07','qc12'=>'qc12','qc21'=>'qc21','qc32'=>'qc32','qc60'=>'qc60','qci-'=>'qci-','qwap'=>'qwap','qtek'=>'qtek','r380'=>'r380','r600'=>'r600','raks'=>'raks','rim9'=>'rim9','rove'=>'rove','s55/'=>'s55/','sage'=>'sage','sams'=>'sams','sc01'=>'sc01','sch-'=>'sch-','scp-'=>'scp-','sdk/'=>'sdk/','se47'=>'se47','sec-'=>'sec-','sec0'=>'sec0','sec1'=>'sec1','semc'=>'semc','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','sk-0'=>'sk-0','sl45'=>'sl45','slid'=>'slid','smb3'=>'smb3','smt5'=>'smt5','sp01'=>'sp01','sph-'=>'sph-','spv '=>'spv ','spv-'=>'spv-','sy01'=>'sy01','samm'=>'samm','sany'=>'sany','sava'=>'sava','scoo'=>'scoo','send'=>'send','siem'=>'siem','smar'=>'smar','smit'=>'smit','soft'=>'soft','sony'=>'sony','t-mo'=>'t-mo','t218'=>'t218','t250'=>'t250','t600'=>'t600','t610'=>'t610','t618'=>'t618','tcl-'=>'tcl-','tdg-'=>'tdg-','telm'=>'telm','tim-'=>'tim-','ts70'=>'ts70','tsm-'=>'tsm-','tsm3'=>'tsm3','tsm5'=>'tsm5','tx-9'=>'tx-9','tagt'=>'tagt','talk'=>'talk','teli'=>'teli','topl'=>'topl','hiba'=>'hiba','up.b'=>'up.b','upg1'=>'upg1','utst'=>'utst','v400'=>'v400','v750'=>'v750','veri'=>'veri','vk-v'=>'vk-v','vk40'=>'vk40','vk50'=>'vk50','vk52'=>'vk52','vk53'=>'vk53','vm40'=>'vm40','vx98'=>'vx98','virg'=>'virg','vite'=>'vite','voda'=>'voda','vulc'=>'vulc','w3c '=>'w3c ','w3c-'=>'w3c-','wapj'=>'wapj','wapp'=>'wapp','wapu'=>'wapu','wapm'=>'wapm','wig '=>'wig ','wapi'=>'wapi','wapr'=>'wapr','wapv'=>'wapv','wapy'=>'wapy','wapa'=>'wapa','waps'=>'waps','wapt'=>'wapt','winc'=>'winc','winw'=>'winw','wonu'=>'wonu','x700'=>'x700','xda2'=>'xda2','xdag'=>'xdag','yas-'=>'yas-','your'=>'your','zte-'=>'zte-','zeto'=>'zeto','acs-'=>'acs-','alav'=>'alav','alca'=>'alca','amoi'=>'amoi','aste'=>'aste','audi'=>'audi','avan'=>'avan','benq'=>'benq','bird'=>'bird','blac'=>'blac','blaz'=>'blaz','brew'=>'brew','brvw'=>'brvw','bumb'=>'bumb','ccwa'=>'ccwa','cell'=>'cell','cldc'=>'cldc','cmd-'=>'cmd-','dang'=>'dang','doco'=>'doco','eml2'=>'eml2','eric'=>'eric','fetc'=>'fetc','hipt'=>'hipt','http'=>'http','ibro'=>'ibro','idea'=>'idea','ikom'=>'ikom','inno'=>'inno','ipaq'=>'ipaq','jbro'=>'jbro','jemu'=>'jemu','java'=>'java','jigs'=>'jigs','kddi'=>'kddi','keji'=>'keji','kyoc'=>'kyoc','kyok'=>'kyok','leno'=>'leno','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-g'=>'lg-g','lge-'=>'lge-','libw'=>'libw','m-cr'=>'m-cr','maui'=>'maui','maxo'=>'maxo','midp'=>'midp','mits'=>'mits','mmef'=>'mmef','mobi'=>'mobi','mot-'=>'mot-','moto'=>'moto','mwbp'=>'mwbp','mywa'=>'mywa','nec-'=>'nec-','newt'=>'newt','nok6'=>'nok6','noki'=>'noki','o2im'=>'o2im','opwv'=>'opwv','palm'=>'palm','pana'=>'pana','pant'=>'pant','pdxg'=>'pdxg','phil'=>'phil','play'=>'play','pluc'=>'pluc','port'=>'port','prox'=>'prox','qtek'=>'qtek','qwap'=>'qwap','rozo'=>'rozo','sage'=>'sage','sama'=>'sama','sams'=>'sams','sany'=>'sany','sch-'=>'sch-','sec-'=>'sec-','send'=>'send','seri'=>'seri','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','siem'=>'siem','smal'=>'smal','smar'=>'smar','sony'=>'sony','sph-'=>'sph-','symb'=>'symb','t-mo'=>'t-mo','teli'=>'teli','tim-'=>'tim-','tosh'=>'tosh','treo'=>'treo','tsm-'=>'tsm-','upg1'=>'upg1','upsi'=>'upsi','vk-v'=>'vk-v','voda'=>'voda','vx52'=>'vx52','vx53'=>'vx53','vx60'=>'vx60','vx61'=>'vx61','vx70'=>'vx70','vx80'=>'vx80','vx81'=>'vx81','vx83'=>'vx83','vx85'=>'vx85','wap-'=>'wap-','wapa'=>'wapa','wapi'=>'wapi','wapp'=>'wapp','wapr'=>'wapr','webc'=>'webc','whit'=>'whit','winw'=>'winw','wmlb'=>'wmlb','xda-'=>'xda-')))
        ;
    }
    /*
	 * 生成随机字符串
	 * @param int $length 生成随机字符串的长度
	 * @param string $char 组成随机字符串的字符串
	 * @return string $string 生成的随机字符串
	 */
	static function str_rand($length = 32, $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
	    if(!is_int($length) || $length < 0) {
	        return false;
	    }

	    $string = '';
	    for($i = $length; $i > 0; $i--) {
	        $string .= $char[mt_rand(0, strlen($char) - 1)];
	    }

	    return $string;
	}
	/**
	 * [str_rand_32 生成唯一32位字符串]
	 * @author 张伟 2018-03-14 
	 * @return [type] [description]
	 */
	static function str_rand_32() {		
		$uniqid = md5(uniqid(microtime(true),true));
	    return $uniqid;
	}
	static function curl_get($url, array $params = array(),$timeout=5){
            $ch = curl_init(); 
            curl_setopt ($ch, CURLOPT_URL, $url);            
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);            
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);             
            $file_contents = curl_exec($ch);             
            curl_close($ch);
            return $file_contents;      
    }
    static function curl_post($url,array $params = array(),$timeout){
            $ch = curl_init();//初始化curl
            curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
            curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            $data = curl_exec($ch);//运行curl
            curl_close($ch);
            return($data);//输出结果            
        }
}
