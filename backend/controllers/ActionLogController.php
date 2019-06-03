<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 4/8/2015
 * Time: 3:01 PM
 */

namespace backend\controllers;


use backend\base\BaseBackController;
use backend\services\ActionLogFilterService;
use common\services\framework\ActionLogService;
use common\helpers\TExportHelper;
use common\helpers\TTimeHelper;
use components\widgets\TPagination;
use Yii;

class ActionLogController extends BaseBackController{

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

        $service = new ActionLogService();
        $dataProvider = $service->search(Yii::$app->request->queryParams,$treeNodeKid,$includeSubNode);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize'=>$pageSize,'totalCount'=>$count]);
        $dataProvider->setPagination($page);

        $actionLogFilterService = new ActionLogFilterService();
        $actionLogFilterModel = $actionLogFilterService->getAllActionLogFilter();

        return $this->render('list', [
            'page' => $page,
            'searchModel' => $service,
            'dataProvider' => $dataProvider,
            'selectNodeId'=>$treeNodeKid,
            'includeSubNode'=>$includeSubNode,
            'forceShowAll'=>$forceShowAll,
            'pageSize'=>$pageSize,
            'actionLogFilterModel'=>$actionLogFilterModel
        ]);
    }

    public function actionExport(){
        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");
        $includeSubNode = Yii::$app->request->getQueryParam("includeSubNode");


        if ($treeNodeKid == '-1')
            $treeNodeKid = '';

        $service = new ActionLogService();
        $results = $service->search(Yii::$app->request->queryParams,$treeNodeKid,$includeSubNode)->query->all();

        $split = ",";
        $header =  Yii::t('common','user_name') . $split . Yii::t('common','real_name')
            . $split . Yii::t('common','relate_{value}',['value'=>Yii::t('common','company')]). $split . Yii::t('common','action_name')
            . $split . Yii::t('common','action_ip'). $split . Yii::t('common','action_time');
        $data = array();

        $i = 0;
        foreach ($results as $r) {
            $data[$i][0] = $r->getUserName();
            $data[$i][1] = $r->getRealName();
            $data[$i][2] = $r->getOrgnizationName();
            $data[$i][3] = $r->getFilterName();
            $data[$i][4] = $r->action_ip;
            $data[$i][5] = TTimeHelper::toDateTime($r->created_at);
            $i++;
        }

        TExportHelper::exportCsv($header, $data, "output", $split);
    }

}