<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 3/21/2015
 * Time: 10:57 AM
 */
namespace backend\services;

use common\models\framework\FwPermission;
use common\base\BaseActiveRecord;
use Yii;
use common\models\framework\FwRolePermission;
use yii\helpers\ArrayHelper;

class RolePermissionService extends FwRolePermission{

    /**
     * 停用角色相关所有权限
     * @param $roleId
     */
    public function stopRelationshipByRoleId($roleId)
    {
        $sourceMode = new FwRolePermission();

        $params = [
            ':role_id'=>$roleId,
            ':status'=>self::STATUS_FLAG_NORMAL,
        ];

        $condition = BaseActiveRecord::getQuoteColumnName("role_id") . ' = :role_id ' .
            ' and ' . BaseActiveRecord::getQuoteColumnName("status") . ' = :status';

        $attributes = [
           'status' => self::STATUS_FLAG_STOP,
           'end_at' => time(),
        ];

        $sourceMode->updateAll($attributes,$condition,$params);
    }

    /**
     * 停用角色相关所有权限
     * @param $roleIds
     */
    public function stopRelationshipByRoleIdList($roleIds)
    {
        $sourceMode = new FwRolePermission();

        $params = [
            ':status'=>self::STATUS_FLAG_NORMAL,
        ];

        $condition = BaseActiveRecord::getQuoteColumnName("role_id") . ' in (' . $roleIds . ') ' .
            ' and ' . BaseActiveRecord::getQuoteColumnName("status") . ' = :status';

        $attributes = [
            'status' => self::STATUS_FLAG_STOP,
            'end_at' => time(),
        ];

        $sourceMode->updateAll($attributes,$condition,$params);
    }

//    public function RemoveRelationship(FwRolePermission $targetModel)
//    {
//        if (isset($targetModel) &&  $targetModel != null)
//        {
//            $sourceMode = new FwRolePermission();
//
//            $params = [
//                ':role_id'=>$targetModel->role_id,
//                ':permission_id'=>$targetModel->permission_id,
//                ':status'=> self::STATUS_FLAG_NORMAL,
//            ];
//
//            $condition = 'role_id = :role_id and permission_id = :permission_id and status = :status ';
//
//            if ($this->IsRelationshipExist($targetModel)) {
//                $sourceMode->deleteAll($condition, $params);
//            }
//        }
//    }

    /**
     * 启用指定的角色权限
     * @param FwRolePermission $targetModel
     */
    public function startRelationship(FwRolePermission $targetModel)
    {
        if (isset($targetModel) &&  $targetModel != null)
        {
            $rolePermissionModel = new FwRolePermission();
            $rolePermissionModel->role_id = $targetModel->role_id;
            $rolePermissionModel->permission_id = $targetModel->permission_id;
            $rolePermissionModel->status = self::STATUS_FLAG_NORMAL;
            $rolePermissionModel->start_at = time();

            if (!$this->isRelationshipExist($targetModel)) {
                $rolePermissionModel->save();
            }
        }
    }

    /**
     * 批量启用关系
     * @param FwRolePermission $targetModel
     */
    public function batchStartRelationship($targetModels)
    {
        if (isset($targetModels) &&  $targetModels != null && count($targetModels) > 0)
        {
            BaseActiveRecord::batchInsertSqlArray($targetModels);
        }
    }

    /**
     * 判断角色权限关系是否存在
     * @param FwRolePermission $targetModel
     * @return bool
     */
    public function isRelationshipExist(FwRolePermission $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $condition = [
                'status' => self::STATUS_FLAG_NORMAL,
                'role_id' => $targetModel->role_id,
                'permission_id' => $targetModel->permission_id
            ];
            $model = FwRolePermission::findOne($condition);

            if ($model != null)
                return true;
            else
                return false;
        } else {
            return true;
        }
    }

   

    /**
     * 判断角色权限的子关系是否存在
     * @param $roleId
     * @param $permissionId
     * @return bool
     */
    public function isSubRelationshipExist($roleId,$permissionId)
    {
        if ($permissionId != null && $permissionId != "")
        {
            $currentModel = new FwPermission();
            $query = $currentModel->find(false)
                ->andFilterWhere(['=','status',self::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['=','parent_permission_id',$permissionId]);

            $result = $query->all();
        }

        if (isset($result) && $result != null) {
            $selectedList = ArrayHelper::map($result, 'kid', 'kid');

            $permissionIdList = array_keys($selectedList);

            $currentModel = new FwRolePermission();
            $query = $currentModel->find(false)
                ->andFilterWhere(['=','status',self::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['in','permission_id',$permissionIdList])
                ->andFilterWhere(['=','role_id',$roleId]);

            $result = $query->all();

            if ($result != null && count($result) > 0)
                return true;
            else
                return false;
        } else {
            return false;
        }
    }
}