<?php  
//����ʱ��  
date_default_timezone_set("Asia/Shanghai");  
//����TOKEN�����������"weixin"�����ڹ��ں������õ�TOKEN  
define("TOKEN", "weixin");  
  
require_once("Utils.php");  
//��ӡ�����URL��ѯ�ַ�����query.xml  
Utils::traceHttp();  
  
$wechatObj = new wechatCallBackapiTest();  
/** 
 * �����"echostr"�ֶΣ�˵����һ��URL��֤���� 
 * ������΢���û�����������Ϣ 
 */  
if (isset($_GET["echostr"])){  
    $wechatObj->valid();  
}else {  
    $wechatObj->responseMsg();  
}  
  
class wechatCallBackapiTest  
{  
    /** 
     * ����΢�Ź��ں�����д��URL����֤�� 
     * ����ϸ���ֱ�ӽ�"echostr"�ֶ�ԭ������ 
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
     * ������֤�Ƿ���΢�ŷ�������������Ϣ 
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
     * ��Ӧ�û���������Ϣ 
     */  
    public function responseMsg()  
    {  
        //��ȡpost���������ݣ���һ��XML��ʽ������  
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];  
        //�����ݴ�ӡ��log.xml  
        Utils::logger($postStr);  
        if (!empty($postStr)){  
            //��XML���ݽ���Ϊһ������  
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);  
            $RX_TYPE = trim($postObj->MsgType);  
            //��Ϣ���ͷ���  
            switch($RX_TYPE){  
                case "event":  
                    $result = $this->receiveEvent($postObj);  
                    break;  
                default:  
                    $result = "unknow msg type:".$RX_TYPE;  
                    break;  
            }  
            //��ӡ��������ݵ�log.xml  
            Utils::logger($result, '���ں�');  
            echo $result;  
        }else{  
            echo "";  
            exit;  
        }  
    }  
  
    /** 
     * �����¼���Ϣ 
     */  
    private function receiveEvent($object)  
    {  
        switch ($object->Event){  
            //��ע���ں��¼�  
            case "subscribe":  
                $content = "��ӭ��ע΢΢һЦ�����";  
                break;  
            default:  
                $content = "";  
                break;  
        }  
        $result = $this->transmitText($object, $content);  
        return $result;  
    }  
  
    /** 
     * �ظ��ı���Ϣ 
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