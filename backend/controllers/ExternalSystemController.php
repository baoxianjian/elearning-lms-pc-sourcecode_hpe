<?php


namespace backend\controllers;

use backend\services\ExternalSystemService;
use common\models\framework\FwExternalSystem;
use common\base\BaseActiveRecord;
use common\helpers\TExportHelper;
use components\widgets\TPagination;
use Yii;
use backend\base\BaseBackController;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ExternalSystemController  extends BaseBackController{

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

        $service = new ExternalSystemService();
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
     * Displays a single FwExternalSystem model.
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
     * Creates a new FwExternalSystem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
//        $this->layout = 'modalWin';
        $model = new FwExternalSystem();
        $model->setScenario("manage");
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model->needReturnKey = true;
            if ($model->save()) {
                return ['result' => 'success', 'kid' => $model->kid];
            }
            else {
                return ['result' => 'failure'];
            }
//            return $this->redirect(['view', 'id' => $model->kid]);
        } else {
            $model->encrypt_mode = FwExternalSystem::ENCRYPT_MODE_NONE;
            $model->security_mode = FwExternalSystem::SECURITY_MODE_PLAIN;
            $model->service_mode = FwExternalSystem::SERVICE_MODE_SERVER;
            $model->status = FwExternalSystem::STATUS_FLAG_NORMAL;
            $model->system_key_is_single = FwExternalSystem::YES;
            $model->duration = 0;
            $model->limit_count = 0;

            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Updates an existing FwExternalSystem model.
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

            if ($model->save()) {
                return ['result' => 'success'];
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
     * Deletes an existing FwExternalSystem model.
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

                if (isset($model) && $model != null && $model->delete()) {
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
     * Finds the FwExternalSystem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwExternalSystem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FwExternalSystem::findOne($id)) !== null) {
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
                FwExternalSystem::removeFromCacheByKid($key);
            }

            $kids = rtrim($kids,",");

            $model = new FwExternalSystem();

            Yii::$app->response->format = Response::FORMAT_JSON;
            try {

                $model->deleteAllByKid($kids);

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

    public function actionExport(){
        $service = new ExternalSystemService();
        $results = $service->search(Yii::$app->request->queryParams)->query->all();

        $split = ",";
        $header =  Yii::t('common','system_code') . $split . Yii::t('common','system_name')
            . $split . Yii::t('common','system_key'). $split . Yii::t('common','system_key_is_single')
            . $split . Yii::t('common','encoding_key'). $split . Yii::t('common','security_mode')
            . $split . Yii::t('common','encrypt_mode'). $split . Yii::t('common','service_mode')
            . $split . Yii::t('common','duration'). $split . Yii::t('common','limit_count')
            . $split . Yii::t('common','memo1'). $split . Yii::t('common','memo2')
            . $split . Yii::t('common','status');
        $data = array();
        $i = 0;
        foreach ($results as $r) {
            $data[$i][0] = $r->system_code;
            $data[$i][1] = $r->system_name;
            $data[$i][2] = $r->system_key;
            $data[$i][3] = $r->getSystemKeyIsSingleText();
            $data[$i][4] = $r->encoding_key;
            $data[$i][5] = $r->getSecurityModeText();
            $data[$i][6] = $r->getEncryptModeText();
            $data[$i][7] = $r->getSecurityModeText();
            $data[$i][8] = $r->duration;
            $data[$i][9] = $r->limit_count;
            $data[$i][10] = $r->memo1;
            $data[$i][11] = $r->memo2;
            $data[$i][12] = $r->getStatusText();
            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}