<?php

namespace common\services\learning;

use common\models\learning\LnHomework;
use common\models\learning\LnHomeworkFile;
use common\models\learning\LnHomeworkResult;
use common\services\framework\UserService;
use common\models\framework\FwOrgnization;
use common\models\framework\FwUser;
use components\widgets\TPagination;
use Yii;
use common\models\learning\LnExamination;
use yii\helpers\ArrayHelper;



class HomeworkService extends LnExamination
{

    const IS_RIGHT_YES = 'yes';
    const IS_RIGHT_NO = 'no';
   /**
     * ��ѯ���Խ��
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     */
    public function searchResultUser($params){
        $query = LnHomeworkResult::find(false);

        if (isset($params['user_id'])){
            $query->andFilterWhere(['=', 'user_id', $params['user_id']]);
        }
        if (isset($params['course_id'])){
            $query->andFilterWhere(['=', 'course_id', $params['course_id']]);
        }
        if (isset($params['mod_id'])){
            $query->andFilterWhere(['=', 'mod_id', $params['mod_id']]);
        }
        if (isset($params['courseactivity_id'])){
            $query->andFilterWhere(['=', 'courseactivity_id', $params['courseactivity_id']]);
        }
        $query->orderBy('created_at');
        $count = $query->count();
        $pages = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
        $data = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        if ($data){
            $commonUserSerivce = new UserService();
            foreach ($data as $k => $v){
                $userModel = FwUser::findOne($v['user_id']);
                $data[$k]['user_name'] = $userModel->real_name;
                $data[$k]['user_location'] = $userModel->location;
                $orgnization = $userModel->orgnization_id;
                $data[$k]['user_orgnization'] = FwOrgnization::findOne($orgnization)->orgnization_name;
                $data[$k]['user_position'] = $commonUserSerivce->getPositionListStringByUserId($v['user_id']);
            }
        }

        return ['data' => $data, 'page' => $pages];
    }

    public function getUserHomeworkResult($user_id, $course_id, $mod_id, $mod_res_id, $company_id, $homework_id)
    {
        $result = [];

        $homework = LnHomework::findOne($homework_id);

        $result['requirement'] = $homework->requirement;

        $result['finish_before_at'] = $homework->finish_before_at;

        $result['homework_mode'] = $homework->homework_mode;

        $result['teacherFiles'] = LnHomeworkFile::findAll(['homework_id' => $homework_id, 'company_id' => $company_id, 'homework_file_type' => LnHomeworkFile::FILE_TYPE_TEACHER]);

        $query = LnHomeworkResult::find(false);

        $query->andFilterWhere(['homework_id' => $homework_id, 'user_id' => $user_id, 'course_id' => $course_id, 'mod_id' => $mod_id, 'mod_res_id' => $mod_res_id, 'company_id' => $company_id])
            ->addOrderBy('created_at desc');

        $homeworkResult = $query->one();

        $result['homeworkResult'] = $homeworkResult;

        $result['userFiles'] = LnHomeworkFile::findAll(['homework_id' => $homework_id, 'company_id' => $company_id, 'homework_file_type' => LnHomeworkFile::FILE_TYPE_STUDENT, 'course_complete_id' => $homeworkResult->course_complete_id]);

        return $result;
    }
    public function deleteGiveUpCoursesHomeworkResult($course_id,$attempt,$userId = null, $companyId = null){
        if (empty($userId)) {
            $userId = Yii::$app->user->getId();
        }
        if (empty($companyId)){
            $companyId = Yii::$app->user->identity->company_id;
        }
        $findAll = LnHomeworkResult::findAll([
            'company_id' => $companyId,
            'user_id' => $userId,
            'course_id' => $course_id,
            'course_attempt_number' => $attempt,
        ]);
        if (!empty($findAll)){
            $kids = ArrayHelper::map($findAll, 'kid', 'kid');
            $kids = array_keys($kids);
            if (is_array($kids)){
                $kids = "'".join("','", $kids)."'";
                LnHomeworkResult::deleteAllByKid($kids);
            }
        }
    }

    /**
     * 查询学员最后一次上传的作业
     * @param $homeworkId
     * @param $userId
     * @param $fileType
     * @param $courseCompleteId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getHomeworkUserFile($homeworkId, $userId, $fileType, $courseCompleteId, $lastAttempt = 1){
        $model = LnHomeworkFile::find(false)
            ->andFilterWhere(['=', 'homework_id', $homeworkId])
            ->andFilterWhere(['=', 'user_id', $userId])
            ->andFilterWhere(['=', 'homework_file_type', $fileType])
            ->andFilterWhere(['=', 'course_complete_id', $courseCompleteId]);
        //$lastAttempt = $model->max('course_attempt_number');

        if (!empty($lastAttempt)) {
            $model->andFilterWhere(['=', 'course_attempt_number', $lastAttempt]);
        }
        $result = $model->orderBy('updated_at desc')
                ->all();

            return $result;
        /*}else{
            return ['data' => null, 'attempt' => 0];
        }*/
    }
}