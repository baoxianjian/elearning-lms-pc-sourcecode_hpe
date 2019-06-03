<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 4/6/2015
 * Time: 9:10 PM
 */

namespace backend\controllers;


use backend\base\BaseBackController;
use backend\services\PermissionService;
use backend\services\RolePermissionService;
use backend\services\RoleService;
use common\models\framework\FwPermission;
use common\models\framework\FwRole;
use common\models\framework\FwRolePermission;
use common\services\framework\TreeNodeService;
use common\base\BaseActiveRecord;
use Yii;
use yii\helpers\Url;
use yii\web\Response;

class RolePermissionController extends BaseBackController{

    public $layout  = 'frame';

    public $needAddNodes = [];

    public function actionTreeData($roleId = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $service = new TreeNodeService();

        $otherService = RoleService::className();//可根据需要换所需的服务

        $otherKid = $roleId;

        $result = $service->listTreeData($otherService, $otherKid, true, false);

        return $result;
    }

    public function actionCreate()
    {
//        $this->layout = 'modalWin';
        if (isset($_POST['jsTree_multi-select-tree_changed_result']) && Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $tree_selected_result = Yii::$app->request->post('jsTree_multi-select-tree_changed_result');
            $tree_selected_result = str_replace('"', "", $tree_selected_result);
            //$tree_displayed_result = Yii::$app->request->post('jsTree_multi-select-tree_displayed_result');

            $roleId = Yii::$app->request->getQueryParam("resultId");
            $permissionService = new PermissionService();
            if ($tree_selected_result != '') {
                $initSelectedNodes = explode(',', str_replace(']', "", str_replace('[', "", $tree_selected_result)));

                $selectedNodes = $initSelectedNodes;

                //根据选中的节点，自动增加父节点
                foreach ($initSelectedNodes as $key => $value)
                {
                    // JSTree控件根节点ID=“-1”，此处特殊处理，过滤掉该值
                    if ($value === '-1') {
                        continue;
                    }

                    $this->needAddNodes = [];
                    $permissionId = $permissionService->getPermissionIdByTreeNodeId($value);
                    $this->getNeedAddNodes($permissionId,$selectedNodes);

                    foreach ($this->needAddNodes as $needKey => $needValue)
                    {
                        array_push($selectedNodes, $needValue);
                    }
                }

                foreach ($selectedNodes as $key => $value) {
                    // JSTree控件根节点ID=“-1”，此处特殊处理，过滤掉该值
                    if ($value === '-1') {
                        continue;
                    }

//                $treeNodeService = new TreeNodeService();
//                $treeTypeKid = $treeNodeService->getTreeTypeId("permission");
//                $hasSubNode =$treeNodeService->hasSubNode($treeNodeId,$treeTypeKid,self::STATUS_FLAG_NORMAL);

//                if (!$hasSubNode) {

//                    $permissionService = new PermissionService();
                    $model = new FwRolePermission();
                    $model->role_id = $roleId;
                    $model->permission_id = $permissionService->getPermissionIdByTreeNodeId($value);

                    $rolePermissionService = new RolePermissionService();
                    $rolePermissionService->startRelationship($model);
//                }
                }

            }

            return ['result' => 'success'];
        }
        else
        {
            $treeTypeCode = "permission";
//            $this->layout = 'modalWin';
            $treeDataUrl = Url::toRoute(['role-permission/tree-data']);
            return $this->renderAjax('//tree-node/multi-select', [
                'formType' => 'role-permission',
                'TreeType' => $treeTypeCode,
                'treeDataUrl' => $treeDataUrl,
                'treeState' => "False"
            ]);
        }
    }



