<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 4/8/2015
 * Time: 3:01 PM
 */

namespace backend\controllers;


use backend\base\BaseBackController;
use backend\services\OrgnizationService;
use backend\services\RoleService;
use common\services\framework\TreeNodeService;
use backend\services\UserService;
use common\models\framework\FwOrgnization;
use common\models\framework\FwUser;
use common\models\framework\FwUserRole;
use common\helpers\TArrayHelper;
use common\helpers\TExportHelper;
use common\helpers\TTimeHelper;
use components\widgets\TPagination;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class UserOnlineController extends BaseBackController{

    public $layout  = 'frame';

    public function actionList()
    {
        $this->layout = 'list';

        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");
        $includeSubNode = Yii::$app->request->getQueryParam("includeSubNode");


        if ($treeNodeKid == '-1')
            $treeNodeKid = '';

//        if ($includeSubNode == '1') {
//            $treeNodeService = new TreeNodeService();
//            $treeTypeId = $treeNodeService->getTreeTypeId('orgnization');
//            if ($treeNodeKid != '') {
//                $treeNodeModel = $treeNodeService->findOne($treeNodeKid);
//
//                $treeNodeIdList = $treeNodeService->getAllNodeIdIncludeSub($treeNodeKid, $treeTypeId, $treeNodeModel->node_id_path . $treeNodeKid . "/%", self::STATUS_FLAG_NORMAL);
//            } else {
//                $treeNodeIdList = $treeNodeService->getAllNodeIdIncludeSub('', $treeTypeId, "/%", self::STATUS_FLAG_NORMAL);
//            }
//        }
//        else
//        {
//            $treeNodeIdList = $treeNodeKid;
//        }

        $forceShowAll = 'False';
        $pageSize = $this->defaultPageSize;

        if (Yii::$app->request->getQueryParam('PageShowAll') != null && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
            $forceShowAll = 'True';
        }
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }

        $service = new UserService();
        $dataProvider = $service->searchOnline(Yii::$app->request->queryParams,$treeNodeKid,$includeSubNode);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize'=>$pageSize,'totalCount'=>$count]);
        $dataProvider->setPagination($page);
        return $this->render('list', [
            'page' => $page,
            'searchModel' => $service,
            'dataProvider' => $dataProvider,
            'selectNodeId'=>$treeNodeKid,
            'includeSubNode'=>$includeSubNode,
            'forceShowAll'=>$forceShowAll,
            'pageSize'=>$pageSize
        ]);
    }


    public function actionExport(){
        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");
        $includeSubNode = Yii::$app->request->getQueryParam("includeSubNode");


        if ($treeNodeKid == '-1')
            $treeNodeKid = '';

        $service = new UserService();
        $results = $service->searchOnline(Yii::$app->request->queryParams,$treeNodeKid,$includeSubNode)->query->all();

        $split = ",";
        $header =  Yii::t('common','user_name') . $split . Yii::t('common','real_name')
            . $split . Yii::t('common','relate_{value}',['value'=>Yii::t('common','orgnization')]). $split . Yii::t('common','last_action_at')
            . $split . Yii::t('common','idle_minute'). $split . Yii::t('common','status');
        $data = array();

        $i = 0;
        foreach ($results as $r) {
            $data[$i][0] = $r->user_name;
            $data[$i][1] = $r->real_name;
            $data[$i][2] = $r->getOrgnizationName();
            $data[$i][3] = TTimeHelper::toDateTime($r->last_action_at);
            $data[$i][4] = round((time() - $r->last_action_at) / 60);
            $data[$i][5] = $r->getStatusText();
            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}