<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/2/2015
 * Time: 3:43 PM
 */

namespace common\services\scorm;


use common\models\learning\LnScormSeqRulecond;

class ScormSeqRulecondService extends LnScormSeqRulecond{

    /**
     * 根据ScormScoId删除相关记录
     * @param $scormScoId
     * @return bool|int
     */
    public function DeleteScormSeqRulecondByScormScoId($scormScoId){

        if ($scormScoId != null) {
            $model = new LnScormSeqRulecond();
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
     * @param $ruleCondsId
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function GetScormSeqRulecondByScormScoId($scormScoId,$ruleCondsId){

        if ($scormScoId != null) {
            $model = new LnScormSeqRulecond();
            $result = $model->find(false)
                ->andFilterWhere(['=', 'scorm_sco_id', $scormScoId])
                ->andFilterWhere(['=', 'rule_conds_id', $ruleCondsId])
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
    public function DeleteScormSeqRulecondByScormId($scormId){

        if ($scormId != null) {
            $model = new LnScormSeqRulecond();
            return $model->deleteAll(['scorm_id'=>$scormId]);
        } else {
            return false;
        }
    }
}