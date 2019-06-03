<?php

namespace backend\controllers;

use backend\base\BaseBackController;
use backend\services\TagCategoryService;
use common\models\framework\FwTagCategory;
use common\base\BaseActiveRecord;
use common\helpers\TExportHelper;
use components\widgets\TPagination;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\base\Exception;
/**
 * TagCategoryController implements the CRUD actions for FwTagCategory model.
 */
class TagCategoryController extends BaseBackController
{
    public $layout  = 'frame';

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionList()
    {
        $this->layout = 'list';
        $service = new TagCategoryService();

        $forceShowAll = 'False';
        $pageSize = $this->defaultPageSize;

        if (Yii::$app->request->getQueryParam('PageShowAll') != null && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
            $forceShowAll = 'True';
        }
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }

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
     * Displays a single TagCategory model.
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
     * Creates a new TagCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
//        $this->layout = 'modalWin';
        $model = new FwTagCategory();
        $model->setScenario("manage");

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $service = new TagCategoryService();

            if ($model->validate()) {
                if ($service->isExistSameTagCategoryCode($model->kid, $model->cate_code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'tag_cate_code')])];
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
            $service = new TagCategoryService();
            $model->sequence_number = $service->findMaxSequenceNumber();
            $model->limitation = FwTagCategory::LIMITATION_NONE;
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Updates an existing TagCategory model.
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
            $service = new TagCategoryService();

            if ($model->validate()) {
                $model->needReturnKey = true;
                if ($service->isExistSameTagCategoryCode($model->kid, $model->cate_code)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'cate_code')])];
                } else if ($model->save()) {
                    $newSequenceNumber = $model->sequence_number;
                    if ($oldSequenceNumber != $newSequenceNumber) {

                        $service->updateSequenceNumber($model->kid, $oldSequenceNumber, $newSequenceNumber, "0");
                    }
                    return ['result' => 'success'];
                } else {
                    return ['result' => 'failure'];
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
     * Deletes an existing TagCategory model.
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
                    if ($model->delete()) {
                        $service = new TagCategoryService();
                        $service->updateSequenceNumber($id, $newSequenceNumber, $newSequenceNumber, "1");
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
     * Finds the TagCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwTagCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FwTagCategory::findOne($id)) !== null) {
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
                FwTagCategory::removeFromCacheByKid($key);

                if ($firstKid == "")
                {
                    $firstKid = $key;
                }
            }

            $kids = rtrim($kids,",");

            $model = new FwTagCategory();

            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
                $tempModel = $this->findModel($firstKid);
                $sequenceNumber = $tempModel->sequence_number;

                $model->deleteAllByKid($kids);


                $service = new TagCategoryService();
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
        $service = new TagCategoryService();
        $results = $service->search(Yii::$app->request->queryParams)->query->all();

        $split = ",";
        $header =  Yii::t('common','tag_cate_code') . $split . Yii::t('common','tag_cate_name')
            . $split . Yii::t('common','limitation');
        $data = array();
        $i = 0;
        foreach ($results as $r) {
            $data[$i][0] = $r->cate_code;
            $data[$i][1] = $r->cate_name;
            $data[$i][2] = $r->getLimitationText();
            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}
