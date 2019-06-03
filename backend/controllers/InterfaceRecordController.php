<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/10/20
 * Time: 17:05
 */

namespace backend\controllers;


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
use backend\services\InterfaceRecordService;
use common\models\boe\BoeInterfaceRecord;


class InterfaceRecordController extends BaseBackController
{
    public $layout = 'frame';

  
   
    public function actionLog()
    {
        return $this->render('log');
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
       
        $service = new InterfaceRecordService();
        $dataProvider = $service->search(Yii::$app->request->queryParams);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
        $dataProvider->setPagination($page);

        return $this->render('list', [
            'page' => $page,
            'searchModel' => $service,
            'dataProvider' => $dataProvider,
            'pageSize' => $pageSize,
            'forceShowAll'=>$forceShowAll,
        ]);
    }

    public function actionView($id)
    {
        return $this->renderAjax('view', [
            'model' => $this->findLogModel($id),
        ]);
    }

   
   

    
    protected function findLogModel($id)
    {
        if (($model = BoeInterfaceRecord::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'data_not_exist'));
        }
    }

   

    public function actionExport(){
        $service = new InterfaceRecordService();
        $results = $service->search(Yii::$app->request->queryParams)->query->all();

        $split = ",";
        $header =  Yii::t('common','record_id') 
       		. $split . Yii::t('common','bo_type')
            . $split . Yii::t('common','change_type')
            . $split . Yii::t('common','handle_result')
            . $split . Yii::t('common','operate_time')
            . $split . Yii::t('common','request_soap')
            . $split . Yii::t('common','response_soap')
            . $split . Yii::t('common','error_message');
        $data = array();
        $i = 0;

        foreach ($results as $r) {
        	$data[$i][0] = $r->kid;
            $data[$i][1] = $r->getBoType();
            $data[$i][2] = $r->getChangeType();
            $data[$i][3] = $r->getHandleResult();
            $data[$i][4] = $r->operate_time;
            $data[$i][5] = str_replace(array("\r\n","\n", "\r"),'',$r->request_soap);
            $data[$i][6] = str_replace(array("\r\n","\n", "\r"),'',$r->response_soap);
            $data[$i][7] = str_replace(array("\r\n","\n", "\r"),'',$r->error_message);
            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}