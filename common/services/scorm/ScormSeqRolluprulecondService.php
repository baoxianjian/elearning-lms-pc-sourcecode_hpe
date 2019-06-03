<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/2/2015
 * Time: 3:43 PM
 */

namespace common\services\scorm;


use common\models\learning\LnScormSeqRollrucond;

class ScormSeqRolluprulecondService extends LnScormSeqRollrucond{

    /**
     * 根据ScormScoId删除相关记录
     * @param $scormScoId
     * @return bool|int
     */
    public function DeleteScormSeqRolluprulecondByScormScoId($scormScoId){

        if ($scormScoId != null) {
            $model = new LnScormSeqRollrucond();
            $model->find(false)
                ->andFilterWhere(['=', 'scorm_sco_id', $scormScoId]);

            return $model->delete();
        } else {
            return false;
        }
    }

    /**
     * 根据ScormScoId获取相关记录
     * @param $rollupRuleId
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function GetScormSeqRolluprulecondByScormScoId($rollupRuleId){

        if ($rollupRuleId != null) {
            $model = new LnScormSeqRollrucond();
            $result = $model->find(false)
                ->andFilterWhere(['=', 'rollup_rule_id', $rollupRuleId])
                ->all();

            return $result;
        } else {
            return null;
        }
    }

    /**
     * 根据ScormScoId删除相关记录
     * @param $scormId
     * @return bool|int
     */
    public function DeleteScormSeqRolluprulecondByScormId($scormId){

        if ($scormId != null) {
            $model = new LnScormSeqRollrucond();
            return $model->deleteAll(['scorm_id'=>$scormId]);
        } else {
            return false;
        }
    }
}