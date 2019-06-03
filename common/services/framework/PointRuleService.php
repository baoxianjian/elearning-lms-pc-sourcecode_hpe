<?php
/**
 * 积分规则服务
 * author: 包显建
 * date: 2016/2/29
 * time: 11:30
 * 增加一条积分规则，需要改动
 * elearning-lms-pc-sourcecode\backend\views\point\_form.php
 * \elearning-lms-pc-sourcecode\common\messages\zh\data.php
 */

namespace common\services\framework;


use common\models\framework\FwPointRule;

use Yii;
use PDO;
use stdClass;
use common\eLearningLMS;


class PointRuleService extends FwPointRule{

    public $curRule=null;
    /**
     * 得到本企业的积分规则列表
     * @param 企业id $cmpid
     * @param 页码 $page
     * @param 每页大小 $size
     * @return array
     */
    public function getPointRuleList($companyId,$page,$size)
    {
         $data=$this->subGetPointRuleList($companyId,$page,$size);
         if($data['count']==0)
         {
              $this->copyRuleAndScale($companyId);
         }
         $data=$this->subGetPointRuleList($companyId,$page,$size);
         return $data;
        
    } 
    
    /**
     * 得到本企业的积分规则条数
     * @param 企业id $companyId
     * @return array
     */
    public function getPointRuleCount($companyId)
    {
         $data=$this->subGetPointRuleList($companyId,0,0,true);
         if($data['count']==0)
         {
              $this->copyRuleAndScale($companyId);
         }
         $data=$this->subGetPointRuleList($companyId,0,0,true);
         return $data['count'];
        
    }
    