    public function actionUpdate($roleId)
    {
        //        $this->layout = 'modalWin';
        if (isset($_POST['jsTree_multi-select-tree_changed_result']) && Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $tree_selected_result = Yii::$app->request->post('jsTree_multi-select-tree_changed_result');
            $tree_displayed_result = Yii::$app->request->post('jsTree_multi-select-tree_displayed_result');

//            $roleId = Yii::$app->request->getQueryParam("resultId");
            $permissionService = new PermissionService();
            if ($tree_selected_result != '') {
                $tree_selected_result = str_replace('"', "", $tree_selected_result);
               // $selectedNodes = explode(',', str_replace(']', "", str_replace('[', "", $tree_selected_result)));
                $initSelectedNodes = explode(',', str_replace(']', "", str_replace('[', "", $tree_selected_result)));
            }
            else {
//                $selectedNodes = [];
                $initSelectedNodes = [];
            }

            if ($tree_displayed_result != '') {
                $tree_displayed_result = str_replace('"', "", $tree_displayed_result);
                $displayedNodes = explode(',', str_replace(']', "", str_replace('[', "", $tree_displayed_result)));
            }
            else {
                $displayedNodes = [];
            }

            $rolePermissionService = new RolePermissionService();
            $rolePermissionService->stopRelationshipByRoleId($roleId);

//            $cacheKey = "PermissionList_RoleId_" . $roleId;
//            BaseActiveRecord::removeFromCache($cacheKey);

            $targetModels = [];

            $selectedNodes = $initSelectedNodes;


            //根据选中的节点，自动增加父节点
            foreach ($initSelectedNodes as $key => $value)
            {
                // JSTree控件根节点ID=“-1”，此处特殊处理，过滤掉该值
                if ($value === '-1') {
                    continue;
                }

                $this->needAddNodes = [];
                $permissionId = $permissionService->getPermissionIdByTreeNodeId($value);
                $this->getNeedAddNodes($permissionId,$selectedNodes);

                foreach ($this->needAddNodes as $needKey => $needValue)
                {
                    array_push($selectedNodes, $needValue);
                }
            }

//            $needStopList = TArrayHelper::array_minus($displayedNodes,$selectedNodes);

            foreach ($selectedNodes as $key => $value) {
                // JSTree控件根节点ID=“-1”，此处特殊处理，过滤掉该值
                if ($value === '-1') {
                    continue;
                }

//                $treeNodeService = new TreeNodeService();
//                $treeTypeKid = $treeNodeService->getTreeTypeId("permission");
//                $hasSubNode =$treeNodeService->hasSubNode($treeNodeId,$treeTypeKid,self::STATUS_FLAG_NORMAL);

//                if (!$hasSubNode) {

//                $permissionService = new PermissionService();
                $rolePermissionModel = new FwRolePermission();
                $rolePermissionModel->role_id = $roleId;
                $rolePermissionModel->permission_id = $permissionService->getPermissionIdByTreeNodeId($value);
                $rolePermissionModel->status = self::STATUS_FLAG_NORMAL;
                $rolePermissionModel->start_at = time();

                array_push($targetModels, $rolePermissionModel);
//                }
            }

            $rolePermissionService->batchStartRelationship($targetModels);

            return ['result' => 'success'];
        }
        else
        {
            $treeTypeCode = "permission";
//            $this->layout = 'modalWin';
            $treeDataUrl = Url::toRoute(['role-permission/tree-data', 'roleId'=>$roleId]);
            return $this->renderAjax('//tree-node/multi-select', [
                'formType' => 'role-permission',
                'TreeType' => $treeTypeCode,
                'treeDataUrl' => $treeDataUrl,
                'treeState' => "False"
            ]);
        }
    }


    public function actionView($roleId)
    {
        if ($roleId != null) {
            if (FwRole::findOne($roleId) != null) {
                $treeTypeCode = "permission";
//            $this->layout = 'modalWin';
                $treeDataUrl = Url::toRoute(['role-permission/tree-data', 'roleId' => $roleId]);
                return $this->renderAjax('//tree-node/multi-select', [
                    'formType' => 'role-permission',
                    'TreeType' => $treeTypeCode,
                    'treeDataUrl' => $treeDataUrl,
                    'treeState' => "False"
                ]);
            }
        }
    }


    /**
     * 获取可增加的上级节点
     * @param $newPermissionId
     * @param $selectedNodes
     */
    public function getNeedAddNodes($newPermissionId,$selectedNodes)
    {
        if ($newPermissionId != null) {
            $model = FwPermission::findOne($newPermissionId);
            if ($model != null) {
                $parent_permission_id = $model->parent_permission_id;

                if ($parent_permission_id != null && $parent_permission_id != "") {
                    $parentModel = FwPermission::findOne($parent_permission_id);
                    $parent_node_id = $parentModel->tree_node_id;

                    if ($parentModel->status == FwPermission::STATUS_FLAG_NORMAL) {
                        if (!in_array($parent_node_id, $selectedNodes) && !in_array($parent_node_id, $this->needAddNodes)) {
                            array_push($this->needAddNodes, $parent_node_id);
                        }

                        $this->getNeedAddNodes($parentModel->kid, $selectedNodes);
                    }
                }
            }
        }
    }
}