<?php


namespace backend\controllers;

use backend\services\ActionLogFilterService;
use common\models\framework\FwActionLogFilter;
use common\models\framework\FwService;
use common\base\BaseActiveRecord;
use common\helpers\TExportHelper;
use components\widgets\TPagination;
use Yii;
use backend\base\BaseBackController;
use yii\base\Exception;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ActionLogFilterController  extends BaseBackController{

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

        if (Yii::$app->request->getQueryParam('PageShowAll') != null
            && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
            $forceShowAll = 'True';
        }
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }

        $service = new ActionLogFilterService();
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
     * Displays a single FwActionLogFilter model.
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
     * Creates a new FwActionLogFilter model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
//        $this->layout = 'modalWin';
        $model = new FwActionLogFilter();
        $model->setScenario("manage");

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $service = new ActionLogFilterService();

            if ($model->validate()) {
                if ($service->isExistSameFilterCode($model->kid, $model->filter_code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'filter_code')])];
                } else {
                    if ($model->save()) {
                        return ['result' => 'success'];
                    } else {
                        return ['result' => 'failure'];
                    }
                }
            }
            else {
                return ['result' => 'failure'];
            }
//            return $this->redirect(['view', 'id' => $model->kid]);
        } else {
            $model->system_flag = FwActionLogFilter::SYSTEM_FLG_ELN_BACKEND;
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Updates an existing FwActionLogFilter model.
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
            $service = new ActionLogFilterService();

            if ($model->validate()) {
                if ($service->isExistSameFilterCode($model->kid, $model->filter_code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'filter_code')])];
                } else if ($model->save()) {
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
     * Deletes an existing FwActionLogFilter model.
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
     * Finds the FwActionLogFilter model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwActionLogFilter the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FwActionLogFilter::findOne($id)) !== null) {
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

                FwActionLogFilter::removeFromCacheByKid($key);

                if ($firstKid == "")
                {
                    $firstKid = $key;
                }
            }

            $kids = rtrim($kids,",");

            $model = new FwActionLogFilter();

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


    public function actionStatus($id, $status)
    {
        try {
            if ( Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model = $this->findModel($id);

                $model->status = $status;

                if ($model->save()) {
                    return ['result' => 'success'];
                }
                else {
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

    public function actionExport(){
        $service = new ActionLogFilterService();
        $results = $service->search(Yii::$app->request->queryParams)->query->all();

        $split = ",";
        $header =  Yii::t('common','filter_code') . $split . Yii::t('common','filter_name')
            . $split . Yii::t('common','controller_id'). $split . Yii::t('common','action_id')
            . $split . Yii::t('common','system_flag'). $split . Yii::t('common','status');
        $data = array();
        $i = 0;
        foreach ($results as $r) {
            $data[$i][0] = $r->filter_code;
            $data[$i][1] = $r->filter_name;
            $data[$i][2] = $r->controller_id;
            $data[$i][3] = $r->action_id;
            $data[$i][4] = $r->system_flag;
            $data[$i][5] = $r->getStatusText();
            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}