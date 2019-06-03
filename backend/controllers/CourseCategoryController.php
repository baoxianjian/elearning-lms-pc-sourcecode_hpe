<?php

namespace backend\controllers;

use Yii;
use backend\base\BaseBackController;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use backend\services\CompanyService;
use common\services\learning\CourseCategoryService;
use common\models\learning\LnCourseCategory;
use common\models\treemanager\FwTreeNode;

/**
 * DomainController implements the CRUD actions for LnCourseCategory model.
 */
class CourseCategoryController extends BaseBackController
{
    /**
     * Displays a single LnCourseCategory model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $service = new CourseCategoryService();
        $domainId = $service->getCourseCategoryIdByTreeNodeId($id);

        if ($domainId != null) {
            $model = $this->findModel($domainId);
        }
        else {
            $model = new LnCourseCategory();
        }
        return $this->renderAjax('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new LnCourseCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($parentNodeId)
    {

        $service = new CourseCategoryService();
        $model = new LnCourseCategory();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model->tree_node_id = Yii::$app->request->getQueryParam("resultId");
            $treeNodeModel = FwTreeNode::findOne($model->tree_node_id);
            if ($treeNodeModel != null) {
                $model->category_name = $treeNodeModel->tree_node_name;
                $model->category_code = $treeNodeModel->tree_node_code;
            }

            if ($parentNodeId != "-1") {
                $parentDomainId = $service->getCourseCategoryIdByTreeNodeId($parentNodeId);
                $model->parent_category_id = $parentDomainId;
            }

            if ($model->save()) {
                return ['result' => 'success'];
            } else {
                return ['result' => 'failure'];
            }
        } else {

            $companyService = new CompanyService();
            if ($parentNodeId == null || $parentNodeId == "" || $parentNodeId == "-1") {
                $companyModel = $companyService->getTopLevelCompany();
                if ($companyModel != null && count($companyModel) == 1)
                {
                    $model->company_id = $companyModel[0]->kid;
                }
            }else{
                $companyModel = $companyService->getSubCompanyByCourseCategoryTreeNodeId($parentNodeId);

                $CourseCategoryId = $service->getCourseCategoryIdByTreeNodeId($parentNodeId);
                $companyId = LnCourseCategory::findOne($CourseCategoryId)->company_id;

                if ($companyModel != null && count($companyModel) >= 1)
                {
                    $model->company_id = $companyId;
                }

            }
            return $this->renderAjax('create', [
                'model' => $model,
                'companyModel'=>$companyModel
            ]);
        }
    }

    /**
     * Updates an existing LnCourseCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $service = new CourseCategoryService();
        $CourseCategoryId = $service->getCourseCategoryIdByTreeNodeId($id);

        $model = $this->findModel($CourseCategoryId);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $treeNodeModel = FwTreeNode::findOne($model->tree_node_id);
            if ($treeNodeModel != null) {
                $model->category_name = $treeNodeModel->tree_node_name;
                $model->category_code = $treeNodeModel->tree_node_code;
            }

            if ($model->save()) {
                return ['result' => 'success'];
            }else {
                return ['result' => 'failure'];
            }
        } else {
            $parentNodeId = FwTreeNode::findOne($id)->parent_node_id;
            $companyService = new CompanyService();
            if ($parentNodeId == null || $parentNodeId == "" || $parentNodeId == "-1") {
                $companyModel = $companyService->getTopLevelCompany();
            }
            else
            {
                $companyModel = $companyService->getSubCompanyByCourseCategoryTreeNodeId($parentNodeId);
            }

            return $this->renderAjax('update', [
                'model' => $model,
                'companyModel'=>$companyModel
            ]);
        }
    }

    /**
     * Deletes an existing LnCourseCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the LnCourseCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return LnCourseCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = LnCourseCategory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common','data_not_exist'));
        }
    }
}
