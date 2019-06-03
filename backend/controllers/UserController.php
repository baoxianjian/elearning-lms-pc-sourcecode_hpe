<?php
/**
 * Created by PhpStorm.
 * FwUser: TangMingQiang
 * Date: 2/22/15
 * Time: 10:49 PM
 */

namespace backend\controllers;

use backend\services\CompanyService;
use backend\services\DomainService;
use common\services\framework\UserRoleService;
use common\models\framework\FwCompany;
use common\services\framework\ExternalSystemService;
use common\services\framework\RbacService;
use common\base\BaseActiveRecord;
use common\helpers\TExportHelper;
use components\widgets\TPagination;
use Yii;
use backend\services\OrgnizationService;
use backend\services\PositionService;
use backend\services\CntManageRefService;
use common\services\framework\TreeNodeService;
use backend\services\UserPositionService;
use common\models\framework\FwOrgnization;
use common\models\treemanager\FwCntManageRef;
use common\services\framework\DictionaryService;
use common\models\framework\FwDictionaryCategory;
use backend\base\BaseBackController;
use backend\services\UserService;
use common\models\framework\FwUser;
use yii\base\Exception;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class UserController extends BaseBackController{

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
//            $treeTypeCode = "orgnization";
//            $treeTypeId = $treeNodeService->getTreeTypeId($treeTypeCode);
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


    /**
     * Displays a single FwUser model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
//        $this->layout = 'modalWin';
        return $this->renderAjax('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new FwUser model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $domainService = new DomainService();
        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");
//        $parentNodeId = Yii::$app->request->getQueryParam("parentNodeId");
//        $this->layout = 'modalWin';
        $model = new FwUser();
        $model->setScenario("manage");

        if ($treeNodeKid == '')
            $orgnizationId = null;
        else
        {
            $orgnizationService = new OrgnizationService();
            $orgnizationId = $orgnizationService->getOrgnizationIdByTreeNodeId($treeNodeKid);

            $orgnizationModel = FwOrgnization::findOne($orgnizationId);

            $model->orgnization_id = $orgnizationId;
            $model->company_id = $orgnizationModel->company_id;
//                $model->domain_id = $orgnizationModel->domain_id;
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->domain_id == null || $model->domain_id == "") {
                return ['result' => 'other', 'message' => Yii::t('common', 'can_not_empty_{value}',
                    ['value' => Yii::t('common','relate_{value}',['value'=>Yii::t('common','domain')])])];
            }

            if ($model->validate()) {
//                $model->password_origin = $model->password_hash;
                $model->setPassword($model->password_hash);
                $model->setAuthToken();
                $model->generateAuthKey();
                $dictionaryService = new DictionaryService();

                $defaultTimezone = $dictionaryService->getDictionaryValueByCode("system","default_timezone");

                if (!empty($model->company_id)) {
                    $companyModel = FwCompany::findOne($model->company_id);
                    if (!empty($companyModel) && !empty($companyModel->theme)) {
                        $defaultTheme = $companyModel->theme;
                        $defaultLanguage = $companyModel->language;
                    }
                }
                if (empty($defaultTheme)) {
                    $defaultTheme = $dictionaryService->getDictionaryValueByCode("system", "default_theme");
                }

                if (empty($defaultLanguage)) {
                    $defaultLanguage = $dictionaryService->getDictionaryValueByCode("system","default_language");
                }

                $model->language = $defaultLanguage;
                $model->timezone = $defaultTimezone;
                $model->theme = $defaultTheme;
                $model->last_pwd_change_at = time();

                $isForceChangePassword = $dictionaryService->getDictionaryValueByCode("system","is_force_change_password");
                if ($isForceChangePassword == null) {
                    $isForceChangePassword = FwUser::NEED_PWD_CHANGE_YES;;
                }

                $model->need_pwd_change = $isForceChangePassword;
                $model->status = FwUser::STATUS_FLAG_NORMAL;

                $validStartAt = $model->valid_start_at;
                if (!empty($validStartAt)) {
                    $model->valid_start_at = strtotime($model->valid_start_at . " 00:00:00");
                }

                $validEndAt = $model->valid_end_at;
                if (!empty($validEndAt)) {
                    $model->valid_end_at = strtotime($model->valid_end_at . " 23:59:59");
                }

                $limitedUserNumber = 0;
                if (!empty($companyModel)) {
                    $limitedUserNumber = $companyModel->limited_user_number;
                }
                $userService = new UserService();
                if ($limitedUserNumber != 0) {
                    $activeUserNumber = $userService->getCompanyUserCount($companyModel->kid);

                    if ($activeUserNumber >= $limitedUserNumber) {
                        return ['result' => 'other', 'message' => Yii::t('common', "active_user_exceed_{number}", ["number" => $limitedUserNumber])];
                    }
                }
                $model->needReturnKey = true;
//                $model->password_repeat = null;
//                $model->email_repeat = null;
                if (!empty($validStartAt) && !empty($validEndAt) && $validEndAt < $validStartAt) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'valid_end_at_cannot_small_than_start_at')];
                }
                else if ($userService->isExistSameUserName($model->kid, $model->user_name)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'user_name')])];
                }
                else if (!empty($model->email) && $userService->isExistSameEmail($model->kid, $model->email)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'email')])];
                } else if ($model->save()) {
                    $userId = $model->kid;
                    $rbacService = new RbacService();
                    $userRoleService = new UserRoleService();
                    $teamManagerRoleId = $rbacService->getRoleId("Team-Manager");
                    $studentRoleId = $rbacService->getRoleId("Student");
                    if ($model->manager_flag == FwUser::MANAGER_FLAG_YES) {
                        $userRoleService->startRelationship($userId, $teamManagerRoleId);
                    }
                    else {
                        $userRoleService->stopRelationshipByUserRoleId($userId, $teamManagerRoleId);
                    }

                    //自动增加学员角色
                    $userRoleService->startRelationship($userId, $studentRoleId);
                    
//                    if ($treeNodeKid != '-1') {
//                        $cntManageModel = new FwCntManageRef();
//                        $cntManageModel->subject_id = $treeNodeKid;
//                        $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_TREE;
//                        $cntManageModel->content_id = $userId;
//                        $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_USER;
//                        $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_BELONG;
//
//                        $cntManageRefService = new CntManageRefService();
//
//                        $cntManageRefService->startRelationship($cntManageModel);
//                    }


                    return ['result' => 'success', 'kid' => $model->kid];
                } else {
                    return ['result' => 'failure'];
                }
            }
            else {
                return ['result' => 'failure'];
            }
//            return $this->redirect(['view', 'id' => $model->kid]);
        } else {
            $orgnizationModel = FwOrgnization::findOne($orgnizationId);
            $dictionaryService = new DictionaryService();
            $genderModel = $dictionaryService->getDictionariesByCategory('gender');
            $model->manager_flag = FwUser::MANAGER_FLAG_NO;
            $model->domain_id = $orgnizationModel->domain_id;
//                 $lptpModel =  $lptpService->Search($lptID)->models;
            $domainModel = $domainService->getExclusivedDomainListByCompanyId($model->company_id);

            $locationModel = $dictionaryService->getDictionariesByCategory('location');
            $themeModel = $dictionaryService->getDictionariesByCategory('theme');
            $languageModel = $dictionaryService->getDictionariesByCategory('language');
            $timezoneModel = $dictionaryService->getDictionariesByCategory('timezone');

            $employeeStatusModel = $dictionaryService->getDictionariesByCategory('employee_status',$model->company_id);
            $workPlaceModel = $dictionaryService->getDictionariesByCategory('work_place',$model->company_id);
            $positionMgrLevelModel = $dictionaryService->getDictionariesByCategory('position_mgr_level',$model->company_id);

            $companyModel = FwCompany::findOne($model->company_id);
            if (!empty($companyModel) && !empty($companyModel->theme)) {
                $model->theme = $companyModel->theme;
            }

            return $this->renderAjax('create', [
                'model' => $model,
                'domainModel' => $domainModel,
                'genderModel'=>$genderModel,
                'locationModel'=>$locationModel,
                'themeModel'=>$themeModel,
                'languageModel'=>$languageModel,
                'timezoneModel'=>$timezoneModel,
                'employeeStatusModel'=>$employeeStatusModel,
                'workPlaceModel'=>$workPlaceModel,
                'positionMgrLevelModel'=>$positionMgrLevelModel,
                'treeNodeKid'=>$treeNodeKid,
            ]);
        }
    }


    /**
     * Updates an existing FwUser model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $domainService = new DomainService();
//        $this->layout = 'modalWin';
        $model = $this->findModel($id);
        $model->setScenario("update");

        $oldPasswordHash =  $model->password_hash;
        $oldEmail =  $model->email;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->domain_id == null || $model->domain_id == "") {
                return ['result' => 'other', 'message' => Yii::t('common', 'can_not_empty_{value}',
                    ['value' => Yii::t('common','relate_{value}',['value'=>Yii::t('common','domain')])])];
            }

            if ($model->validate()) {
                if ($model->password_hash == '')
                    $model->password_hash = $oldPasswordHash;
                else {
                    if ($model->password_hash != $model->password_repeat)
                    {
                        return ['result' => 'other', 'message' => Yii::t('common', 'password_repeat_error')];
                    }

                    $model->setPassword($model->password_hash);
                    $model->last_pwd_change_at = time();

                    $dictionaryService = new DictionaryService();
                    $isForceChangePassword = $dictionaryService->getDictionaryValueByCode("system","is_force_change_password");
                    if ($isForceChangePassword == null) {
                        $isForceChangePassword = FwUser::NEED_PWD_CHANGE_YES;;
                    }
                    $model->need_pwd_change = $isForceChangePassword;
                }

                if ($oldEmail != $model->email && $model->email != $model->email_repeat)
                {
                    return ['result' => 'other', 'message' => Yii::t('common', 'email_repeat_error')];
                }

                $validStartAt = $model->valid_start_at;
                if (!empty($validStartAt)) {
                    $model->valid_start_at = strtotime($model->valid_start_at . " 00:00:00");
                }

                $validEndAt = $model->valid_end_at;
                if (!empty($validEndAt)) {
                    $model->valid_end_at = strtotime($model->valid_end_at . " 23:59:59");
                }

                $userService = new UserService();
                if (!empty($validStartAt) && !empty($validEndAt) && $validEndAt < $validStartAt) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'valid_end_at_cannot_small_than_start_at')];
                }
                else if ($userService->isExistSameUserName($model->kid, $model->user_name)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'user_name')])];
                }
                else if (!empty($model->email) && $userService->isExistSameEmail($model->kid, $model->email)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'email')])];
                } else if ($model->save()) {
                    $rbacService = new RbacService();
                    $userRoleService = new UserRoleService();
                    $teamManagerRoleId = $rbacService->getRoleId("Team-Manager");
                    
                    if ($model->manager_flag == FwUser::MANAGER_FLAG_YES) {
                        $userRoleService->startRelationship($id, $teamManagerRoleId);
                    }
                    else {
                        $userRoleService->stopRelationshipByUserRoleId($id, $teamManagerRoleId);
                    }
                    
                    return ['result' => 'success'];
                } else {
                    return ['result' => 'failure'];
                }
            }
            else {
                return ['result' => 'failure'];
            }
//            return $this->redirect(['view', 'id' => $model->kid]);
        } else {
            $dictionaryService = new DictionaryService();
            $genderModel = $dictionaryService->getDictionariesByCategory('gender');

            $model->password_hash = null;
            $model->password_repeat = null;
            $model->email_repeat = $model->email;

            if (!empty($model->valid_start_at)) {
                $model->valid_start_at = date('Y-m-d', $model->valid_start_at);
            }
            if (!empty($model->valid_end_at)) {
                $model->valid_end_at = date('Y-m-d', $model->valid_end_at);
            }

            $domainModel = $domainService->getExclusivedDomainListByCompanyId($model->company_id);

            $locationModel = $dictionaryService->getDictionariesByCategory('location');
            $themeModel = $dictionaryService->getDictionariesByCategory('theme');
            $languageModel = $dictionaryService->getDictionariesByCategory('language');
            $timezoneModel = $dictionaryService->getDictionariesByCategory('timezone');

            $employeeStatusModel = $dictionaryService->getDictionariesByCategory('employee_status',$model->company_id);
            $workPlaceModel = $dictionaryService->getDictionariesByCategory('work_place',$model->company_id);
            $positionMgrLevelModel = $dictionaryService->getDictionariesByCategory('position_mgr_level',$model->company_id);

            return $this->renderAjax('update', [
                'model' => $model,
                'genderModel'=>$genderModel,
                'locationModel'=>$locationModel,
                'themeModel'=>$themeModel,
                'languageModel'=>$languageModel,
                'timezoneModel'=>$timezoneModel,
                'employeeStatusModel'=>$employeeStatusModel,
                'workPlaceModel'=>$workPlaceModel,
                'positionMgrLevelModel'=>$positionMgrLevelModel,
                'domainModel' => $domainModel
            ]);
        }

    }

    /**
     * Deletes an existing FwUser model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        try {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
//                $model = $this->findModel($id);
                $model = new FwUser();

//                if (isset($model) && $model != null) {

                $kids = "'" . $id . "'";

                FwUser::removeFromCacheByKid($id);

//                $cntManageRefService = new CntManageRefService();
//                $cntManageRefService->stopRelationshipByContentIdList($kids, FwCntManageRef::CONTENT_TYPE_USER);

                $userPositionService = new UserPositionService();
                $userPositionService->stopRelationshipByUserId($kids);

                $userRoleService = new UserRoleService();
                $userRoleService->stopRelationshipByUserId($kids);

                $model->deleteAllByKid($kids);

                $externalSystemService = new ExternalSystemService();
                $externalSystemService->deleteUserInfoByUserId($id);

//                    $model->delete();

                return ['result' => 'success'];
//                }
//                else
//                {
//                    return ['result' => 'failure'];
//                }
            } else {
                return ['result' => 'failure'];
            }
        } catch (Exception $ex) {
            return ['result' => 'failure'];
        }
    }

    /**
     * Finds the FwUser model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwUser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FwUser::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common','data_not_exist'));
        }
    }


    public function actionBatchDelete()
    {
        if (isset($_POST['datalist']) && Yii::$app->request->isAjax) {
            $keys = $_POST['datalist'];
            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
                $model = new FwUser();

                $kids = "";
                foreach ($keys as $key)
                {
                    $kids = $kids . "'" . $key . "',";

                    FwUser::removeFromCacheByKid($key);
                }

                $kids = rtrim($kids,",");

//                $cntManageRefService = new CntManageRefService();
//                $cntManageRefService->stopRelationshipByContentIdList($kids,FwCntManageRef::CONTENT_TYPE_USER);

                $userPositionService = new UserPositionService();
                $userPositionService->stopRelationshipByUserIdList($kids);

                $userRoleService = new UserRoleService();
                $userRoleService->stopRelationshipByUserIdList($kids);

                $externalSystemService = new ExternalSystemService();
                $externalSystemService->deleteUserInfoByUserIdList($kids);

                $model->deleteAllByKid($kids);


                return ['result'=>'success'];
            }
            catch (Exception $ex) {
                return ['result' => 'failure'];
            }
        }
        else
        {
            return $this->redirect(['index']);
        }
    }

    public function actionStatus($id, $status)
    {
        try {
            if ( Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                if (strstr($id,','))
                {
                    $keys = explode(",", $id);
                }
                else {
                    $keys[] = $id;
                }

                $kids = "";
                foreach ($keys as $key)
                {
                    $kids = $kids . "'" . $key . "',";

                    FwUser::removeFromCacheByKid($key);
                }

                $kids = rtrim($kids,",");


                $userService = new UserService();
                $userService->changeStatusByUserIdList($kids,$status);

                return ['result' => 'success'];
            }
            else
            {
                return ['result' => 'failure'];
            }
        }
        catch (Exception $ex) {
            return ['result' => 'failure'];
        }
    }

    public function actionResetPass($id)
    {
        try {
            if ( Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                if (strstr($id,','))
                {
                    $keys = explode(",", $id);
                }
                else {
                    $keys[] = $id;
                }

                $dictionaryService = new DictionaryService();
                $isForceChangePassword = $dictionaryService->getDictionaryValueByCode("system","is_force_change_password");
                if ($isForceChangePassword == null) {
                    $isForceChangePassword = FwUser::NEED_PWD_CHANGE_YES;;
                }

                $kids = "";
                foreach ($keys as $key)
                {
                    $kids = $kids . "'" . $key . "',";

                    FwUser::removeFromCacheByKid($key);
                }

                $kids = rtrim($kids,",");

                $dictionaryService = new DictionaryService();
                $defaultPass = $dictionaryService->getDictionaryValueByCode("system","default_password");
                $userService = new UserService();
                $userService->resetPasswordByUserIdList($kids,$defaultPass,$isForceChangePassword);

                return ['result' => 'success'];
            }
            else
            {
                return ['result' => 'failure'];
            }
        }
        catch (Exception $ex) {
            return ['result' => 'failure'];
        }
    }



    public function actionMove($id)
    {
//        $this->layout = 'modalWin';
        if (isset($_POST['jsTree_select-tree_selected_result']) && Yii::$app->request->isAjax) {
            $tree_selected_result = Yii::$app->request->post('jsTree_select-tree_selected_result');
            $targetTreeNodeId = str_replace('"]',"",str_replace('["',"",$tree_selected_result));
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($targetTreeNodeId == '-1')
                $targetTreeNodeId = '';

            if (strstr($id,','))
            {
                $keyList = explode(",", $id);
            }
            else {
                $keyList[] = $id;
            }

//            $checkPass = false;

            $orgnizationId = null;
            $companyId = null;
            $domainId = null;
//            $sourceTreeNodeId = '';

            if ($targetTreeNodeId == '') {
                return ['result' => 'other', 'message' => Yii::t('common', 'cannot_move_in_root')];
            }
            else {
                $kids = "";
                $batchModel = [];

                $orgnizationService = new OrgnizationService();
                $orgnizationId = $orgnizationService->getOrgnizationIdByTreeNodeId($targetTreeNodeId);

                $orgnizationModel = FwOrgnization::findOne($orgnizationId);
                if ($orgnizationModel != null) {
                    $companyId = $orgnizationModel->company_id;
                    $domainId = $orgnizationModel->domain_id;
                }

                if (!empty($keyList) && count($keyList) > 0) {
                    foreach ($keyList as $key) {
                        $kids = $kids . "'" . $key . "',";

                        FwUser::removeFromCacheByKid($key);

//                        $cntManageModel = new FwCntManageRef();
//                        $cntManageModel->subject_id = $targetTreeNodeId;
//                        $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_TREE;
//                        $cntManageModel->content_id = $key;
//                        $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_USER;
//                        $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_BELONG;
//                        $cntManageModel->status = self::STATUS_FLAG_NORMAL;
//                        $cntManageModel->start_at = time();
//
//                        array_push($batchModel, $cntManageModel);
                    }

                    $kids = rtrim($kids, ",");

                    $userService = new UserService();
                    $userService->moveDataByKidList($kids, $companyId, $orgnizationId, $domainId);



//                    $cntManageRefService = new CntManageRefService();
//                    //先停用所有旧的关系
//                    $cntManageRefService->stopRelationshipByContentIdList($kids, FwCntManageRef::CONTENT_TYPE_USER, FwCntManageRef::REFERENCE_TYPE_BELONG,FwCntManageRef::SUBJECT_TYPE_TREE);
//
//                    //再添加关系
//                    $cntManageRefService->batchStartRelationship($batchModel);
                }

            }

            return ['result' => 'success'];
//            return ['result'=>'failure'];
        }
        else
        {
            $treeTypeCode = "orgnization";

            if (strstr($id,','))
            {
                $id =  substr($id,0,strpos($id,','));
            }
            $model = $this->findModel($id);

            if ($model->orgnization_id != '') {
                $sourceTreeNodeId = FwOrgnization::findOne($model->orgnization_id)->tree_node_id;
            }
            else
            {
                $sourceTreeNodeId = '';
            }

            return $this->renderAjax('//tree-node/select', [
                'sourceTreeNodeId' => $sourceTreeNodeId,
                'formType' => 'move',
                'TreeType' => $treeTypeCode
            ]);
        }
    }


    public function actionExport(){

        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");
        $includeSubNode = Yii::$app->request->getQueryParam("includeSubNode");

        if ($treeNodeKid == '-1')
            $treeNodeKid = '';


        $service = new UserService();
        $managerFlag = null;
        $results = $service->search(Yii::$app->request->queryParams,$managerFlag,$treeNodeKid,$includeSubNode)->query->all();

        $split = ",";
        $header = Yii::t('common','user_name') . $split . Yii::t('common','real_name'). $split . Yii::t('common','nick_name')
            . $split . Yii::t('common','gender'). $split . Yii::t('common','email'). $split . Yii::t('common','birthday')
            . $split . Yii::t('common','mobile_no'). $split . Yii::t('common','home_phone_no')
            . $split . Yii::t('common','relate_{value}',['value'=>Yii::t('common','company')]). $split . Yii::t('common','relate_{value}',['value'=>Yii::t('common','orgnization')])
            . $split . Yii::t('common','relate_{value}',['value'=>Yii::t('common','domain')])
            . $split . Yii::t('common','reporting_manager'). $split . Yii::t('common','manager_flag') . $split . Yii::t('common','status');
        $data = array();
        $i = 0;

        foreach ($results as $r) {
            $data[$i][0] = $r->user_name;
            $data[$i][1] = $r->real_name;
            $data[$i][2] = $r->nick_name;
            $data[$i][3] = $r->gender;
            $data[$i][4] = $r->email;
            $data[$i][5] = $r->birthday;
            $data[$i][6] = $r->mobile_no;
            $data[$i][7] = $r->home_phone_no;
            $data[$i][8] = $r->getCompanyName();
            $data[$i][9] = $r->getOrgnizationName();
            $data[$i][10] = $r->getDomainName();
            $data[$i][11] = $r->getReportingManagerName();
            $data[$i][12] = $r->getManagerFlagText();
            $data[$i][13] = $r->getStatusText();

            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}