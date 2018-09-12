<?php 
/**
 *龙支付接
 */
class Tool_Ccb{

    //接口API URL地址
    const bankURL = 'https://ibsbjstar.ccb.com.cn/CCBIS/ccbMain?CCB_IBSVersion=V6';
    private $PUB;

    //建行提供的密钥，需要登陆建行商户后台下载   
    private  $key;

    //商户代码          
    private  $MERCHANTID;
    //商户柜台代码       
    private  $POSID; 
    //银行分行代码           
    private  $BRANCHID;
    //订单编号      
    private  $ORDERID; 
    //订单金额        
    private  $PAYMENT;
    //币种            
    private  $CURCODE;
    //交易码           
    private  $TXCODE;
    //备注1       
    private  $REMARK1; 
    //备注2          
    private  $REMARK2;           
    //返回类型
    private  $RETURNTYPE;
    //公钥后30位        
    private  $PUB32TR2;
    //MAC校验          
    private  $MAC;               

    private  $tmp = '';
    private  $temp_New = '';
    private  $temp_New1 = '';


    private $params = array();
    /**
     * [__construct 构造方法]
     * @author 张伟 2018-03-21 
     * @param  [type] $MERCHANTID [description]
     * @param  [type] $POSID      [description]
     * @param  [type] $BRANCHID   [description]
     * @param  [type] $key        [description]
     */
    public function __construct($MERCHANTID, $POSID, $BRANCHID, $PUB)
    {
        $this->MERCHANTID = $MERCHANTID;
        $this->POSID = $POSID;
        $this->BRANCHID = $BRANCHID;
        $this->PUB = $PUB;
    }   
    /**
     * [createQRcode 创建二维码代码]
     * @author 张伟 2018-03-26 
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function createQRcode( $array )
    {
        
        $this->params['MERCHANTID']=$this->$MERCHANTID;
        $this->params['POSID']=$this->$POSID;
        $this->params['BRANCHID']=$this->$BRANCHID;
        $this->params['ORDERID']=$array['ORDERID'];
        $this->params['PAYMENT']=$array['PAYMENT']
        $this->params['CURCODE']=$array['CURCODE'];
        $this->params['TXCODE']=$array['TXCODE'];
        $this->params['RETURNTYPE']=$array['RETURNTYPE'];
        $this->params['TIMEOUT']=$array['TIMEOUT'];

        $url = $this->MakeUrl( $this->params );
        return $result;
    }


    //完整的串
    //MERCHANTID=123456789&POSID=000000000&BRANCHID=110000000&ORDERID=19991101234&PAYMENT=0.01&CURCODE=01&TXCODE=530550&REMARK1=&REMARK2=&RETURNTYPE=3&TIMEOUT=&PUB=30819d300d06092a864886f70d0108

    /**
     * [MakeSign 生成完整Url]
     * @author 张伟 2018-03-26 
     * @param  [type] $params [description]
     */
    public function MakeUrl( $params ){
        //步骤一：生成string串
        $string = $this->ToUrlParams($params);
        //签名步骤二：在string后加入KEY
        $string1= $string . "&PUB=".$this->PUB;
        //签名步骤三：MD5加密
        $MAC = md5($string1);
        //签名步骤四：拼接龙支付url
        $result =  bankURL."&".$string."&MAC".$MAC; 
        return $result;
    }
    /**
    * 将参数拼接为url: key=value&key=value
    * @param $params
    * @return string
    */
    public function ToUrlParams( $params ){
        $string = '';
        if( !empty($params) ){
        $array = array();
        foreach( $params as $key => $value ){
        $array[] = $key.'='.$value;
        }
        $string = implode("&",$array);
        }
        return $string;
    }
    /**
    * 生成签名
    * @return 签名
    */
    public function MakeSign( $params ){
    //签名步骤一：按字典序排序数组参数
        ksort($params);
        $string = $this->ToUrlParams($params);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$this->key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
    /**
     * [curl_post post获取参数]
     * @Author   张伟-vincent
     * @DateTime 2018-05-16
     * @link     [www.mrsix.me]
     * @param    [type]         $url     [description]
     * @param    array          $params  [description]
     * @param    [type]         $timeout [description]
     * @return   [type]                  [description]
     */
    public function curl_post($url,array $params = array(),$timeout){
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

    //echo get_data_from_server("127.0.0.1",55533,"POSID=100001329&BRANCHID=500000000&ORDERID=GWA10081217305965959&PAYMENT=0.01&CURCODE=01&REMARK1=&REMARK2=&SUCCESS=Y&TYPE=1&REFERER=http://114.255.7.208/page/bankpay.do&CLIENTIP=114.255.7.194&SIGN=9a7efc7f15f4b0e7f8fba52649d6b97ae33fad44598a7ca1c26196e8ddba00ecf91a596346e4bfd3cc6d2bdba6c085a3cdb0f231d865d7856e37de89846a371c8bc09f8f2643284260499e1d3f464d9ca9d379fe8af3202a09fc83d39f5c68501a4627d62a3ae891d4b0ff6aa21d61f6ba0e9c8bc5840b292af853d2736ce04a\n");
    /**
     * [replyNotify 龙支付验签方法]
     * @Author   张伟-vincent
     * @DateTime 2018-05-16
     * @link     [www.zxliu.cn]
     * @return   [type]         [description]
     */
    public function ccbReplyNotify($data){
        if($data['SUCCESS']=="Y"){
            $sign["POSID"]=$data['POSID'];
            $sign["BRANCHID"]=$data['BRANCHID'];
            $sign["ORDERID"]=$data['ORDERID'];
            $sign["PAYMENT"]=$data['PAYMENT'];
            $sign["CURCODE"]=$data['CURCODE'];
            $sign["REMARK1"]=$data['REMARK1'];
            $sign["REMARK2"]=$data['REMARK2'];
            if(isset($data['ACC_TYPE'])){
                $sign["ACC_TYPE"]=$data['ACC_TYPE'];   
            }

            if(isset($data("REFERER")){
               $sign["REFERER"]=$data['REFERER']; 
            }
            if(isset($data("CLIENTIP")){
               $sign["CLIENTIP"]=$data['CLIENTIP']; 
            }
            if(isset($data("ACCDATE")){
               $sign["ACCDATE"]=$data['ACCDATE']; 
            } 
            if(isset($data("USRMSG")){
                $sign["USRMSG"]=$data['USRMSG'];      
            }   
            if(isset($data['PAYTYPE'])){
                $sign["PAYTYPE"]=$data['PAYTYPE'];
            }
            if(isset($data['SIGN'])){
                 $sign["SIGN"]=$data['SIGN'];
            }                                       
            $sign["SUCCESS"]=$data['SUCCESS'];
            $sign["ORDERID"]=$data['ORDERID'];
            
            $ccbSign=$this->MakeCcbSign($sign);

           return  $this->get_data_from_server( "127.0.0.1",55533,$ccbSign )

        }
    }
    /**
    * 建行龙支付回调验签
    * @return 签名
    */
    public function MakeCcbSign( $params ){
    //签名步骤一：按字典序排序数组参数
        ksort($params);
        $string = $this->ToUrlParams($params);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$this->key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
    /**
     * [get_data_from_server 龙支付socket验签接口]
     * @author 张伟 2018-05-17 
     * @param  [type] $address      [description]
     * @param  [type] $service_port [description]
     * @param  [type] $send_data    [description]
     * @return [type]               [description]
     */
    public function get_data_from_server($address, $service_port, $send_data) {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket < 0) {
                echo "socket创建失败 " . socket_strerror($socket) . "\n";
            } else {
                echo "socket创建成功 \n";
            }   
            $result = socket_connect($socket, $address, $service_port);
            if ($result < 0) {
                echo "SOCKETÁ¬lianjie xinxi: ($result) " . socket_strerror($result) . "\n";
            } else {
                echo "OK.\n";
            }   
            //·¢ËÍÃüÁî
            $in = $send_data;
            $out = '';
            socket_write($socket, $in, strlen($in));

            while ($out = socket_read($socket, 2048)) {
                echo $out;
            }

            socket_close($socket);
            echo "OK,He He.\n\n";
            return $out;
    }
    /**
     * [refund 龙支付退款方法]
     * @author 张伟 2018-05-17 
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function refund($params){
       $refund_xml=$this->refund_to_xml($params);

    }
    /**
    * 龙支付退款xml字符拼接
    * @param $params 参数名称
    * return string 返回组装的xml
    **/
    public function refund_to_xml( $params ){
        if(!is_array($params)|| count($params) <= 0)
        {
            return false;
        }
        $xml = "<?xml version='1.0' encoding='GB2312' standalone='yes' ?> ";
        $xml = ."<TX>";
        foreach ($params as $key=>$val)
        {   
            if($val=="LANGUAGE"){
                $xml = ."<TX_INFO>";   
                $xml.="<".$key.">".$val."</".$key.">";  
            }else if($val=="REFUND_CODE"){
                $xml.="<".$key.">".$val."</".$key.">";     
                $xml = ."</TX_INFO>";               
            }else{
                 $xml.="<".$key.">".$val."</".$key.">";  
            } 
        }
        $xml.="</TX>";
        return $xml; 
    }
    /**
     * [refund 龙支付下载方法]
     * @author 张伟 2018-05-17 
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function download($params){
       $download_xml=$this->download_to_xml($params);
       
    }
    /**
    * 龙支付下载xml字符拼接
    * @param $params 参数名称
    * return string 返回组装的xml
    **/
    public function download_to_xml( $params ){
        if(!is_array($params)|| count($params) <= 0)
        {
            return false;
        }
        $xml = "<?xml version='1.0' encoding='GB2312' standalone='yes' ?> ";
        $xml = ."<TX>";
        foreach ($params as $key=>$val)
        {   
            if($val=="SOURCE"){
                $xml = ."<TX_INFO>";   
                $xml.="<".$key.">".$val."</".$key.">";  
            }else if($val=="LOCAL_REMOTE"){
                $xml.="<".$key.">".$val."</".$key.">";     
                $xml = ."</TX_INFO>";               
            }else{
                 $xml.="<".$key.">".$val."</".$key.">";  
            } 
        }
        $xml.="</TX>";
        return $xml; 
    }
    /**
    * 将xml转为array
    * @param string $xml
    * return array
    */
    public function xml_to_data($xml){ 
        if(!$xml){
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true); 
        return $data;
    }

    /**
    * 以post方式提交xml到对应的接口url
    * 
    * @param string $xml 需要post的xml数据
    * @param string $url url
    * @param bool $useCert 是否需要证书，默认不需要
    * @param int $second url执行超时时间，默认30s
    * @throws WxPayException
    */
    public function postXmlCurl($xml, $url, $useCert = false, $second = 30){ 
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if($useCert == true){
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        //curl_setopt($ch,CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        //curl_setopt($ch,CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
        curl_close($ch);
        return $data;
        } else { 
        $error = curl_errno($ch);
        curl_close($ch);
        return false;
        }
    }
}
?>