<?php
/**
* @name:课件图书服务器
* @author:baoxianjian
* @date:15:09 2016/1/26
*/

namespace common\services\learning;

use common\models\learning\LnCoursewareBook;
use common\services\framework\ExternalSystemService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use common\helpers\TNetworkHelper; 

class CoursewareBookService extends LnCoursewareBook{

    public function searchBookFromDouban($word,$id,$isbn,$name)
    {
        $word=trim($word);
        $id=trim($id);
        $isbn=trim($isbn);
        
        if(!$id && !$word && !$isbn && !$name){return null;}
        
        //https://api.douban.com/v2/book/search?q=python&count=5&fields=id,title
        //
       
        $externalSystemService = new ExternalSystemService();
        $api_address =$externalSystemService->getExternalSystemInfoByExternalSystemCode('douban-book')->api_address;

        if($id)
        {
            $url = $api_address .'/'. $id;
        }
        else if($isbn)
        {
            $url = $api_address .'/isbn/'. $isbn;
        }
        else if($name)
        {
           $url = $api_address."/search?q=" .urlencode($name)."&count=1";
        }
        else
        {
            $url = $api_address."search?q=" .urlencode($word)."&count=10&fields=id,title,publisher";
        }

        $result=TNetworkHelper::HttpGet($url,null);

        if($result['content'])
        {
           $temp=$result['content'];
           if($id || $isbn)
           {  
              $temp=json_decode($temp);
              return $temp; 
           } 
           $temp=json_decode($temp,1);
           
           if($name)
           {
               return $temp['books'][0];
           }
           
           return $temp['books'];
        }
        return null;
    }
}