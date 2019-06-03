<?php


namespace backend\controllers;

use common\models\treemanager\FwTreeType;
use common\base\BaseActiveRecord;
use common\helpers\TExportHelper;
use components\widgets\TPagination;
use Yii;
use backend\base\BaseBackController;
use backend\services\TreeTypeService;
use yii\base\Exception;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TreeTypeController  extends BaseBackController{

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

        $service = new TreeTypeService();
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
     * Displays a single FwTreeType model.
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
     * Creates a new FwTreeType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
//        $this->layout = 'modalWin';
        $model = new FwTreeType();
        $model->setScenario("manage");

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $service = new TreeTypeService();

            if ($model->validate()) {
                if ($service->isExistSameTreeTypeCode($model->kid, $model->tree_type_code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'tree_type_code')])];
                } else {
                    $oldSequenceNumber = $service->findMaxSequenceNumber();
                    $model->needReturnKey = true;
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
//            return $this->redirect(['view', 'id' => $model->kid]);
        } else {
            $service = new TreeTypeService();
            $model->sequence_number = $service->findMaxSequenceNumber();
            $model->limitation = FwTreeType::LIMITATION_NONE;
            $model->code_gen_way = FwTreeType::CODE_GEN_WAY_SYSTEM;
            $model->max_level = 0;
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Updates an existing FwTreeType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
//        $this->layout = 'modalWin';
        $model = $this->findModel($id);
        $model->setScenario("manage");

        $oldSequenceNumber = $model->sequence_number;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $service = new TreeTypeService();

            if ($model->validate()) {
                $model->needReturnKey = true;
                if ($service->isExistSameTreeTypeCode($model->kid, $model->tree_type_code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'tree_type_code')])];
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
//            return $this->redirect(['view', 'id' => $model->kid]);
        } else {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }

    }

    /**
     * Deletes an existing FwTreeType model.
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
//                    $key = $model->kid;
                    if ($model->delete()) {
                        $service = new TreeTypeService();
                        $service->updateSequenceNumber($id, $newSequenceNumber, $newSequenceNumber, "1");
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
     * Finds the FwTreeType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwTreeType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FwTreeType::findOne($id)) !== null) {
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

                if ($firstKid == "")
                {
                    $firstKid = $key;
                }
            }

            $kids = rtrim($kids,",");

            $model = new FwTreeType();

            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
                $tempModel = $this->findModel($firstKid);
                $sequenceNumber = $tempModel->sequence_number;

                $model->deleteAllByKid($kids);


                $service = new TreeTypeService();
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

    public function actionExport(){
        $service = new TreeTypeService();
        $results = $service->search(Yii::$app->request->queryParams)->query->all();

        $split = ",";
        $header = Yii::t('common','tree_type_code')
            . $split . Yii::t('common','tree_type_name'). $split . Yii::t('common','code_gen_way'). $split . Yii::t('common','code_prefix')
            . $split . Yii::t('common','limitation'). $split . Yii::t('common','max_level');
        $data = array();
        $i = 0;
        foreach ($results as $r) {
            $data[$i][0] = $r->tree_type_code;
            $data[$i][1] = $r->tree_type_name;
            $data[$i][2] = $r->getCodeGenWayText();
            $data[$i][3] = $r->code_prefix;
            $data[$i][4] = $r->getLimitationText();
            $data[$i][5] = $r->max_level;
            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}