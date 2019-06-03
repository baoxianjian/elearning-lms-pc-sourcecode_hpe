<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 3/21/2015
 * Time: 10:57 AM
 */
namespace backend\services;

use common\models\framework\FwRole;
use common\models\framework\FwUser;
use common\models\framework\FwUserManager;
use common\base\BaseActiveRecord;
use Yii;
use common\models\framework\FwUserRole;
use common\models\framework\FwUserPosition;

class UserManagerService extends FwUserManager{

    /**
     * 停用用户相关所有经理
     * @param $userId
     */
    public function stopRelationshipByUserId($userId)
    {
        $sourceMode = new FwUserManager();

        $params = [
           ':user_id'=>$userId,
           ':status'=> self::STATUS_FLAG_NORMAL,
        ];

        $condition = BaseActiveRecord::getQuoteColumnName("user_id") . ' = :user_id' .
            " and " . BaseActiveRecord::getQuoteColumnName("status") . ' = :status ';

        $attributes = [
           'status' => self::STATUS_FLAG_STOP,
           'end_at' => time(),
        ];

        $sourceMode->updateAll($attributes,$condition,$params);
    }


    /**
     * 停用经理相关所有下属
     * @param $managerId
     */
    public function stopRelationshipByManagerId($managerId)
    {
        $sourceMode = new FwUserManager();

        $params = [
            ':manager_id'=>$managerId,
            ':status'=> self::STATUS_FLAG_NORMAL,
        ];

        $condition = BaseActiveRecord::getQuoteColumnName("manager_id") . ' = :manager_id' .
            " and " . BaseActiveRecord::getQuoteColumnName("status") . ' = :status ';

        $attributes = [
            'status' => self::STATUS_FLAG_STOP,
            'end_at' => time(),
        ];

        $sourceMode->updateAll($attributes,$condition,$params);
    }


    /**
     * 启用指定的用户经理
     * @param FwUserManager $targetModel
     */
    public function startRelationship(FwUserManager $targetModel)
    {
        if (isset($targetModel) &&  $targetModel != null)
        {
            $userManageModel = new FwUserManager();
            $userManageModel->user_id = $targetModel->user_id;
            $userManageModel->manager_id = $targetModel->manager_id;
            $userManageModel->reporting_model = $targetModel->reporting_model;
            $userManageModel->status = self::STATUS_FLAG_NORMAL;
            $userManageModel->start_at = time();

            if (!$this->isRelationshipExist($targetModel)) {
                $userManageModel->save();
            }
        }
    }

    /**
     * 批量启用关系
     * @param FwUserManager $targetModel
     */
    public function batchStartRelationship($targetModels)
    {
        if (isset($targetModels) &&  $targetModels != null && count($targetModels) > 0)
        {
            BaseActiveRecord::batchInsertSqlArray($targetModels);
        }
    }

    /**
     * 判断用户经理关系是否存在
     * @param FwUserManager $targetModel
     * @return bool
     */
    public function isRelationshipExist(FwUserManager $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $condition = [
                'status' => self::STATUS_FLAG_NORMAL,
                'user_id' => $targetModel->user_id,
                'manager_id' => $targetModel->manager_id,
                'reporting_model' => $targetModel->reporting_model
            ];
            $model = FwUserManager::findOne($condition);

            if ($model != null)
                return true;
            else
                return false;
        } else {
            return true;
        }
    }
}