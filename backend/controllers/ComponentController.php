<?php


namespace backend\controllers;

use common\base\BaseActiveRecord;
use common\helpers\TExportHelper;
use components\widgets\TPagination;
use Yii;
use backend\base\BaseBackController;
use backend\services\ComponentService;
use common\models\learning\LnComponent;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ComponentController  extends BaseBackController{

    public $layout  = 'frame';

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionList()
    {
        $this->layout = 'list';

        $forceShowAll = 'False';
        $pageSize = $this->defaultPageSize;

        if (Yii::$app->request->getQueryParam('PageShowAll') != null && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
            $forceShowAll = 'True';
        }
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }

        $service = new ComponentService();
        $dataProvider = $service->search(Yii::$app->request->queryParams);

        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize'=>$pageSize,'totalCount'=>$count]);
        $dataProvider->setPagination($page);
        return $this->render('list', [
            'page' => $page,
            'searchModel' => $service,
            'dataProvider' => $dataProvider,
            'forceShowAll'=>$forceShowAll,
            'pageSize'=>$pageSize
        ]);
    }

    /**
     * Displays a single LnComponent model.
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
     * Creates a new LnComponent model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ComponentService();
        $model->setScenario("manage");

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $service = new ComponentService();

            if ($model->file_type == "")
                $model->file_type = null;

            if ($model->validate()) {
                if ($service->isExistSameComponentCode($model->kid, $model->component_code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'component_code')])];
                } else {
                    $oldSequenceNumber = $service->findMaxSequenceNumber();
                    $model->needReturnKey = true;
//                    $model->saveEncode = false;//因为保存的内容包含html，所以要设置为false
                    if ($model->save()) {
                        $newSequenceNumber = $model->sequence_number;
                        if ($oldSequenceNumber != $newSequenceNumber) {
                            $service->updateSequenceNumber($model->kid, $oldSequenceNumber, $newSequenceNumber, "0");
                        }
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
            $service = new ComponentService();
            $model->sequence_number = $service->findMaxSequenceNumber();
            $model->transfer_type = LnComponent::TRANSFER_TYPE_NORMAL;
            $model->is_display_mobile = LnComponent::DISPLAY_MOBILE_NO;
            $model->is_display_pc = LnComponent::DISPLAY_PC_YES;
            $model->is_allow_download = LnComponent::ALLOW_DOWNLOAD_NO;
            $model->is_need_upload = LnComponent::YES;
            $model->is_allow_reuse = LnComponent::YES;
            $model->is_use_vendor = LnComponent::NO;
            $model->feature_content_type= LnComponent::FEATURE_CONTENT_TYPE_NONE;
            $model->window_mode= LnComponent::WINDOW_MODE_BIG;
            $model->complete_rule = LnComponent::COMPLETE_RULE_SCORE;
            $model->is_record_score = LnComponent::YES;
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Updates an existing LnComponent model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->setScenario("manage");

        $oldSequenceNumber = $model->sequence_number;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $service = new ComponentService();

            if ($model->file_type == "")
                $model->file_type = null;

            if ($model->validate()) {
//                $model->saveEncode = false;//因为保存的内容包含html，所以要设置为false
                $model->needReturnKey = true;
                if ($service->isExistSameComponentCode($model->kid, $model->component_code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'component_code')])];
                } else if ($model->save()) {

                    $newSequenceNumber = $model->sequence_number;
                    if ($oldSequenceNumber != $newSequenceNumber) {

                        $service->updateSequenceNumber($model->kid, $oldSequenceNumber, $newSequenceNumber, "0");
                    }
                    return ['result' => 'success'];
                } else {
                    return ['result' => 'failure'];
                }
            }
            else {
                return ['result' => 'failure'];
            }
        } else {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }

    }

    /**
     * Deletes an existing LnComponent model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        try {
            if ( Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model = $this->findModel($id);

                if (isset($model) && $model != null) {
                    $newSequenceNumber = $model->sequence_number;
                    if ($model->delete()) {
                        $service = new ComponentService();
                        $service->updateSequenceNumber($id, $newSequenceNumber, $newSequenceNumber , "1");
                    }

                    return ['result' => 'success'];
                }
                else
                {
                    return ['result' => 'failure'];
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

    /**
     * Finds the LnComponent model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return LnComponent the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ComponentService::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common','data_not_exist'));
        }
    }


    public function actionBatchDelete()
    {
        if (isset($_POST['datalist']) && Yii::$app->request->isAjax) {
            $keys = $_POST['datalist'];

            $firstKid = "";

            $kids = "";
            foreach ($keys as $key)
            {
                $kids = $kids . "'" . $key . "',";

                LnComponent::removeFromCacheByKid($key);

                if ($firstKid == "")
                {
                    $firstKid = $key;
                }
            }

            $kids = rtrim($kids,",");

            $model = new ComponentService();

            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
                $tempModel = $this->findModel($firstKid);
                $sequenceNumber = $tempModel->sequence_number;

                $model->deleteAllByKid($kids);


                $service = new ComponentService();
                $service->updateSequenceNumber($firstKid, $sequenceNumber, $sequenceNumber , "1");

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

    public function actionSequenceNumber($componentType)
    {
        $service = new ComponentService();
        if (Yii::$app->request->isAjax &&  Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $sequenceNumber = $service->findMaxSequenceNumber($componentType);

            return ['result' => 'success' , 'sequenceNumber' => $sequenceNumber];
        }

        return ['result' => 'failure'];
    }

    public function actionExport(){
        $service = new ComponentService();
        $results = $service->search(Yii::$app->request->queryParams)->query->all();

        $split = ",";
        $header =  Yii::t('common','title') . $split . Yii::t('common','component_code') . $split . Yii::t('common','component_type')
            . $split . Yii::t('common','component_category'). $split . Yii::t('common','file_type'). $split . Yii::t('common','is_display_pc')
            . $split . Yii::t('common','is_display_mobile'). $split . Yii::t('common','is_allow_download')
            . $split . Yii::t('common','transfer_type'). $split . Yii::t('common','is_need_upload')
            . $split . Yii::t('common','is_allow_reuse'). $split . Yii::t('common','feature_content_type')
            . $split . Yii::t('common','window_mode'). $split . Yii::t('common','feature_content')
            . $split . Yii::t('common','default_time'). $split . Yii::t('common','component_default_credit');
        $data = array();

        $i = 0;
        foreach ($results as $r) {
            $data[$i][0] = $r->title;
            $data[$i][1] = $r->component_code;
            $data[$i][2] = $r->getComponentTypeText();
            $data[$i][3] = $r->getComponentCategoryText();
            $data[$i][4] = str_replace(",","，",$r->file_type);

            $data[$i][5] = $r->getDisplayPCText();
            $data[$i][6] = $r->getDisplayMobileText();
            $data[$i][7] = $r->getAllowDownloadText();
            $data[$i][8] = $r->getTransferTypeText();
            $data[$i][9] = $r->getNeedUploadText();
            $data[$i][10] = $r->getAllowReuseText();

            $data[$i][11] = $r->getFeatureContentTypeText();
            $data[$i][12] = $r->getWindowModeText();
            $data[$i][13] = str_replace(",","，",$r->feature_content);
            $data[$i][14] = $r->default_time;
            $data[$i][15] = $r->default_credit;
            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}