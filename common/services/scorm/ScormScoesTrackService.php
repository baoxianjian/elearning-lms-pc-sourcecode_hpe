<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/2/2015
 * Time: 3:43 PM
 */

namespace common\services\scorm;


use common\base\BaseActiveRecord;
use common\helpers\TBaseHelper;
use common\models\framework\FwDictionary;
use common\models\framework\FwPrimaryKey;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnModRes;
use common\models\learning\LnScormScoesTrack;
use common\models\learning\LnScormScoesTrackMongo;
use common\services\framework\DictionaryService;
use stdClass;
use yii;

class ScormScoesTrackService extends LnScormScoesTrack{

    const SCORM_12 = "SCORM_1.2";
    const SCORM_13 = "SCORM_1.3";
    const SCORM_AICC = "AICC";
    const SESSION_KEY_LIST = 'Session_key_list';


    /**
     * 清理Track数据
     * @param $courseRegId
     * @param $modResId
     * @param $currentAttempt
     * @return bool|int
     */
    public function deleteTrackData($courseRegId, $modResId, $currentAttempt)
    {
        if (!empty($courseRegId) && !empty($modResId)) {
            if (!TBaseHelper::isUseMongoDB()) {
                $dictionaryService = new DictionaryService();
                $isDeleteScormTrack = $dictionaryService->getDictionaryValueByCode("system", "is_delete_scorm_track");
                if ($isDeleteScormTrack == null) {
                    $isDeleteScormTrack = FwDictionary::NO;
                }

                if ($isDeleteScormTrack == FwDictionary::NO) {
                    //只删除历史数据(即比当次小的尝试)
                    $params = [
                        ':course_reg_id' => $courseRegId,
                        ':mod_res_id' => $modResId,
                        ':attempt' => $currentAttempt,
                    ];

                    $condition = 'course_reg_id = :course_reg_id and mod_res_id = :mod_res_id AND attempt < :attempt';
//                $condition = 'course_reg_id = :course_reg_id and mod_res_id = :mod_res_id AND attempt < :attempt and attempt > 1';
                } else {
                    //删除当次尝试
                    $params = [
                        ':course_reg_id' => $courseRegId,
                        ':mod_res_id' => $modResId,
                        ':attempt' => $currentAttempt,
                    ];

                    $condition = 'course_reg_id = :course_reg_id and mod_res_id = :mod_res_id and attempt = :attempt';
                }

                $model = new LnScormScoesTrack();
                return $model->deleteAll($condition, $params);
            } else {
                $condition = [
                    'course_reg_id' => $courseRegId,
                    'mod_res_id' => $modResId,
                    'attempt' => $currentAttempt,
                ];

                $model = new LnScormScoesTrackMongo();
                return $model->deleteAll($condition);
            }
        }
    }


    /**
     * 根据ScormId删除相关记录
     * @param $scormId
     * @return bool|int
     */
    public function deleteScormScoesTrackByScormId($scormId){

        if ($scormId != null) {
            if (!TBaseHelper::isUseMongoDB()) {
                $model = new LnScormScoesTrack();

                $params = [
                    ':scorm_id' => $scormId,
                ];

                $condition = 'scorm_id = :scorm_id';

                return $model->deleteAll($condition, $params);
            }
            else {
                $condition = [
                    'scorm_id' => $scormId,
                ];

                $model = new LnScormScoesTrackMongo();
                return $model->deleteAll($condition);
            }
        } else {
            return false;
        }
    }



