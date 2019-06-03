<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/25
 * Time: 22:33
 */

namespace common\services\social;


use common\models\social\SoQuestionCare;

class QuestionCareService extends SoQuestionCare
{
    /**
     * 停用用户所有关注关系
     * @param $userId
     */
    public function StopRelationshipByUserId($userId)
    {
        $sourceMode = new SoQuestionCare();

        $params = [
            ':user_id' => $userId,
            ':status' => self::STATUS_FLAG_NORMAL,
        ];

        $condition = 'user_id = :user_id and status = :status ';

        $attributes = [
            'status' => self::STATUS_FLAG_STOP,
            'end_at' => time(),
        ];

        $sourceMode->updateAll($attributes, $condition, $params);
    }

    /**
     * 停用指定的关注关系
     * @param SoQuestionCare $targetModel
     */
    public function StopRelationship(SoQuestionCare $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $sourceMode = new SoQuestionCare();

            $params = [
                ':user_id' => $targetModel->user_id,
                ':question_id' => $targetModel->question_id,
                ':status' => self::STATUS_FLAG_NORMAL,
            ];

            $condition = 'user_id = :user_id and question_id = :question_id and status = :status ';

            $attributes = [
                'status' => self::STATUS_FLAG_STOP,
                'end_at' => time(),
            ];

            if ($this->IsRelationshipExist($targetModel)) {
                $sourceMode->updateAll($attributes, $condition, $params);
            }
        }
    }

    /**
     * 启用指定的问题关注
     * @param SoQuestionCare $targetModel
     */
    public function startRelationship(SoQuestionCare $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $userRoleModel = new SoQuestionCare();
            $userRoleModel->user_id = $targetModel->user_id;
            $userRoleModel->question_id = $targetModel->question_id;
            $userRoleModel->status = self::STATUS_FLAG_NORMAL;
            $userRoleModel->start_at = time();

            if (!$this->IsRelationshipExist($targetModel)) {
                $userRoleModel->save();
            }
        }
    }

    /**
     * 判断问题关注关系是否存在
     * @param SoQuestionCare $targetModel
     * @return bool
     */
    public function IsRelationshipExist(SoQuestionCare $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $condition = [
                'status' => self::STATUS_FLAG_NORMAL,
                'user_id' => $targetModel->user_id,
                'question_id' => $targetModel->question_id
            ];
            $model = SoQuestionCare::findOne($condition);

            if ($model != null)
                return true;
            else
                return false;
        } else {
            return true;
        }
    }

    public function getAllCareUserId($uid)
    {
        $query = SoQuestionCare::find(false);
        $query->andFilterWhere(['=', 'status', SoQuestionCare::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'user_id', $uid]);

        return $query->all();
    }

    public function getAllUserId($uid)
    {
        $query = SoQuestionCare::find(false);
        $query->andFilterWhere(['=', 'status', SoQuestionCare::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'question_id', $uid]);

        return $query->all();
    }
}