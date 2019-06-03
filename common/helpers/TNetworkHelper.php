<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 4/14/2015
 * Time: 8:00 PM
 */
namespace common\helpers;

class TNetworkHelper
{

    public static function getClientRealIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
        }
        return $ip;
    }

    public static function getClientMacAddress()
    {
        @exec("arp -a", $array); //执行arp -a命令，结果放到数组$array中

        $mac = null;
        foreach ($array as $value) {
            if ( //匹配结果放到数组$mac_array
                strpos($value, $_SERVER["REMOTE_ADDR"]) &&
                preg_match("/(:?[0-9a-f]{2}[:-]){5}[0-9a-f]{2}/i", $value, $mac_array)
            ) {
                $mac = $mac_array[0];
                break;
            }
        }
        return $mac; //输出客户端MAC
    }


    /**
     * 发送Json对象数据
     *
     * @param $url 请求url
     * @param $data 参数数组
     * @return array
     */
    public static function HttpPost($url, $data)
    {
        $jsonStr = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );

        $return_content = curl_exec($ch);
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);

        curl_close($ch);

        $result = array();
        $result['content'] = $return_content;
        $result['code'] = $return_code;
        $result['time'] = $total_time;

        return $result;
    }

    /**
     * 发送Json对象数据
     *
     * @param $url 请求url
     * @param $data 参数数组
     * @return array
     */
    public static function HttpGet($url, $params)
    {
        //amended by baoxianjian 15:10 2016/1/26
        if(is_array($params) && count($params)>0 )
        {
            $paramStr = http_build_query($params);
            $paramStr = str_replace(['fq1=', 'fq2='], ['fq=', 'fq='], $paramStr);
        }
        if(defined('HIGHLIGHT_STYLE') && HIGHLIGHT_STYLE==1)
        {   
           $paramStr.='&hl.simple.pre=<font+color%3D"%23FF0000">&hl.simple.post=<%2Ffont>&hl.tag.pre=<font+color%3D"%23FF0000">&hl.tag.post=<%2Ffont>';
        }                                                                            
        
        if($paramStr)
        {
            $url = $url.'?'.$paramStr;
        }
        
        if($_GET['debug'])
        {
            echo  $url;
        }
        
        
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //added by baoxianjian 15:10 2016/1/26
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $return_content = curl_exec($ch);
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        curl_close($ch);

        $result = array();
        $result['content'] = $return_content;
        $result['code'] = $return_code;
        $result['time'] = $total_time;

        return $result;
    }
}