    /**
     * 根据当前尝试获取相关成绩数据
     * @param $userId
     * @param $courseRegId
     * @param $modResId
     * @param $scoId
     * @param $attempt
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getScoesTrackResultByAttempt($courseRegId,$modResId,$scoId,$attempt,$withSession = false)
    {
        if (!empty($courseRegId)) {
            $sessionKey = "ScoesTrackResultListAttempt_CourseRegId_" . $courseRegId . "_ModResId_" . $modResId . "_ScoId_" . $scoId . "_Attempt_" . strval($attempt);
            $isUseMongoDB = TBaseHelper::isUseMongoDB();

            if ($withSession && Yii::$app->session->has($sessionKey)) {
                $finalResult = Yii::$app->session->get($sessionKey);
            }
            else {
                if (!$isUseMongoDB) {
                    $model = new LnScormScoesTrack();
                    $result = $model->find(false)
                        ->andFilterWhere(['=', 'course_reg_id', $courseRegId])
                        ->andFilterWhere(['=', 'mod_res_id', $modResId])
                        ->andFilterWhere(['=', 'scorm_sco_id', $scoId])
                        ->andFilterWhere(['=', 'attempt', $attempt])
//            ->andFilterWhere(['=','user_id',$userId])
//            ->addOrderBy(['element' => SORT_ASC])
                        ->all();
                }
                else {
                    $model = new LnScormScoesTrackMongo();
                    $result = $model->find(false)
                        ->andFilterWhere(['=', 'course_reg_id', $courseRegId])
                        ->andFilterWhere(['=', 'mod_res_id', $modResId])
                        ->andFilterWhere(['=', 'scorm_sco_id', $scoId])
                        ->andFilterWhere(['=', 'attempt', $attempt])
                        ->one();
                }

                $finalResult = [];
                $alreadySessionedList = [];
                $alreadySessionedListKey = "AlreadySessionedElementList_CourseRegId_" . $courseRegId . "_ModResId_" . $modResId . "_ScoId_" . $scoId . "_Attempt_" . strval($attempt);
                if ($withSession && Yii::$app->session->has($alreadySessionedListKey)) {
                    $alreadySessionedList = Yii::$app->session->get($alreadySessionedListKey);
                }

                if (!empty($result)) {
                    if (!$isUseMongoDB) {
                        foreach ($result as $track) {
                            $element = $track->element;
                            $finalResult[$element] = $track;

                            $sessionElementKey = "ScoesTrackElementResultAttempt_CourseRegId_" . $courseRegId . "_ModResId_" . $modResId . "_ScoId_" . $scoId . "_Element_" . $element . "_Attempt_" . strval($attempt);
                            if ($withSession) {
                                Yii::$app->session->set($sessionElementKey, $track);
                                $this->addSessionKey($sessionElementKey);

                                if (!in_array($sessionElementKey, $alreadySessionedList)) {
                                    array_push($alreadySessionedList, $sessionElementKey);
                                }
                            }
                        }
                    }
                    else {
                        if ( isset($result->elementlist)) {
                            foreach ($result->elementlist as $element => $value) {
                                $element = str_replace("_", ".", $element);
                                $temp = new stdClass();
                                $temp->kid = $result->getPrimaryKey();
                                $temp->course_reg_id = $courseRegId;
                                $temp->scorm_sco_id = $scoId;
                                $temp->mod_res_id = $modResId;
                                $temp->attempt = $attempt;
                                $temp->element = $element;
                                $temp->value = $value;

                                $finalResult[$element] = $temp;

                                $sessionElementKey = "ScoesTrackElementResultAttempt_CourseRegId_" . $courseRegId . "_ModResId_" . $modResId . "_ScoId_" . $scoId . "_Element_" . $element . "_Attempt_" . strval($attempt);
                                if ($withSession) {
                                    Yii::$app->session->set($sessionElementKey, $temp);
                                    $this->addSessionKey($sessionElementKey);

                                    if (!in_array($sessionElementKey, $alreadySessionedList)) {
                                        array_push($alreadySessionedList, $sessionElementKey);
                                    }
                                }
                            }
                        }
                    }


                    if ($withSession) {
                        Yii::$app->session->set($alreadySessionedListKey, $alreadySessionedList);
                        $this->addSessionKey($alreadySessionedListKey);
                    }
                }

                if ($withSession) {
                    Yii::$app->session->set($sessionKey, $finalResult);
                    $this->addSessionKey($sessionKey);
                }
            }

            return $finalResult;
        }
        else {
            return null;
        }
    }


    /**
     * 获取尝试情况
     * @param $courseRegId
     * @param $modResId
     * @param null $element
     * @return static
     */
    public function getDistinctAttempts($courseRegId,$modResId,$element=null,$scoId=null)
    {
        if (!TBaseHelper::isUseMongoDB()) {
            $model = new LnScormScoesTrack();
            $result = $model->find(false)
                ->andFilterWhere(['=', 'course_reg_id', $courseRegId])
                ->andFilterWhere(['=', 'mod_res_id', $modResId])
//                ->andFilterWhere(['=', 'user_id', $userId])
                ->andFilterWhere(['=', 'element', $element])
                ->andFilterWhere(['=', 'scorm_sco_id', $scoId])
//            ->addOrderBy(['attempt' => SORT_ASC])
                ->select(['attempt'])
                ->distinct();
        } else {
            $model = new LnScormScoesTrackMongo();
            $result = $model->find(false)
                ->andFilterWhere(['=', 'course_reg_id', $courseRegId])
                ->andFilterWhere(['=', 'mod_res_id', $modResId])
//                ->andFilterWhere(['=', 'user_id', $userId])
//                ->andFilterWhere(['<>', 'elementlist.'. $element, ""])
                ->andFilterWhere(['=', 'scorm_sco_id', $scoId])
//            ->addOrderBy(['attempt' => SORT_ASC])
                ->select(['attempt'])
                ->distinct();
        }
        return $result;
    }


