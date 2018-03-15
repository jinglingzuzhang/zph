<?php  
//设置时区  
date_default_timezone_set("Asia/Shanghai");  
//定义TOKEN常量，这里的"weixin"就是在公众号里配置的TOKEN  
define("TOKEN", "weixin");  
  
require_once("Utils.php");  
//打印请求的URL查询字符串到query.xml  
Utils::traceHttp();  
  
$wechatObj = new wechatCallBackapiTest();  
/** 
 * 如果有"echostr"字段，说明是一个URL验证请求， 
 * 否则是微信用户发过来的信息 
 */  
if (isset($_GET["echostr"])){  
    $wechatObj->valid();  
}else {  
    $wechatObj->responseMsg();  
}  
  
class wechatCallBackapiTest  
{  
    /** 
     * 用于微信公众号里填写的URL的验证， 
     * 如果合格则直接将"echostr"字段原样返回 
     */  
    public function valid()  
    {  
        $echoStr = $_GET["echostr"];  
        if ($this->checkSignature()){  
            echo $echoStr;  
            exit;  
        }  
    }  
  
    /** 
     * 用于验证是否是微信服务器发来的消息 
     * @return bool 
     */  
    private function checkSignature()  
    {  
        $signature = $_GET["signature"];  
        $timestamp = $_GET["timestamp"];  
        $nonce = $_GET["nonce"];  
  
        $token = TOKEN;  
        $tmpArr = array($token, $timestamp, $nonce);  
        sort($tmpArr);  
        $tmpStr = implode($tmpArr);  
        $tmpStr = sha1($tmpStr);  
  
        if ($tmpStr == $signature){  
            return true;  
        }else {  
            return false;  
        }  
    }  
  
    /** 
     * 响应用户发来的消息 
     */  
    public function responseMsg()  
    {  
        //获取post过来的数据，它一个XML格式的数据  
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];  
        //将数据打印到log.xml  
        Utils::logger($postStr);  
        if (!empty($postStr)){  
            //将XML数据解析为一个对象  
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);  
            $RX_TYPE = trim($postObj->MsgType);  
            //消息类型分离  
            switch($RX_TYPE){  
                case "event":  
                    $result = $this->receiveEvent($postObj);  
                    break;  
                default:  
                    $result = "unknow msg type:".$RX_TYPE;  
                    break;  
            }  
            //打印输出的数据到log.xml  
            Utils::logger($result, '公众号');  
            echo $result;  
        }else{  
            echo "";  
            exit;  
        }  
    }  
  
    /** 
     * 接收事件消息 
     */  
    private function receiveEvent($object)  
    {  
        switch ($object->Event){  
            //关注公众号事件  
            case "subscribe":  
                $content = "欢迎关注微微一笑很倾城";  
                break;  
            default:  
                $content = "";  
                break;  
        }  
        $result = $this->transmitText($object, $content);  
        return $result;  
    }  
  
    /** 
     * 回复文本消息 
     */  
    private function transmitText($object, $content)  
    {  
        $xmlTpl = "<xml>  
    <ToUserName><![CDATA[%s]]></ToUserName>  
    <FromUserName><![CDATA[%s]]></FromUserName>  
    <CreateTime><![CDATA[%s]]></CreateTime>  
    <MsgType><![CDATA[text]]></MsgType>  
    <Content><![CDATA[%s]]></Content>  
</xml>";  
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);  
        return $result;  
    }  
}  