<?php

namespace backend\controllers;

use backend\services\CompanyService;
use backend\services\ExternalSystemService;
use backend\services\OrgnizationService;
use backend\services\PositionService;
use backend\services\UserPositionService;
use common\models\framework\FwCompanySystem;
use common\models\framework\FwExternalSystem;
use common\models\framework\FwOrgnization;
use common\models\framework\FwUser;
use common\models\framework\FwUserPosition;
use common\services\framework\UserService;
use common\helpers\TArrayHelper;
use Yii;
use backend\base\BaseBackController;
use common\models\framework\FwCompany;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
/**
 * CompanyController implements the CRUD actions for Company model.
 */
class CompanyExternalSystemController extends BaseBackController
{
    /**
     * Creates a new UserPosition model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $companySystem =  Yii::$app->request->post("company_system");

            if (isset($companySystem) && $companySystem != null && count($companySystem)> 0) {
                $batchModel = [];
                foreach ($companySystem as $key) {
                    $model = new FwCompanySystem();

                    $model->company_id = $key;
                    $model->system_id = Yii::$app->request->getQueryParam("resultId");
                    $model->status = self::STATUS_FLAG_NORMAL;
                    $model->start_at = time();

                    array_push($batchModel, $model);
                }

                $externalSystemService = new ExternalSystemService();

                $externalSystemService->batchStartRelationship($batchModel);
            }

            return ['result' => 'success'];
        } else {
            $availableList = [];
            $selected_keys = [];

            $companyService = new CompanyService();
            $result = $companyService->getTopLevelCompany();

            if (!empty($result))
                $availableList = ArrayHelper::map($result,'kid', 'company_name');

            return $this->renderAjax('create', [
                'availableList'=>$availableList,
                'selected_keys'=>$selected_keys,
            ]);
        }
    }

    /**
     * Updates an existing FwCompanySystem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $userId
     * @return mixed
     */
    public function actionUpdate($systemId)
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $companySystem =  Yii::$app->request->post("company_system");

            $externalSystemService = new ExternalSystemService();
            //先停用所有旧的关系
            $externalSystemService->stopRelationshipBySystemId($systemId);

            if (isset($companySystem) && $companySystem != null && count($companySystem)> 0) {
                $batchModel = [];
                foreach ($companySystem as $key) {
                    $model = new FwCompanySystem();

                    $model->company_id = $key;
                    $model->system_id = $systemId;
                    $model->status = self::STATUS_FLAG_NORMAL;
                    $model->start_at = time();

                    array_push($batchModel, $model);
                }

                //再添加停关系
                $externalSystemService->batchStartRelationship($batchModel);
            }

            return ['result' => 'success'];
        } else {
            $companyService = new CompanyService();
            $result = $companyService->getTopLevelCompany();


            $availableList = ArrayHelper::map($result,'kid', 'company_name');

            $externalSystemService = new ExternalSystemService();
            $selectedResult = $externalSystemService->getCompanyListBySystemId($systemId);

            $selectedList = ArrayHelper::map($selectedResult,'company_id', 'fwCompany.company_name');

            $finalAvailableList = array_unique(array_merge($availableList,$selectedList));

            $selected_keys = array_keys($selectedList);

            return $this->renderAjax('update', [
                'availableList'=>$finalAvailableList,
                'selected_keys'=>$selected_keys,
            ]);
        }
    }

    public function actionView($systemId)
    {
//        $this->layout = 'modalWin';
        $externalSystemService = new ExternalSystemService();
        $selectedResult = $externalSystemService->getCompanyListBySystemId($systemId);

        $selected_keys = ArrayHelper::map($selectedResult,'company_id', 'fwCompany.company_name');

        return $this->renderAjax('view', [
            'selected_keys' => $selected_keys,
        ]);
    }

}
