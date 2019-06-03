<?php
/**
 * 积分规则服务
 * author: 包显建
 * date: 2016/2/29
 * time: 11:30
 */

namespace common\services\framework;


use common\models\framework\FwUserPointDetail;
use Yii;


class UserPointDetailService extends FwUserPointDetail{


    /**
    * 根据查询条件得到结果条数
    * 
    * @param string $userid 用户id
    * @param array $srow 查询数组
    */
    public function getCountBySearch($userid,$srow=null)
    {
        if(!$userid=trim($userid)){return false;}

        $query=FwUserPointDetail::find(false);
        $query->andWhere(['=','user_id', $userid]);

        //end_time>=created_at >=start_time
        if(isset($srow['rule_id']))
        {
            $query->andWhere(['=','point_rule_id',trim($srow['rule_id'])]);
        }
        if(isset($srow['get_from_id']))
        {
            $query->andWhere(['=','get_from_id',trim($srow['get_from_id'])]);
        }
        if(isset($srow['start_time']))
        {
            $start_time=intval($srow['start_time']);
            $query->andWhere(['>=','created_at',$start_time]);
        }
        if(isset($srow['end_time']))
        {
            $end_time=intval($srow['end_time']);
//            var_dump($end_time);echo '<br/>';
            $query->andWhere(['<=','created_at',$end_time]);
        }
        /*
        var_dump($userid);
        var_dump($srow);
        echo $start_time,'<br/>',date('y-m-d H:i:s',$start_time),'<br/>';
        echo $end_time,'<br/>',date('y-m-d H:i:s',$end_time),'<br/>';
        echo $query->createCommand()->getSql(); exit;
        */ 
        return $query->count();
    }


}