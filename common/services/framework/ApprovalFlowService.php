<?php
/**
 * Created by Eclipse.
 * User: adophpeer
 * Date: 3/01/16
 * Time: 11:00 AM
 */

namespace common\services\framework;

use common\base\BaseActiveRecord;
use Yii;
use common\models\framework\FwApprovalFlow;

class ApprovalFlowService extends FwApprovalFlow{

	/**
	 * 添加课程审批数据
	 * @param string $courseId
	 * @param string $applierId
	 * @param string $approvalRule
	 * @param string $approvalBy
	 */
    public function addApprovalFlowOfCourse($courseId,$applierId,$approvalRule,$approvedBy){
    	$model = new FwApprovalFlow();
    	$model->event_id = $courseId;
    	$model->event_type = FwApprovalFlow::EVENT_TYPE_COURSE_APPLY;
    	$model->applier_id = $applierId;
    	$model->applier_at = time();
    	$model->approval_rule = $approvalRule;
    	$model->approved_by = $approvedBy;
    	$model->needReturnKey = true;
    	if ($model->save() !== false){
    		return $model->kid;
    	}else{
    		return false;
    	}
    }
	
}