<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 3/21/2015
 * Time: 10:57 AM
 */
namespace backend\services;

use common\base\BaseActiveRecord;
use Yii;
use common\models\framework\FwUserPosition;

class UserPositionService extends FwUserPosition{

    /**
     * 停用岗位相关所有人员
     * @param $roleIds
     */
    public function stopRelationshipByPositionId($positionId)
    {
        $sourceMode = new FwUserPosition();

        $params = [
            ':position_id'=>$positionId,
            ':status'=>self::STATUS_FLAG_NORMAL,
        ];

        $condition = BaseActiveRecord::getQuoteColumnName("position_id") . ' = :position_id' .
            ' and ' . BaseActiveRecord::getQuoteColumnName("status") . ' = :status';

        $attributes = [
            'status' => self::STATUS_FLAG_STOP,
            'end_at' => time(),
        ];

        $sourceMode->updateAll($attributes,$condition,$params);
    }

    /**
     * 停用岗位相关所有权限
     * @param $roleIds
     */
    public function stopRelationshipByUserId($userId)
    {
        $sourceMode = new FwUserPosition();

        $params = [
            ':user_id'=>$userId,
            ':status'=>self::STATUS_FLAG_NORMAL,
        ];

        $condition = BaseActiveRecord::getQuoteColumnName("user_id") . ' = :user_id' .
            ' and ' . BaseActiveRecord::getQuoteColumnName("status") . ' = :status';

        $attributes = [
            'status' => self::STATUS_FLAG_STOP,
            'end_at' => time(),
        ];

        $sourceMode->updateAll($attributes,$condition,$params);
    }

    /**
     * 停用岗位相关所有权限
     * @param $roleIds
     */
    public function stopRelationshipByPositionIdList($positionIds)
    {
        $sourceMode = new FwUserPosition();

        $params = [
            ':status'=>self::STATUS_FLAG_NORMAL,
        ];

        $condition = BaseActiveRecord::getQuoteColumnName("position_id") . ' in (' . $positionIds . ')' .
            ' and ' . BaseActiveRecord::getQuoteColumnName("status") . ' = :status';

        $attributes = [
            'status' => self::STATUS_FLAG_STOP,
            'end_at' => time(),
        ];

        $sourceMode->updateAll($attributes,$condition, $params);
    }


    /**
     * 停用用户列表相关所有岗位
     * @param $userId
     */
    public function stopRelationshipByUserIdList($userIds)
    {
        if (!empty($userIds)) {
            $sourceMode = new FwUserPosition();

            $params = [
                ':status' => self::STATUS_FLAG_NORMAL,
            ];
            
            $condition = BaseActiveRecord::getQuoteColumnName("user_id") . ' in (' . $userIds . ')' .
                ' and ' . BaseActiveRecord::getQuoteColumnName("status") . ' = :status';

            $attributes = [
                'status' => self::STATUS_FLAG_STOP,
                'end_at' => time(),
            ];

            $sourceMode->updateAll($attributes, $condition, $params);
        }
    }

    /**
     * 停用指定的用户岗位
     * @param FwUserPosition $targetModel
     */
    public function stopRelationship(FwUserPosition $targetModel)
    {
        if (isset($targetModel) &&  $targetModel != null)
        {
            $sourceMode = new FwUserPosition();

            $params = [
                ':user_id'=>$targetModel->user_id,
                ':position_id'=>$targetModel->position_id,
                ':status'=> self::STATUS_FLAG_NORMAL,
            ];

            $condition =  BaseActiveRecord::getQuoteColumnName("user_id") . ' = :user_id' .
                ' and ' . BaseActiveRecord::getQuoteColumnName("position_id") . ' = :position_id' .
                ' and ' . BaseActiveRecord::getQuoteColumnName("status") . ' = :status';

            $attributes = [
               'status' => self::STATUS_FLAG_STOP,
               'end_at' => time(),
            ];

            if ($this->isRelationshipExist($targetModel)) {
               $sourceMode->updateAll($attributes, $condition, $params);
            }
        }
    }

    /**
     * 批量启用关系
     * @param FwUserPosition $targetModel
     */
    public function batchStartRelationship($targetModels)
    {
        if (isset($targetModels) &&  $targetModels != null && count($targetModels) > 0)
        {
            BaseActiveRecord::batchInsertSqlArray($targetModels);
        }
    }

//    public function RemoveRelationship(FwUserPosition $targetModel)
//    {
//        if (isset($targetModel) &&  $targetModel != null)
//        {
//            $sourceMode = new FwUserPosition();
//
//            $params = [
//                ':user_id'=>$targetModel->user_id,
//                ':position_id'=>$targetModel->position_id,
//            ];
//
//            $condition = 'user_id = :user_id and position_id = :position_id ';
//
//            if ($this->IsRelationshipExist($targetModel)) {
//                $sourceMode->deleteAll($condition, $params);
//            }
//        }
//    }

    /**
     * 启用指定的用户岗位
     * @param FwUserPosition $targetModel
     */
    public function startRelationship(FwUserPosition $targetModel)
    {
        if (isset($targetModel) &&  $targetModel != null)
        {
            $userPositionModel = new FwUserPosition();
            $userPositionModel->user_id = $targetModel->user_id;
            $userPositionModel->position_id = $targetModel->position_id;
            $userPositionModel->is_master = $targetModel->is_master;
            $userPositionModel->status = self::STATUS_FLAG_NORMAL;
            $userPositionModel->start_at = time();

            if (!$this->isRelationshipExist($targetModel)) {
                $userPositionModel->save();
            }
        }
    }

    /**
     * 判断用户岗位关系是否存在
     * @param FwUserPosition $targetModel
     * @return bool
     */
    public function isRelationshipExist(FwUserPosition $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $condition = [
                'status' => self::STATUS_FLAG_NORMAL,
                'user_id' => $targetModel->user_id,
                'position_id' => $targetModel->position_id
            ];
            $model = FwUserPosition::findOne($condition);

            if ($model != null)
                return true;
            else
                return false;
        } else {
            return true;
        }
    }
}