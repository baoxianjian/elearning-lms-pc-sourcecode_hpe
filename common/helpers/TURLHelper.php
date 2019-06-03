<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/15/15
 * Time: 5:00 PM
 */

namespace common\helpers;

use Yii;
use yii\helpers\Html;

class TURLHelper {

    private static function keyED($txt,$encrypt_key) //定义一个keyED
    {
        $encrypt_key = md5($encrypt_key);
        $ctr=0;
        $tmp = "";
        for($i=0;$i<strlen($txt);$i++)
        {
            if ($ctr==strlen($encrypt_key))
                $ctr=0;
            $tmp.= substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1);
            $ctr++;
        }
        return $tmp;
    }
    private static function encrypt($txt,$key)
    {
        $encrypt_key = md5(mt_rand(0,100));
        $ctr=0;
        $tmp = "";
        for ($i=0;$i<strlen($txt);$i++)
        {
            if ($ctr==strlen($encrypt_key))
                $ctr=0;
            $tmp.=substr($encrypt_key,$ctr,1) . (substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1));
            $ctr++;
        }
        return TURLHelper::keyED($tmp,$key);
    }
    private static function decrypt($txt,$key)
    {
        $txt = TURLHelper::keyED($txt,$key);
        $tmp = "";
        for($i=0;$i<strlen($txt);$i++)
        {
            $md5 = substr($txt,$i,1);
            $i++;
            $tmp.= (substr($txt,$i,1) ^ $md5);
        }
        return $tmp;
    }
    public static function encryptURL($url)
    {
        $encryptKey = 'hpe';
        return rawurlencode(base64_encode(TURLHelper::encrypt($url,$encryptKey)));
    }
    public static function decryptURL($url)
    {
        $encryptKey = 'hpe';
        return TURLHelper::decrypt(base64_decode(rawurldecode($url)),$encryptKey);
    }

    public static function getURL($str)
    {
//        $encryptKey = 'hpe';
        $str = TURLHelper::decryptURL($str);
        $url_array = explode('&',$str);
        if (is_array($url_array))
        {
            foreach ($url_array as $var)
            {
                $var_array = explode("=",$var);
                $vars[$var_array[0]]=$var_array[1];
            }
        }
        return $vars;
    }

    public static function generateShortCode($number = null) {
        if (empty($number))
            $number = rand(1, PHP_INT_MAX);

        $out   = "";
        $codes = "abcdefghjkmnpqrstuvwxyz23456789ABCDEFGHJKMNPQRSTUVWXYZ";

        while ($number > 53) {
            $key    = $number % 54;
            $number = floor($number / 54) - 1;
            $out    = $codes[$key].$out;
        }

        $temp = $codes[intval($number)];
        $result = $temp . $out;
        return $result;
    }

    /**
     * 获取指定url标题
     * @param $url url
     * @return string 标题
     */
    public static function getTitleByUrl($url) {
        $result = "";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
//        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
        if (strpos($url, "https://") !== false) {
            $UserAgent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
            curl_setopt($ch, CURLOPT_USERAGENT, $UserAgent);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        $data = curl_exec($ch);
        curl_close($ch);
        $pos = strpos($data, 'utf-8');
        if ($pos === false) {
            $data = iconv("gbk", "utf-8", $data);
        }
        preg_match("/<title>(.*)<\/title>/i", $data, $title);

        if (isset($title) && !empty($title[1])) {
            $result = $title[1];
            $isUTF8 = TStringHelper::isUTF8($result);
            if (!$isUTF8) {
                $result = iconv("gb2312", "utf-8//IGNORE", $result);
            }
        }
        if (empty($result) || strtolower($result) == '302 found') {
            return '无法获取标题';
        } else {
            return Html::decode($result);
        }
    }
    
    public static function createUrl($params){

        if (!empty(Yii::$app->request->getQueryParam("lang"))) {
            $language = Yii::$app->request->getQueryParam("lang");
                if (!is_array($params)) {
                $arr = [];
                array_push($arr,$params);
                $params = array_merge($arr, ["lang" => $language]);
            }
            else {
                $params = array_merge($params, ["lang" => $language]);
            }

            return Yii::$app->urlManager->createUrl($params);
        }
        else {
            return Yii::$app->urlManager->createUrl($params);
        }
    }
}