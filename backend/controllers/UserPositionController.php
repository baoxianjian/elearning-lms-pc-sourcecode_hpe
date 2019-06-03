<?php

namespace backend\controllers;

use backend\services\CompanyService;
use backend\services\OrgnizationService;
use backend\services\PositionService;
use backend\services\UserPositionService;
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
class UserPositionController extends BaseBackController
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

            $userPosition =  Yii::$app->request->post("user_position");
            $userPositionMaster =  Yii::$app->request->post("user_position_master");

            if (isset($userPosition) && $userPosition != null && count($userPosition)> 0) {

                foreach ($userPosition as $key) {
                    $model = new FwUserPosition();

                    $model->position_id = $key;
                    $model->user_id = Yii::$app->request->getQueryParam("resultId");

                    $isMaster = FwUserPosition::NO;
                    foreach ($userPositionMaster as $single) {
                        if ($single == $key) {
                            $isMaster = FwUserPosition::YES;
                            break;
                        }
                    }
//                    $isMaster = array_key_exists($key,$userPositionMaster) ? FwUserPosition::YES:FwUserPosition::NO;
                    $model->is_master = $isMaster;

                    $userPositionService = new UserPositionService();
                    $userPositionService->startRelationship($model);
                }
            }

            return ['result' => 'success'];
        } else {
            $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");

            $availableList = [];
            $selected_keys = [];
//            $availableIsMasterList = [];
            $selected_master_keys = [];

            if ($treeNodeKid == '-1')
                $orgnizationId = null;
            else
            {
                $orgnizationService = new OrgnizationService();
                $orgnizationId = $orgnizationService->getOrgnizationIdByTreeNodeId($treeNodeKid);

                $companyModel = FwOrgnization::findOne($orgnizationId);

                if ($companyModel != null)
                {
                    $companyId = $companyModel->company_id;

                    $positionService = new PositionService();

                    $result = $positionService->getAvailablePositionByCompanyId($companyId);

                    $availableList = ArrayHelper::map($result,'kid', 'position_display_name');


//                    foreach ($result as $single) {
//                        $availableIsMasterList[$single->kid] = Yii::t('common','yes');
//                    }

                }

            }

            return $this->renderAjax('create', [
                'availableList'=>$availableList,
                'selected_keys'=>$selected_keys,
//                'availableIsMasterList'=>$availableIsMasterList,
                'selected_master_keys'=>$selected_master_keys,
            ]);
        }
    }

    /**
     * Updates an existing UserPosition model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $userId
     * @return mixed
     */
    public function actionUpdate($userId)
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $commonUserService = new UserService();
//            $selectedResult = $commonUserService->getPositionListByUserId($userId, false);
//            $selectedList = ArrayHelper::map($selectedResult,'position_id', 'position_display_name');

//            $selected_keys = array_keys($selectedList);

            $userPosition =  Yii::$app->request->post("user_position");

            $userPositionMaster =  Yii::$app->request->post("user_position_master");


            $userPositionService = new UserPositionService();
            $userPositionService->stopRelationshipByUserId($userId);

            $targetModels = [];

            if (isset($userPosition) && $userPosition != null && count($userPosition)> 0) {

//                $needStopList = TArrayHelper::array_minus($selected_keys,$userPosition);

//                $needStartList = TArrayHelper::array_minus($userPosition,$selected_keys);

//                $kids = "";
                foreach ($userPosition as $key) {
//                    $kids = $kids . "'" . $key . "',";

                    $userPositionModel = new FwUserPosition();

                    $userPositionModel->position_id = $key;
                    $userPositionModel->user_id = $userId;
                    $userPositionModel->status = self::STATUS_FLAG_NORMAL;

                    $isMaster = FwUserPosition::NO;
                    foreach ($userPositionMaster as $single) {
                        if ($single == $key) {
                            $isMaster = FwUserPosition::YES;
                            break;
                        }
                    }
//                    $isMaster = array_key_exists($key,$userPositionMaster) ? FwUserPosition::YES:FwUserPosition::NO;
                    $userPositionModel->is_master = $isMaster;
                    $userPositionModel->start_at = time();

                    array_push($targetModels, $userPositionModel);
                }
//                $kids = rtrim($kids,",");

                $userPositionService->batchStartRelationship($targetModels);
            }

            return ['result' => 'success'];
        } else {

            $companyId = FwUser::findOne($userId)->company_id;

            $positionService = new PositionService();

            $result = $positionService->getAvailablePositionByCompanyId($companyId);

            $availableList = ArrayHelper::map($result,'kid', 'position_display_name');


//            $availableIsMasterList = ArrayHelper::map($result,'kid', '是');

            $commonUserService = new UserService();
            $selectedResult = $commonUserService->getPositionListByUserId($userId, false);
            $selectedList = ArrayHelper::map($selectedResult,'position_id', 'position_display_name');

            $finalAvailableList = array_unique(array_merge($availableList,$selectedList));

//            $availableIsMasterList = [];
//            foreach ($finalAvailableList as $key=> $single) {
//                $availableIsMasterList[$key] = Yii::t('common','yes');
//            }

            $selected_keys = array_keys($selectedList);

            $selected_master_keys = [];
            foreach ($selectedResult as $single) {
                if ($single->is_master == FwUserPosition::YES) {
                    $selected_master_keys[] = $single->position_id;
                }
            }

            return $this->renderAjax('update', [
                'availableList'=>$finalAvailableList,
                'selected_keys'=>$selected_keys,
//                'availableIsMasterList'=>$availableIsMasterList,
                'selected_master_keys'=>$selected_master_keys,
            ]);
        }
    }

    public function actionView($userId)
    {
//        $this->layout = 'modalWin';
        $commonUserService = new UserService();
        $selectedResult = $commonUserService->getPositionListByUserId($userId, false);
        $selected_keys = ArrayHelper::map($selectedResult, 'position_id', 'position_display_name');

        $selected_master_keys = [];
        foreach ($selectedResult as $single) {
            if ($single->is_master == FwUserPosition::YES) {
                $selected_master_keys[] = $single->position_id;
            }
        }

        $model = FwUser::findOne($userId);
        return $this->renderAjax('view', [
            'model' => $model,
            'selected_keys' => $selected_keys,
            'selected_master_keys' => $selected_master_keys,
        ]);
    }

}
