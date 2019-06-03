<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/2/2015
 * Time: 3:43 PM
 */

namespace common\services\scorm;


use common\models\learning\LnScormSeqRuleconds;

class ScormSeqRulecondsService extends LnScormSeqRuleconds{

    /**
     * 根据ScormScoId删除相关记录
     * @param $scormScoId
     * @return bool|int
     */
    public function DeleteScormSeqRulecondsByScormScoId($scormScoId){

        if ($scormScoId != null) {
            $model = new LnScormSeqRuleconds();
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
    public function GetScormSeqRulecondsByScormScoId($scormScoId,$action){

        if ($scormScoId != null) {
            $model = new LnScormSeqRuleconds();
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
    public function DeleteScormSeqRulecondsByScormId($scormId){

        if ($scormId != null) {
            $model = new LnScormSeqRuleconds();
            return $model->deleteAll(['scorm_id'=>$scormId]);
        } else {
            return false;
        }
    }
}