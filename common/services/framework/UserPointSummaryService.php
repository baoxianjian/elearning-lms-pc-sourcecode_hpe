<?php
/**
 * 用户积分汇总服务层
 * author: 包显建
 * date: 2016/3/15
 * time: 14:11
 */

namespace common\services\framework;


use common\models\framework\FwUserPointSummary;
use Yii;
use stdClass;
use PDO;
use common\eLearningLMS;

class UserPointSummaryService extends FwUserPointSummary{


    /**
     * 事务处理，给用户转积分
     * add by baoxianjian 2016.3.15 15:38
     * @param $to_userid 目标用户
     * @param $trans_type 交易类型  TRANS_TYPE_IN/OUT/GET
     * @param $trans_point 交易积分额度
     * @param $point_rule_id 积分规则id
     * @param $get_from 源 GET_FROM_USER/ADMIN/SYSTEM
     * @param $get_from_id 源id
     * @param $reason 备注
     * @return array
     */
    private function transUserPoint($to_userid,$trans_type,$trans_point,$point_rule_id,$get_from,$get_from_id,$reason)
    {
        if(!$to_userid=trim($to_userid)){return array('result'=>2,'message'=>'to_userid is null.');}
        if(!$trans_type=intval($trans_type)){return array('result'=>3,'message'=>'trans_type is null.');}
        if(!$trans_point=doubleval($trans_point)){return array('result'=>4,'message'=>'trans_point is null.');}
        $point_rule_id=trim($point_rule_id);
        if(!$get_from=trim($get_from)){return array('result'=>5,'message'=>'get_from is null.');}
        $get_from_id=trim($get_from_id);
        $reason=trim($reason);


        /*
        $str="CALL pro_trans_user_point('{$to_userid}','{$trans_type}','{$trans_point}','{$point_rule_id}','{$get_from}','{$get_from_id}','{$reason}',@result,@message);";
        echo $str;
        exit;
        */

        $sql='CALL pro_trans_user_point(:to_userid,:trans_type,:trans_point,:point_rule_id,:get_from,:get_from_id,:reason,@result,@message,@cur_available_point)';

        $inputParams = array();

        $paramToUserid = new stdClass();
        $paramToUserid->name = ":to_userid";
        $paramToUserid->value = $to_userid;
        $paramToUserid->type = PDO::PARAM_STR;
        $inputParams[] = $paramToUserid;

        $paramTransType = new stdClass();
        $paramTransType->name = ":trans_type";
        $paramTransType->value = $trans_type;
        $paramTransType->type = PDO::PARAM_STR;
        $inputParams[] = $paramTransType;

        $paramTransPoint = new stdClass();
        $paramTransPoint->name = ":trans_point";
        $paramTransPoint->value = $trans_point;
        $paramTransPoint->type = PDO::PARAM_STR;
        $inputParams[] = $paramTransPoint;

        $paramPointRuleId = new stdClass();
        $paramPointRuleId->name = ":point_rule_id";
        $paramPointRuleId->value = $point_rule_id;
        $paramPointRuleId->type = PDO::PARAM_STR;
        $inputParams[] = $paramPointRuleId;

        $paramGetFrom = new stdClass();
        $paramGetFrom->name = ":get_from";
        $paramGetFrom->value = $get_from;
        $paramGetFrom->type = PDO::PARAM_STR;
        $inputParams[] = $paramGetFrom;

        $paramGetFromId = new stdClass();
        $paramGetFromId->name = ":get_from_id";
        $paramGetFromId->value = $get_from_id;
        $paramGetFromId->type = PDO::PARAM_STR;
        $inputParams[] = $paramGetFromId;

        $paramReason = new stdClass();
        $paramReason->name = ":reason";
        $paramReason->value = $reason;
        $paramReason->type = PDO::PARAM_STR;
        $inputParams[] = $paramReason;

        /*
        $outParams = array();

        $paramResult = new stdClass();
        $paramResult->name = "Result";
        $paramResult->value = 'a';
        $outParams[] = $paramResult;

        $paramMessage = new stdClass();
        $paramMessage->name = "Message";
        $paramMessage->value = 'b';
        $outParams[] = $paramMessage;
        */

        /*
        eLearningLMS::execute($sql,$inputParams);

        $db = \Yii::$app->db;
        $result= $db->createCommand("select @result")->queryScalar();
        $message= $db->createCommand("select @message")->queryScalar();
        $cur_available_point= $db->createCommand("select @cur_available_point")->queryScalar();
        */
        $procedureResult=eLearningLMS::queryAll($sql,$inputParams);
         
        $result=$procedureResult[0]['result'];
        $message=$procedureResult[0]['message'];
        $cur_available_point=$procedureResult[0]['cur_available_point'];
        
        $trans_op='+';
        if(in_array($trans_type,array(self::TRANS_TYPE_OUT,self::TRANS_TYPE_DEDUCT)))
        {
            $trans_op='-';
        }
        
        //同时修改session值
        //Yii::$app->session->set('available_point',$cur_available_point);
                
        return array('result'=>$result,'message'=>$message,'trans_type'=>$trans_type,'trans_op'=>$trans_op,'trans_point'=>$trans_point,'available_point'=>$cur_available_point);

        /*
        $reg = "davafy@davafy.com";
        $cmd = \Yii::$app->db->createCommand("call test1(:reg,@s)");
        $cmd->bindParam(':reg',$reg,PDO::PARAM_STR,50);
        $res = $cmd->queryOne();
        $s = \Yii::$app->db->createCommand("select @s");
        $ret = $s->queryOne();
        */
    }


