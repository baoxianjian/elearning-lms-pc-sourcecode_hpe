<?php

namespace backend\controllers;

use backend\services\CompanyService;
use backend\services\DomainService;
use common\models\framework\FwExternalSystemValue;
use common\models\treemanager\FwTreeNode;
use common\services\framework\DictionaryService;
use common\services\framework\ExternalSystemService;
use common\base\BaseActiveRecord;
use Yii;
use backend\base\BaseBackController;
use backend\services\OrgnizationService;
use common\models\framework\FwOrgnization;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * OrgnizationController implements the CRUD actions for FwOrgnization model.
 */
class OrgnizationController extends BaseBackController
{
    /**
     * Displays a single FwOrgnization model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $service = new OrgnizationService();
        $orgnizationId = $service->getOrgnizationIdByTreeNodeId($id);

        if ($orgnizationId != null) {
            $model = $this->findModel($orgnizationId);
        }
        else {
            $model = new FwOrgnization();
        }
        return $this->renderAjax('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new FwOrgnization model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($parentNodeId)
    {
//        $this->layout = 'modalWin';
        $service = new OrgnizationService();
        $model = new FwOrgnization();
        $domainService = new DomainService();


        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model->tree_node_id = Yii::$app->request->getQueryParam("resultId");

            $treeNodeModel = FwTreeNode::findOne($model->tree_node_id);
            if ($treeNodeModel != null) {
                $model->orgnization_name = $treeNodeModel->tree_node_name;
                $model->orgnization_code = $treeNodeModel->tree_node_code;
            }
//            $model->tree_node_id = $resultId;

//            $treeNodeService = new TreeNodeService();
//            $parentNodeId = $treeNodeService->findOne($model->tree_node_id)->parent_node_id;

            if ($parentNodeId != "-1") {
                $parentOrgnizationId = $service->getOrgnizationIdByTreeNodeId($parentNodeId);
                $model->parent_orgnization_id = $parentOrgnizationId;
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

                $domainModel = [];

                if ($model->company_id != null)
                {
                    $domainModel = $domainService->getExclusivedDomainListByCompanyId($model->company_id);
                }

                if ($domainModel != null && count($domainModel) == 1)
                {
                    $model->domain_id = $domainModel[0]->kid;
                }
            }
            else
            {
                $companyModel = $companyService->getSubCompanyByOrgTreeNodeId($parentNodeId);

                $orgnizationId = $service->getOrgnizationIdByTreeNodeId($parentNodeId);
                $companyId = FwOrgnization::findOne($orgnizationId)->company_id;

                if ($companyModel != null && count($companyModel) >= 1) {
                    $model->company_id = $companyId;
                }

                $domainModel = $domainService->getExclusivedDomainListByCompanyId($model->company_id);

                if ($domainModel != null && count($domainModel) >= 1) {
                    $domainId = FwOrgnization::findOne($orgnizationId)->domain_id;
                    $model->domain_id = $domainId;
                }
            }

            $model->is_default_orgnization = FwOrgnization::NO;
            $model->is_make_org = FwOrgnization::NO;
            $model->is_service_site = FwOrgnization::NO;

            $dictionaryService = new DictionaryService();
            $orgnizationLevelModel = $dictionaryService->getDictionariesByCategory('orgnization_level',$model->company_id);

            return $this->renderAjax('create', [
                'model' => $model,
                'companyModel'=>$companyModel,
                'domainModel'=>$domainModel,
                'orgnizationLevelModel' => $orgnizationLevelModel
            ]);
        }
    }

    public function actionDomain($companyId)
    {
        $service = new DomainService();
        if (Yii::$app->request->isAjax &&  Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $domainList = $service->getExclusivedDomainListByCompanyId($companyId);

            return ['result' => 'success' , 'domainList' => $domainList];
        }

        return ['result' => 'failure'];
    }

    /**
     * Updates an existing FwOrgnization model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $service = new OrgnizationService();
        $domainService = new DomainService();
        $orgnizationId = $service->getOrgnizationIdByTreeNodeId($id);

        $model = $this->findModel($orgnizationId);

        $old_company_id = $model->company_id;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $treeNodeModel = FwTreeNode::findOne($model->tree_node_id);
            if ($treeNodeModel != null) {
                $model->orgnization_name = $treeNodeModel->tree_node_name;
                $model->orgnization_code = $treeNodeModel->tree_node_code;
            }
//            $model->tree_node_id = Yii::$app->request->getQueryParam("resultId");
//            $model->tree_node_id = $resultId;
            $model->needReturnKey = true;
            if ($model->save()) {
                $new_company_id = $model->company_id;
                if ($old_company_id != $new_company_id) {
                    $service->updateReleatedUserOrgnizationInfo($model->kid);
                }
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
                $companyModel = $companyService->getSubCompanyByOrgTreeNodeId($parentNodeId);
            }

            $domainModel = $domainService->getExclusivedDomainListByCompanyId($model->company_id);

            $dictionaryService = new DictionaryService();
            $orgnizationLevelModel = $dictionaryService->getDictionariesByCategory('orgnization_level',$model->company_id);

            return $this->renderAjax('update', [
                'model' => $model,
                'companyModel'=>$companyModel,
                'domainModel'=>$domainModel,
                'orgnizationLevelModel' => $orgnizationLevelModel
            ]);
        }
    }

    /**
     * Deletes an existing FwOrgnization model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->delete()) {
            $externalSystemService = new ExternalSystemService();
            $externalSystemService->deleteOrgnizationInfoByOrgnizationId($id);
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the FwOrgnization model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwOrgnization the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FwOrgnization::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common','data_not_exist'));
        }
    }
}