    /**
     * 获取指定尝试次数相关元素的数据
     * @param $courseRegId
     * @param $modResId
     * @param $userId
     * @param $element
     * @param $attempt
     * @return LnScormScoesTrack
     */
    public function getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scormScoId,$element,$attempt, $withSession = false)
    {
        if (!empty($courseRegId)) {
            $sessionKey = "ScoesTrackElementResultAttempt_CourseRegId_" . $courseRegId . "_ModResId_"  . $modResId . "_ScoId_" . $scormScoId . "_Element_" . $element . "_Attempt_" . strval($attempt);
            $alreadySessionedListKey = "AlreadySessionedElementList_CourseRegId_" . $courseRegId . "_ModResId_"  . $modResId . "_ScoId_" . $scormScoId . "_Attempt_" . strval($attempt);
            if ($withSession && Yii::$app->session->has($sessionKey))
            {
                $result = Yii::$app->session->get($sessionKey);
            }
            else {
                if (!TBaseHelper::isUseMongoDB()) {
                    $model = new LnScormScoesTrack();
                    $result = $model->find(false)
                        ->andFilterWhere(['=', 'course_reg_id', $courseRegId])
                        ->andFilterWhere(['=', 'scorm_sco_id', $scormScoId])
                        ->andFilterWhere(['=', 'mod_res_id', $modResId])
//                ->andFilterWhere(['=', 'user_id', $userId])
                        ->andFilterWhere(['=', 'element', $element])
                        ->andFilterWhere(['=', 'attempt', $attempt])
                        ->one();
                }
                else {
                    $result = new stdClass();
                    $model = new LnScormScoesTrackMongo();
                    $temp = $model->find(false)
                        ->andFilterWhere(['=', 'course_reg_id', $courseRegId])
                        ->andFilterWhere(['=', 'scorm_sco_id', $scormScoId])
                        ->andFilterWhere(['=', 'mod_res_id', $modResId])
//                        ->andFilterWhere(['=', 'element', $element])
                        ->andFilterWhere(['=', 'attempt', $attempt])
                        ->one();

                    if ($temp == null){
                        $result->kid = null;
                        $result->course_reg_id  = $courseRegId;
                        $result->scorm_sco_id  = $scormScoId;
                        $result->mod_res_id  = $modResId;
                        $result->attempt  = $attempt;
                        $result->element = $element;
                        $result->value = null;
                    }
                    else {
                        $result->kid = $temp->getPrimaryKey();
                        $result->course_reg_id = $courseRegId;
                        $result->scorm_sco_id = $scormScoId;
                        $result->mod_res_id = $modResId;
                        $result->attempt = $attempt;
                        $result->element = $element;

                        if (isset($temp->elementlist[$element])) {
                            $result->value = $temp->elementlist[$element];
                        }
                        else {
                            $result->value = null;
                        }
                    }
                }


                if ($withSession) {
                    $alreadySessionedList = [];
                    if (Yii::$app->session->has($alreadySessionedListKey)) {
                        $alreadySessionedList = Yii::$app->session->get($alreadySessionedListKey);
                    }

                    if (!in_array($sessionKey, $alreadySessionedList)) {
                        array_push($alreadySessionedList, $sessionKey);
                        Yii::$app->session->set($alreadySessionedListKey, $alreadySessionedList);
                        $this->addSessionKey($alreadySessionedListKey);
                    }

                    Yii::$app->session->set($sessionKey, $result);
                    $this->addSessionKey($sessionKey);
                }
            }
            return $result;
        } else {
            return null;
        }
    }

    /**
     * 清除缓存Track记录
     * @param $courseRegId
     * @param $modResId
     * @param $scoId
     * @param $attempt
     * @param bool $withSession
     */
    public function removeTrackSessionData($courseRegId, $modResId, $scoId, $attempt, $withSession=true)
    {
        if ($withSession) {
            //初始化时，要清除一下缓存，以免垃圾数据
            $listSessionKey = "ScoesTrackResultListAttempt_CourseRegId_" . $courseRegId . "_ModResId_" . $modResId . "_ScoId_" . $scoId . "_Attempt_" . strval($attempt);

            if (Yii::$app->session->has($listSessionKey)) {
                Yii::$app->session->remove($listSessionKey);
            }

            $alreadySessionedListKey = "AlreadySessionedElementList_CourseRegId_" . $courseRegId . "_ModResId_" . $modResId . "_ScoId_" . $scoId . "_Attempt_" . strval($attempt);
            if (Yii::$app->session->has($alreadySessionedListKey)) {
                $alreadySessionedList = Yii::$app->session->get($alreadySessionedListKey);

                if (count($alreadySessionedList) > 0) {

                    foreach ($alreadySessionedList as $elementKey) {
                        if (Yii::$app->session->has($elementKey)) {
                            Yii::$app->session->remove($elementKey);
                        }
                    }

                    Yii::$app->session->remove($alreadySessionedListKey);
                }
            }
        }
    }

    /**
     * 检查ScormScoes是否已经通过
     * @param $courseRegId
     * @param $modResId
     * @param $scormScoId
     * @param $userId
     * @param $attempt
     * @return bool
     */
    public function checkIsScormScoesCompletedByAttempt($courseRegId, $modResId, $scormScoId, $attempt, $scorm, $withSession = false)
    {
        //cmi.core.lesson_status：Indicates whether the learner has completed the SCO.(v1.2).(“passed”,“completed”, “incomplete”,“failed”, “not attempted”, “unknown”,“browsed”).
        //cmi.completion_status：Indicates whether the learner has completed the SCO.(v2004).(“completed”, “incomplete”,“not attempted”, “unknown”).
        //cmi.success_status：Indicates whether the learner has mastered the SCO.(v2004).(“passed”, “failed”, “unknown”).
        $element = "cmi.core.lesson_status";
        if ($scorm->scorm_version == self::SCORM_13) {
            $element = "cmi.completion_status";
        }
        $scormScoesTrackService = new ScormScoesTrackService();
        $result = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scormScoId,$element, $attempt,$withSession);

        if (!empty($result) && in_array($result->value, array('completed', 'passed'))) {
            $successStatusElement = "cmi.success_status";
            $resultSuccessStatus = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId, $modResId, $scormScoId, $successStatusElement, $attempt, $withSession);
            if (!empty($resultSuccessStatus) && $resultSuccessStatus->value == "failed") {
                return false;
            } else {
                return true;
            }
        } else {
            if ($scorm->scorm_version == self::SCORM_13) {
                $element = "cmi.success_status";
                $resultSuccessStatus = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId, $modResId, $scormScoId, $element, $attempt, $withSession);
                if (!empty($resultSuccessStatus) && $resultSuccessStatus->value == "passed") {
                    return true;
                } else {
                    return false;
                }
            }
            else {
                return false;
            }
        }
    }


    /**
     * 删除制定元素记录
     * @param $courseRegId
     * @param $modResId
     * @param $scoId
     * @param $element
     * @param $attempt
     */
    public function deleteScoesTrackInfoByElement($courseRegId, $modResId, $scoId, $element, $attempt)
    {
        if (!TBaseHelper::isUseMongoDB()) {
            $model = new LnScormScoesTrack();

            $params = [
                ':attempt' => $attempt,
                ':course_reg_id' => $courseRegId,
                ':mod_res_id' => $modResId,
                ':scorm_sco_id' => $scoId,
                ':element' => $element,
            ];

            $condition = 'attempt = :attempt and course_reg_id = :course_reg_id and mod_res_id = :mod_res_id "
        . "and scorm_sco_id = :scorm_sco_id and element = :element';


            return $model->deleteAll($condition, $params);
        }
        else {
            $model = new LnScormScoesTrackMongo();

            $condition = [
                'attempt' => $attempt,
                'course_reg_id' => $courseRegId,
                'mod_res_id' => $modResId,
                'scorm_sco_id' => $scoId,
            ];

            $element = str_replace('.', '_', $element);//Mongodb字段不支持"."

            $attributes = [
                'elementlist.' . $element
            ];

            return $model->unsetAll($attributes,$condition);
        }
    }


    /**
     * 重置完成情况
     * @param $courseRegId
     * @param $modResId
     * @param $attempt
     * @param null $scoId
     */
    public function resetScoesTrackInfo($courseRegId, $modResId, $attempt, $scoId = null)
    {
        if (!TBaseHelper::isUseMongoDB()) {
            $model = new LnScormScoesTrack();

            if (!empty($modResId)) {
                if (!empty($scoId)) {

                    $params = [
                        ':attempt' => $attempt,
                        ':course_reg_id' => $courseRegId,
                        ':mod_res_id' => $modResId,
                        ':scorm_sco_id' => $scoId,
                    ];

                    $condition = 'attempt = :attempt and course_reg_id = :course_reg_id and mod_res_id = :mod_res_id and scorm_sco_id = :scorm_sco_id';
                } else {
                    $params = [
                        ':attempt' => $attempt,
                        ':course_reg_id' => $courseRegId,
                        ':mod_res_id' => $modResId,
                    ];

                    $condition = 'attempt = :attempt and course_reg_id = :course_reg_id and mod_res_id = :mod_res_id';
                }
            } else {
                $params = [
                    ':attempt' => $attempt,
                    ':course_reg_id' => $courseRegId,
                ];

                $condition = 'attempt = :attempt and course_reg_id = :course_reg_id';
            }

            return $model->deleteAll($condition, $params);
        }
        else {
            $model = new LnScormScoesTrackMongo();

            if (!empty($modResId)) {
                if (!empty($scoId)) {
                    $condition = [
                        'attempt' => $attempt,
                        'course_reg_id' => $courseRegId,
                        'mod_res_id' => $modResId,
                        'scorm_sco_id' => $scoId,
                    ];
                } else {
                    $condition = [
                        'attempt' => $attempt,
                        'course_reg_id' => $courseRegId,
                        'mod_res_id' => $modResId,
                    ];
                }
            } else {
                $condition = [
                    'attempt' => $attempt,
                    'course_reg_id' => $courseRegId,
                ];
            }

            return $model->deleteAll($condition);
        }
    }


    /**
     * 插入跟踪数据
     * @param $courseRegId
     * @param $courseCompleteProcessId
     * @param $courseCompleteFinalId
     * @param $modResId
     * @param $scorm
     * @param $scormScoId
     * @param $userId
     * @param $element
     * @param $attempt
     * @param $value
     * @param null $trackdata
     * @param bool $withSession
     * @param bool $forcecompleted
     * @param null $systemKey
     * @param bool $courseComplete
     * @param bool $getCetification
     * @param bool $isRepeatUpdate
     * @return null|string
     */
    public function insertTrackData($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $scormScoId, $userId, $element, $attempt, $value,
                                    $trackdata = null, $withSession = false, $forcecompleted=false, $systemKey=null, &$courseComplete =false, &$getCetification=false, $isRepeatUpdate = true)
    {
        if (!empty($courseRegId) && !empty($courseCompleteProcessId) && !empty($courseCompleteFinalId)) {
            $trackId = null;
            $isUseMongoDB = TBaseHelper::isUseMongoDB();

            $modResModel = LnModRes::findOne($modResId);
            $scormId = $scorm->kid;

            $track = null;
            $listSessionKey = "ScoesTrackResultListAttempt_CourseRegId_" . $courseRegId . "_ModResId_" . $modResId . "_ScoId_" . $scormScoId . "_Attempt_" . strval($attempt);
            $elmentSessionKey = "ScoesTrackElementResultAttempt_CourseRegId_" . $courseRegId . "_ModResId_" . $modResId . "_ScoId_" . $scormScoId . "_Element_" . $element . "_Attempt_" . strval($attempt);
            if ($trackdata !== null) {
                if ($withSession && Yii::$app->session->has($listSessionKey)) {
                    $trackdata = Yii::$app->session->get($listSessionKey);
                }
            }
            if ($trackdata !== null) {
                $track = $trackdata[$element];
            } else {
                $track = $this->getScormScoesTrackElementInfoByAttempt($courseRegId, $modResId, $scormScoId, $element, $attempt, $withSession);
            }
            $isChange = false;//数据是否发生过变化
            $value = urldecode($value);
            if (!empty($track)) {
                $trackId = $track->kid;

                if ($isRepeatUpdate) {
                    if ($track->value != $value) {
                        $isChange = true;
                        if ($element != 'x.start.time') { // Don't update x.start.time - keep the original value.
                            $track->value = $value;

                            if (!$isUseMongoDB) {
                                if (!empty($systemKey)) {
                                    $track->systemKey = $systemKey;
                                }
                                $track->save();
                            }
                        }
                    }
                }
            } else {
                if (!$isUseMongoDB) {
                    $track = new LnScormScoesTrack();

                    $track->user_id = $userId;
                    $track->scorm_id = $scormId;
                    $track->course_reg_id = $courseRegId;
                    $track->course_complete_id = $courseCompleteFinalId;
                    $track->course_id = $modResModel->course_id;
                    $track->courseware_id = $modResModel->courseware_id;
                    $track->mod_id = $modResModel->mod_id;
                    $track->mod_res_id = $modResId;
                    $track->scorm_sco_id = $scormScoId;
                    $track->attempt = $attempt;
                    $track->element = $element;
                    $track->value = $value;
                    if (!empty($systemKey)) {
                        $track->systemKey = $systemKey;
                    }
                    $track->needReturnKey = true;
                    if ($track->save()) {
                        $isChange = true;
                        $trackId = $track->kid;
                    }
                }
                else {
                    $temp = new LnScormScoesTrackMongo();

                    $temp->user_id = $userId;
                    $temp->scorm_id = $scormId;
                    $temp->course_reg_id = $courseRegId;
                    $temp->course_complete_id = $courseCompleteFinalId;
                    $temp->course_id = $modResModel->course_id;
                    $temp->courseware_id = $modResModel->courseware_id;
                    $temp->mod_id = $modResModel->mod_id;
                    $temp->mod_res_id = $modResId;
                    $temp->scorm_sco_id = $scormScoId;
                    $temp->attempt = $attempt;
                    if (!empty($systemKey)) {
                        $temp->systemKey = $systemKey;
                    }
                    if ($temp->save()) {
                        $trackId = $temp->getPrimaryKey();
                    }

                    if ($trackId !== null) {
                        $track = new stdClass();
                        $track->kid = $trackId;
                        $track->course_reg_id = $courseRegId;
                        $track->scorm_sco_id = $scormScoId;
                        $track->mod_res_id = $modResId;
                        $track->attempt = $attempt;
                        $track->element = $element;
                        $track->value = $value;
//                                array_push($batchUpdateModel, $track);
                    }
                }
            }

            if ($isChange) {
                if ($trackdata !== null) {
                    $trackdata[$element] = $track;
                }

                if (!$isUseMongoDB) {

                }
                else {
                    if ($trackId !== null && !empty($trackdata) && count($trackdata) > 0) {
                        $newElementList = [];

                        foreach ($trackdata as $key=>$single) {
                            $key = str_replace('.', '_', $key);
                            $newElementList[$key] = $single->value;
                        }

                        $model = LnScormScoesTrackMongo::findOne($trackId);
                        $model->elementlist = $newElementList;

                        if (!empty($systemKey)) {
                            $model->systemKey = $systemKey;
                        }

                        $model->save();
                    }
                }
            }

            if ($withSession && $isChange) {
                Yii::$app->session->set($elmentSessionKey, $track);
                $this->addSessionKey($elmentSessionKey);
                if ($trackdata !== null) {
                    Yii::$app->session->set($listSessionKey, $trackdata);
                    $this->addSessionKey($listSessionKey);
                }
            }

            if ($isChange) {
//            $resourceCompleteService = new ResourceCompleteService();
//            $isResComplete = $resourceCompleteService->isResComplete($courseCompleteFinalId, $modResId);
//            if (!$isResComplete) {
                //20160117:只要有变化就继续记录，因为对于有些课件，可能只有掌握状态，没有明确的完成状态，所以要永远重复记录
                //只要资源学习未完成，就应该要统计成绩
                $isMaster = false;

                $allowRepeat = true; //对于scorm课件，始终要允许反复写（因为有可能掌握的部分，提前就把完成表给“完成”掉了
                $scormService = new ScormService();
                if (in_array($element, array('cmi.completion_status', 'cmi.core.lesson_status', 'cmi.success_status'))
                    // && in_array($track ->value, array('completed', 'passed'))) //没及格的，也要记录成绩
                ) {
                    //如果是先记录成绩，后记录完成状态
                    //strstr($element, '.score.raw')
//                    $element = ["cmi.core.score.raw", "cmi.score.raw"];
//                    $trackList = $scormScoesTrackService->GetScormScoesTrackElementListByAttempt($courseRegId, $modResId, $scormScoId, $userId, $element, $attempt);
//
//                    if (!empty($trackList)) {
                    //完成某个课程的前提是要这个单元先要有成绩
                    //唐：暂时取消这检查，某些课程是没有成绩的。（如：数据中心基础）

                    if ($track->value == 'passed') {
                        //paased表示只是掌握，completed才是完成
                        $isMaster = true;
                    }

                    $scormService->scorm_update_grades($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $userId, $attempt, $allowRepeat, $isMaster, $withSession,$systemKey,$courseComplete,$getCetification);
//                    }
                } else if (in_array($element, array('cmi.core.score.raw', 'cmi.score.raw'))) {
                    //如果是先记录完成状态，后记录成绩
                    $scormService->scorm_update_grades($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $userId, $attempt, $allowRepeat, $isMaster, $withSession,$systemKey,$courseComplete,$getCetification);
                }
//            }
            }
            return $trackId;
        } else {
            return null;
        }
    }


    /**
     * 批量处理跟踪数据
     * @param $courseRegId
     * @param $modResId
     * @param $scormScoId
     * @param $userId
     * @param $element
     * @param $attempt
     * @param $value
     * @param bool|false $forcecompleted
     * @param null $trackdata
     * @return mixed|null|string
     */
    function batchInsertTrackData($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$scormScoId,$userId, $elementList,  $attempt,
                                  $trackdata,$withSession = false,$systemKey=null,&$courseComplete =false,&$getCetification=false,&$courseId=null,&$certificationId=null)
    {
        $courseComplete = false;
        $isChange = false;//数据是否发生过变化
        $isMaster = false;
        $isComplete = false;
        $needUpdateScore = false;
        $allowRepeat = true; //对于scorm课件，始终要允许反复写（因为有可能掌握的部分，提前就把完成表给“完成”掉了
        $isUseMongoDB = TBaseHelper::isUseMongoDB();

        if (!empty($courseRegId) && !empty($courseCompleteProcessId) && !empty($courseCompleteFinalId)) {
            $modResModel = LnModRes::findOne($modResId);
            $scormId = $scorm->kid;

            $listSessionKey = "ScoesTrackResultListAttempt_CourseRegId_" . $courseRegId . "_ModResId_" . $modResId . "_ScoId_" . $scormScoId . "_Attempt_" . strval($attempt);

            $batchInsertModel = [];
            $batchUpdateModel = [];

            $trackId = null;

            foreach ($elementList as $element => $value) {
                $isSingleChange = false;//数据是否发生过变化
                $element = str_replace('__', '.', $element);
                $value = urldecode($value);
                if (substr($element, 0, 3) == 'cmi') {
                    $track = null;

                     $elmentSessionKey = "ScoesTrackElementResultAttempt_CourseRegId_" . $courseRegId . "_ModResId_" . $modResId . "_ScoId_" . $scormScoId . "_Element_" . $element . "_Attempt_" . strval($attempt);

                    if ($trackdata !== null && count($trackdata) > 0) {
                        if ($isUseMongoDB) {
                            foreach ($trackdata as $single) {
                                if ($trackId == null) {
                                    $trackId = $single->kid;
                                    break;
                                }
                            }
                        }
                        $track = $trackdata[$element];
                    }
                    if ($track != null) {
                        $trackId = $track->kid;
                        if ($track->value != $value) {
                            $isChange = true;
                            $isSingleChange = true;
                            if ($element != 'x.start.time') { // Don't update x.start.time - keep the original value.
                                $track->value = $value;
                                if (!empty($systemKey)) {
                                    $track->systemKey = $systemKey;
                                }
                                array_push($batchUpdateModel, $track);
                            }
                        }
                    } else {
                        if (!$isUseMongoDB) {
                            $track = new LnScormScoesTrack();

                            $track->user_id = $userId;
                            $track->scorm_id = $scormId;
                            $track->course_reg_id = $courseRegId;
                            $track->course_complete_id = $courseCompleteFinalId;
                            $track->course_id = $modResModel->course_id;
                            $track->courseware_id = $modResModel->courseware_id;
                            $track->mod_id = $modResModel->mod_id;
                            $track->mod_res_id = $modResId;
                            $track->scorm_sco_id = $scormScoId;
                            $track->attempt = $attempt;
                            $track->element = $element;
                            $track->value = $value;
                            $track->needReturnKey = true;
                            if (!empty($systemKey)) {
                                $track->systemKey = $systemKey;
                            }

                            $genkey = FwPrimaryKey::generateNextPrimaryID($track->tableName());
                            $track->kid = $genkey;
                            array_push($batchInsertModel, $track);
                        }
                        else {
                            if ($trackId == null) {
                                $temp = new LnScormScoesTrackMongo();

                                $temp->user_id = $userId;
                                $temp->scorm_id = $scormId;
                                $temp->course_reg_id = $courseRegId;
                                $temp->course_complete_id = $courseCompleteFinalId;
                                $temp->course_id = $modResModel->course_id;
                                $temp->courseware_id = $modResModel->courseware_id;
                                $temp->mod_id = $modResModel->mod_id;
                                $temp->mod_res_id = $modResId;
                                $temp->scorm_sco_id = $scormScoId;
                                $temp->attempt = $attempt;
                                if (!empty($systemKey)) {
                                    $temp->systemKey = $systemKey;
                                }
                                if ($temp->save()) {
                                    $trackId = $temp->getPrimaryKey();
                                }
                            }

                            if ($trackId !== null) {
                                $track = new stdClass();
                                $track->kid = $trackId;
                                $track->course_reg_id = $courseRegId;
                                $track->scorm_sco_id = $scormScoId;
                                $track->mod_res_id = $modResId;
                                $track->attempt = $attempt;
                                $track->element = $element;
                                $track->value = $value;
//                                array_push($batchUpdateModel, $track);
                            }
                        }

                        if ($track != null) {
                            $isChange = true;
                            $isSingleChange = true;
                        }
                    }

                    if ($isSingleChange) {
                        if (in_array($element, array('cmi.completion_status', 'cmi.core.lesson_status', 'cmi.success_status'))) {
                            $needUpdateScore = true;

                            if ($track->value == 'passed') {
                                //paased表示只是掌握，completed才是完成
                                $isMaster = true;
                            }

                            if ($track->value == 'completed') {
                                //paased表示只是掌握，completed才是完成
                                $isComplete = true;
                            }
                        } else if (in_array($element, array('cmi.core.score.raw', 'cmi.score.raw'))) {
                            $needUpdateScore = true;
                        }
                    }

                    if ($withSession && $isSingleChange) {
                        Yii::$app->session->set($elmentSessionKey, $track);
                        $this->addSessionKey($elmentSessionKey);
                        $trackdata[$element] = $track;
                    }
                }

                if ($withSession && $isChange) {
                    Yii::$app->session->set($listSessionKey, $trackdata);
                    $this->addSessionKey($listSessionKey);
                }
            }

            if (!$isUseMongoDB) {
                //先批量插入
                if (!empty($batchInsertModel) && count($batchInsertModel) > 0) {
                    BaseActiveRecord::batchInsertNormalMode($batchInsertModel, $errMsg, true);
                }

                //再批量更新
                if (!empty($batchUpdateModel) && count($batchUpdateModel) > 0) {
                    BaseActiveRecord::batchUpdateNormalMode($batchUpdateModel, $errMsg, true);
                }
            }
            else {
                if ($trackId !== null && !empty($trackdata) && count($trackdata) > 0) {
                    $newElementList = [];

                    foreach ($trackdata as $key=>$single) {
                        $key = str_replace('.', '_', $key);
                        $newElementList[$key] = $single->value;
                    }

                    $model = LnScormScoesTrackMongo::findOne($trackId);
                    $model->elementlist = $newElementList;

                    if (!empty($systemKey)) {
                        $model->systemKey = $systemKey;
                    }

                    $model->save();
                }
            }
        }

        if ($isChange && $needUpdateScore) {
            if ($isComplete) {
                $isMaster = false;//如果是完成的，则肯定不只是掌握
            }

            $scormService = new ScormService();
            $scormService->scorm_update_grades($courseRegId, $courseCompleteProcessId, $courseCompleteFinalId, $modResId, $scorm, $userId, $attempt,
                $allowRepeat, $isMaster, $withSession, $systemKey,$courseComplete,$getCetification,$courseId,$certificationId);
        }
    }

    /**
     * @param $key
     */
    private function addSessionKey($key)
    {
        $keyList = Yii::$app->session->get(self::SESSION_KEY_LIST, []);
        array_push($keyList, $key);
        Yii::$app->session->set(self::SESSION_KEY_LIST, $keyList);
    }

    public function cleanSessionList()
    {
        $keyList = Yii::$app->session->get(self::SESSION_KEY_LIST, []);

        foreach ($keyList as $key) {
            if (Yii::$app->session->has($key)) {
                Yii::$app->session->remove($key);
            }
        }

        Yii::$app->session->set(self::SESSION_KEY_LIST, []);
    }
}