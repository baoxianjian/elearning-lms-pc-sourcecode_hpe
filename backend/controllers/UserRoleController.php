<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 4/8/2015
 * Time: 3:01 PM
 */

namespace backend\controllers;


use backend\base\BaseBackController;
use backend\services\CntManageRefService;
use backend\services\CompanyService;
use backend\services\DomainService;
use backend\services\OrgnizationService;
use backend\services\RoleService;
use backend\services\TeacherService;
use common\services\framework\TreeNodeService;
use common\models\framework\FwRole;
use common\services\framework\UserCompanyService;
use common\services\framework\UserOrgnizationService;
use common\services\framework\UserRoleService;
use backend\services\UserService;
use common\models\framework\FwUser;
use common\models\framework\FwUserRole;
use common\models\treemanager\FwCntManageRef;
use common\services\framework\RbacService;
use common\services\framework\UserDomainService;
use common\helpers\TArrayHelper;
use components\widgets\TPagination;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Response;

class UserRoleController extends BaseBackController{

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
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $commonUserService = new \common\services\framework\UserService();
            $selectedResult = $commonUserService->getRoleListByUserId($userId, false);
            $selectedList = ArrayHelper::map($selectedResult,'role_id', 'role_display_name');

            $selected_keys = array_keys($selectedList);

            $userRole =  Yii::$app->request->post("user_role");

            $userRoleService = new UserRoleService();
            $userRoleService->stopRelationshipByUserId($userId);

            $targetModels = [];
            if (isset($userRole) && $userRole != null && count($userRole)> 0) {

//                $needStopList = TArrayHelper::array_minus($selected_keys,$userRole);

                $needStartList = TArrayHelper::array_minus($userRole,$selected_keys);

                foreach ($userRole as $key) {
                    $userRoleModel = new FwUserRole();
                    $userRoleModel->user_id = $userId;
                    $userRoleModel->role_id = $key;
                    $userRoleModel->status = self::STATUS_FLAG_NORMAL;
                    $userRoleModel->start_at = time();

                    array_push($targetModels, $userRoleModel);
                }

                $userRoleService = new UserRoleService();
                $userRoleService->batchStartRelationship($targetModels);

                if ($needStartList != null && count($needStartList) > 0) {
                    foreach ($needStartList as $key) {
                        if ($key != null && $key != "") {
                            $roleModel = FwRole::findOne($key);
                            if (!empty($roleModel)) {
                                if ($roleModel->role_code == "Teacher") {
                                    $teacherService = new TeacherService();
                                    $teacherService->addTeacherByUserID($userId);
                                }
                            }
                        }
                    }
                }
            }


