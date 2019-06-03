<?php

namespace backend\controllers;

use backend\services\CompanyService;
use common\models\framework\FwCompany;
use common\models\treemanager\FwTreeNode;
use common\services\framework\ExternalSystemService;
use common\base\BaseActiveRecord;
use Yii;
use backend\base\BaseBackController;
use backend\services\DomainService;
use common\models\framework\FwDomain;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * DomainController implements the CRUD actions for FwDomain model.
 */
class DomainController extends BaseBackController
{
    /**
     * Displays a single FwDomain model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $service = new DomainService();
        $domainId = $service->getDomainIdByTreeNodeId($id);

        if ($domainId != null) {
            $model = $this->findModel($domainId);
        }
        else {
            $model = new FwDomain();
        }
        return $this->renderAjax('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new FwDomain model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($parentNodeId)
    {
        $service = new DomainService();
        $model = new FwDomain();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model->tree_node_id = Yii::$app->request->getQueryParam("resultId");
            $treeNodeModel = FwTreeNode::findOne($model->tree_node_id);
            if ($treeNodeModel != null) {
                $model->domain_name = $treeNodeModel->tree_node_name;
                $model->domain_code = $treeNodeModel->tree_node_code;
            }
//            $model->tree_node_id = $resultId;

            if ($parentNodeId != "-1") {
                $parentDomainId = $service->getDomainIdByTreeNodeId($parentNodeId);
                $model->parent_domain_id = $parentDomainId;
            }

            if (!empty($model->company_id)) {
                $limitedDomainNumber = 0;

                $companyModel = FwCompany::findOne($model->company_id);
                if (!empty($companyModel)) {
                    $limitedDomainNumber = $companyModel->limited_domain_number;
                }
                if ($limitedDomainNumber != 0) {
                    $companyDoaminNumber = $service->getCompanyDomainCount($model->company_id);

                    if ($companyDoaminNumber >= $limitedDomainNumber) {
                        return ['result' => 'other', 'message' => Yii::t('common', "active_domain_exceed_{number}", ["number" => $limitedDomainNumber])];
                    }
                }
            }

            if ($model->save()) {
                return ['result' => 'success'];
            } else {
                return ['result' => 'failure'];
            }
        } else {
            $companyService = new CompanyService();
            if ($parentNodeId == null || $parentNodeId == "" || $parentNodeId == "-1") {
                $companyModel = $companyService->getTopLevelCompany(false);
                if ($companyModel != null && count($companyModel) == 1)
                {
                    $model->company_id = $companyModel[0]->kid;
                }
            }
            else
            {
                $companyModel = $companyService->getSubCompanyByDomainTreeNodeId($parentNodeId);

                $orgnizationId = $service->getDomainIdByTreeNodeId($parentNodeId);
                $companyId = FwDomain::findOne($orgnizationId)->company_id;

                if ($companyModel != null && count($companyModel) >= 1)
                {
                    $model->company_id = $companyId;
                }

            }
            $model->share_flag = FwDomain::SHARE_FLAG_EXCLUSIVE;
            return $this->renderAjax('create', [
                'model' => $model,
                'companyModel'=>$companyModel
            ]);
        }
    }

    /**
     * Updates an existing FwDomain model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $service = new DomainService();
        $DomainId = $service->getDomainIdByTreeNodeId($id);

        $model = $this->findModel($DomainId);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $treeNodeModel = FwTreeNode::findOne($model->tree_node_id);
            if ($treeNodeModel != null) {
                $model->domain_name = $treeNodeModel->tree_node_name;
                $model->domain_code = $treeNodeModel->tree_node_code;
            }
//            $model->tree_node_id = Yii::$app->request->getQueryParam("resultId");
//            $model->tree_node_id = $resultId;

            if ($model->save()) {
                return ['result' => 'success'];
            }else {
                return ['result' => 'failure'];
            }
        } else {
            $parentNodeId = FwTreeNode::findOne($id)->parent_node_id;
            $companyService = new CompanyService();
            if ($parentNodeId == null || $parentNodeId == "" || $parentNodeId == "-1") {
                $companyModel = $companyService->getTopLevelCompany(false);
            }
            else
            {
                $companyModel = $companyService->getSubCompanyByDomainTreeNodeId($parentNodeId);
            }

            return $this->renderAjax('update', [
                'model' => $model,
                'companyModel'=>$companyModel
            ]);
        }
    }

    /**
     * Deletes an existing FwDomain model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->delete()) {
            $externalSystemService = new ExternalSystemService();
            $externalSystemService->deleteDomainInfoByDomainId($id);
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the FwDomain model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwDomain the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FwDomain::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common','data_not_exist'));
        }
    }
}