    /**
     * 用户A支付积分给用户B
     * add by baoxianjian 2016.3.15 15:38
     * @param string $my_user_id 用户A
     * @param string $trans_point 交易积分额度
     * @param string $rec_user_id 用户B
     * @param string $reason 备注
     * @return array
     */
    public function transPayToUser($my_user_id,$trans_point,$rec_user_id,$reason)
    {
        if(!$my_user_id=trim($my_user_id)){return array('result'=>6,'message'=>'my_user_id is null.');}
        $trans_type= parent::TRANS_TYPE_OUT;
        //if(!$trans_point=doubleval($trans_point)){return false;}
        $get_from= parent::GET_FROM_USER;
        if(!$rec_user_id=trim($rec_user_id)){return array('result'=>7,'message'=>'rec_user_id is null.');}
        $reason=trim($reason);

        return $this->transUserPoint($my_user_id, $trans_type, $trans_point, 'NULL', $get_from,  $rec_user_id, $reason);
    }

    /**
     * 用户A从规则中获得积分
     * add by baoxianjian 2016.3.15 15:38
     * @param $my_user_id 用户A
     * @param $trans_type 交易类型
     * @param $trans_point 交易积分额度
     * @param $point_rule_id 积分规则id
     * @param $get_from_id 获取源id
     * @param $reason 备注
     * @return array
     */
    public function transGetFromRule($my_user_id,$trans_type,$trans_point,$point_rule_id,$get_from_id,$reason)
    {
        if(!$my_user_id=trim($my_user_id)){return array('result'=>6,'message'=>'my_user_id is null.');}
        //$trans_type= parent::TRANS_TYPE_GET;
        //if(!$trans_point=doubleval($trans_point)){return false;}
        if(!in_array($trans_type,array(parent::TRANS_TYPE_GET,parent::TRANS_TYPE_DEDUCT)))
        {
            return array('result'=>9,'message'=>'trans_type is ont available.');
        }
        $get_from= parent::GET_FROM_RULE;
        if(!$point_rule_id=trim($point_rule_id)){return array('result'=>8,'message'=>'point_rule_id is null.');}
        $get_from_id=trim($get_from_id);
        $reason=trim($reason);

        return $this->transUserPoint($my_user_id, $trans_type, $trans_point, $point_rule_id, $get_from, $get_from_id, $reason);
    }
    
    /**
    * 根据用户id得到其可用积分
    * 
    * @param double $userId
    */
    public function getAvaliablePointByUserId($userId)
    {
        if(!$userId=trim($userId)){return false;}
        $query=FwUserPointSummary::find(false)->addSelect(['available_point'])->andWhere(['=','user_id', $userId]);
        $data=$query->one();
        $available_point=round(doubleval($data->available_point),2);
        //Yii::$app->session->set('available_point',$available_point);
        return $available_point;  
    }
    



}