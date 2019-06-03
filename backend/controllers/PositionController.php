<?php

namespace backend\controllers;

use backend\base\BaseBackController;
use backend\services\CntManageRefService;
use backend\services\CompanyService;
use backend\services\PositionService;
use backend\services\UserPositionService;
use common\services\framework\TreeNodeService;
use common\models\framework\FwCompany;
use common\models\framework\FwPosition;
use common\models\treemanager\FwCntManageRef;
use common\services\framework\RbacService;
use common\base\BaseActiveRecord;
use common\helpers\TExportHelper;
use components\widgets\TPagination;
use Yii;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * PositionController implements the CRUD actions for FwPosition model.
 */
class PositionController extends BaseBackController{

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
//            $treeTypeId = $treeNodeService->getTreeTypeId('company');
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

        $service = new PositionService();
        $dataProvider = $service->search(Yii::$app->request->queryParams,$treeNodeKid,$includeSubNode);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize'=>$pageSize,'totalCount'=>$count]);
        $dataProvider->setPagination($page);

        $rbacService = new RbacService();
        $userId = Yii::$app->user->getId();
        $isSpecialUser = $rbacService->isSpecialUser($userId);

        return $this->render('list', [
            'page' => $page,
            'searchModel' => $service,
            'dataProvider' => $dataProvider,
            'selectNodeId'=>$treeNodeKid,
            'includeSubNode'=>$includeSubNode,
            'forceShowAll'=>$forceShowAll,
            'pageSize'=>$pageSize,
            'isSpecialUser' => $isSpecialUser,
        ]);
    }


    /**
     * Displays a single FwPosition model.
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
     * Creates a new FwPosition model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");
//        $parentNodeId = Yii::$app->request->getQueryParam("parentNodeId");
//        $this->layout = 'modalWin';
        $model = new FwPosition();
        $model->setScenario("manage");

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($treeNodeKid == '') {
                $companyId = null;
                $model->share_flag = FwPosition::SHARE_FLAG_SHARE;
            }
            else
            {
                $companyService = new CompanyService();
                $companyId = $companyService->getCompanyIdByTreeNodeId($treeNodeKid);
                $model->company_id = $companyId;
                $model->share_flag = FwPosition::SHARE_FLAG_EXCLUSIVE;
            }

            $model->limitation = FwPosition::LIMITATION_NONE;
            if ($model->validate()) {
                $model->needReturnKey = true;
                $positionService = new PositionService();
                if ($positionService->isExistSamePositionCode($model->kid, $model->company_id, $model->position_code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'position_code')])];
                }
                else if ($model->save()) {
//                    if ($treeNodeKid != '') {
//                        $cntManageModel = new FwCntManageRef();
//                        $cntManageModel->subject_id = $treeNodeKid;
//                        $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_TREE;
//                        $cntManageModel->content_id = $model->kid;
//                        $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_POSITION;
//                        $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_BELONG;
//
//                        $cntManageRefService = new CntManageRefService();
//
//                        $cntManageRefService->startRelationship($cntManageModel);
//                    }

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
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Updates an existing FwPosition model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
//        $this->layout = 'modalWin';
        $model = $this->findModel($id);
        $model->setScenario("manage");


        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->validate()) {

                $positionService = new PositionService();
                if ($positionService->isExistSamePositionCode($model->kid,  $model->company_id, $model->position_code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'position_code')])];
                }
                else if ($model->save()) {
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
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }

    }

    /**
     * Deletes an existing FwPosition model.
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
                if (isset($model) && $model != null && $model->delete()) {
                    $kids = "'" . $id . "'";
                    $userPositionService = new UserPositionService();
                    $userPositionService->stopRelationshipByPositionIdList($kids);

                    return ['result' => 'success'];
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

    /**
     * Finds the FwPosition model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwPosition the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FwPosition::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common','data_not_exist'));
        }
    }


    public function actionBatchDelete()
    {
        if (isset($_POST['datalist']) && Yii::$app->request->isAjax) {
            $keys = $_POST['datalist'];

            $kids = "";
            foreach ($keys as $key)
            {
                $kids = $kids . "'" . $key . "',";
                FwPosition::removeFromCacheByKid($key);
            }

            $kids = rtrim($kids,",");

            $model = new FwPosition();

            Yii::$app->response->format = Response::FORMAT_JSON;
            try {

                $model->deleteAllByKid($kids);

                $userPositionService = new UserPositionService();
                $userPositionService->stopRelationshipByPositionIdList($kids);

                return ['result'=>'success'];
            }
            catch (Exception $ex)
            {
                return ['result'=>'failure'];
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
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                if (strstr($id, ',')) {
                    $keys = explode(",", $id);
                } else {
                    $keys[] = $id;
                }

                $kids = "";
                foreach ($keys as $key) {
                    $kids = $kids . "'" . $key . "',";
                    FwPosition::removeFromCacheByKid($key);
                }

                $kids = rtrim($kids, ",");

                $positionService = new PositionService();
                $positionService->changeStatusByKidList($kids, $status);

                return ['result' => 'success'];
            } else {
                return ['result' => 'failure'];
            }
        } catch (Exception $ex) {
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

            $companyId = null;
//            $sourceTreeNodeId = '';

            if ($targetTreeNodeId == '') {
                return ['result' => 'other', 'message' => Yii::t('common', 'cannot_move_in_root')];
            }
            else {
                $kids = "";
                $batchModel = [];

                $companyService = new CompanyService();
                $companyId = $companyService->getCompanyIdByTreeNodeId($targetTreeNodeId);

                if (!empty($keyList) && count($keyList) > 0) {
                    foreach ($keyList as $key) {
                        $kids = $kids . "'" . $key . "',";
                        FwPosition::removeFromCacheByKid($key);

//                        $cntManageModel = new FwCntManageRef();
//                        $cntManageModel->subject_id = $targetTreeNodeId;
//                        $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_TREE;
//                        $cntManageModel->content_id = $key;
//                        $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_POSITION;
//                        $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_BELONG;
//                        $cntManageModel->status = self::STATUS_FLAG_NORMAL;
//                        $cntManageModel->start_at = time();
//
//                        array_push($batchModel, $cntManageModel);
                    }

                    $kids = rtrim($kids, ",");

                    $positionService = new PositionService();
                    $positionService->moveDataByKidList($kids, $companyId);

//                    $cntManageRefService = new CntManageRefService();
//                    //先停用所有旧的关系
//                    $cntManageRefService->stopRelationshipByContentIdList($kids, FwCntManageRef::CONTENT_TYPE_POSITION, FwCntManageRef::REFERENCE_TYPE_BELONG, FwCntManageRef::SUBJECT_TYPE_TREE);
//
//                    //再添加关系
//                    $cntManageRefService->batchStartRelationship($batchModel);

                }

                return ['result' => 'success'];
            }
        }
        else
        {
            $treeTypeCode = "company";

            if (strstr($id,','))
            {
                $id =  substr($id,0,strpos($id,','));
            }

            $model = $this->findModel($id);

            if ($model->company_id != '' && $model->company_id != null) {
                $sourceTreeNodeId = FwCompany::findOne($model->company_id)->tree_node_id;
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

//        if ($includeSubNode == '1') {
//            $treeNodeService = new TreeNodeService();
//            $treeTypeId = $treeNodeService->getTreeTypeId('company');
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

        $service = new PositionService();
        $results = $service->search(Yii::$app->request->queryParams,$treeNodeKid,$includeSubNode)->query->all();

        $split = ",";
        $header = Yii::t('common','position_code') . $split . Yii::t('common','position_name'). $split . Yii::t('common','share_flag')
            . $split . Yii::t('common','relate_{value}',['value'=>Yii::t('common','company')])
            . $split . Yii::t('common','status');
        $data = array();
        $i = 0;

        foreach ($results as $r) {
            $data[$i][0] = $r->position_code;
            $data[$i][1] = $r->position_name;
            $data[$i][2] = $r->getShareFlagText();
            $data[$i][3] = $r->getCompanyName();
            $data[$i][4] = $r->getStatusText();

            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}
