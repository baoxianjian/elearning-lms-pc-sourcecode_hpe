<?php

namespace backend\controllers;

use backend\base\BaseBackController;
use backend\services\CompanyDictionaryService;
use backend\services\CompanyService;
use backend\services\DictionaryCategoryService;
use backend\services\WechatTemplateService;
use common\models\framework\FwCompany;
use common\models\framework\FwDictionary;
use common\models\framework\FwDictionaryCategory;
use common\models\framework\FwWechatTemplate;
use common\services\framework\RbacService;
use common\helpers\TExportHelper;
use components\widgets\TPagination;
use Yii;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * CompanyDictionaryController implements the CRUD actions for FwDictionary model.
 */
class CompanyDictionaryController extends BaseBackController
{

    public $layout = 'frame';

    public function actionList()
    {
        $this->layout = 'list';

        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");
        $includeSubNode = Yii::$app->request->getQueryParam("includeSubNode");

        if ($treeNodeKid == '-1') {
            $treeNodeKid = '';
        }

        $forceShowAll = 'False';
        $pageSize = $this->defaultPageSize;

        if (Yii::$app->request->getQueryParam('PageShowAll') != null && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
            $forceShowAll = 'True';
        }
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }

        $service = new CompanyDictionaryService();
        $dataProvider = $service->search(Yii::$app->request->queryParams,$treeNodeKid,$includeSubNode);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
        $dataProvider->setPagination($page);

        $rbacService = new RbacService();
        $userId = Yii::$app->user->getId();
        $isSpecialUser = $rbacService->isSpecialUser($userId);

        $dictionaryCategoryService = new DictionaryCategoryService();
        $dictionaryCategoryModel = $dictionaryCategoryService->getAllDictionaryCategory(FwDictionaryCategory::CATE_TYPE_COMPANY);

        return $this->render('list', [
            'page' => $page,
            'searchModel' => $service,
            'dataProvider' => $dataProvider,
            'selectNodeId' => $treeNodeKid,
            'includeSubNode' => $includeSubNode,
            'forceShowAll' => $forceShowAll,
            'pageSize' => $pageSize,
            'isSpecialUser' => $isSpecialUser,
            'dictionaryCategoryModel'=>$dictionaryCategoryModel,
        ]);
    }

    /**
     * Finds the FwWechatTemplate model based on its primary key value.
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

    public function actionExport(){

        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");
        $includeSubNode = Yii::$app->request->getQueryParam("includeSubNode");

        if ($treeNodeKid == '-1')
            $treeNodeKid = '';

        $service = new CompanyDictionaryService();
        $results = $service->search(Yii::$app->request->queryParams,$treeNodeKid,$includeSubNode)->query->all();

        $split = ",";
        $header = Yii::t('common','dictionary_cate_name') . $split.Yii::t('common','dictionary_code')
            . $split . Yii::t('common','dictionary_name')
            . $split . Yii::t('common','dictionary_value')
            . $split . Yii::t('common','status');
        $data = array();
        $i = 0;

        foreach ($results as $r) {
            $data[$i][0] = $r->DictionaryCategoryName;
            $data[$i][1] = $r->dictionary_code;
            $data[$i][2] = $r->dictionary_name;
            $data[$i][3] = $r->dictionary_value;
            $data[$i][4] = $r->getStatusText();

            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}