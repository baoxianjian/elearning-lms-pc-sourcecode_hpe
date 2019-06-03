<?php
/**
 * Created by PhpStorm.
 * FwUser: Alex Liu
 * Date: 5/5/2016
 * Time: 13:49 PM
 */

namespace backend\controllers;

use backend\base\BaseBackController;
use backend\services\CntManageRefService;
use backend\services\CompanyService;
use backend\services\DictionaryCategoryService;
use backend\services\DomainService;
use backend\services\DomainWorkplaceService;
use backend\services\WechatTemplateService;
use backend\services\WorkPlaceService;
use common\models\framework\FwCompany;
use common\models\framework\FwDictionary;
use common\models\framework\FwDomainWorkplace;
use common\models\treemanager\FwCntManageRef;
use common\services\framework\DictionaryService;
use common\services\framework\RbacService;
use common\services\framework\TreeNodeService;
use common\services\framework\UserDomainService;
use common\helpers\TExportHelper;
use components\widgets\TPagination;
use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class WorkPlaceController extends BaseBackController
{

    public $layout = 'frame';

    public function actionList()
    {
        $this->layout = 'list';

        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");
        $includeSubNode = Yii::$app->request->getQueryParam("includeSubNode");

        if ($treeNodeKid == '-1')
            $treeNodeKid = '';

        $forceShowAll = 'False';
        $pageSize = $this->defaultPageSize;

        if (Yii::$app->request->getQueryParam('PageShowAll') != null && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
            $forceShowAll = 'True';
        }
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }

        $service = new WorkPlaceService();
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
     * Displays a single FwDictionary model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->renderAjax('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new FwDictionary model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");
//        $parentNodeId = Yii::$app->request->getQueryParam("parentNodeId");
//        $this->layout = 'modalWin';
        $model = new FwDictionary();
        $model->setScenario("manage");

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($treeNodeKid != '') {
                $companyService = new CompanyService();
                $companyId = $companyService->getCompanyIdByTreeNodeId($treeNodeKid);
                $model->company_id = $companyId;

                $model->status = FwDictionary::STATUS_FLAG_NORMAL;

                if ($model->validate()) {
                    $service = new WorkPlaceService();
                    if ($service->isExistSameDictionaryCode($model->kid, $model->company_id, $model->dictionary_code)) {
                        return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                            ['value' => Yii::t('common', 'template_code')])];
                    } else {
                        $model->needReturnKey = true;
                        if ($model->save()) {

//                            if ($treeNodeKid != '') {
//                                $cntManageModel = new FwCntManageRef();
//                                $cntManageModel->subject_id = $treeNodeKid;
//                                $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_TREE;
//                                $cntManageModel->content_id = $model->kid;
//                                $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_WECHAT_TEMPLATE;
//                                $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_BELONG;
//
//                                $cntManageRefService = new CntManageRefService();
//
//                                $cntManageRefService->startRelationship($cntManageModel);
//                            }
                        }
                        return ['result' => 'success'];
                    }
                }
            } else {
                return ['result' => 'other', 'message' => Yii::t('common', 'cannot_add_in_root')];
            }
//            return $this->redirect(['view', 'id' => $model->kid]);
        } else {
            $service = new DictionaryService();
            $cateId = $service->getDictionaryCateIdByCateCode('work_place');

            return $this->renderAjax('create', [
                'model' => $model,
                'cateId' => $cateId,
            ]);
        }
    }

    /**
     * Updates an existing FwDictionary model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        return $this->renderAjax('update', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the FwDictionary model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwDictionary the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FwDictionary::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'DataNotExist'));
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

                $service = new WechatTemplateService();
                $service->changeStatusByKidList($kids, $status);

                return ['result' => 'success'];
            } else {
                return ['result' => 'failure'];
            }
        } catch (Exception $ex) {
            return ['result' => 'failure'];
        }
    }

    public function actionDomain($id)
    {
        $treeTypeCode = "domain";
        $treeFlag = $treeTypeCode;
        $suffix = '';
        if (isset($treeFlag) && $treeFlag != null && $treeFlag != '')
            $suffix = '_' . $treeFlag;

//        $this->layout = 'modalWin';
        if (isset($_POST['jsTree_multi-select-tree_changed_result' . $suffix]) && Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $tree_selected_result = Yii::$app->request->post('jsTree_multi-select-tree_changed_result' . $suffix);
//            $tree_displayed_result = Yii::$app->request->post('jsTree_multi-select-tree_displayed_result'.$suffix);

            if ($tree_selected_result != '') {
                $selectedNodes = explode(',', str_replace(']', "", str_replace('[', "", $tree_selected_result)));
            } else {
                $selectedNodes = [];
            }

//            if ($tree_displayed_result != '') {
//                $displayedNodes = explode(',', str_replace(']', "", str_replace('[', "", $tree_displayed_result)));
//            }
//            else {
//                $displayedNodes = [];
//            }

            $domainWorkplaceService = new DomainWorkplaceService();
            $domainWorkplaceService->stopRelationshipByWorkplaceIdList($id);

            $targetModels = [];

//            $needStopList = TArrayHelper::array_minus($displayedNodes,$selectedNodes);

            foreach ($selectedNodes as $key => $value) {
                $treeNodeId = str_replace('"', "", $value);

                if ($treeNodeId === '-1') {
                    continue;
                }

                $domianService = new DomainService();
                $domainId = $domianService->getDomainIdByTreeNodeId($treeNodeId);

                $domainWorkplaceModel = new FwDomainWorkplace();
                $domainWorkplaceModel->workplace_id = $id;
                $domainWorkplaceModel->domain_id = $domainId;
                $domainWorkplaceModel->status = self::STATUS_FLAG_NORMAL;
                $domainWorkplaceModel->start_at = time();

                array_push($targetModels, $domainWorkplaceModel);
            }

            $domainWorkplaceService->batchStartRelationship($targetModels);

            return ['result' => 'success'];
        } else {
            $treeDataUrl = Url::toRoute(['work-place/domain-tree-data', 'id' => $id]);
            return $this->renderAjax('//tree-node/multi-select', [
                'formType' => 'work-place-domain',
                'TreeType' => $treeTypeCode,
                'treeDataUrl' => $treeDataUrl,
                'treeState' => "False",
                'treeFlag' => $treeTypeCode,
                'needRegister' => 'True'
            ]);
        }
    }

    public function actionDomainTreeData($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $service = new TreeNodeService();

        $otherService = WorkPlaceService::className();//可根据需要换所需的服务

        $otherKid = $id;

        $result = $service->listTreeData($otherService, $otherKid, true, false);

        return $result;
    }
}