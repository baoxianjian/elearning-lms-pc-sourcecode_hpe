<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/9/15
 * Time: 11:31 AM
 */

namespace backend\controllers;

use backend\services\CompanyService;
use backend\services\DomainService;
use backend\services\OrgnizationService;
use backend\services\PermissionService;
use common\models\framework\FwDomain;
use common\models\framework\FwOrgnization;
use common\models\framework\FwPermission;
use common\models\framework\FwPrimaryKey;
use common\models\learning\LnCourseCategory;
use common\models\learning\LnCoursewareCategory;
use common\models\treemanager\FwTreeNode;
use common\models\treemanager\FwTreeType;
use common\services\learning\CourseCategoryService;
use common\services\learning\CoursewareCategoryService;
use common\services\framework\RbacService;
use common\helpers\TExportHelper;
use components\widgets\TPagination;
use backend\base\BaseBackController;
use common\services\framework\TreeNodeService;
use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TreeNodeController extends BaseBackController{

    public $layout  = 'frame';

    public function actionIndex()
    {
        $treeType = Yii::$app->request->getQueryParam("TreeType");

        if (isset($treeType)) {
            $service = new TreeNodeService();
            $treeTypeId = $service->getTreeTypeId($treeType);
            if ($treeTypeId != "") {
                $treeTypeModel = FwTreeType::findOne($treeTypeId);
                $TreeTypeName = $treeTypeModel->getI18nName();
                $TreeTypeCode = $treeTypeModel->tree_type_code;
                return $this->render('index', [
                    'TreeType' => $treeType,
                    'TreeTypeName' => $TreeTypeName,
                    'TreeTypeCode' => $TreeTypeCode
                ]);
            }
            else {
                throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
            }
        }else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }


    public function actionPermission()
    {
        $treeType = $this->action->id;

        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        if ($treeTypeId != "") {
            $treeTypeModel = FwTreeType::findOne($treeTypeId);
            $TreeTypeName = $treeTypeModel->getI18nName();
            $TreeTypeCode = $treeTypeModel->tree_type_code;
            return $this->render('index', [
                'TreeType' => $treeType,
                'TreeTypeName' => $TreeTypeName,
                'TreeTypeCode' => $TreeTypeCode
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }

    public function actionCompany()
    {
        $treeType = $this->action->id;

        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        if ($treeTypeId != "") {
            $treeTypeModel = FwTreeType::findOne($treeTypeId);
            $TreeTypeName = $treeTypeModel->getI18nName();
            $TreeTypeCode = $treeTypeModel->tree_type_code;
            return $this->render('index', [
                'TreeType' => $treeType,
                'TreeTypeName' => $TreeTypeName,
                'TreeTypeCode' => $TreeTypeCode
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }

    public function actionDomain()
    {
        $treeType = $this->action->id;

        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        if ($treeTypeId != "") {
            $treeTypeModel = FwTreeType::findOne($treeTypeId);
            $TreeTypeName = $treeTypeModel->getI18nName();
            $TreeTypeCode = $treeTypeModel->tree_type_code;
            return $this->render('index', [
                'TreeType' => $treeType,
                'TreeTypeName' => $TreeTypeName,
                'TreeTypeCode' => $TreeTypeCode
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }


    public function actionCourseCategory()
    {
        $treeType = $this->action->id;

        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);

        if ($treeTypeId != "") {
            $treeTypeModel = FwTreeType::findOne($treeTypeId);
            $TreeTypeName = $treeTypeModel->getI18nName();
            $TreeTypeCode = $treeTypeModel->tree_type_code;
            return $this->render('index', [
                'TreeType' => $treeType,
                'TreeTypeName' => $TreeTypeName,
                'TreeTypeCode' => $TreeTypeCode
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }


    public function actionCoursewareCategory()
    {
        $treeType = $this->action->id;

        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);

        if ($treeTypeId != "") {
            $treeTypeModel = FwTreeType::findOne($treeTypeId);
            $TreeTypeName = $treeTypeModel->getI18nName();
            $TreeTypeCode = $treeTypeModel->tree_type_code;
            return $this->render('index', [
                'TreeType' => $treeType,
                'TreeTypeName' => $TreeTypeName,
                'TreeTypeCode' => $TreeTypeCode
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }

    public function actionOrgnization()
    {
        $treeType = $this->action->id;

        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        if ($treeTypeId != "") {
            $treeTypeModel = FwTreeType::findOne($treeTypeId);
            $TreeTypeName = $treeTypeModel->getI18nName();
            $TreeTypeCode = $treeTypeModel->tree_type_code;
            return $this->render('index', [
                'TreeType' => $treeType,
                'TreeTypeName' => $TreeTypeName,
                'TreeTypeCode' => $TreeTypeCode
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }


    public function actionTree()
    {
        $treeType = Yii::$app->request->getQueryParam("TreeType");
        $contentName = Yii::$app->request->getQueryParam("ContentName");
        $ListRoute = Yii::$app->request->getQueryParam("ListRoute");
        $includeRoot = Yii::$app->request->getQueryParam("IncludeRoot");
        $mergeRoot = Yii::$app->request->getQueryParam("MergeRoot");
        $showContentCount = Yii::$app->request->getQueryParam("ShowContentCount");
        $openAllNode = Yii::$app->request->getQueryParam("OpenAllNode");
        $DeleteNode = Yii::$app->request->getQueryParam("DeleteNode");
        $EditNode = Yii::$app->request->getQueryParam("EditNode");
        $ListRouteParams = Yii::$app->request->getQueryParam("ListRouteParams");

        if (empty($ListRoute))
            $ListRoute = $contentName.'/list';
//        $this->layout = 'left';
        return $this->renderAjax('tree',[
            'TreeType' => $treeType,
            'ContentName' => $contentName,
            'ListRoute' => $ListRoute,
            'needRegister'=> 'True',
            'includeRoot'=> $includeRoot,
            'mergeRoot'=> $mergeRoot,
            'showContentCount' => $showContentCount,
            'openAllNode' => $openAllNode,
            'DeleteNode' => $DeleteNode,
            'EditNode' => $EditNode,
            'ListRouteParams' => $ListRouteParams,
        ]);
    }

    public function actionList()
    {
        $this->layout = 'list';

        $service = new TreeNodeService();

        $treeType = Yii::$app->request->getQueryParam("TreeType");
        $parentNodeId = Yii::$app->request->getQueryParam("TreeNodeKid");
        $treeTypeId = $service->getTreeTypeId($treeType);
//        $paramArray = Yii::$app->request->getQueryParam("TreeNodeService");
//        if (isset($paramArray) && $paramArray != null)
//            $status = $paramArray['status'];
//        else
//            $status = null;

        $includeSubNode = Yii::$app->request->getQueryParam("includeSubNode");


        if ($parentNodeId == '-1')
            $parentNodeId = '';

//        if ($includeSubNode == '1') {
//            if ($parentNodeId != '') {
//                $treeNodeModel = FwTreeNode::findOne($parentNodeId);
//
//                $treeNodeIdList = $service->getAllSubNodeId($parentNodeId, $treeTypeId, $treeNodeModel->node_id_path . $parentNodeId . "/%", $status);
//            } else {
//                $treeNodeIdList = $service->getAllSubNodeId('', $treeTypeId, "/%", $status);
//            }
//        }
//        else
//        {
//            $treeNodeIdList = $parentNodeId;
//        }


        $treeTypeModel = FwTreeType::findOne($treeTypeId);

        $forceShowAll = 'False';
        $pageSize = $this->defaultPageSize;

        if (Yii::$app->request->getQueryParam('PageShowAll') != null && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
            $forceShowAll = 'True';
        }
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }

        if ($treeTypeModel != null) {

            $dataProvider = $service->search(Yii::$app->request->queryParams, $treeType, $parentNodeId, $includeSubNode);
            $count = $dataProvider->totalCount;
            $page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
            $dataProvider->setPagination($page);

            $TreeTypeName = $treeTypeModel->getI18nName();
            $TreeTypeCode = $treeTypeModel->tree_type_code;

            return $this->render('list', [
                'page' => $page,
                'searchModel' => $service,
                'dataProvider' => $dataProvider,
                'treeType' => $treeType,
                'treeTypeId' => $treeTypeId,
                'parentNodeId' => $parentNodeId,
                'limitation' => $treeTypeModel->limitation,
                'TreeTypeName' => $TreeTypeName,
                'TreeTypeCode' => $TreeTypeCode,
                'forceShowAll' => $forceShowAll,
                'includeSubNode' => $includeSubNode,
                'pageSize' => $pageSize
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'data_not_exist'));
        }
    }


    public function actionTreeData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $service = new TreeNodeService();
        $result = $service->listTreeData(null,null,true,false);//管理页面不能使用Session数据

        return $result;
    }


    public function actionCreate()
    {
        $this->layout = 'modalWin';
        $model = new FwTreeNode();
        $model->setScenario("manage");

        $service = new TreeNodeService();

        $treeTypeId = Yii::$app->request->getQueryParam("treeTypeId");
        $parentNodeId = Yii::$app->request->getQueryParam("parentNodeId");

        if ($parentNodeId == "")
            $parentNodeId = "-1";

        $model->sequence_number = $service->findMaxSequenceNumber($treeTypeId,$parentNodeId);
        $model->tree_type_id =$treeTypeId;

        $model->status = self::STATUS_FLAG_NORMAL;
        $treeTypeModel = FwTreeType::findOne($treeTypeId);


        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->sequence_number == null)
                $model->sequence_number = $service->findMaxSequenceNumber($treeTypeId,$parentNodeId);

            if ($model->validate()) {

//                $treeNodeId = Yii::$app->request->getQueryParam("treeNodeId");

//                $model->kid = $treeNodeId;


                if ($service->isExistSameTreeNodeCode($model->kid, $model->tree_node_code,$model->tree_type_id)) {
                    $value = Yii::t('common', '{value}_tree_node_code',['value'=>$treeTypeModel->getI18nName()]);
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => $value])];
                } else  {
                    if ($parentNodeId == '-1') {

                        if ($model->kid == null) {
                            $genkey = FwPrimaryKey::generateNextPrimaryID($model->tableName());
                            if ($genkey != null)
                                $model->kid = $genkey;
                        }
//                        $model->parent_node_id = '';
                        $model->root_node_id = $model->kid;
                        $model->tree_level = 1;
                        $model->node_id_path = "/";
                        $model->node_name_path = "/";
                        $model->node_code_path = "/";
                    }
                    else
                    {
                        $model->parent_node_id = $parentNodeId;

                        $parentTreeModel = $this->findModel($model->parent_node_id);

                        if ($treeTypeModel->max_level != null && $treeTypeModel->max_level != 0 &&  $parentTreeModel->tree_level >= $treeTypeModel->max_level)
                        {
                            return ['result' => 'other', 'message' => Yii::t('common', 'reach_max_level')];
                        }
                        else {
                            $model->root_node_id = $parentTreeModel->root_node_id;
                            $model->tree_level = $parentTreeModel->tree_level + 1;
                            $model->node_id_path = $parentTreeModel->node_id_path . $parentTreeModel->kid . "/";
                            $model->node_name_path = $parentTreeModel->node_name_path . $parentTreeModel->tree_node_name . "/";
                            $model->node_code_path = $parentTreeModel->node_code_path . $parentTreeModel->tree_node_code . "/";
                        }
                    }
                    $model->needReturnKey = true;
                    if ($model->save()) {
                        $oldSequenceNumber = $service->findMaxSequenceNumber($treeTypeId,$parentNodeId);
                        $newSequenceNumber = $model->sequence_number;
                        if ($oldSequenceNumber != $newSequenceNumber) {
                            $service->updateSequenceNumber($model->kid,$treeTypeId,$parentNodeId, $oldSequenceNumber, $newSequenceNumber, "0");
                        }

                        return ['result' => 'success', 'kid' => $model->kid];
                    } else {
                        return ['result' => 'failure'];
                    }
                }
            }
            else {
                return ['result' => 'failure'];
//                return ActiveForm::validate($model);
            }
//            return $this->redirect(['view', 'id' => $model->kid]);
        } else {

            if ($treeTypeModel->code_gen_way == FwTreeType::CODE_GEN_WAY_SYSTEM) {

                if ($parentNodeId <> "-1") {
                    $parentCode = $this->findModel($parentNodeId)->tree_node_code;
                }

                if (!isset($parentCode))
                    $parentCode = $treeTypeModel->code_prefix;

                $model->tree_node_code = $service->findMaxNodeCode($treeTypeId,$parentNodeId,$parentCode);
            }

            if ($parentNodeId <> "-1") {
                $model->parent_node_id = $parentNodeId;
            }

            $rbacService = new RbacService();
            $userId = Yii::$app->user->getId();
            $isSpecialUser = $rbacService->isSpecialUser($userId);

            return $this->render('create', [
                'model' => $model,
//                'limitation' => $treeTypeModel->limitation,
//                'codeGenWay' => $treeTypeModel->code_gen_way,
                'isSpecialUser' => $isSpecialUser,
                'formType' => 'create',
                'parentNodeId'=>$parentNodeId
            ]);
        }
    }

    /**
     * Displays a single TreeNode model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
//        $this->layout = 'modalWin';

        $rbacService = new RbacService();
        $userId = Yii::$app->user->getId();
        $isSpecialUser = $rbacService->isSpecialUser($userId);
        return $this->renderAjax('view', [
            'model' => $this->findModel($id),
            'isSpecialUser' => $isSpecialUser,
        ]);
    }

    public function actionUpdate($id)
    {
//        $this->layout = 'modalWin';
        $model = $this->findModel($id);
        $model->setScenario("manage");

        $oldSequenceNumber = $model->sequence_number;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $service = new TreeNodeService();


            if ($model->sequence_number == null)
                $model->sequence_number = $oldSequenceNumber;

            if ($model->validate()) {
                $model->needReturnKey = true;
                if ($service->isExistSameTreeNodeCode($model->kid, $model->tree_node_code,$model->tree_type_id)) {
                    $treeTypeModel = FwTreeType::findOne($model->tree_type_id);
                    $value = Yii::t('common', '{value}_tree_node_code',['value'=>$treeTypeModel->getI18nName()]);
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => $value])];
                } else if ($model->save()) {
                    $newSequenceNumber = $model->sequence_number;
                    if ($oldSequenceNumber != $newSequenceNumber) {

                        $service->updateSequenceNumber($model->kid, $model->tree_type_id,$model->parent_node_id,$oldSequenceNumber, $newSequenceNumber, "0");
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

//            $treeTypeModel = FwTreeType::findOne($model->tree_type_id);
            $rbacService = new RbacService();
            $userId = Yii::$app->user->getId();
            $isSpecialUser = $rbacService->isSpecialUser($userId);

            return $this->renderAjax('update', [
                'model' => $model,
//                'limitation' => $treeTypeModel->limitation,
//                'codeGenWay' => $treeTypeModel->code_gen_way,
                'isSpecialUser' => $isSpecialUser,
                'formType' => 'update'
            ]);
        }

    }

    /**
     * Deletes an existing TreeNode model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        try {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model = $this->findModel($id);

                if (isset($model) && $model != null) {

                    $treeTypeId = $model->tree_type_id;
                    $parentNodeId = $model->parent_node_id;
                    $sequenceNumber = $model->sequence_number;

                    $treeTypeModel = FwTreeType::findOne($treeTypeId);

                    $treeNodeService = new TreeNodeService();

                    $isAllowDelete = true;
                    $errorMessage = null;
                    if ($treeNodeService->isExistSubTreeNode($id)) {
                        $isAllowDelete = false;
                        $errorMessage = Yii::t('common', 'exist_subnode_cannot_delete_node');
                    }

                    if ($isAllowDelete) {
                        if ($treeTypeModel->tree_type_code == 'domain') {
                            $domainService = new DomainService();
                            $domainId = $domainService->getDomainIdByTreeNodeId($id);
                            if ($domainService->isExistUser($domainId)) {
                                $isAllowDelete = false;
                                $errorMessage = Yii::t('common', 'exist_user_cannot_delete_domain');
                            }
                        } else if ($treeTypeModel->tree_type_code == 'company') {
                            $companyService = new CompanyService();
                            $companyId = $companyService->getCompanyIdByTreeNodeId($id);
                            if ($companyService->isExistOrgnization($companyId)) {
                                $isAllowDelete = false;
                                $errorMessage = Yii::t('common', 'exist_orgnization_cannot_delete_company');
                            }
                        } else if ($treeTypeModel->tree_type_code == 'orgnization') {
                            $orgnizationService = new OrgnizationService();
                            $orgId = $orgnizationService->getOrgnizationIdByTreeNodeId($id);
                            if ($orgnizationService->isExistUser($orgId)) {
                                $isAllowDelete = false;
                                $errorMessage = Yii::t('common', 'exist_user_cannot_delete_org');
                            }
                        } else if ($treeTypeModel->tree_type_code == 'permission') {
                            $permissionService = new PermissionService();
                        } else if ($treeTypeModel->tree_type_code == 'course-category') {
                            $courseCategoryService = new CourseCategoryService();
                            $courseId = $courseCategoryService->getCourseCategoryIdByTreeNodeId($id);
                            if ($courseCategoryService->isExistCourse($courseId)) {
                                $isAllowDelete = false;
                                $errorMessage = Yii::t('common', 'exist_course_cannot_delete_cate');
                            }
                        } else if ($treeTypeModel->tree_type_code == 'courseware-category') {
                            $coursewareCategoryService = new CoursewareCategoryService();
                            $coursewareId = $coursewareCategoryService->getCoursewareCategoryIdByTreeNodeId($id);
                            if ($coursewareCategoryService->isExistCourseware($coursewareId)) {
                                $isAllowDelete = false;
                                $errorMessage = Yii::t('common', 'exist_courseware_cannot_delete_cate');
                            }
                        }
                    }

                    if ($isAllowDelete) {
                        $service = new TreeNodeService();
                        $allNodeId = $service->getAllNodeIdIncludeSub($id, $treeTypeId, $model->node_id_path . $id . "/%");

                        if (isset($allNodeId) && count($allNodeId) > 0) {
                            if (isset($treeTypeModel)) {
                                if ($treeTypeModel->tree_type_code == 'domain') {
                                    $domainService->deleteRelateData($allNodeId);
                                } else if ($treeTypeModel->tree_type_code == 'company') {
                                    $companyService->deleteRelateData($allNodeId);
                                } else if ($treeTypeModel->tree_type_code == 'orgnization') {
                                    $orgnizationService->deleteRelateData($allNodeId);
                                } else if ($treeTypeModel->tree_type_code == 'permission') {
                                    $permissionService->deleteRelateData($allNodeId);
                                } else if ($treeTypeModel->tree_type_code == 'course-category') {
                                    $courseCategoryService->deleteRelateData($allNodeId);
                                } else if ($treeTypeModel->tree_type_code == 'courseware-category') {
                                    $coursewareCategoryService->deleteRelateData($allNodeId);
                                }
                            }
                        }

                        $service->deleteSubNode($treeTypeId, $model->root_node_id, $model->node_id_path . $id . "/%");

                        if ($model->delete()) {
                            $service->updateSequenceNumber($id, $treeTypeId, $parentNodeId, $sequenceNumber, $sequenceNumber, "1");

                            return ['result' => 'success'];
                        } else {
                            return ['result' => 'failure'];
                        }
                    } else {
                        return ['result' => 'failure', 'message' => $errorMessage];
                    }
                } else {
                    return ['result' => 'failure'];
                }
            } else {
                return ['result' => 'failure'];
            }
        } catch (Exception $ex) {
            return ['result' => 'failure'];
        }
    }

    public function actionBatchDelete()
    {
        if (isset($_POST['datalist']) && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
                $service = new TreeNodeService();

                $keys = $_POST['datalist'];

                $firstKid = "";

                $kids = "";
                $parentNodeModel = null;
                $treeTypeModel = null;
                $errorMessage = null;
                foreach ($keys as $key) {
                    if (empty($treeTypeModel)) {
                        if (empty($parentNodeModel)) {
                            $parentNodeModel = $this->findModel($key);
                        }
                        $treeTypeId = $parentNodeModel->tree_type_id;
                        $treeTypeModel = FwTreeType::findOne($treeTypeId);
                    }

                    $isAllowDelete = true;
                    if ($service->isExistSubTreeNode($key)) {
                        $isAllowDelete = false;
                        $errorMessage = Yii::t('common', 'exist_subnode_cannot_delete_node');
                    }

                    if ($isAllowDelete) {
                        if ($treeTypeModel->tree_type_code == 'domain') {
                            $domainService = new DomainService();
                            $domainId = $domainService->getDomainIdByTreeNodeId($key);
                            if ($domainService->isExistUser($domainId)) {
                                $isAllowDelete = false;
                                $errorMessage = Yii::t('common', 'exist_user_cannot_delete_domain');
                            }
                        } else if ($treeTypeModel->tree_type_code == 'company') {
                            $companyService = new CompanyService();
                            $companyId = $companyService->getCompanyIdByTreeNodeId($key);
                            if ($companyService->isExistOrgnization($companyId)) {
                                $isAllowDelete = false;
                                $errorMessage = Yii::t('common', 'exist_orgnization_cannot_delete_company');
                            }
                        } else if ($treeTypeModel->tree_type_code == 'orgnization') {
                            $orgnizationService = new OrgnizationService();
                            $orgId = $orgnizationService->getOrgnizationIdByTreeNodeId($key);
                            if ($orgnizationService->isExistUser($orgId)) {
                                $isAllowDelete = false;
                                $errorMessage = Yii::t('common', 'exist_user_cannot_delete_org');
                            }
                        } else if ($treeTypeModel->tree_type_code == 'permission') {
                            $permissionService = new PermissionService();
                        } else if ($treeTypeModel->tree_type_code == 'course-category') {
                            $courseCategoryService = new CourseCategoryService();
                            $courseId = $courseCategoryService->getCourseCategoryIdByTreeNodeId($key);
                            if ($courseCategoryService->isExistCourse($courseId)) {
                                $isAllowDelete = false;
                                $errorMessage = Yii::t('common', 'exist_course_cannot_delete_cate');
                            }
                        } else if ($treeTypeModel->tree_type_code == 'courseware-category') {
                            $coursewareCategoryService = new CoursewareCategoryService();
                            $coursewareId = $coursewareCategoryService->getCoursewareCategoryIdByTreeNodeId($key);
                            if ($coursewareCategoryService->isExistCourseware($coursewareId)) {
                                $isAllowDelete = false;
                                $errorMessage = Yii::t('common', 'exist_courseware_cannot_delete_cate');
                            }
                        }
                    }

                    if ($isAllowDelete) {
                        $kids = $kids . "'" . $key . "',";

                        $allNodeId = $service->getAllNodeIdIncludeSub($key, $treeTypeId, $parentNodeModel->node_id_path . $key . "/%");


                        if (isset($allNodeId) && count($allNodeId) > 0) {
                            if (isset($treeTypeModel)) {
                                if ($treeTypeModel->tree_type_code == 'domain') {
                                    $domainService->deleteRelateData($allNodeId);
                                } else if ($treeTypeModel->tree_type_code == 'company') {
                                    $companyService->deleteRelateData($allNodeId);
                                } else if ($treeTypeModel->tree_type_code == 'orgnization') {
                                    $orgnizationService->deleteRelateData($allNodeId);
                                } else if ($treeTypeModel->tree_type_code == 'permission') {
                                    $permissionService->deleteRelateData($allNodeId);
                                } else if ($treeTypeModel->tree_type_code == 'course-category') {
                                    $courseCategoryService->deleteRelateData($allNodeId);
                                } else if ($treeTypeModel->tree_type_code == 'courseware-category') {
                                    $coursewareCategoryService->deleteRelateData($allNodeId);
                                }
                            }
                        }

                        $service->deleteSubNode($treeTypeId, $parentNodeModel->root_node_id, $parentNodeModel->node_id_path . $key . "/%");

                        if ($firstKid == "") {
                            $firstKid = $key;
                        }
                    }
                }

                if (!empty($kids)) {
                    $kids = rtrim($kids, ",");

                    $model = new FwTreeNode();

                    $tempModel = $this->findModel($firstKid);
                    $sequenceNumber = $tempModel->sequence_number;

                    $treeTypeId = $tempModel->tree_type_id;
                    $parentNodeId = $tempModel->parent_node_id;

                    $model->deleteAllByKid($kids);

                    $service->updateSequenceNumber($firstKid, $treeTypeId, $parentNodeId, $sequenceNumber, $sequenceNumber, "1");

                    return ['result' => 'success'];
                } else {
                    return ['result' => 'failure', 'message' => $errorMessage];
                }
            } catch (Exception $ex) {
                return ['result' => 'failure'];
            }
        } else {
            return $this->redirect(['index']);
        }
    }


    /**
     * Finds the TreeType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwTreeNode the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FwTreeNode::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common','data_not_exist'));
        }
    }


    public function actionStatus($id, $status)
    {
        try {
            if ( Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model = $this->findModel($id);
                $treeTypeModel = FwTreeType::findOne($model->tree_type_id);

                $isAllowStop = true;
                $errorMessage = null;
                $service = new TreeNodeService();
                if ($status == FwTreeNode::STATUS_FLAG_STOP) {
                    if ($service->isExistSubTreeNode($id)) {
                        $isAllowStop = false;
                        $errorMessage = Yii::t('common', 'exist_subnode_cannot_stop_node');
                    }

                    if ($isAllowStop) {
                        if ($treeTypeModel->tree_type_code == 'domain') {
                            $domainService = new DomainService();
                            $domainId = $domainService->getDomainIdByTreeNodeId($id);
                            if ($domainService->isExistUser($domainId)) {
                                $isAllowStop = false;
                                $errorMessage = Yii::t('common', 'exist_user_cannot_stop_domain');
                            }
                        } else if ($treeTypeModel->tree_type_code == 'company') {
                            $companyService = new CompanyService();
                            $companyId = $companyService->getCompanyIdByTreeNodeId($id);
                            if ($companyService->isExistOrgnization($companyId)) {
                                $isAllowStop = false;
                                $errorMessage = Yii::t('common', 'exist_orgnization_cannot_stop_company');
                            }
                        } else if ($treeTypeModel->tree_type_code == 'orgnization') {
                            $orgnizationService = new OrgnizationService();
                            $orgId = $orgnizationService->getOrgnizationIdByTreeNodeId($id);
                            if ($orgnizationService->isExistUser($orgId)) {
                                $isAllowStop = false;
                                $errorMessage = Yii::t('common', 'exist_user_cannot_stop_org');
                            }
                        } else if ($treeTypeModel->tree_type_code == 'permission') {
                            $permissionService = new PermissionService();
                        } else if ($treeTypeModel->tree_type_code == 'course-category') {
                            $courseCategoryService = new CourseCategoryService();
                            $courseId = $courseCategoryService->getCourseCategoryIdByTreeNodeId($id);
                            if ($courseCategoryService->isExistCourse($courseId)) {
                                $isAllowStop = false;
                                $errorMessage = Yii::t('common', 'exist_course_cannot_stop_cate');
                            }
                        } else if ($treeTypeModel->tree_type_code == 'courseware-category') {
                            $coursewareCategoryService = new CoursewareCategoryService();
                            $coursewareId = $coursewareCategoryService->getCoursewareCategoryIdByTreeNodeId($id);
                            if ($coursewareCategoryService->isExistCourseware($coursewareId)) {
                                $isAllowStop = false;
                                $errorMessage = Yii::t('common', 'exist_courseware_cannot_stop_cate');
                            }
                        }
                    }
                }

                if ($isAllowStop) {
//                $model->setScenario("manage");
                    $allNodeId = $service->getAllNodeIdIncludeSub($id, $model->tree_type_id, $model->node_id_path . $id . "/%");

                    if (isset($allNodeId) && count($allNodeId) > 0) {
                        if (isset($treeTypeModel)) {
                            if ($treeTypeModel->tree_type_code == 'domain') {
                                $domainModel = new FwDomain();
                                $service->changeStatusRelateData($domainModel, $allNodeId, $status);
                            } else if ($treeTypeModel->tree_type_code == 'company') {
                                $companyModel = new FwDomain();
                                $service->changeStatusRelateData($companyModel, $allNodeId, $status);
                            } else if ($treeTypeModel->tree_type_code == 'orgnization') {
                                $orgnizationModel = new FwOrgnization();
                                $service->changeStatusRelateData($orgnizationModel, $allNodeId, $status);
                            } else if ($treeTypeModel->tree_type_code == 'permission') {
                                $permissionModel = new FwPermission();
                                $service->changeStatusRelateData($permissionModel, $allNodeId, $status);
                            } else if ($treeTypeModel->tree_type_code == 'course-category') {
                                $courseCategoryModel = new LnCourseCategory();
                                $service->changeStatusRelateData($courseCategoryModel, $allNodeId, $status);
                            } else if ($treeTypeModel->tree_type_code == 'courseware-category') {
                                $coursewareCategoryModel = new LnCoursewareCategory();
                                $service->changeStatusRelateData($coursewareCategoryModel, $allNodeId, $status);
                            }
                        }
                    }

                    $service->changeStatusSubNode($model->tree_type_id, $model->root_node_id, $model->node_id_path . $id . "/%", $status);

                    $model->status = $status;


                    if ($model->save()) {

                        if ($status == FwTreeNode::STATUS_FLAG_NORMAL) {
                            $service->activeParentNode($id);

                            if (isset($treeTypeModel)) {
                                if ($treeTypeModel->tree_type_code == 'domain') {
                                    $domainService = new DomainService();
                                    $targetId = $domainService->getDomainIdByTreeNodeId($id);
                                    $domainService->activeParentNode($targetId);
                                } else if ($treeTypeModel->tree_type_code == 'company') {
                                    $companyService = new CompanyService();
                                    $targetId = $companyService->getCompanyIdByTreeNodeId($id);
                                    $companyService->activeParentNode($targetId);
                                } else if ($treeTypeModel->tree_type_code == 'orgnization') {
                                    $orgnizationService = new OrgnizationService();
                                    $targetId = $orgnizationService->getOrgnizationIdByTreeNodeId($id);
                                    $orgnizationService->activeParentNode($targetId);
                                } else if ($treeTypeModel->tree_type_code == 'permission') {
                                    $permissionService = new PermissionService();
                                    $targetId = $permissionService->getPermissionIdByTreeNodeId($id);
                                    $permissionService->activeParentNode($targetId);
                                } else if ($treeTypeModel->tree_type_code == 'course-category') {
                                    $courseCategoryService = new CourseCategoryService();
                                    $targetId = $courseCategoryService->getCourseCategoryIdByTreeNodeId($id);
                                    $courseCategoryService->activeParentNode($targetId);
                                } else if ($treeTypeModel->tree_type_code == 'courseware-category') {
                                    $coursewareCategoryService = new CoursewareCategoryService();
                                    $targetId = $coursewareCategoryService->getCoursewareCategoryIdByTreeNodeId($id);
                                    $coursewareCategoryService->activeParentNode($targetId);
                                }
                            }
                        }


                        return ['result' => 'success'];

                    } else {
                        return ['result' => 'failure'];
                    }
                }
                else {
                    return ['result' => 'failure', 'message' => $errorMessage];
                }
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
        $this->layout = 'modalWin';
        if (isset($_POST['jsTree_select-tree_selected_result']) && Yii::$app->request->isAjax) {
            $tree_selected_result = Yii::$app->request->post('jsTree_select-tree_selected_result');
            $targetTreeNodeId = str_replace('"]',"",str_replace('["',"",$tree_selected_result));
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($targetTreeNodeId == '-1')
                $targetTreeNodeId = null;


            $model = $this->findModel($id);
            $sourceTreeNodeId = $model->parent_node_id;
            $treeTypeId = $model->tree_type_id;
            $oldSequenceNumber = $model->sequence_number;

            if ($sourceTreeNodeId == $targetTreeNodeId || $targetTreeNodeId == $id)
            {
                return ['result' => 'other', 'message' => Yii::t('common', 'same_node_move')];
            }
            else if ($model->status == self::STATUS_FLAG_STOP)
            {
                return ['result' => 'other', 'message' => Yii::t('common', 'can_not_move_stop')];
            }
            else {
//                $oldNodeIdPath = $model->node_id_path;
//                $oldNodeNamePath = $model->node_name_path;
//                $oldNodeNamePath = $model->node_code_path;

                $model->parent_node_id = $targetTreeNodeId;

                $service = new TreeNodeService();
                $treeTypeModel = FwTreeType::findOne($treeTypeId);

                if ($targetTreeNodeId != null) {
                    $newModel = FwTreeNode::findOne($targetTreeNodeId);

                    $maxLevel = $treeTypeModel->max_level;
                    $subLevel = $service->calculateSubLevel($treeTypeId, $model->kid);
                    $estimateMaxLevel = $newModel->tree_level + 1 + $subLevel;

                    if ($newModel != null && $model->root_node_id == $newModel->root_node_id && $model->tree_level < $newModel->tree_level) {
                        //是否目标是自己的子节点
                        return ['result' => 'other', 'message' => Yii::t('common', 'child_node_move')];
                    } else if ($newModel != null && $maxLevel != 0 && $estimateMaxLevel > $maxLevel) {
                        //达到树的层数上限
                        return ['result' => 'other', 'message' => Yii::t('common', 'reach_max_level')];
                    } else {
                        $newNodeIdPath = $newModel->node_id_path . $newModel->kid . '/';
                        $newNodeNamePath = $newModel->node_name_path . $newModel->tree_node_name . '/';
                        $newNodeCodePath = $newModel->node_code_path . $newModel->tree_node_code . '/';

                        $model->sequence_number = $service->findMaxSequenceNumber($treeTypeId, $targetTreeNodeId);
                        $model->node_id_path = $newNodeIdPath;
                        $model->node_code_path = $newNodeCodePath;
                        $model->node_name_path = $newNodeNamePath;
                        $model->root_node_id = $newModel->root_node_id;
                        $model->tree_level = $newModel->tree_level + 1;
                    }

                } else {
                    $newNodeIdPath = '/';
                    $newNodeNamePath = '/';
                    $newNodeCodePath = '/';

                    $model->parent_node_id = null;
                    $model->sequence_number = $service->findMaxSequenceNumber($treeTypeId, $targetTreeNodeId);
                    $model->node_id_path = $newNodeIdPath;
                    $model->node_code_path = $newNodeCodePath;
                    $model->node_name_path = $newNodeNamePath;
                    $model->root_node_id = $model->kid;
                    $model->tree_level = 1;

                }

                $service->moveSubNodePath($treeTypeId, $model->kid, $targetTreeNodeId);

                if ($model->save()) {

                    $service->updateSequenceNumber($id, $treeTypeId, $sourceTreeNodeId, $oldSequenceNumber, $oldSequenceNumber, "1");

                    $treeTypeCode = $treeTypeModel->tree_type_code;

                    if ($treeTypeCode == "company" || $treeTypeCode == "orgnization" || $treeTypeCode == "permission" ||
                        $treeTypeCode == "domain" || $treeTypeCode == 'course-category' || $treeTypeCode == 'courseware-category'
                    ) {
                        if ($treeTypeCode == "company") {
                            $targetService = new CompanyService();
                        } else if ($treeTypeCode == "orgnization") {
                            $targetService = new OrgnizationService();
                        } else if ($treeTypeCode == "permission") {
                            $targetService = new PermissionService();
                        } else if ($treeTypeCode == "domain") {
                            $targetService = new DomainService();
                        } else if ($treeTypeCode == 'course-category') {
                            $targetService = new CourseCategoryService();
                        } else if ($treeTypeCode == 'courseware-category') {
                            $targetService = new CoursewareCategoryService();
                        }

                        if (isset($targetService)) {
                            $targetService->updateParentIdByTreeNodeId($model->kid, $targetTreeNodeId);
                        }
                    }

                    return ['result' => 'success'];
                }
            }
//            return ['result'=>'failure'];
        }
        else
        {
            $model = $this->findModel($id);
            $treeTypeId = $model->tree_type_id;

            $treeTypeCode = FwTreeType::findOne($treeTypeId)->tree_type_code;

            return $this->render('select', [
                'sourceTreeNodeId' => $id,
                'formType' => 'move',
                'TreeType' => $treeTypeCode
            ]);
        }
    }

    public function actionSelectTree()
    {
//        $this->layout = 'modalWin';
        $treeDataUrl = Yii::$app->request->getQueryParam('treeDataUrl');
        $treeFlag = Yii::$app->request->getQueryParam('treeFlag');
        $needRegister = Yii::$app->request->getQueryParam('needRegister');
        $includeRoot = Yii::$app->request->getQueryParam("IncludeRoot");
        $mergeRoot = Yii::$app->request->getQueryParam("MergeRoot");
        $showContentCount = Yii::$app->request->getQueryParam("ShowContentCount");
        $openAllNode = Yii::$app->request->getQueryParam("OpenAllNode");

        return $this->renderAjax('select-tree',[
            'treeDataUrl'=>$treeDataUrl,
            'treeFlag'=>$treeFlag,
            'needRegister'=>$needRegister,
            'includeRoot'=> $includeRoot,
            'mergeRoot'=> $mergeRoot,
            'showContentCount' => $showContentCount,
            'openAllNode' => $openAllNode,
        ]);
    }

    public function actionMultiSelectTree()
    {
//        $this->layout = 'modalWin';
        $treeDataUrl = Yii::$app->request->getQueryParam('treeDataUrl');
        $treeState = Yii::$app->request->getQueryParam('treeState');
        $treeFlag = Yii::$app->request->getQueryParam('treeFlag');
        $needRegister = Yii::$app->request->getQueryParam('needRegister');
        $includeRoot = Yii::$app->request->getQueryParam("IncludeRoot");
        $mergeRoot = Yii::$app->request->getQueryParam("MergeRoot");
        $showContentCount = Yii::$app->request->getQueryParam("ShowContentCount");
        $openAllNode = Yii::$app->request->getQueryParam("OpenAllNode");

        return $this->renderAjax('multi-select-tree',[
            'treeDataUrl'=>$treeDataUrl,
            'treeState'=>$treeState,
            'treeFlag'=>$treeFlag,
            'needRegister'=>$needRegister,
            'includeRoot'=> $includeRoot,
            'mergeRoot'=> $mergeRoot,
            'showContentCount' => $showContentCount,
            'openAllNode' => $openAllNode,
        ]);
    }

    public function actionExport(){
        $service = new TreeNodeService();

        $treeType = Yii::$app->request->getQueryParam("TreeType");
        $parentNodeId = Yii::$app->request->getQueryParam("TreeNodeKid");
        $treeTypeId = $service->getTreeTypeId($treeType);
        $treeTypeModel = FwTreeType::findOne($treeTypeId);
        if ($parentNodeId == '-1')
            $parentNodeId = '';

        $includeSubNode = Yii::$app->request->getQueryParam("includeSubNode");

        if (!empty($treeTypeModel)) {
            $results = $service->search(Yii::$app->request->queryParams,$treeType,$parentNodeId,$includeSubNode)->query->all();
        }
        else {
            $results = null;
        }

        $split = ",";
        $header = Yii::t('common','tree_node_code') . $split . Yii::t('common','tree_node_name')
            . $split . Yii::t('common','parent_node_name'). $split . Yii::t('common','status');
        $data = array();
        $i = 0;
        foreach ($results as $r) {
            $data[$i][0] = $r->tree_node_code;
            $data[$i][1] = $r->tree_node_name;
            $data[$i][2] = $r->getParentNodeText();
            $data[$i][3] = $r->getStatusText();

            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}