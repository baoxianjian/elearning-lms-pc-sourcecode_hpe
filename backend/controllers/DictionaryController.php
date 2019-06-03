<?php

namespace backend\controllers;

use backend\base\BaseBackController;
use backend\services\CompanyService;
use backend\services\DictionaryCategoryService;
use backend\services\DictionaryService;
use common\models\framework\FwDictionary;
use common\models\framework\FwDictionaryCategory;
use common\base\BaseActiveRecord;
use common\helpers\TExportHelper;
use common\helpers\TLoggerHelper;
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
 * DictionaryController implements the CRUD actions for FwDictionary model.
 */
class DictionaryController extends BaseBackController
{
    public $layout = 'frame';

    public function actionIndex()
    {
//        TLoggerHelper::Message("test");
        return $this->render('index');
    }

    public function actionList()
    {
        $this->layout = 'list';
        $dictionaryCategoryService = new DictionaryCategoryService();
        $dictionaryCategoryModel = $dictionaryCategoryService->getAllDictionaryCategory(FwDictionaryCategory::CATE_TYPE_SYSTEM);

        $forceShowAll = 'False';
        $pageSize = $this->defaultPageSize;

        if (Yii::$app->request->getQueryParam('PageShowAll') != null && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
            $forceShowAll = 'True';
        }
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }

