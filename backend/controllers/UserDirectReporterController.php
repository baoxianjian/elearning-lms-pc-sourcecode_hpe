<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 4/8/2015
 * Time: 3:01 PM
 */

namespace backend\controllers;


use backend\base\BaseBackController;
use backend\services\OrgnizationService;
use backend\services\RoleService;
use common\services\framework\TreeNodeService;
use backend\services\UserManagerService;
use backend\services\UserService;
use common\models\framework\FwCompany;
use common\models\framework\FwOrgnization;
use common\models\framework\FwUser;
use common\models\framework\FwUserManager;
use common\models\framework\FwUserRole;
use common\services\framework\UserOrgnizationService;
use common\helpers\TArrayHelper;
use components\widgets\TPagination;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class UserDirectReporterController extends BaseBackController{

    public $layout  = 'frame';

    public function actionList()
    {
        $this->layout = 'list';

        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");
        $includeSubNode = Yii::$app->request->getQueryParam("includeSubNode");


        if ($treeNodeKid == '-1')
            $treeNodeKid = '';

//        if ($includeSubNode == '1') {
//            $treeNodeService = new TreeNodeService();
//            $treeTypeId = $treeNodeService->getTreeTypeId('orgnization');
//            if ($treeNodeKid != '') {
//                $treeNodeModel = $treeNodeService->findOne($treeNodeKid);
//
//                $treeNodeIdList = $treeNodeService->getAllNodeIdIncludeSub($treeNodeKid, $treeTypeId, $treeNodeModel->node_id_path . $treeNodeKid . "/%", self::STATUS_FLAG_NORMAL);
//            } else {
//                $treeNodeIdList = $treeNodeService->getAllNodeIdIncludeSub('', $treeTypeId, "/%", self::STATUS_FLAG_NORMAL);
//            }
//        }
//        else
//        {
//            $treeNodeIdList = $treeNodeKid;
//        }

        $forceShowAll = 'False';
        $pageSize = $this->defaultPageSize;

        if (Yii::$app->request->getQueryParam('PageShowAll') != null && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
            $forceShowAll = 'True';
        }
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }

        $service = new UserService();
        $managerFlag = null;
        $dataProvider = $service->search(Yii::$app->request->queryParams,$managerFlag,$treeNodeKid,$includeSubNode);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize'=>$pageSize,'totalCount'=>$count]);
        $dataProvider->setPagination($page);
        return $this->render('list', [
            'page' => $page,
            'searchModel' => $service,
            'dataProvider' => $dataProvider,
            'selectNodeId'=>$treeNodeKid,
            'includeSubNode'=>$includeSubNode,
            'forceShowAll'=>$forceShowAll,
            'pageSize'=>$pageSize
        ]);
    }


    public function actionUpdate($userId)
    {
        $userModel = FwUser::findOne($userId);
        $reportingModel = FwCompany::findOne($userModel->company_id)->reporting_model;

        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

//            $userService = new \common\services\framework\UserService();
//            $selectedResult = $userService->getReportingManagerByUserId($userId, false);
//            $selectedList = ArrayHelper::map($selectedResult,'kid', 'user_display_name');

//            $selected_keys = array_keys($selectedList);

            $reportingManager =  Yii::$app->request->post("reporting_manager");

            $userManageService = new UserManagerService();
            $userManageService->stopRelationshipByUserId($userId);

            $targetModels = [];

            if (isset($reportingManager) && $reportingManager != null && count($reportingManager)> 0) {

                if (count($reportingManager)> 1 && $reportingModel == FwUserManager::REPORTING_MODEL_LINE_MANAGER) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'reporting_manager_exceed_one')];
                }
                else {
//                    $needStopList = TArrayHelper::array_minus($selected_keys, $reportingManager);

//                    $needStartList = TArrayHelper::array_minus($reportingManager, $selected_keys);

//                    $kids = "";
                    $reportingManagerId = null;
                    foreach ($reportingManager as $key) {
//                        $kids = $kids . "'" . $key . "',";
                        $reportingManagerId = $key;

                        $userManageModel = new FwUserManager();

                        $userManageModel->manager_id = $reportingManagerId;
                        $userManageModel->user_id = $userId;
                        $userManageModel->reporting_model = $reportingModel;
                        $userManageModel->status = self::STATUS_FLAG_NORMAL;
                        $userManageModel->start_at = time();

                        array_push($targetModels, $userManageModel);
                    }
//                    $kids = rtrim($kids,",");

                    if ($userModel != null && $userModel->reporting_manager_id != $reportingManagerId) {
                        //不管是什么模式，都更新一直直接领导；如果是部门经理模式，以最后一个为准
                        $userModel->reporting_manager_id = $reportingManagerId;

                        $userModel->save();
                    }

                    $userManageService->batchStartRelationship($targetModels);

                }
            }
            else {
                $userModel->reporting_manager_id = null;
                $userModel->save();
            }


            return ['result' => 'success'];
        } else {

//            $userOrgnizationService = new UserOrgnizationService();
            $userService = new UserService();

//            $userOrgnizationModel = $userOrgnizationService->getManagedListByUserId($userId, null, false);
//
//            if ($userOrgnizationModel != null && count($userOrgnizationModel) > 0) {
//                $selectedList = ArrayHelper::map($userOrgnizationModel, 'tree_node_id', 'tree_node_id');
//
//                $treeNodeIdList = array_keys($selectedList);
//
//                $result = $userService->getAvailableReportingManageUserByTreeNodeIdList($treeNodeIdList, $userId, $reportingModel);
//            } else {

                $orgnizationId = FwUser::findOne($userId)->orgnization_id;

                $treeNodeKid = FwOrgnization::findOne($orgnizationId)->tree_node_id;

//                $treeNodeService = new TreeNodeService();
//                $treeTypeId = $treeNodeService->getTreeTypeId('orgnization');
//                if ($treeNodeKid != null && $treeNodeKid != '') {
//                    $treeNodeModel = $treeNodeService->findOne($treeNodeKid);
//
//                    $treeNodeIdList = $treeNodeService->getAllNodeIdIncludeSub($treeNodeKid, $treeTypeId, $treeNodeModel->node_id_path . $treeNodeKid . "/%", self::STATUS_FLAG_NORMAL);
//                } else {
//                    $treeNodeIdList = $treeNodeService->getAllNodeIdIncludeSub('', $treeTypeId, "/%", self::STATUS_FLAG_NORMAL);
//                }

                $result = $userService->getAvailableReportingManageUserByTreeNodeId($treeNodeKid, $userId, $reportingModel);
//            }


            $availableList = ArrayHelper::map($result,'kid', 'user_display_name');

            $commonUserService = new \common\services\framework\UserService();

            $selectedResult = $commonUserService->getReportingManagerByUserId($userId, false);
            $selectedList = ArrayHelper::map($selectedResult,'kid', 'user_display_name');

//            $finalAvailableList = $availableList + $selectedList;
            $finalAvailableList = array_unique(array_merge($availableList,$selectedList));

            $selected_keys = array_keys($selectedList);

            return $this->renderAjax('update', [
                'model' => $userModel,
                'availableList'=>$finalAvailableList,
                'selected_keys'=>$selected_keys,
            ]);
        }
    }


    public function actionView($userId)
    {
        $userService = new \common\services\framework\UserService();
        $selectedResult = $userService->getReportingManagerByUserId($userId, false);
        $selected_keys = ArrayHelper::map($selectedResult, 'kid', 'user_display_name');

        $model = FwUser::findOne($userId);
        return $this->renderAjax('view', [
            'model' => $model,
            'selected_keys' => $selected_keys,
        ]);
    }
}