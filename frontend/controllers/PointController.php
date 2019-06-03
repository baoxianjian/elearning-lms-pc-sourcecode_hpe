<?php
/**
 * 积分控制器
 * author: 包显建
 * date: 2016/2/29
 * time: 11:35
 **/

namespace frontend\controllers;

use yii;

use yii\web\Response;
use frontend\base\BaseFrontController;
use common\services\framework\PointRuleService;
use common\services\framework\UserPointSummaryService;
use components\widgets\TPagination;

use common\services\interfaces\service\TransactionInterface;

class PointController extends BaseFrontController
{                                    
	public $layout = 'frame';
    
	//主页渲染
	public function actionIndex()
    {                             
        $cmpid =  Yii::$app->user->identity->company_id;
        $userid = Yii::$app->user->getId();
        
        $page=Yii::$app->request->getQueryParam('page');
        $size = $this->defaultPageSize;
        
        $pointRuleService=new PointRuleService();
        $list=$pointRuleService->getPointRuleList($cmpid,$page, $size);
        
        $cycleRanges=$pointRuleService->getCycleRanges();
        $statuses=$pointRuleService->getStatuses();

        $pages = new TPagination(['defaultPageSize' =>$size, 'totalCount' =>$list['count']]);
        
        $params['list']=$list['data'];
        $params['pages']=$pages;
        $params['cycleRanges']=$cycleRanges;
        $params['statuses']=$statuses;
        
		return $this->render('index',$params);
    }


    public function actionSaveRule()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            
            $service=new PointRuleService();
            
            $row['cycle_range']=Yii::$app->request->post('cr');
            $row['standard_value']=Yii::$app->request->post('sv');
            $id=Yii::$app->request->post('id');                    
            $count=$service->updatePointRuleByPk($row,$id);
            if($count==0)
            {
                return ['result' => 'failed', 'msg' => Yii::t('common', 'save_failed')];
            }
            $row=$service->getRowByPK($id);
        
            $row2['standard_value']=$row['standard_value'];
            $row2['cycle_range']=$row['cycle_range'];
            $row2['cycle_range_text']=$service->getCycleRanges($row['cycle_range']);

            return ['result' => 'success', 'msg' =>  Yii::t('common', 'save_success'), 'row'=>$row2];
        
        }
      //  exit('success');
        
    }
                    
    public function actionStart()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            
            $row['status']=intval(Yii::$app->request->post('start'));
            $id=Yii::$app->request->post('id');   
            
            $service=new PointRuleService();
            $count=$service->updatePointRuleByPk($row,$id);
             
            if($count==0)
            {                 
                return ['result' => 'failed', 'msg' => Yii::t('common', 'save_failed')];
            }
            $row=$service->getRowByPK($id);
        
            $row2['status']=$row['status'];
            $row2['status_text']=$service->getStatuses($row['status']);

            return ['result' => 'success', 'msg' => Yii::t('common', 'save_success'), 'row'=>$row2];
        }  
    }
                                                               
    public function actionTest()
    {
        $pointRuleService=new PointRuleService();

       
        // $pointRuleService->checkActionForPoint($cmpid,$userid,'Login','Formal-Training');
        $r=$pointRuleService->curUserCheckActionForPoint('Search');    
        print_r($r);echo '<br/>';
        /*
        $r=$pointRuleService->curUserCheckActionForPoint('Search');
        print_r($r);echo '<br/>';
        $r=$pointRuleService->curUserCheckActionForPoint('Search');
        print_r($r);echo '<br/>';
        $r=$pointRuleService->curUserCheckActionForPoint('Search');
        print_r($r);echo '<br/>';
        */
        exit;
         
         /*  
        $userPointSummaryService=new UserPointSummaryService(); 
        
        $avaliablePoint=$userPointSummaryService->getAvaliablePointByUserId('19DCAC17-1627-C90B-1794-458B376D2F8E');
        var_dump($avaliablePoint);
        */ 
        
         /*            
        $transactionInterface =new TransactionInterface();
        $r=$transactionInterface->payForUser('', '00000000-0000-0000-0000-000000000002', '1', '转账', TransactionInterface::TRANSACTION_TYPE_POINT);
        print_r($r);
        exit;
         */

        
    }

    //积分交易
    function actionPointTrans()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $fromUserId = Yii::$app->user->identity->getId();
            $toUserId = Yii::$app->request->post('uid');
            $number = Yii::$app->request->post('num');
            $reason = Yii::$app->request->post('desc');

            //payForUser($fromUserId, $toUserId, $number, $reason, $transactionType)

            $transactionInterface = new TransactionInterface();
            $pointResult = $transactionInterface->payForUser($fromUserId, $toUserId, $number, $reason, TransactionInterface::TRANSACTION_TYPE_POINT);

            $pointResult['show_point'] = $pointResult['trans_op'] . $pointResult['trans_point'];


            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($pointResult['result'] == 1) {
                return ['result' => 'success', 'pointResult' => $pointResult];
            }
            
            return ['result' => 'failed'];
        }
    }

    //得到当前用户可用积分
    public function actionAvaliablePoint(){
        $uid=Yii::$app->user->identity->getId();
        
        $userPointSummaryService=new UserPointSummaryService();
        $avaliable_point=$userPointSummaryService->getAvaliablePointByUserId($uid);
        
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['avaliable_point' =>$avaliable_point ];
    }

}