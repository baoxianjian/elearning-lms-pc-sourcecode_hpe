<?php
/**
 * Created by PhpStorm.
 * User: t62539
 * Date: 4/2/2016
 * Time: 6:56 PM
 */

namespace common\services\framework;


use common\models\framework\FwUserRole;
use common\base\BaseActiveRecord;

class UserRoleService extends FwUserRole
{
    /**
     * 停用用户相关所有角色
     * @param $userId
     */
    public function stopRelationshipByUserId($userId)
    {
        $sourceMode = new FwUserRole();

        $params = [
            ':user_id'=>$userId,
            ':status'=> self::STATUS_FLAG_NORMAL,
        ];

        $condition = BaseActiveRecord::getQuoteColumnName("user_id") . ' = :user_id'
            . ' and ' . BaseActiveRecord::getQuoteColumnName("status") . ' = :status';

        $attributes = [
            'status' => self::STATUS_FLAG_STOP,
            'end_at' => time(),
        ];

        $sourceMode->updateAll($attributes,$condition,$params);
    }
    
    /**
     * 启用指定的用户角色
     * @param $userId
     * @param $roleId
     */
    public function startRelationship($userId, $roleId)
    {
        $userRoleModel = new FwUserRole();
        $userRoleModel->user_id = $userId;
        $userRoleModel->role_id = $roleId;
        $userRoleModel->status = self::STATUS_FLAG_NORMAL;
        $userRoleModel->start_at = time();

        if (!$this->isRelationshipExist($userId, $roleId)) {
            $userRoleModel->save();
        }
    }

    /**
     * 启用指定用户的指定角色
     * @param $userId
     * @param $roleId
     */
    public function stopRelationshipByUserRoleId($userId, $roleId)
    {
        $sourceMode = new FwUserRole();

        $params = [
            ':user_id'=>$userId,
            ':role_id'=>$roleId,
            ':status'=> self::STATUS_FLAG_NORMAL,
        ];

        $condition = BaseActiveRecord::getQuoteColumnName("user_id") . ' = :user_id'
            . ' and ' . BaseActiveRecord::getQuoteColumnName("role_id") . ' = :role_id'
            . ' and ' . BaseActiveRecord::getQuoteColumnName("status") . ' = :status';

        $attributes = [
            'status' => self::STATUS_FLAG_STOP,
            'end_at' => time(),
        ];

        $sourceMode->updateAll($attributes,$condition,$params);
    }


    /**
     * 判断用户角色关系是否存在
     * @param $userId
     * @param $roleId
     * @return bool
     */
    public function isRelationshipExist($userId, $roleId)
    {
        $condition = [
            'status' => self::STATUS_FLAG_NORMAL,
            'user_id' => $userId,
            'role_id' => $roleId
        ];
        $model = FwUserRole::findOne($condition);

        if ($model != null)
            return true;
        else
            return false;
    }


    /**
     * 停用用户列表相关所有岗位
     * @param $userId
     */
    public function stopRelationshipByUserIdList($userIds)
    {
        if (!empty($userIds)) {
            $sourceMode = new FwUserRole();

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
     * 批量启用关系
     * @param FwUserRole $targetModel
     */
    public function batchStartRelationship($targetModels)
    {
        if (isset($targetModels) &&  $targetModels != null && count($targetModels) > 0)
        {
            BaseActiveRecord::batchInsertSqlArray($targetModels);
        }
    }
}