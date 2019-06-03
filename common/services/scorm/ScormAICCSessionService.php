<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/2/2015
 * Time: 3:43 PM
 */

namespace common\services\scorm;


use common\models\learning\LnScormAiccSession;

class ScormAICCSessionService extends LnScormAiccSession{

    /**
     * 获取指定尝试次数相关元素的数据
     * @param $courseRegId
     * @param $modResId
     * @param $userId
     * @param $attempt
     * @return LnScormAiccSession
     */
    public function getScormAICCSessionByAttempt($courseRegId,$modResId,$scormScoId,$userId,$attempt, $time)
    {
        $model = new LnScormAiccSession();
        $result = $model->find(false)
            ->andFilterWhere(['=','course_reg_id',$courseRegId])
            ->andFilterWhere(['=','scorm_sco_id',$scormScoId])
            ->andFilterWhere(['=','mod_res_id',$modResId])
            ->andFilterWhere(['=','user_id',$userId])
            ->andFilterWhere(['=','attempt',$attempt])
            ->andFilterWhere(['>=','updated_at',$time])
            ->one();

        return $result;
    }


    /**
     * 获取指定SessionId相关元素的数据
     * @param $hacpSession
     * @param $time
     * @return LnScormAiccSession
     */
    public function getScormAICCSessionBySessionId($hacpSession, $time)
    {
        $model = new LnScormAiccSession();
        $result = $model->find(false)
            ->andFilterWhere(['=','hacp_session',$hacpSession])
            ->andFilterWhere(['>=','updated_at',$time])
            ->one();

        return $result;
    }


    /**
     * 重置Session情况
     * @param $courseRegId
     */
    public function resetScoesSessionInfo($courseRegId,$modResId,$attempt,$scoId=null)
    {
        $model = new LnScormAiccSession();

        if (!empty($modResId)) {

            if (!empty($scoId)) {

                $params = [
                    ':attempt' => $attempt,
                    ':course_reg_id' => $courseRegId,
                    ':mod_res_id' => $modResId,
                    ':scorm_sco_id' => $scoId,
                ];

                $condition = 'attempt = :attempt and course_reg_id = :course_reg_id and mod_res_id = :mod_res_id and scorm_sco_id = :scorm_sco_id';
            }
            else {
                $params = [
                    ':attempt' => $attempt,
                    ':course_reg_id' => $courseRegId,
                    ':mod_res_id' => $modResId,
                ];

                $condition = 'attempt = :attempt and course_reg_id = :course_reg_id and mod_res_id = :mod_res_id';
            }
        }
        else {
            $params = [
                ':attempt' => $attempt,
                ':course_reg_id' => $courseRegId,
            ];

            $condition = 'attempt = :attempt and course_reg_id = :course_reg_id';
        }


        $model->deleteAll($condition,$params);
    }
}