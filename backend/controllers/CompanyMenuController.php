<?php

namespace backend\controllers;

use backend\base\BaseBackController;
use backend\services\CntManageRefService;
use backend\services\CompanyService;
use backend\services\PositionService;
use backend\services\CompanyMenuService;
use common\models\framework\FwCompanyMenu;
use common\services\framework\TreeNodeService;
use common\models\framework\FwCompany;
use common\models\treemanager\FwCntManageRef;
use common\services\framework\RbacService;
use common\helpers\TExportHelper;
use common\helpers\TFileHelper;
use common\helpers\TFileUploadHelper;
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
use yii\web\UploadedFile;

/**
 * PositionController implements the CRUD actions for FwCompanyMenu model.
 */
class CompanyMenuController extends BaseBackController
{

    public $layout = 'frame';

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
//        } else {
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

        $service = new CompanyMenuService();
        $dataProvider = $service->search(Yii::$app->request->queryParams, $treeNodeKid, $includeSubNode);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
        $dataProvider->setPagination($page);

        $rbacService = new RbacService();
        $userId = Yii::$app->user->getId();
        $isSpecialUser = $rbacService->isSpecialUser($userId);

        return $this->render('list', [
            'page' => $page,
            'searchModel' => $service,
            'dataProvider' => $dataProvider,
            'selectNodeId' => $treeNodeKid,
            'includeSubNode' => $includeSubNode,
            'forceShowAll' => $forceShowAll,
            'pageSize' => $pageSize,
            'isSpecialUser' => $isSpecialUser,
        ]);
    }


    /**
     * Displays a single FwCompanyMenu model.
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
     * Creates a new FwCompanyMenu model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");
//        $parentNodeId = Yii::$app->request->getQueryParam("parentNodeId");
//        $this->layout = 'modalWin';
        $model = new FwCompanyMenu();
        $model->setScenario("manage");

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($treeNodeKid == '') {
                $companyId = null;
                $model->share_flag = FwCompanyMenu::SHARE_FLAG_SHARE;
            } else {
                $companyService = new CompanyService();
                $companyId = $companyService->getCompanyIdByTreeNodeId($treeNodeKid);
                $model->company_id = $companyId;
                $model->share_flag = FwCompanyMenu::SHARE_FLAG_EXCLUSIVE;
            }

            $model->status = FwCompanyMenu::STATUS_FLAG_NORMAL;
            $model->limitation = FwCompanyMenu::LIMITATION_NONE;

            if ($model->parent_menu_id == '') {
                $model->parent_menu_id = null;
            }

            if ($model->validate()) {
                $service = new CompanyMenuService();
                if ($service->isExistSameMenuCode($model->kid, $model->company_id, $model->menu_type, $model->menu_code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'template_code')])];
                } else {
                    $model->needReturnKey = true;
                    if ($model->save()) {

//                        if ($treeNodeKid != '') {
//                            $cntManageModel = new FwCntManageRef();
//                            $cntManageModel->subject_id = $treeNodeKid;
//                            $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_TREE;
//                            $cntManageModel->content_id = $model->kid;
//                            $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_COMPANY_MENU;
//                            $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_BELONG;
//
//                            $cntManageRefService = new CntManageRefService();
//
//                            $cntManageRefService->startRelationship($cntManageModel);
//                        }
                    }
                    return ['result' => 'success'];
                }
            }
//            return $this->redirect(['view', 'id' => $model->kid]);
        } else {
            $model->menu_type = FwCompanyMenu::MENU_TYPE_PORTAL;
            $model->action_type = FwCompanyMenu::ACTION_TYPE_URL;

            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Updates an existing FwCompanyMenu model.
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

            if ($model->parent_menu_id == '') {
                $model->parent_menu_id = null;
            }

            if ($model->validate()) {

                $templateId = $model->kid;

                $service = new CompanyMenuService();

                if ($service->isExistSameMenuCode($templateId, $model->company_id, $model->menu_type, $model->menu_code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'template_code')])];
                } else {
                    $model->save();
                    return ['result' => 'success'];
                }
            } else {
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
     * Deletes an existing FwCompanyMenu model.
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
     * Finds the FwCompanyMenu model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwCompanyMenu the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FwCompanyMenu::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'data_not_exist'));
        }
    }


    public function actionBatchDelete()
    {
        if (isset($_POST['datalist']) && Yii::$app->request->isAjax) {
            $keys = $_POST['datalist'];

            $kids = "";
            foreach ($keys as $key) {
                $kids = $kids . "'" . $key . "',";
            }

            $kids = rtrim($kids, ",");

            $model = new FwCompanyMenu();

            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
                $model->deleteAllByKid($kids);

                return ['result' => 'success'];
            } catch (Exception $ex) {
                return ['result' => 'failure'];
            }
        } else {
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
                }

                $kids = rtrim($kids, ",");

                $service = new CompanyMenuService();
                $service->changeStatusByKidList($kids, $status);

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
            $targetTreeNodeId = str_replace('"]', "", str_replace('["', "", $tree_selected_result));
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($targetTreeNodeId == '-1')
                $targetTreeNodeId = '';

            if (strstr($id, ',')) {
                $keyList = explode(",", $id);
            } else {
                $keyList[] = $id;
            }

//            $checkPass = false;

            $companyId = null;
//            $sourceTreeNodeId = '';

            if ($targetTreeNodeId == '') {
                return ['result' => 'other', 'message' => Yii::t('common', 'cannot_move_in_root')];
            } else {
                $kids = "";
                $batchModel = [];

                $companyService = new CompanyService();
                $companyId = $companyService->getCompanyIdByTreeNodeId($targetTreeNodeId);

                if (!empty($keyList) && count($keyList) > 0) {
                    foreach ($keyList as $key) {
                        $kids = $kids . "'" . $key . "',";

//                        $cntManageModel = new FwCntManageRef();
//                        $cntManageModel->subject_id = $targetTreeNodeId;
//                        $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_TREE;
//                        $cntManageModel->content_id = $key;
//                        $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_COMPANY_MENU;
//                        $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_BELONG;
//                        $cntManageModel->status = self::STATUS_FLAG_NORMAL;
//                        $cntManageModel->start_at = time();
//
//                        array_push($batchModel, $cntManageModel);
                    }

                    $kids = rtrim($kids, ",");

                    $CompanyMenuService = new CompanyMenuService();
                    $CompanyMenuService->moveDataByKidList($kids, $companyId);

//                    $cntManageRefService = new CntManageRefService();
//                    //先停用所有旧的关系
//                    $cntManageRefService->stopRelationshipByContentIdList($kids, FwCntManageRef::CONTENT_TYPE_COMPANY_MENU, FwCntManageRef::REFERENCE_TYPE_BELONG, FwCntManageRef::SUBJECT_TYPE_TREE);
//
//                    //再添加关系
//                    $cntManageRefService->batchStartRelationship($batchModel);
                }
            }

            return ['result' => 'success'];

        } else {
            $treeTypeCode = "company";

            if (strstr($id, ',')) {
                $id = substr($id, 0, strpos($id, ','));
            }

            $model = $this->findModel($id);

            if ($model->company_id != '' && $model->company_id != null) {
                $sourceTreeNodeId = FwCompany::findOne($model->company_id)->tree_node_id;
            } else {
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

        $service = new CompanyMenuService();
        $results = $service->search(Yii::$app->request->queryParams, $treeNodeKid, $includeSubNode)->query->all();

        $split = ",";
        $header = Yii::t('common','menu_code') . $split . Yii::t('common','menu_name')
            . $split . Yii::t('common','menu_type')
            . $split . Yii::t('common','relate_{value}',['value'=>Yii::t('common','company')])
            . $split . Yii::t('common','action_url')
            . $split . Yii::t('common','action_parameter')
            . $split . Yii::t('common','action_type')
            . $split . Yii::t('common','action_target')
            . $split . Yii::t('common','action_icon')
            . $split . Yii::t('common','action_class')
            . $split . Yii::t('common','action_tip')
            . $split . Yii::t('common','share_flag')
            . $split . Yii::t('common','status');
        $data = array();
        $i = 0;

        foreach ($results as $r) {
            $data[$i][0] = $r->menu_code;
            $data[$i][1] = $r->menu_name;
            $data[$i][2] = $r->getMenuTypeName();
            $data[$i][3] = $r->getCompanyName();
            $data[$i][4] = $r->action_url;
            $data[$i][5] = $r->action_parameter;
            $data[$i][6] = $r->getActionTypeName();
            $data[$i][7] = $r->action_target;
            $data[$i][8] = $r->action_icon;
            $data[$i][9] = $r->action_class;
            $data[$i][10] = $r->action_tip;
            $data[$i][11] = $r->getShareFlagText();
            $data[$i][12] = $r->getStatusText();

            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}
