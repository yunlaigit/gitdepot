<?php
/**
 * 微信 app支付 方式
 */
Yaf_Loader::import('SDK/wxpayapi/lib/WxPay.Api.php');
class ANDROIDWxPay{
    #统一下单 
    public function preOrder($tBody,$tTotal_fee){
        $WxPayUnifiedOrder = new WxPayUnifiedOrder;
        $tOrderno = Tool_Fnc::build_order_no();
        $WxPayUnifiedOrder->SetOut_trade_no($tOrderno);//商品订单号
        $WxPayUnifiedOrder->SetBody($tBody);//商品描述
        $WxPayUnifiedOrder->SetToTal_fee($tTotal_fee);//总金额，单位为 分
        $WxPayUnifiedOrder->SetTrade_type('APP');//交易方式
        $tResponse = WxPayApi::unifiedOrder($WxPayUnifiedOrder);
        $tResponse['out_trade_no'] = $tOrderno;
        return $tResponse;
    }

    #异步处理 通知地址
    public function notifyUrl($callback){
        $tRes = WxPayApi::notify($callback,$error);
        if(!is_array($tRes) || count($tRes) <= 0) {
            throw new WxPayException("数组数据异常！");
        }
        $xml = "<xml>";
        foreach ($tRes as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml; 
    }

    #查询订单
    public function orderquery($tOut_trade_no){
        $WxPayOrderQuery = new WxPayOrderQuery;
        $WxPayOrderQuery->SetOut_trade_no($tOut_trade_no);//商户订单号
        $tResponse = WxPayApi::orderQuery($WxPayOrderQuery);
        return $tResponse;
    }

    #关闭订单
    public function closeorder($tOut_trade_no){
        $WxPayCloseOrder = new WxPayCloseOrder;
        $WxPayCloseOrder->SetOut_trade_no($tOut_trade_no);//商户订单号
        $tResponse = WxPayApi::orderQuery($WxPayCloseOrder);
        return $tResponse;
    }
}
