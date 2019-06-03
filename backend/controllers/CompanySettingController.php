<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 4/8/2015
 * Time: 3:01 PM
 */

namespace backend\controllers;


use backend\base\BaseBackController;
use backend\services\CompanyService;
use backend\services\CompanySettingService;
use common\services\framework\DictionaryService;
use backend\services\OrgnizationService;
use backend\services\RoleService;
use common\services\framework\TreeNodeService;
use backend\services\UserManagerService;
use backend\services\UserService;
use common\models\framework\FwCompany;
use common\models\framework\FwCompanySetting;
use common\models\framework\FwOrgnization;
use common\models\framework\FwUser;
use common\models\framework\FwUserManager;
use common\models\framework\FwUserRole;
use common\services\framework\UserOrgnizationService;
use common\base\BaseActiveRecord;
use common\helpers\TArrayHelper;
use components\widgets\TPagination;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CompanySettingController extends BaseBackController{

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

        $service = new CompanySettingService();
        $dataProvider = $service->search(Yii::$app->request->queryParams,$treeNodeKid,$includeSubNode);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize'=>$pageSize,'totalCount'=>$count]);
        $dataProvider->setPagination($page);

        $dictionaryService = new DictionaryService();
        $dictionaryModel = $dictionaryService->getDictionariesByCategory('company_setting');

        return $this->render('list', [
            'page' => $page,
            'searchModel' => $service,
            'dataProvider' => $dataProvider,
            'selectNodeId'=>$treeNodeKid,
            'includeSubNode'=>$includeSubNode,
            'forceShowAll'=>$forceShowAll,
            'dictionaryModel'=>$dictionaryModel,
            'pageSize'=>$pageSize
        ]);
    }


    public function actionCreate()
    {
        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");
        $model = new FwCompanySetting();
        $model->setScenario("manage");

        if ($treeNodeKid == '')
            $companyId = null;
        else
        {
            $companyService = new CompanyService();
            $companyId = $companyService->getCompanyIdByTreeNodeId($treeNodeKid);
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model->company_id = $companyId;

            if ($model->validate()) {
                $companySettingService = new CompanySettingService();
                if ($companySettingService->isExistSameCode($companyId,$model->kid, $model->code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'setting_code')])];
                }
                else {
                    if ($model->save()) {
                        $cacheKey = "CompanySetting_CompanyId_" . $companyId . "_Code_" . $model->code;
                        BaseActiveRecord::saveToCache($cacheKey, $model);

                        return ['result' => 'success'];
                    } else {
                        return ['result' => 'failure'];
                    }
                }
            }
            else {
                return ['result' => 'failure'];
            }
        } else {
            $dictionaryService = new DictionaryService();
            $dictionaryModel = $dictionaryService->getDictionariesByCategory('company_setting');

            return $this->renderAjax('create', [
                'model' => $model,
                'dictionaryModel' => $dictionaryModel
            ]);
        }
    }

    public function actionUpdate($id)
    {
//        $this->layout = 'modalWin';
        $model = $this->findModel($id);
        $model->setScenario("manage");

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->validate()) {
                $companySettingService = new CompanySettingService();
                if ($companySettingService->isExistSameCode($model->company_id,$model->kid, $model->code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'setting_code')])];
                }
                else {
                    if ($model->save()) {

                        $cacheKey = "CompanySetting_CompanyId_" . $model->company_id . "_Code_" . $model->code;
                        BaseActiveRecord::saveToCache($cacheKey, $model);

                        return ['result' => 'success'];
                    } else {
                        return ['result' => 'failure'];
                    }
                }
            }
            else {
                return ['result' => 'failure'];
            }
        } else {
            $dictionaryService = new DictionaryService();
            $dictionaryModel = $dictionaryService->getDictionariesByCategory('company_setting');
            return $this->renderAjax('update', [
                'model' => $model,
                'dictionaryModel' => $dictionaryModel
            ]);
        }
    }

    public function actionDefaultValue($dictionaryCode)
    {
        $service = new DictionaryService();
        if (Yii::$app->request->isAjax &&  Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $defaultValue = $service->getDictionaryValueByCode('company_setting',$dictionaryCode);

            return ['result' => 'success' , 'defaultValue' => $defaultValue];
        }

        return ['result' => 'failure'];
    }

    public function actionView($id)
    {
        return $this->renderAjax('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Finds the FwCompanySetting model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwCompanySetting the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FwCompanySetting::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common','data_not_exist'));
        }
    }
}