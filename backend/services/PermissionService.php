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

class PermissionService extends FwPermission{

    /**
     * 根据树节点ID获取权限ID
     * @param $id
     * @return null|string
     */
    public function getPermissionIdByTreeNodeId($id)
    {
        if ($id != null && $id != "") {
            $permissionModel = new FwPermission();

            $permissionResult = $permissionModel->findOne(['tree_node_id' => $id]);

            if ($permissionResult != null)
            {
                $permissionId = $permissionResult->kid;
            }
            else
            {
                $permissionId = null;
            }
        }
        else
        {
            $permissionId = null;
        }

        return $permissionId;
    }

    /**
     * 根据树节点ID，删除相关权限
     * @param $treeNodeId
     */
    public function deleteRelateData($treeNodeId)
    {
        $model = new FwPermission();

        $kids = "";
        foreach ($treeNodeId as $key) {
            $kids = $kids . "'" .  $key . "',";
            
            $permissionKey = $this->getPermissionIdByTreeNodeId($key);

            FwPermission::removeFromCacheByKid($permissionKey);
        }

        $kids = rtrim($kids, ",");

        $model->deleteAll(BaseActiveRecord::getQuoteColumnName("tree_node_id") . " in (".$kids.")");
    }


    /**
     * 激活父节点
     * @param $kid
     */
    public function activeParentNode($kid)
    {
        $model = FwPermission::findOne($kid);

        $parent_node_id = $model->parent_permission_id;

        if ($parent_node_id != null && $parent_node_id != "")
        {
            $parentModel = FwPermission::findOne($parent_node_id);

            if ($parentModel->status != FwPermission::STATUS_FLAG_NORMAL)
            {
                $parentModel->status = FwPermission::STATUS_FLAG_NORMAL;
                $parentModel->needReturnKey = true;
                $parentModel->save();

                $this->activeParentNode($parentModel->kid);
            }
        }
    }

    /**
     * 根据树节点ID，更新相关权限的状态
     * @param $treeNodeId
     * @param $targetTreeNodeId
     */
    public function updateParentIdByTreeNodeId($treeNodeId,$targetTreeNodeId)
    {
        $permissionId = $this->getPermissionIdByTreeNodeId($treeNodeId);
        $targetPermissionId = $this->getPermissionIdByTreeNodeId($targetTreeNodeId);
        if ($permissionId != null) {
            $permissionModel = FwPermission::findOne($permissionId);
            $permissionModel->parent_permission_id = $targetPermissionId;

            $permissionModel->save();
        }
    }

}