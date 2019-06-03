<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 15/12/9
 * Time: 下午3:57
 */

namespace api\services;



use common\models\framework\FwSystemInfo;

class SystemInfoService extends FwSystemInfo
{
    public function checkUpdateBySystem($system_code){

        $result = FwSystemInfo::find(false)
            ->andFilterWhere(['=','system_code',$system_code])
            ->one();

        return $result;
    }
}