    /**
    * 检查当前行为（规则），看是否能得到积分
    * @param string $companyId company_id 企业id
    * @param string $userid user id 用户id
    * @param string $pcode point code 积分代码/行为代码
    * @param string $scode scene code 场景代码
    * @param string $resid 资源idid
    */
    private function _checkActionForPoint($companyId,$userid,$pcode,$scode='',$resid='')
    {
       if(!$companyId=trim($companyId)){return array('result'=>'2','message'=>'company id is null.');}
       if(!$userid=trim($userid)){return array('result'=>'3','message'=>'user id is null.');}
       if(!$pcode=trim($pcode)){return array('result'=>'4','message'=>'point code is null.');}
       $scode=trim($scode);
     
       #检查规则和权重是否存在
       #$this->checkRuleAndScaleExists($cmpid,$pcode,$scode);
       
       $scaleService = new PointRuleScaleService();

       #得到对应规则
       if($this->curRule)
       {
          $row_rule=$this->curRule; 
       }
       else
       {
          $row_rule=$this->getRowByCmpIdAndPCode($companyId,$pcode); 
       } 
       $row_scale=$scaleService->getRowByCmpIdAndSCode($companyId,$scode);

       #如果没有则从模板中复制一份
       if(!$row_rule || !$row_scale)
       {
           $this->copyRuleAndScale($companyId);

           #再重新得到
           $row_rule=$this->getRowByCmpIdAndPCode($companyId,$pcode);
           $row_scale=$scaleService->getRowByCmpIdAndSCode($companyId,$scode);
       }

       #如果得不到规则
       if(!$row_rule){return array('result'=>'5','message'=>'can not find corresponding rule.');}
       
       #状态判断
       if($row_rule['status']!=FwPointRule::STATUS_FLAG_NORMAL)
       {
           return array('result'=>'6','message'=>'the status of rule is not in normal.');
       }

       $rule_id = $row_rule['kid'];  
       $standard_value = $row_rule['standard_value'];
       $scale_value = $row_scale['scale_value'];
       
       #如果得不到积分值
       if(!$standard_value){return array('result'=>'7','message'=>'the standard_value of rule is null.');}

       #如果没有权重值，则设为1
       if(!$scale_value){$scale_value=1;}
       $scale_value=1; //固定为1
     
       #真实积分=标准积分*权重
       $real_point = $standard_value*$scale_value;
       $real_point = round($real_point,2);//保留2位小数

       $detailService = new UserPointDetailService();         
       $today = strtotime(date("Y-m-d"));
       $now = time();
       $srow=array('rule_id'=>$rule_id);  
       $oneday=86400;
             
         /*      
         var_dump($row_rule);
         echo '<br/>';
         echo $userid,'<br/>',$rule_id ,'<br/>';
         */
       #预先定义资源id错误
       $resid_error=array('result'=>'8','message'=>'resid is null.');

       $rule_runing_key=$pcode.$resid.$row_rule['cycle_range'].'_is_runing';
 

       #根据循环周期判断
       switch($row_rule['cycle_range'])
       {
           case FwPointRule::CYCLE_RANGE_NOT_LIMIT: //不限制
           {
               $count=0;
               break;
           }
           case FwPointRule::CYCLE_RANGE_ONE_TIME: //一次性
           {     
               if(!$this->checkResIdForRule($pcode,$resid,$srow)){return $resid_error;}
               $count=$detailService->getCountBySearch($userid,$srow);
               break;
           }
           case FwPointRule::CYCLE_RANGE_EVERY_DAY: //今天
           {
               $srow['start_time']=$today;
               if(!$this->checkResIdForRule($pcode,$resid,$srow)){return $resid_error;}
               $count=$detailService->getCountBySearch($userid,$srow);
               break;
           }
           case FwPointRule::CYCLE_RANGE_EVERY_WEEK: //一周
           {
               $srow['start_time']=$today-($oneday*7);
               //$srow['end_time']=$today;
               if(!$this->checkResIdForRule($pcode,$resid,$srow)){return $resid_error;}
               $count=$detailService->getCountBySearch($userid,$srow);
               break;
           }
           case FwPointRule::CYCLE_RANGE_EVERY_MONTH: //一月
           {
               $srow['start_time']=strtotime(date('Y-m'));
               //$srow['start_time']=$today-($oneday*30) strtotime("+1 month")
               //$srow['start_time']=$today-($oneday*30);
               //$srow['end_time']=$today;
               if(!$this->checkResIdForRule($pcode,$resid,$srow)){return $resid_error;}
               $count=$detailService->getCountBySearch($userid,$srow);
               break;
           }
           case FwPointRule::CYCLE_RANGE_EVERY_YEAR: //一年
           {   
               $srow['start_time']=strtotime(date('Y').'-1-1');
               //echo date('Y-m-d H:i:s',strtotime(date("Y-m")));   exit;
               //$srow['end_time']=$today;
               if(!$this->checkResIdForRule($pcode,$resid,$srow)){return $resid_error;}
               $count=$detailService->getCountBySearch($userid,$srow);
               break;
           }
           default://周期值不对
           {
               return array('result'=>'9','message'=>'the cycle_range of rule is not correct.');
           }
       }
       

       //如果在循环周期内没有获得积分记录
       if($count==0)
       {     
           $trans_type=UserPointSummaryService::TRANS_TYPE_GET;   
           if($row_rule['point_op']=='-')
           {
                $trans_type=UserPointSummaryService::TRANS_TYPE_DEDUCT;
           } 
         
           $userPointSummaryService = new UserPointSummaryService();
           $r= $userPointSummaryService->transGetFromRule($userid, $trans_type, $real_point, $rule_id,$resid,Yii::t('data', $row_rule['i18n_flag']));


           $r['point_name']=Yii::t('data', $row_rule['i18n_flag']);
           $r['show_point']=$row_rule['point_op'].$r['trans_point'];
          
/*
           Yii::$app->session->set('point_show_point',$r['trans_op'].$r['trans_point']);
           Yii::$app->session->set('point_name',$r['point_name']);
           Yii::$app->session->set('point_available_point',$r['available_point']);
*/         
           return $r; 
           /*
           var_dump($r); 
           exit;
           */
       }
       return array('result'=>'10','message'=>'already got point from rule.');
    }
    
