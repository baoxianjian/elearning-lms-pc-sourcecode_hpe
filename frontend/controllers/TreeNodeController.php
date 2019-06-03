<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/9/15
 * Time: 11:31 AM
 */

namespace frontend\controllers;

use common\models\framework\FwPrimaryKey;
use common\models\treemanager\FwTreeNode;
use common\models\treemanager\FwTreeType;
use common\services\framework\OrgnizationService;
use common\services\learning\CourseCategoryService;
use common\services\learning\CoursewareCategoryService;
use common\services\framework\RbacService;
use components\widgets\TPagination;
use Faker\Provider\it_IT\Company;
use Yii;
use yii\helpers\Url;
use frontend\base\BaseFrontController;
use common\services\framework\TreeNodeService;
use yii\base\Exception;
use yii\bootstrap\ActiveForm;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TreeNodeController extends BaseFrontController{

    public $layout  = 'frame';

    public function actionTreeData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $service = new TreeNodeService();
        $result = $service->listTreeData(null,null,true,false);//管理页面不能使用Session数据

        return $result;
    }

    public function actionTree()
    {
        $treeType = Yii::$app->request->getQueryParam("TreeType");
        $companyId = Yii::$app->request->getQueryParam("companyId");
        $contentName = Yii::$app->request->getQueryParam("ContentName");
        $ListRoute = Yii::$app->request->getQueryParam("ListRoute");
        $includeRoot = Yii::$app->request->getQueryParam("IncludeRoot");
        $mergeRoot = Yii::$app->request->getQueryParam("MergeRoot");
        $showContentCount = Yii::$app->request->getQueryParam("ShowContentCount");
        $openAllNode = Yii::$app->request->getQueryParam("OpenAllNode");
        $DeleteNode = Yii::$app->request->getQueryParam("DeleteNode");
        $EditNode = Yii::$app->request->getQueryParam("EditNode");
        $ListRouteParams = Yii::$app->request->getQueryParam("ListRouteParams");

//        $this->layout = 'left';
        return $this->renderAjax('tree',[
            'TreeType' => $treeType,
            'companyId' => $companyId,
            'ContentName' => $contentName,
            'ListRoute' => $ListRoute,
            'needRegister'=> 'True',
            'includeRoot'=> $includeRoot,
            'mergeRoot'=> $mergeRoot,
            'showContentCount' => $showContentCount,
            'openAllNode' => $openAllNode,
            'DeleteNode' => $DeleteNode,
            'EditNode' => $EditNode,
            'ListRouteParams' => $ListRouteParams,
        ]);
    }


    public function actionSelectTree()
    {
//        $this->layout = 'modalWin';
        $treeDataUrl = Yii::$app->request->getQueryParam('treeDataUrl');
        $treeFlag = Yii::$app->request->getQueryParam('treeFlag');
        $needRegister = Yii::$app->request->getQueryParam('needRegister');
        $includeRoot = Yii::$app->request->getQueryParam("IncludeRoot");
        $mergeRoot = Yii::$app->request->getQueryParam("MergeRoot");
        $showContentCount = Yii::$app->request->getQueryParam("ShowContentCount");
        $openAllNode = Yii::$app->request->getQueryParam("OpenAllNode");

        return $this->renderAjax('select-tree',[
            'treeDataUrl'=>$treeDataUrl,
            'treeFlag'=>$treeFlag,
            'needRegister'=>$needRegister,
            'includeRoot'=> $includeRoot,
            'mergeRoot'=> $mergeRoot,
            'showContentCount' => $showContentCount,
            'openAllNode' => $openAllNode,
        ]);
    }

    public function actionMultiSelectTree()
    {
//        $this->layout = 'modalWin';
        $treeDataUrl = Yii::$app->request->getQueryParam('treeDataUrl');
        $treeState = Yii::$app->request->getQueryParam('treeState');
        $treeFlag = Yii::$app->request->getQueryParam('treeFlag');
        $needRegister = Yii::$app->request->getQueryParam('needRegister');
        $includeRoot = Yii::$app->request->getQueryParam("IncludeRoot");
        $mergeRoot = Yii::$app->request->getQueryParam("MergeRoot");
        $showContentCount = Yii::$app->request->getQueryParam("ShowContentCount");
        $openAllNode = Yii::$app->request->getQueryParam("OpenAllNode");

        return $this->renderAjax('multi-select-tree',[
            'treeDataUrl'=>$treeDataUrl,
            'treeState'=>$treeState,
            'treeFlag'=>$treeFlag,
            'needRegister'=>$needRegister,
            'includeRoot'=> $includeRoot,
            'mergeRoot'=> $mergeRoot,
            'showContentCount' => $showContentCount,
            'openAllNode' => $openAllNode,
        ]);
    }
}