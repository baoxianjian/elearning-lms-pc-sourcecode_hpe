<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/2/2015
 * Time: 3:43 PM
 */

namespace common\services\scorm;


use common\models\learning\LnScormSeqObjective;

class ScormSeqObjectiveService extends LnScormSeqObjective{

    /**
     * 根据ScormScoId删除相关记录
     * @param $scormScoId
     * @return bool|int
     */
    public function DeleteScormSeqObjectiveByScormScoId($scormScoId){

        if ($scormScoId != null) {
            $model = new LnScormSeqObjective();
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
     * @return LnScormSeqObjective
     */
    public function GetScormSeqObjectiveByScormScoId($scormScoId){

        if ($scormScoId != null) {
            $model = new LnScormSeqObjective();
            $result = $model->find(false)
                ->andFilterWhere(['=', 'scorm_sco_id', $scormScoId])
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
    public function DeleteScormSeqObjectiveByScormId($scormId){

        if ($scormId != null) {
            $model = new LnScormSeqObjective();
            return $model->deleteAll(['scorm_id'=>$scormId]);
        } else {
            return false;
        }
    }
}