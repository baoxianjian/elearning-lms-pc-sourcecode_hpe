<?php
/**
 * 积分控制器
 * author: 包显建
 * date: 2016/3/25
 * time: 10:20
 **/

 
namespace backend\controllers;

use common\helpers\TExportHelper;
use components\widgets\TPagination;
use Yii;
use backend\base\BaseBackController;
use backend\services\PointRuleService;
use yii\web\Response;
use common\services\framework\TreeNodeService;
use yii\web\NotFoundHttpException;
use common\models\framework\FwPointRule;
use common\models\treemanager\FwTreeNode;

class PointController  extends BaseBackController
{

    public $layout = 'frame';

    public function actionIndex()
    {
        $service = new PointRuleService();
        return $this->render('index', ['searchModel' => $service,]);
    }

    public function actionList()
    {
        $this->layout = 'list';

        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");
//        $includeSubNode = Yii::$app->request->getQueryParam("includeSubNode");
        
       

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
//        } else {
//            $treeNodeIdList = $treeNodeKid;
//            //$treeNodeModel = FwTreeNode::findOne($treeNodeKid,false,true,false,false);
//            //echo $treeNodeModel->tree_node_name;
//        }

        $forceShowAll = 'False';
        $pageSize = $this->defaultPageSize;

        if (Yii::$app->request->getQueryParam('PageShowAll') != null && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
            $forceShowAll = 'True';
        }
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }

        $service = new PointRuleService();
        $dataProvider = $service->search(Yii::$app->request->queryParams, $treeNodeKid);

        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
        $dataProvider->setPagination($page);

        $cycleRanges = $service->getCycleRanges();
        $statuses = $service->getStatuses();


        return $this->render('list', [
            'page' => $page,
            'searchModel' => $service,
            'dataProvider' => $dataProvider,
            'forceShowAll' => $forceShowAll,
            'pageSize' => $pageSize,
            'cycleRanges' => $cycleRanges,
            'statuses' => $statuses,
            'treeType' => 'company',
            'treeNodeKid' => $treeNodeKid,
//            'includeSubNode' => $includeSubNode,
        ]);
    }

    /**
     * Displays a single LnComponent model.
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
     * change status
     * @param $id
     * @param $status
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionStatus($id, $status)
    {
        if (Yii::$app->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            $model = $this->findModel($id);
            $model->status = $status;

            if ($model->save()) {
                return ['result' => 'success'];
            }
            return ['result' => 'failure'];
        }
    }


    /**
     * Updates an existing FwPointRule model.
     * If update is successful, the browser will be redirected to the 'list' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // $model->setScenario("manage");
        $service = new PointRuleService();
        $cycleRanges = $service->getCycleRanges();
        $statuses = $service->getStatuses();

        // $oldSequenceNumber = $model->sequence_number;

        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $sv_temp=Yii::$app->request->post('sv');
            
            if(in_array($sv_temp{0},array('+','-')))
            {
                $model->point_op=$sv_temp{0};
                $model->standard_value = substr($sv_temp,1);
            }
            else
            {
                $model->point_op='+';
                $model->standard_value = $sv_temp;
            }
            
            $model->cycle_range = Yii::$app->request->post('cr'); 
             

            if ($model->save()) {
                return ['result' => 'success', 'msg' => Yii::t('common', 'save_success')];
            }
            else {
                return ['result' => 'failed', 'msg' => Yii::t('common', 'save_failed')];
            }
        } else {
            
            $cycleRangeSel[$model->cycle_range]=' selected="selected"';
            
            return $this->renderAjax('update', [
                'model' => $model,
                'cycleRanges' => $cycleRanges,
                'statuses' => $statuses,
                'cycleRangeSel'=>$cycleRangeSel
            ]);
        }
    }

    /**
     * @param $id
     * @return FwPointRule
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = PointRuleService::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'data_not_exist'));
        }
    }


    /**
     * export rule data to cvs file
     */
    public function actionExport()
    {

        $treeNodeKid = Yii::$app->request->getQueryParam("TreeNodeKid");

        if ($treeNodeKid == '-1')
            $treeNodeKid = '';

        $service = new PointRuleService();
        $results = $service->search(Yii::$app->request->queryParams, $treeNodeKid)->query->all();

        $cycleRanges = $service->getCycleRanges();
        $statuses = $service->getStatuses();

        //规则名称	周期范围	单次分值	是否启用

        $split = ",";
        $header = Yii::t('common', 'point_name') . $split . Yii::t('common', 'cycle_range') 
            . $split . Yii::t('common', 'standard_value') . $split . Yii::t('common', 'is_in_using');

        $data = array();

        $i = 0;
        foreach ($results as $r) {
            $data[$i][0] = $r->point_name;
            $data[$i][1] = $cycleRanges[$r->cycle_range];
            $data[$i][2] = $r->point_op . $r->standard_value;
            $data[$i][3] = $statuses[$r->status];

            $i++;
        }
        TExportHelper::exportCsv($header, $data, "output", $split);
    }
}
