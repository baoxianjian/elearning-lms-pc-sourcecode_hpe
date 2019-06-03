<?php

namespace backend\controllers;

use Yii;
use backend\base\BaseBackController;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use backend\services\CompanyService;
use common\services\learning\CoursewareCategoryService;
use common\models\learning\LnCoursewareCategory;
use common\models\treemanager\FwTreeNode;

/**
 * DomainController implements the CRUD actions for LnCoursewareCategory model.
 */
class CoursewareCategoryController extends BaseBackController
{
    /**
     * Displays a single LnCoursewareCategory model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $service = new CoursewareCategoryService();
        $domainId = $service->getCoursewareCategoryIdByTreeNodeId($id);

        if ($domainId != null) {
            $model = $this->findModel($domainId);
        }
        else {
            $model = new LnCoursewareCategory();
        }
        return $this->renderAjax('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new LnCoursewareCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($parentNodeId)
    {

        $service = new CoursewareCategoryService();
        $model = new LnCoursewareCategory();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model->tree_node_id = Yii::$app->request->getQueryParam("resultId");
            $treeNodeModel = FwTreeNode::findOne($model->tree_node_id);
            if ($treeNodeModel != null) {
                $model->category_name = $treeNodeModel->tree_node_name;
                $model->category_code = $treeNodeModel->tree_node_code;
            }

            if ($parentNodeId != "-1") {
                $parentDomainId = $service->getCoursewareCategoryIdByTreeNodeId($parentNodeId);
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
                $companyModel = $companyService->getSubCompanyByCoursewareCategoryTreeNodeId($parentNodeId);

                $CoursewareCategoryId = $service->getCoursewareCategoryIdByTreeNodeId($parentNodeId);
                $companyId = LnCoursewareCategory::findOne($CoursewareCategoryId)->company_id;

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
     * Updates an existing LnCoursewareCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $service = new CoursewareCategoryService();
        $CoursewareCategoryId = $service->getCoursewareCategoryIdByTreeNodeId($id);

        $model = $this->findModel($CoursewareCategoryId);

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
                $companyModel = $companyService->getSubCompanyByCoursewareCategoryTreeNodeId($parentNodeId);
            }

            return $this->renderAjax('update', [
                'model' => $model,
                'companyModel'=>$companyModel
            ]);
        }
    }

    /**
     * Deletes an existing LnCoursewareCategory model.
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
     * Finds the LnCoursewareCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return LnCoursewareCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = LnCoursewareCategory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common','data_not_exist'));
        }
    }
}