    /**
    * 检查当前行为（规则），看是否能得到积分
    * @param string $companyId company_id 企业id
    * @param string $userid user id 用户id
    * @param string $pcode point code 积分代码/行为代码
    * @param string $scode scene code 场景代码
    * @param string $resid 资源idid
    */
    public function checkActionForPoint($companyId,$userid,$pcode,$scode='',$resid='')
    {
       //rule设为成员，减少一次规则查询
       $this->curRule=$this->getRowByCmpIdAndPCode($companyId,$pcode);

       //当前规则是否在处理中
      $rule_runing_key=$pcode.$resid.$this->curRule['cycle_range'].'_is_runing';

      //等待次数
       $sleep_count=0;

       //如果当前规则正在处理，则线程等待，防止同一用户在同一规则上并发（这样会脏读数据）
       while(Yii::$app->session->has($rule_runing_key) && Yii::$app->session->get($rule_runing_key)==true)
       {
           //最大等待10次
            if($sleep_count>10)
            {
                Yii::$app->session->set($rule_runing_key,false);
                break;
            }
            //每次等待1秒
            sleep(1);
            $sleep_count++;    
       }

       //开始处理规则
       Yii::$app->session->set($rule_runing_key,true);
 
       $r=$this->_checkActionForPoint($companyId,$userid,$pcode,$scode,$resid);

       //规则处理完毕
       Yii::$app->session->set($rule_runing_key,false);
       return $r;
    }

    /**
     * 当前用户检查当前行为（规则），看是否能得到积分
     * @param $pcode
     * @param string $pcode point code 积分代码/行为代码
     * @param string $scode scene code 场景代码
     * @param string $resid 资源idid
     * @return array
    */
    public function curUserCheckActionForPoint($pcode,$scode='',$resid='')
    {
        $cmpid =  Yii::$app->user->identity->company_id;
        $userid = Yii::$app->user->getId();
        
        $r=$this->checkActionForPoint($cmpid,$userid,$pcode,$scode,$resid);
        if(Yii::$app->request->post('debug'))
        {
              var_dump($r);exit;
        }
        return $r;
    }
    
    private function checkResIdForRule($pcode,$resid,&$srow)
    {
        //,'Publish-Page','Publish-Event','Publish-Book','Publish-Sharing'
        $need_check_resid_list=
        array(
'Register-Online-Course',
'Register-Face-Course',
'Open-Shared-Page',
'Open-Shared-Event',
'Open-Shared-Book',
'Download-Page',
'Download-Event',
'Download-Book',
'Download-Experience',
'Complete-Online-Course',
'Complete-F2F-Course',
'Pass-Exam',
'Complete-Investigation',
'Complete-Questionare',
'Get-Certification',
'Attention-Question',
'Attention-People',
'Collect-Course',
'Collect-Question',
'Mark-Course',
'Comment-Course-Question',
'Comment-Common-Question',
'Reply-Course-Question',
'Reply-Common-Question'
        );
        if(in_array($pcode,$need_check_resid_list))
        {
            if(!$resid){return false;}
            $srow['get_from_id']=$resid;
        }
        return true;
    }




    /**
     * 得到本企业的积分规则列表
     * @param $cmpid 企业id
     * @param $page 页码
     * @param $size 每页大小
     * @param $onlyCount 只返回条数
     * @return array
     */
    private function subGetPointRuleList($cmpid,$page,$size,$onlyCount=false)
    {
        if(!$cmpid=trim($cmpid)){return false;}
        $page=intval($page);
        $size=intval($size);

        $query=FwPointRule::find(false);
        $query->select('kid,company_id,point_type,point_code,point_name,cycle_range,point_op,standard_value,accumulate_increment,max_increment,is_template,status')
            ->andWhere(['=','company_id', $cmpid]);
        if($onlyCount)
        {
            $list['count']=$query->count();
            return $list;                
        }
        $query->limit($size)->offset($this->getOffset($page, $size));

        //$query->andWhere(['=', 'company_id', $companyId]);
        //echo $query->createCommand()->getSql();exit;

        $list['data']=$query->all();
        $list['count']=$query->count();

        return $list;
    }
    
    


