<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/25
 * Time: 22:33
 */

namespace common\services\social;


use common\models\social\SoUserAttention;

class UserAttentionService extends SoUserAttention
{
    /**
     * 停用用户所有关注关系
     * @param $userId
     */
    public function StopRelationshipByUserId($userId)
    {
        $sourceMode = new SoUserAttention();

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
     * @param SoUserAttention $targetModel
     */
    public function StopRelationship(SoUserAttention $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $sourceMode = new SoUserAttention();

            $params = [
                ':user_id' => $targetModel->user_id,
                ':attention_id' => $targetModel->attention_id,
                ':status' => self::STATUS_FLAG_NORMAL,
            ];

            $condition = 'user_id = :user_id and attention_id = :attention_id and status = :status ';

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
     * 启用指定的用户关注
     * @param SoUserAttention $targetModel
     */
    public function startRelationship(SoUserAttention $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $userRoleModel = new SoUserAttention();
            $userRoleModel->user_id = $targetModel->user_id;
            $userRoleModel->attention_id = $targetModel->attention_id;
            $userRoleModel->status = self::STATUS_FLAG_NORMAL;
            $userRoleModel->start_at = time();

            if (!$this->IsRelationshipExist($targetModel)) {
                $userRoleModel->save();
            }
        }
    }

    /**
     * 判断用户关注关系是否存在
     * @param SoUserAttention $targetModel
     * @return bool
     */
    public function IsRelationshipExist(SoUserAttention $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $condition = [
                'status' => self::STATUS_FLAG_NORMAL,
                'user_id' => $targetModel->user_id,
                'attention_id' => $targetModel->attention_id
            ];
            $model = SoUserAttention::findOne($condition);

            if ($model != null)
                return true;
            else
                return false;
        } else {
            return true;
        }
    }

    public function getAllAttentionUserId($uid)
    {
        $query = SoUserAttention::find(false);
        $query->andFilterWhere(['=', 'status', SoUserAttention::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'user_id', $uid]);

        return $query->all();
    }

    public function getAllUserId($uid)
    {
        $query = SoUserAttention::find(false);
        $query->andFilterWhere(['=', 'status', SoUserAttention::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'attention_id', $uid]);

        return $query->all();
    }
}