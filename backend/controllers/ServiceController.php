<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/10/20
 * Time: 17:05
 */

namespace backend\controllers;

use common\models\framework\FwService;
use common\models\framework\FwServiceLog;
use common\base\BaseActiveRecord;
use common\helpers\TExportHelper;
use common\helpers\TTimeHelper;
use Yii;
use backend\base\BaseBackController;
use backend\services\ServiceLogService;
use backend\services\ServiceService;
use components\widgets\TPagination;
use yii\web\NotFoundHttpException;
use yii\base\Exception;
use yii\web\Response;

class ServiceController extends BaseBackController
{
    public $layout = 'frame';

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionIndexList()
    {
        $this->layout = 'list';

        $pageSize = $this->defaultPageSize;
        $service = new ServiceService();
        $result = $service->getServiceList();

        $dataProvider = $service->search(Yii::$app->request->queryParams);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
        $dataProvider->setPagination($page);

        return $this->render('index-list', [
            'page' => $page,
            'searchModel' => $service,
            'dataProvider' => $dataProvider,
            'pageSize' => $pageSize,
            'result' => $result,
        ]);
    }
    public function actionIndexView($id)
    {
        return $this->renderAjax('index-view', [
            'model' => $this->findServiceModel($id),
        ]);
    }
    public function actionLog()
    {
        return $this->render('log');
    }

    public function actionList()
    {
        $this->layout = 'list';

        $pageSize = $this->defaultPageSize;
        $serviceService = new ServiceService();
        $servicelist = $serviceService->getServiceList();

        $service = new ServiceLogService();
        $dataProvider = $service->search(Yii::$app->request->queryParams);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
        $dataProvider->setPagination($page);

        return $this->render('list', [
            'page' => $page,
            'searchModel' => $service,
            'dataProvider' => $dataProvider,
            'pageSize' => $pageSize,
            'serviceList' => $servicelist,
        ]);
    }

    public function actionView($id)
    {
        return $this->renderAjax('view', [
            'model' => $this->findLogModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new FwService();
        $model->setScenario("manage");
        
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $service = new ServiceService();

            if ($model->validate()) {
                if ($service->isExistSameServiceCode($model->kid, $model->service_code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'service_code')])];
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
        } else {
            $model->service_status = FwService::SERVICE_STATUS_RUNNING;
            $model->is_log = FwService::NO;
            $model->is_allow_restart = FwService::NO;
            $model->restart_cycle = FwService::RESTART_CYCLE_NONE;
            $model->service_type = FwService::SERVICE_TYPE_NORMAL;
            return $this->renderAjax('index-create', [
                'model' => $model,
            ]);
        }
    }
    public function actionUpdate($id)
    {
        $model = $this->findServiceModel($id);
        $model->setScenario("manage");
        
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $service = new ServiceService();

            if ($model->validate()) {
                if ($service->isExistSameServiceCode($model->kid, $model->service_code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'service_code')])];
                } else if ($model->save()) {
                    return ['result' => 'success'];
                } else {
                    return ['result' => 'failure'];
                }
            }
            else {
                return ['result' => 'failure'];
            }
        } else {
            return $this->renderAjax('index-update', [
                'model' => $model,
            ]);
        }

    }
    public function actionDelete($id)
    {
        try {
            if ( Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model = $this->findServiceModel($id);

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
     * Finds the TagCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwServiceLog the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findLogModel($id)
    {
        if (($model = FwServiceLog::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'data_not_exist'));
        }
    }

    /**
     * Finds the TagCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwService the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findServiceModel($id)
    {
        if (($model = FwService::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'data_not_exist'));
        }
    }

    public function actionExport(){
        $service = new ServiceLogService();
        $results = $service->search(Yii::$app->request->queryParams)->query->all();

        $split = ",";
        $header =  Yii::t('common','service_name') . $split . Yii::t('common','action_status')
            . $split . Yii::t('common','service_log') . $split . Yii::t('common','action_time');
        $data = array();
        $i = 0;
//
//        'fwService.service_name',
// 'attribute' => 'action_status',
//            'value' => function ($model, $key, $index, $cloumn) {
//            return $model->getActionStatusText();
//        },
//
//        service_log
        foreach ($results as $r) {
            $data[$i][0] = $r->getServiceName();
            $data[$i][1] = $r->getActionStatusText();
            $data[$i][2] = str_replace(array("\r\n","\n", "\r"),'',str_replace(",","，",$r->service_log));
            $data[$i][3] = TTimeHelper::toDateTime($r->created_at);
            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }


    public function actionIndexExport(){
        $service = new ServiceService();
        $results = $service->search(Yii::$app->request->queryParams)->query->all();

        $split = ",";
        $header =  Yii::t('common','service_code') . $split . Yii::t('common','service_name')
            . $split . Yii::t('common','is_log'). $split . Yii::t('common','service_status')
            . $split . Yii::t('common','service_run_at');
        $data = array();
        $i = 0;

        foreach ($results as $r) {
            $data[$i][0] = $r->service_code;
            $data[$i][1] = $r->service_name;
            $data[$i][2] = $r->getIsLogText();
            $data[$i][3] = $r->getServiceStatusText();
            $data[$i][4] = $r->run_at;
            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}