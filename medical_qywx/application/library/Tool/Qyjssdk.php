<?php
class Tool_Qyjssdk {
    public $corpId;  
    public $accessToken;
    public $secret;
    public $url;

    public function __construct($url) {
      $this->corpId = Yaf_Registry::get('config')->qywx->default->corpId;         
      $this->url = $url;      
    }

    public function getSignPackage($corpSecret) {
      $tQywx = new Tool_Qyweixin();

      //获取ApiTicket
      $jsapiTicket = $tQywx->qywxJsApiTicket($this->corpId, $corpSecret);

      // 注意 URL 一定要动态获取，不能 hardcode.
      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
      // $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      $url = urldecode($this->url);

      $timestamp = time();
      $nonceStr  = $this->createNonceStr();

      //这里参数的顺序要按照 key 值 ASCII 码升序排序
      $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

      $signature = sha1($string);

      $signPackage = array(
          "appId"     => $this->corpId,
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