    /**
     * 根据主键修改一条数据
     * @param $row 要修改的字段
     * @param $id 主键id
     * @return bool|int
     */
    public function updatePointRuleByPk($row,$id)
    {
        if(!$id=trim($id)){return false;}

        $data = FwPointRule::find(false)->andWhere(array('kid' => $id))->one();


        if(isset($row['cycle_range']))
        {
            $data['cycle_range']=intval($row['cycle_range']);
        }
        if(isset($row['standard_value']))
        {
            $data['standard_value']=doubleval($row['standard_value']);
        }
        if(isset($row['status']))
        {
            $data['status']=intval($row['status']);
        }

        $count = FwPointRule::updateAll($data, 'kid=:kid', [":kid"=>$data['kid']]);
        return $count;
    }



    /**
     * 根据主键得到一条数据
     * @param string $id primary key
     */
    public function getRowByPK($id)
    {
        if(!$id=trim($id)){return false;}
        $data = FwPointRule::find(false)->andWhere(array('kid' => $id))->one();
        //$data['cycle_range_text']=$this->getCycleRanges($data['cycle_range']);
        return $data;
    }

    /**
     * 得到本企业的某个规则，根据规则code
     * @param string $cmpid 企业id
     * @param string $pcode 规则编码
     */
    public function getRowByCmpIdAndPCode($cmpid,$pcode)
    {
        if(!$cmpid=trim($cmpid)){return false;}
        if(!$pcode=trim($pcode)){return false;}
        $data = FwPointRule::find(false)->andWhere(array('company_id' => $cmpid,'point_code'=>$pcode))->one();
        return $data;
    }

    /**
     * 复制（初始化）本企业的积分规则和权重
     * @param string $companyId 企业id
     */
    public function copyRuleAndScale($companyId)
    {
        if(!$companyId=trim($companyId)){return false;}

        $sql='CALL pro_copy_point_rule_scale(:companyId)';

        $inputParams = array();

        $paramCmpId = new stdClass();
        $paramCmpId->name = ":companyId";
        $paramCmpId->value = $companyId;
        $paramCmpId->type = PDO::PARAM_STR;
        $inputParams[] = $paramCmpId;

        eLearningLMS::execute($sql,$inputParams);
        return true;
    }
    
    public function countCourseAndCetificationPoint($courseComplete,$getCetification,$courseId,$certificationId)
    {
        if($courseComplete)
        {
            $result_temp1=$this->curUserCheckActionForPoint('Complete-Online-Course','Learning-Portal',$courseId);
        }
        if($getCetification)
        {
            $result_temp2=$this->curUserCheckActionForPoint('Get-Certification','Learning-Portal',$certificationId);
        }
        $show_point=0;
        $available_point=0;
        $point_name='';
        
        if(isset($result_temp1) && $result_temp1['result']==1)
        {
            $show_point+=round(doubleval($result_temp1['trans_op'].$result_temp1['trans_point']),2);
            $available_point=$result_temp1['available_point'];
            $point_name.=$result_temp1['point_name'];
        }
        if(isset($result_temp2) && $result_temp2['result']==1)
        {
            $show_point+=round(doubleval($result_temp2['trans_op'].$result_temp2['trans_point']),2);
            $available_point=$result_temp2['available_point'];
            $point_name.=$result_temp2['point_name'];
        }

        /*
        Yii::$app->session->set('point_show_point',$show_point);
        Yii::$app->session->set('point_name',$point_name);
        Yii::$app->session->set('point_available_point',$r['available_point']);
        */
        
        return array('show_point'=>$show_point,'point_name'=>$point_name,'available_point'=>$available_point);
    }
    

}