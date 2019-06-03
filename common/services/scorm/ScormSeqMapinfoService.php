<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/2/2015
 * Time: 3:43 PM
 */

namespace common\services\scorm;


use common\models\learning\LnScormSeqMapinfo;

class ScormSeqMapinfoService extends LnScormSeqMapinfo{

    /**
     * 根据ScormScoId删除相关记录
     * @param $scormScoId
     * @return bool|int
     */
    public function DeleteScormSeqMapinfoByScormScoId($scormScoId){

        if ($scormScoId != null) {
            $model = new LnScormSeqMapinfo();
            $model->find(false)
                ->andFilterWhere(['=', 'scorm_sco_id', $scormScoId]);

            return $model->delete();
        } else {
            return false;
        }
    }

    /**
     * 根据ScormId删除相关记录
     * @param $scormId
     * @return bool|int
     */
    public function DeleteScormSeqMapinfoByScormId($scormId){

        if ($scormId != null) {
            $model = new LnScormSeqMapinfo();
            return $model->deleteAll(['scorm_id'=>$scormId]);
        } else {
            return false;
        }
    }
}