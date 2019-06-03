<?php

namespace backend\controllers;

use backend\base\BaseBackController;
use backend\services\TagCategoryService;
use backend\services\TagService;
use common\models\framework\FwCompany;
use common\models\framework\FwTag;
use common\models\framework\FwUser;
use common\services\framework\RbacService;
use common\services\framework\UserCompanyService;
use common\base\BaseActiveRecord;
use common\helpers\TExportHelper;
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
 * TagController implements the CRUD actions for FwTag model.
 */
class TagController extends BaseBackController
{
    public $layout  = 'frame';

    public function actionIndex()
    {
//        TLoggerHelper::Message("test");
        return $this->render('index');
    }

    public function actionList()
    {
        $this->layout = 'list';
        $tagCategoryService = new TagCategoryService();
        $tagCategoryModel = $tagCategoryService->getAllTagCategory();

        $forceShowAll = 'False';
        $pageSize = $this->defaultPageSize;

        if (Yii::$app->request->getQueryParam('PageShowAll') != null && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
            $forceShowAll = 'True';
        }
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }

        $userId = Yii::$app->user->getId();
        $userCompanyService = new UserCompanyService();
        $companyModel = $userCompanyService->getManagedListByUserId($userId, null, false);

        $service = new TagService();
        $dataProvider = $service->search(Yii::$app->request->queryParams);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize'=>$pageSize,'totalCount'=>$count]);
        $dataProvider->setPagination($page);
        return $this->render('list', [
            'page' => $page,
            'searchModel' => $service,
            'tagCategoryModel'=>$tagCategoryModel,
            'companyModel'=>$companyModel,
            'dataProvider' => $dataProvider,
            'forceShowAll'=>$forceShowAll,
            'pageSize'=>$pageSize
        ]);
    }

    /**
     * Displays a single FwTag model.
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
     * Creates a new FwTag model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
//        $this->layout = 'modalWin';
        $model = new FwTag();
        $model->setScenario("super_manage");
        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();
            $rbacService = new RbacService();

            if (!$rbacService->isSpecialUser($userId)) {
                $model->company_id = FwUser::findOne($userId)->company_id;
                $model->setScenario("manage");
            }
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->company_id == '')
            {
                $model->company_id = null;
            }
            $model->reference_count = 0;

            $service = new TagService();

            if ($model->validate()) {
                $tagId = $model->kid;
                $tagCategoryId = $model->tag_category_id;
                $companyId = $model->company_id;
                if ($service->isExistSameTagValue($tagId,$companyId, $tagCategoryId, $model->tag_value)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'tag_cate_code')])];
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

            $userCompanyService = new UserCompanyService();
            $companyModel = $userCompanyService->getManagedListByUserId($userId, null, false);

            $tagCategoryService = new TagCategoryService();
            $tagCategoryModel = $tagCategoryService->getAllTagCategory();

            return $this->renderAjax('create', [
                'model' => $model,
                'tagCategoryModel' => $tagCategoryModel,
                'companyModel' => $companyModel
            ]);
        }
    }


    /**
     * Updates an existing FwTag model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
//        $this->layout = 'modalWin';
        $model = $this->findModel($id);
        $model->setScenario("manage");
        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();
            $rbacService = new RbacService();

            if (!$rbacService->isSpecialUser($userId)) {
                $model->company_id = FwUser::findOne($userId)->company_id;
                $model->setScenario("manage");
            }
        }


        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $service = new TagService();

            if ($model->validate()) {
                $tagId = $model->kid;
                $tagCategoryId = $model->tag_category_id;
                $companyId = $model->company_id;

                if ($service->isExistSameTagValue($tagId, $companyId, $tagCategoryId, $model->tag_value)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'tag_cate_code')])];
                } else if ($model->save()) {
                    return ['result' => 'success'];
                } else {
                    return ['result' => 'failure'];
                }
            } else {
                return ['result' => 'failure'];
            }
//            return $this->redirect(['view', 'id' => $model->kid]);
        } else {

            $userCompanyService = new UserCompanyService();
            $companyModel = $userCompanyService->getManagedListByUserId($userId, null, false);

            $tagCategoryService = new TagCategoryService();
            $tagCategoryModel = $tagCategoryService->getAllTagCategory();

            return $this->renderAjax('update', [
                'model' => $model,
                'tagCategoryModel' => $tagCategoryModel,
                'companyModel' => $companyModel
            ]);
        }
    }

    /**
     * Deletes an existing FwTag model.
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
                    $tagService = new \common\services\framework\TagService();
                    $tagService->stopRelationshipByTagId($id);

                    $model->delete();

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
     * Finds the FwTag model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwTag the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FwTag::findOne($id)) !== null) {
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
                FwTag::removeFromCacheByKid($key);
            }

            $kids = rtrim($kids,",");

            $model = new FwTag();

            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
                $tagService = new \common\services\framework\TagService();
                $tagService->stopRelationshipByTagIdList($kids);

                $model->deleteAllByKid($kids);

//                $tagService->ResetReferenceCount($kids);

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
        $service = new TagService();
        $results = $service->search(Yii::$app->request->queryParams)->query->all();

        $split = ",";
        $header =  Yii::t('common','tag_cate_name') . $split . Yii::t('common','relate_{value}',['value'=>Yii::t('common','company')])
            . $split . Yii::t('common','tag_value'). $split . Yii::t('common','reference_count');
        $data = array();
        $i = 0;

        foreach ($results as $r) {
            $data[$i][0] = $r->getTagCategoryName();
            $data[$i][1] = $r->getCompanyName();
            $data[$i][2] = $r->tag_value;
            $data[$i][3] = $r->reference_count;
            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}
