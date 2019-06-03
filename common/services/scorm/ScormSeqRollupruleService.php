<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/2/2015
 * Time: 3:43 PM
 */

namespace common\services\scorm;


use common\models\learning\LnScormSeqRollru;

class ScormSeqRollupruleService extends LnScormSeqRollru{

    /**
     * 根据ScormScoId删除相关记录
     * @param $scormScoId
     * @return bool|int
     */
    public function DeleteScormSeqRollupruleByScormScoId($scormScoId){

        if ($scormScoId != null) {
            $model = new LnScormSeqRollru();
            $model->find(false)
                ->andFilterWhere(['=', 'scorm_sco_id', $scormScoId]);

            return $model->delete();
        } else {
            return false;
        }
    }

    /**
     * 根据ScormScoId获取相关记录
     * @param $scormScoId
     * @param $action
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function GetScormSeqRollupruleByScormScoId($scormScoId,$action = null){

        if ($scormScoId != null) {
            $model = new LnScormSeqRollru();
            $result = $model->find(false)
                ->andFilterWhere(['=', 'scorm_sco_id', $scormScoId])
                ->andFilterWhere(['=', 'action', $action])
                ->all();

            return $result;
        } else {
            return null;
        }
    }


    /**
     * 根据ScormId删除相关记录
     * @param $scormId
     * @return bool|int
     */
    public function DeleteScormSeqRollupruleByScormId($scormId){

        if ($scormId != null) {
            $model = new LnScormSeqRollru();
            return $model->deleteAll(['scorm_id'=>$scormId]);
        } else {
            return false;
        }
    }
}