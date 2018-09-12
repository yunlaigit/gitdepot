<?php
class Tool_Jssdk {
  public $appId;
  public $appSecret;
  public $url;
  
  public function __construct($url) {
    $tWeixinarr = Yaf_Registry::get("config")->weixin->default->toArray();      
    $this->appId = $tWeixinarr['appID'];
    $this->appSecret = $tWeixinarr['appsecret'];
    $this->url = $url;
  }

  public function getSignPackage() {
    $tWeixin = new Tool_Weixin;

    //获取ApiTicket
    $jsapiTicket = $tWeixin->wxVerifyJsApiTicket();

    // 注意 URL 一定要动态获取，不能 hardcode.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    //$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url = urldecode($this->url);

    $timestamp = time();
    $nonceStr = $this->createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage; 
  }

  public function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

}

