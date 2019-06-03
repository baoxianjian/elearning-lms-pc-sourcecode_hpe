<?php


namespace backend\services;

use common\models\framework\FwCompany;
use common\models\framework\FwUser;
use common\models\learning\LnTeacher;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class TeacherService extends LnTeacher{

    /**
     * 增加讲师记录
     * @param $userId
     */
    public function addTeacherByUserID($userId)
    {
        $teacherList = $this->getTeacherByUserID($userId);

        if (empty($teacherList))
        {
            $userModel = FwUser::findOne($userId);
            $companyId = $userModel->company_id;

            if (!empty($companyId)) {
                $companyModel = FwCompany::findOne($companyId);
                $teacherModel = new LnTeacher();
                $teacherModel->user_id = $userId;
                $teacherModel->teacher_name = $userModel->real_name;
                $teacherModel->teacher_type = LnTeacher::TEACHER_TYPE_INTERNAL;
                $teacherModel->company_id = $companyId;
                $teacherModel->company_name = $companyModel->company_name;
                $teacherModel->gender = $userModel->gender;
                $teacherModel->birthday = $userModel->birthday;

                $teacherModel->mobile_no = $userModel->mobile_no;
                $teacherModel->home_phone_no = $userModel->home_phone_no;
                $teacherModel->language = $userModel->language;
                $teacherModel->timezone = $userModel->timezone;
                $teacherModel->data_from = LnTeacher::DATA_FROM_USER_MANAGEMENT;

                $teacherModel->save();
            }
        }
    }

    /**
     * 获取用户对应的讲师记录
     * @param $userId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getTeacherByUserID($userId)
    {
        if (!empty($userId)) {
            $userModel = FwUser::findOne($userId);
            $companyId = $userModel->company_id;

            if (!empty($companyId)) {
                $model = new LnTeacher();
                $result = $model->find(false)
                    ->andFilterWhere(['=', 'company_id', $companyId])
                    ->andFilterWhere(['=', 'teacher_type', LnTeacher::TEACHER_TYPE_INTERNAL])
                    ->andFilterWhere(['=', 'teacher_name', $userModel->real_name])
                    ->andFilterWhere(['=', 'user_id', $userId])
                    ->all();

                return $result;
            }
        }

        return null;
    }
}