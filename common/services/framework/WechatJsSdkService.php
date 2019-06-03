<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/4/7
 * Time: 17:33
 */

namespace common\services\framework;

use Yii;
use common\services\framework\WechatService;

class WechatJsSdkService {
    private $companyId;
    private $appId;
    private $appSecret;
    private $noncestr;
    private $timestamp;
    private $ticket;
    private $debug;
    private $url;

    private $wechatService;

    public function __construct($companyId,$appId,$appSecret,$debug = false) {
        $this->companyId = $companyId;
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->wechatService = new WechatService();
        $this->debug = $debug;
    }

    private function jsapiTicket($access_token) {
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
        $ticket = Yii::$app->cache->get('wx_jsapi_ticket');

        if($ticket) return $ticket;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($response,true);

        if(isset($res['ticket']) && !empty($res['ticket'])) {
            Yii::$app->cache->set('wx_jsapi_ticket',$res['ticket'],110*60);
            return $res['ticket'];
        }
        return null;
    }

    private function signature() {
        $this->noncestr = $this->str_random(16);
        $this->timestamp = time();
        $result = $error = null;
        $token = $this->wechatService->getAccessToken($this->companyId,$result,$error);
        $this->ticket = $this->jsapiTicket($result->accessToken);
        $this->url = Yii::$app->request->getQueryParam('url') ? Yii::$app->request->getQueryParam('url') : Yii::$app->request->getUrl();
        $tmpArr = array(
            'noncestr' => $this->noncestr,
            'timestamp' => $this->timestamp,
            'jsapi_ticket' => $this->ticket,
            'url' => $this->url
        );
        ksort($tmpArr, SORT_STRING);
        $str = [];
        foreach($tmpArr as $k => $v) {
            $str[] = $k.'='.$v;
        }
        $signature = sha1( implode("&",$str) );
        return $signature;
    }

    public function config() {
        return [
            'debug' => $this->debug,
            'signature' => $this->signature(),
            'appId' => $this->appId,
            'timestamp' => $this->timestamp,
            'nonceStr' => $this->noncestr,
            //'ticket' => $this->ticket,
            'url' => $this->url,
            'jsApiList' => [
                'startRecord',
                'stopRecord',
                'onVoiceRecordEnd',
                'playVoice',
                'stopVoice',
                'translateVoice',
                'uploadVoice',
                'downloadVoice',
                'onVoicePlayEnd',
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'scanQRCode',
                'openLocation',
                'getLocation',
                'chooseImage',
                'previewImage',
                'uploadImage',
                'downloadImage'
            ]
        ];
    }

    private function str_random($len = 15) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $len; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}