        $service = new DictionaryService();
        $dataProvider = $service->search(Yii::$app->request->queryParams);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
        $dataProvider->setPagination($page);
        return $this->render('list', [
            'page' => $page,
            'searchModel' => $service,
            'dictionaryCategoryModel' => $dictionaryCategoryModel,
            'dataProvider' => $dataProvider,
            'forceShowAll' => $forceShowAll,
            'pageSize' => $pageSize
        ]);
    }

    /**
     * Displays a single Dictionary model.
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
     * Creates a new Dictionary model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($type = null)
    {
        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");
//        $this->layout = 'modalWin';
        $model = new FwDictionary();
        $model->setScenario("manage");

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $service = new DictionaryService();

            if ($model->validate()) {
                $companyId = null;
                if ($treeNodeKid) {
                    $companyService = new CompanyService();
                    $companyId = $companyService->getCompanyIdByTreeNodeId($treeNodeKid);
                    $model->company_id = $companyId;
                }

                $dictionaryId = $model->kid;
                $dictionaryCategoryId = $model->dictionary_category_id;
                if ($service->isExistSameDictionaryCode($dictionaryId, $dictionaryCategoryId, $model->dictionary_code, $companyId)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'cate_code')])];
                } else {
                    $oldSequenceNumber = $service->findMaxSequenceNumber($dictionaryCategoryId, $companyId);
                    $model->status = self::STATUS_FLAG_NORMAL;
                    $model->needReturnKey = true;
                    if ($model->save()) {

                        $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicCode_" . $model->dictionary_code;
                        if ($model->company_id) {
                            $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                        }
                        BaseActiveRecord::saveToCache($cacheKey, $model);

                        $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicValue_" . $model->dictionary_value;
                        if ($model->company_id) {
                            $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                        }
                        BaseActiveRecord::saveToCache($cacheKey, $model);

                        $cacheKey = "DictionaryList_CateId_" . $dictionaryCategoryId;
                        if ($model->company_id) {
                            $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                        }
                        BaseActiveRecord::removeFromCache($cacheKey);
                        $newSequenceNumber = $model->sequence_number;
                        if ($oldSequenceNumber != $newSequenceNumber) {
                            $service->updateSequenceNumber($model->kid, $dictionaryCategoryId, $oldSequenceNumber, $newSequenceNumber, "0", $model->company_id);
                        }
                        return ['result' => 'success'];
                    } else {
                        return ['result' => 'failure'];
                    }
                }
            } else {
                return ['result' => 'failure'];
            }
//            return $this->redirect(['view', 'id' => $model->kid]);
        } else {
            $cateType = null;

            if ($type === 'company') {
                $cateType = FwDictionaryCategory::CATE_TYPE_COMPANY;
            } elseif ($type === 'system') {
                $cateType = FwDictionaryCategory::CATE_TYPE_SYSTEM;
            }

            $dictionaryCategoryService = new DictionaryCategoryService();
            $dictionaryCategoryModel = $dictionaryCategoryService->getAllDictionaryCategory($cateType);

            return $this->renderAjax('create', [
                'model' => $model,
                'dictionaryCategoryModel' => $dictionaryCategoryModel,
            ]);
        }
    }

    public function actionSequenceNumber($dictionaryCategoryId, $treeNodeKid = null)
    {
        $service = new DictionaryService();
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $companyId = null;
            if ($treeNodeKid) {
                $companyService = new CompanyService();
                $companyId = $companyService->getCompanyIdByTreeNodeId($treeNodeKid);
            }

            $sequenceNumber = $service->findMaxSequenceNumber($dictionaryCategoryId, $companyId);

            return ['result' => 'success', 'sequenceNumber' => $sequenceNumber];
        }

        return ['result' => 'failure'];
    }


    /**
     * Updates an existing Dictionary model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id, $type = null)
    {
//        $this->layout = 'modalWin';
        $model = $this->findModel($id);
        $model->setScenario("manage");

        $oldSequenceNumber = $model->sequence_number;
        $oldDictionaryCategoryId = $model->dictionary_category_id;

        $oldDictionaryCode = $model->dictionary_code;
        $oldDictionaryValue = $model->dictionary_value;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $service = new DictionaryService();

            if ($model->validate()) {
                $dictionaryId = $model->kid;
                $dictionaryCategoryId = $model->dictionary_category_id;
                $model->needReturnKey = true;
                if ($service->isExistSameDictionaryCode($dictionaryId, $dictionaryCategoryId, $model->dictionary_code, $model->company_id)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'cate_code')])];
                } else if ($model->save()) {

                    if ($model->dictionary_code == "default_language") {
                        $cacheLanguageKey = "Common_Language";
                        BaseActiveRecord::saveToCache($cacheLanguageKey, $model->dictionary_value);
                    } else if ($model->dictionary_code == "default_theme") {
                        $cacheThemeKey = "Common_Theme";
                        BaseActiveRecord::saveToCache($cacheThemeKey, $model->dictionary_value);
                    }

                    $newSequenceNumber = $model->sequence_number;
                    if ($oldDictionaryCategoryId != $dictionaryCategoryId) {
                        $oldSequenceNumber = $service->findMaxSequenceNumber($dictionaryCategoryId, $model->company_id);
                        if ($oldSequenceNumber != $newSequenceNumber) {
                            $service->updateSequenceNumber($model->kid, $dictionaryCategoryId, $oldSequenceNumber, $newSequenceNumber, "0", $model->company_id);
                        }
                    } else if ($oldSequenceNumber != $newSequenceNumber) {
                        $service->updateSequenceNumber($model->kid, $dictionaryCategoryId, $oldSequenceNumber, $newSequenceNumber, "0", $model->company_id);
                    }

                    $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicCode_" . $oldDictionaryCode;
                    if ($model->company_id) {
                        $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                    }
                    BaseActiveRecord::removeFromCache($cacheKey);

                    $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicValue_" . $oldDictionaryValue;
                    if ($model->company_id) {
                        $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                    }
                    BaseActiveRecord::removeFromCache($cacheKey);

                    $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicCode_" . $model->dictionary_code;
                    if ($model->company_id) {
                        $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                    }
                    BaseActiveRecord::saveToCache($cacheKey, $model);

                    $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicValue_" . $model->dictionary_value;
                    if ($model->company_id) {
                        $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                    }
                    BaseActiveRecord::saveToCache($cacheKey, $model);


                    $cacheKey = "DictionaryList_CateId_" . $dictionaryCategoryId;
                    if ($model->company_id) {
                        $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                    }
                    BaseActiveRecord::removeFromCache($cacheKey);

                    return ['result' => 'success'];
                } else {
                    return ['result' => 'failure'];
                }
            } else {
                return ['result' => 'failure'];
            }
//            return $this->redirect(['view', 'id' => $model->kid]);
        } else {
            $cateType = null;

            if ($type === 'company') {
                $cateType = FwDictionaryCategory::CATE_TYPE_COMPANY;
            } elseif ($type === 'system') {
                $cateType = FwDictionaryCategory::CATE_TYPE_SYSTEM;
            }

            $dictionaryCategoryService = new DictionaryCategoryService();
            $dictionaryCategoryModel = $dictionaryCategoryService->getAllDictionaryCategory($cateType);

            return $this->renderAjax('update', [
                'model' => $model,
                'dictionaryCategoryModel' => $dictionaryCategoryModel,
            ]);
        }

    }

    /**
     * Deletes an existing Dictionary model.
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
                    $newSequenceNumber = $model->sequence_number;
                    $dictionaryCategoryId = $model->dictionary_category_id;
                    if ($model->delete()) {

                        if ($model->dictionary_code == "default_language") {
                            $cacheLanguageKey = "Common_Language";
                            BaseActiveRecord::removeFromCache($cacheLanguageKey);
                        } else if ($model->dictionary_code == "default_theme") {
                            $cacheThemeKey = "Common_Theme";
                            BaseActiveRecord::removeFromCache($cacheThemeKey);
                        }

                        $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicCode_" . $model->dictionary_code;
                        if ($model->company_id) {
                            $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                        }
                        BaseActiveRecord::removeFromCache($cacheKey);

                        $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicValue_" . $model->dictionary_value;
                        if ($model->company_id) {
                            $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                        }
                        BaseActiveRecord::removeFromCache($cacheKey);

                        $cacheKey = "DictionaryList_CateId_" . $dictionaryCategoryId;
                        if ($model->company_id) {
                            $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                        }
                        BaseActiveRecord::removeFromCache($cacheKey);

                        $service = new DictionaryService();

                        $service->updateSequenceNumber($id, $dictionaryCategoryId, $newSequenceNumber, $newSequenceNumber, "1", $model->company_id);
                    }
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
     * Finds the Dictionary model based on its primary key value.
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
            throw new NotFoundHttpException(Yii::t('common', 'data_not_exist'));
        }
    }


    public function actionBatchDelete()
    {
        if (isset($_POST['datalist']) && Yii::$app->request->isAjax) {
            $keys = $_POST['datalist'];

            $firstKid = "";
            $firstDictionaryCategoryId = "";
            $firstModel = null;

            $kids = "";
            foreach ($keys as $key) {
                $kids = $kids . "'" . $key . "',";
                FwDictionary::removeFromCacheByKid($key);

                $model = $this->findModel($key);

                if (isset($model) && $model != null) {
                    $dictionaryCategoryId = $model->dictionary_category_id;

                    if ($model->dictionary_code == "default_language") {
                        $cacheLanguageKey = "Common_Language";
                        BaseActiveRecord::removeFromCache($cacheLanguageKey);
                    } else if ($model->dictionary_code == "default_theme") {
                        $cacheThemeKey = "Common_Theme";
                        BaseActiveRecord::removeFromCache($cacheThemeKey);
                    }

                    $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicCode_" . $model->dictionary_code;
                    if ($model->company_id) {
                        $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                    }
                    BaseActiveRecord::removeFromCache($cacheKey);

                    $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicValue_" . $model->dictionary_value;
                    if ($model->company_id) {
                        $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                    }
                    BaseActiveRecord::removeFromCache($cacheKey);

                    $cacheKey = "DictionaryList_CateId_" . $dictionaryCategoryId;
                    if ($model->company_id) {
                        $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                    }
                    BaseActiveRecord::removeFromCache($cacheKey);
                }

                if (empty($firstKid)) {
                    $firstKid = $key;
                    $firstModel = $model;
                    $firstDictionaryCategoryId = $firstModel->dictionary_category_id;
                }
            }

            $kids = rtrim($kids, ",");

            $model = new FwDictionary();

            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
                $sequenceNumber = $firstModel->sequence_number;
                $companyId = $firstModel->company_id;

                $model->deleteAllByKid($kids);


                $service = new DictionaryService();
                $service->updateSequenceNumber($firstKid, $firstDictionaryCategoryId, $sequenceNumber, $sequenceNumber, "1", $companyId);

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
                    $model = $this->findModel($key);

                    $kids = $kids . "'" . $key . "',";
                    FwDictionary::removeFromCacheByKid($key);

                    if (isset($model) && $model != null) {
                        $dictionaryCategoryId = $model->dictionary_category_id;

                        if ($model->dictionary_code == "default_language") {
                            $cacheLanguageKey = "Common_Language";
                            BaseActiveRecord::removeFromCache($cacheLanguageKey);
                        } else if ($model->dictionary_code == "default_theme") {
                            $cacheThemeKey = "Common_Theme";
                            BaseActiveRecord::removeFromCache($cacheThemeKey);
                        }

                        $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicCode_" . $model->dictionary_code;
                        if ($model->company_id) {
                            $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                        }
                        BaseActiveRecord::removeFromCache($cacheKey);

                        $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicValue_" . $model->dictionary_value;
                        if ($model->company_id) {
                            $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                        }
                        BaseActiveRecord::removeFromCache($cacheKey);

                        $cacheKey = "DictionaryList_CateId_" . $dictionaryCategoryId;
                        if ($model->company_id) {
                            $cacheKey = $cacheKey . '_ComId_' . $model->company_id;
                        }
                        BaseActiveRecord::removeFromCache($cacheKey);
                    }
                }

                $kids = rtrim($kids, ",");

                $dictionaryService = new DictionaryService();
                $dictionaryService->changeStatusByKidList($kids, $status);

                return ['result' => 'success'];
            } else {
                return ['result' => 'failure'];
            }
        } catch (Exception $ex) {
            return ['result' => 'failure'];
        }
    }

    public function actionExport()
    {
        $service = new DictionaryService();
        $results = $service->search(Yii::$app->request->queryParams)->query->all();

        $split = ",";
        $header = Yii::t('common', 'dictionary_cate_name') . $split . Yii::t('common', 'dictionary_code')
            . $split . Yii::t('common', 'dictionary_name') . $split . Yii::t('common', 'dictionary_value')
            . $split . Yii::t('common', 'status');
        $data = array();
        $i = 0;

        foreach ($results as $r) {
            $data[$i][0] = $r->getDictionaryCategoryName();
            $data[$i][1] = $r->dictionary_code;
            $data[$i][2] = $r->dictionary_name;
            $data[$i][3] = $r->dictionary_value;
            $data[$i][4] = $r->getStatusText();
            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}