            return ['result' => 'success'];
        } else {

            $companyId = FwUser::findOne($userId)->company_id;

            $roleService = new RoleService();

            $result = $roleService->getAvailableRoleByCompanyId($companyId);

            $availableList = ArrayHelper::map($result,'kid', 'role_display_name');

            $commonUserService = new \common\services\framework\UserService();

            $selectedResult = $commonUserService->getRoleListByUserId($userId, false);
            $selectedList = ArrayHelper::map($selectedResult, 'role_id', 'role_display_name');

            $finalAvailableList = array_unique(array_merge($availableList,$selectedList));

            $selected_keys = array_keys($selectedList);

            $model = FwUser::findOne($userId);
            return $this->renderAjax('update', [
                'model' => $model,
                'availableList'=>$finalAvailableList,
                'selected_keys'=>$selected_keys,
                'userId' => $userId
            ]);
        }
    }


    public function actionView($userId)
    {
//        $this->layout = 'modalWin';
        $commonUserService = new \common\services\framework\UserService();
        $selectedResult = $commonUserService->getRoleListByUserId($userId, false);
        $selected_keys = ArrayHelper::map($selectedResult, 'role_id', 'role_display_name');

        $model = FwUser::findOne($userId);
        return $this->renderAjax('view', [
            'model' => $model,
            'selected_keys' => $selected_keys,
            'userId' => $userId,
        ]);
    }


    public function actionDomain($userId)
    {
        $treeTypeCode = "domain";
        $treeFlag = $treeTypeCode;
        $suffix = '';
        if (isset($treeFlag) && $treeFlag != null && $treeFlag != '')
            $suffix = '_' . $treeFlag;

//        $this->layout = 'modalWin';
        if (isset($_POST['jsTree_multi-select-tree_changed_result'.$suffix]) && Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $tree_selected_result = Yii::$app->request->post('jsTree_multi-select-tree_changed_result'.$suffix);
//            $tree_displayed_result = Yii::$app->request->post('jsTree_multi-select-tree_displayed_result'.$suffix);

            if ($tree_selected_result != '') {
                $selectedNodes = explode(',', str_replace(']', "", str_replace('[', "", $tree_selected_result)));
            }
            else {
                $selectedNodes = [];
            }

//            if ($tree_displayed_result != '') {
//                $displayedNodes = explode(',', str_replace(']', "", str_replace('[', "", $tree_displayed_result)));
//            }
//            else {
//                $displayedNodes = [];
//            }

            $cntManageRefService = new CntManageRefService();
            $cntManageRefService->stopRelationshipBySubjectIdList($userId,FwCntManageRef::SUBJECT_TYPE_USER,FwCntManageRef::CONTENT_TYPE_DOMAIN,FwCntManageRef::REFERENCE_TYPE_MANGER);

            $targetModels = [];

//            $needStopList = TArrayHelper::array_minus($displayedNodes,$selectedNodes);

            foreach ($selectedNodes as $key => $value) {
                $treeNodeId = str_replace('"', "", $value);

                // JSTree控件根节点ID=“-1”，此处特殊处理，过滤掉该值
                if ($treeNodeId === '-1') {
                    continue;
                }

                $domianService = new DomainService();
                $domainId = $domianService->getDomainIdByTreeNodeId($treeNodeId);

                $cntManageModel = new FwCntManageRef();
                $cntManageModel->subject_id = $userId;
                $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_USER;
                $cntManageModel->content_id = $domainId;
                $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_DOMAIN;
                $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_MANGER;
                $cntManageModel->status = self::STATUS_FLAG_NORMAL;
                $cntManageModel->start_at = time();

                array_push($targetModels, $cntManageModel);
            }

            $cntManageRefService->batchStartRelationship($targetModels);

            return ['result' => 'success'];
        }
        else
        {
            $treeDataUrl = Url::toRoute(['user-role/domain-tree-data','userId'=>$userId]);
            return $this->renderAjax('//tree-node/multi-select', [
                'formType' => 'user-domain',
                'TreeType' => $treeTypeCode,
                'treeDataUrl' => $treeDataUrl,
                'treeState' => "False",
                'treeFlag' => $treeTypeCode,
                'needRegister' => 'True'
            ]);
        }
    }


    public function actionOrgnization($userId)
    {
        $treeTypeCode = "orgnization";
        $treeFlag = $treeTypeCode;
        $suffix = '';
        if (isset($treeFlag) && $treeFlag != null && $treeFlag != '')
            $suffix = '_' . $treeFlag;

        if (isset($_POST['jsTree_multi-select-tree_changed_result'.$suffix]) && Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $tree_selected_result = Yii::$app->request->post('jsTree_multi-select-tree_changed_result'.$suffix);
//            $tree_displayed_result = Yii::$app->request->post('jsTree_multi-select-tree_displayed_result'.$suffix);

            if ($tree_selected_result != '') {
                $selectedNodes = explode(',', str_replace(']', "", str_replace('[', "", $tree_selected_result)));
            }
            else {
                $selectedNodes = [];
            }

//            if ($tree_displayed_result != '') {
//                $displayedNodes = explode(',', str_replace(']', "", str_replace('[', "", $tree_displayed_result)));
//            }
//            else {
//                $displayedNodes = [];
//            }

            $cntManageRefService = new CntManageRefService();
            $cntManageRefService->stopRelationshipBySubjectIdList($userId,FwCntManageRef::SUBJECT_TYPE_USER,FwCntManageRef::CONTENT_TYPE_ORGNIZATION,FwCntManageRef::REFERENCE_TYPE_MANGER);

            $targetModels = [];

//            $needStopList = TArrayHelper::array_minus($displayedNodes,$selectedNodes);

            foreach ($selectedNodes as $key => $value) {
                $treeNodeId = str_replace('"', "", $value);

                // JSTree控件根节点ID=“-1”，此处特殊处理，过滤掉该值
                if ($treeNodeId === '-1') {
                    continue;
                }

                $orgnizationService = new OrgnizationService();
                $orgnizationId = $orgnizationService->getOrgnizationIdByTreeNodeId($treeNodeId);

                $cntManageModel = new FwCntManageRef();
                $cntManageModel->subject_id = $userId;
                $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_USER;
                $cntManageModel->content_id = $orgnizationId;
                $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_ORGNIZATION;
                $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_MANGER;
                $cntManageModel->status = self::STATUS_FLAG_NORMAL;
                $cntManageModel->start_at = time();

                array_push($targetModels, $cntManageModel);
            }

            $cntManageRefService->batchStartRelationship($targetModels);

            return ['result' => 'success'];
        }
        else
        {
            $treeDataUrl = Url::toRoute(['user-role/orgnization-tree-data','userId'=>$userId]);
            return $this->renderAjax('//tree-node/multi-select', [
                'formType' => 'user-orgnization',
                'TreeType' => $treeTypeCode,
                'treeDataUrl' => $treeDataUrl,
                'treeState' => "False",
                'treeFlag' => $treeTypeCode,
                'needRegister' => 'False'
            ]);
        }
    }


    public function actionCompany($userId)
    {
        $treeTypeCode = "company";
        $treeFlag = $treeTypeCode;
        $suffix = '';
        if (isset($treeFlag) && $treeFlag != null && $treeFlag != '')
            $suffix = '_' . $treeFlag;

//        $this->layout = 'modalWin';
        if (isset($_POST['jsTree_multi-select-tree_changed_result'.$suffix]) && Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $tree_selected_result = Yii::$app->request->post('jsTree_multi-select-tree_changed_result'.$suffix);
//            $tree_displayed_result = Yii::$app->request->post('jsTree_multi-select-tree_displayed_result'.$suffix);

            if ($tree_selected_result != '') {
                $selectedNodes = explode(',', str_replace(']', "", str_replace('[', "", $tree_selected_result)));
            }
            else {
                $selectedNodes = [];
            }

//            if ($tree_displayed_result != '') {
//                $displayedNodes = explode(',', str_replace(']', "", str_replace('[', "", $tree_displayed_result)));
//            }
//            else {
//                $displayedNodes = [];
//            }

            $cntManageRefService = new CntManageRefService();
            $cntManageRefService->stopRelationshipBySubjectIdList($userId,FwCntManageRef::SUBJECT_TYPE_USER,FwCntManageRef::CONTENT_TYPE_COMPANY,FwCntManageRef::REFERENCE_TYPE_MANGER);

            $targetModels = [];

//            $needStopList = TArrayHelper::array_minus($displayedNodes,$selectedNodes);

            foreach ($selectedNodes as $key => $value) {
                $treeNodeId = str_replace('"', "", $value);

                // JSTree控件根节点ID=“-1”，此处特殊处理，过滤掉该值
                if ($treeNodeId === '-1') {
                    continue;
                }

                $companyService = new CompanyService();
                $companyId = $companyService->getCompanyIdByTreeNodeId($treeNodeId);

                $cntManageModel = new FwCntManageRef();
                $cntManageModel->subject_id = $userId;
                $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_USER;
                $cntManageModel->content_id = $companyId;
                $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_COMPANY;
                $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_MANGER;
                $cntManageModel->status = self::STATUS_FLAG_NORMAL;
                $cntManageModel->start_at = time();
                array_push($targetModels, $cntManageModel);
            }

            $cntManageRefService->batchStartRelationship($targetModels);

            return ['result' => 'success'];
        }
        else
        {
            $treeDataUrl = Url::toRoute(['user-role/company-tree-data','userId'=>$userId]);
            return $this->renderAjax('//tree-node/multi-select', [
                'formType' => 'user-company',
                'TreeType' => $treeTypeCode,
                'treeDataUrl' => $treeDataUrl,
                'treeState' => "False",
                'treeFlag' => $treeTypeCode,
                'needRegister' => 'False'
            ]);
        }
    }

    public function actionCompanyTreeData($userId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $service = new TreeNodeService();

        $otherService = UserCompanyService::className();//可根据需要换所需的服务

        $otherKid = $userId;

        $result = $service->listTreeData($otherService, $otherKid, true, false);

        return $result;
    }

    public function actionDomainTreeData($userId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $service = new TreeNodeService();

        $otherService = UserDomainService::className();//可根据需要换所需的服务

        $otherKid = $userId;

        $result = $service->listTreeData($otherService, $otherKid, true, false);

        return $result;
    }

    public function actionOrgnizationTreeData($userId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $service = new TreeNodeService();

        $otherService = UserOrgnizationService::className();//可根据需要换所需的服务

        $otherKid = $userId;

        $result = $service->listTreeData($otherService, $otherKid, true, false);

        return $result;
    }

    public function actionCompanyView($userId)
    {
        $treeTypeCode = "company";

        $treeDataUrl = Url::toRoute(['user-role/company-tree-data','userId'=>$userId]);
        return $this->renderAjax('//tree-node/multi-select', [
            'formType' => 'user-company',
            'TreeType' => $treeTypeCode,
            'treeDataUrl' => $treeDataUrl,
            'treeState' => "False",
            'treeFlag' => $treeTypeCode,
            'needRegister' => 'False'
        ]);
    }

    public function actionOrgnizationView($userId)
    {
        $treeTypeCode = "orgnization";

        $treeDataUrl = Url::toRoute(['user-role/orgnization-tree-data','userId'=>$userId]);
        return $this->renderAjax('//tree-node/multi-select', [
            'formType' => 'user-orgnization',
            'TreeType' => $treeTypeCode,
            'treeDataUrl' => $treeDataUrl,
            'treeState' => "False",
            'treeFlag' => $treeTypeCode,
            'needRegister' => 'False'
        ]);
    }


    public function actionDomainView($userId)
    {
        $treeTypeCode = "domain";

        $treeDataUrl = Url::toRoute(['user-role/domain-tree-data','userId'=>$userId]);
        return $this->renderAjax('//tree-node/multi-select', [
            'formType' => 'user-domain',
            'TreeType' => $treeTypeCode,
            'treeDataUrl' => $treeDataUrl,
            'treeState' => "False",
            'treeFlag' => $treeTypeCode,
            'needRegister' => 'False'
        ]);
    }
}