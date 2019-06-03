<?php
/**
 * 用户积分明细服务
 * author: 包显建
 * date: 2016/3/11
 * time: 11:02
 */

namespace common\services\framework;


use common\models\framework\FwPointRuleScale;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use common\eLearningLMS;


class PointRuleScaleService extends FwPointRuleScale{




    /**
     * 得到本企业的一条权重数据，根据场景编码
     * @param $cmpid 企业id
     * @param $scode 场景编码 scene_code
     * @return array
     */
    public function getRowByCmpIdAndSCode($cmpid,$scode)
    {
        if(!$cmpid=trim($cmpid)){return false;}
        if(!$scode=trim($scode)){return false;}
        $data = FwPointRuleScale::find(false)->andWhere(array('company_id' => $cmpid,'scene_code'=>$scode))->one();
        return $data;
    }

 
}