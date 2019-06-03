<?php

namespace backend\controllers;

use common\models\treemanager\FwTreeNode;
use common\base\BaseActiveRecord;
use Yii;
use backend\base\BaseBackController;
use backend\services\PermissionService;
use common\models\framework\FwPermission;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * PermissionController implements the CRUD actions for FwPermission model.
 */
class PermissionController extends BaseBackController
{
    /**
     * Displays a single FwPermission model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $service = new PermissionService();
        $permissionId = $service->getPermissionIdByTreeNodeId($id);

        if ($permissionId != null) {
            $model = $this->findModel($permissionId);
        }
        else {
            $model = new FwPermission();
        }
        return $this->renderAjax('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new FwPermission model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($parentNodeId)
    {
//        $this->layout = 'modalWin';
        $model = new FwPermission();
        $service = new PermissionService();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model->tree_node_id = Yii::$app->request->getQueryParam("resultId");

            $treeNodeModel = FwTreeNode::findOne($model->tree_node_id);
            if ($treeNodeModel != null) {
                $model->permission_name = $treeNodeModel->tree_node_name;
                $model->permission_code = $treeNodeModel->tree_node_code;
            }

//            $model->tree_node_id = $resultId;

            if ($parentNodeId != "-1") {
                $parentPermissionId = $service->getPermissionIdByTreeNodeId($parentNodeId);
                $model->parent_permission_id = $parentPermissionId;
            }

            if ($model->save()) {
                return ['result' => 'success'];
            }else {
                return ['result' => 'failure'];
            }
        } else {
            $model->permission_type = FwPermission::PERMISSION_TYPE_MENU;
            $model->action_type = FwPermission::ACTION_TYPE_URL;

            if ($parentNodeId != "-1") {
                $parentPermissionId = $service->getPermissionIdByTreeNodeId($parentNodeId);
                $model->system_flag = FwPermission::findOne($parentPermissionId)->system_flag;
            }
            else {
                $model->system_flag = FwPermission::SYSTEM_FLG_ELN_BACKEND;
            }

            $model->limitation = FwPermission::LIMITATION_NONE;
            $model->is_display = FwPermission::DISPLAY_FLAG_NO;

            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing FwPermission model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
//        $this->layout = 'modalWin';
        $service = new PermissionService();
        $permissionId = $service->getPermissionIdByTreeNodeId($id);

        $model = $this->findModel($permissionId);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $treeNodeModel = FwTreeNode::findOne($model->tree_node_id);
            if ($treeNodeModel != null) {
                $model->permission_name = $treeNodeModel->tree_node_name;
                $model->permission_code = $treeNodeModel->tree_node_code;
            }
//            $model->tree_node_id = Yii::$app->request->getQueryParam("resultId");
//            $model->tree_node_id = $resultId;

            if ($model->save()) {
                return ['result' => 'success'];
            }else {
                return ['result' => 'failure'];
            }
        } else {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing FwPermission model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the FwPermission model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwPermission the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FwPermission::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common','data_not_exist'));
        }
    }
}
