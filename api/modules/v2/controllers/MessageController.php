<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 16/1/22
 * Time: 下午5:18
 */

namespace api\modules\v2\controllers;


use api\base\BaseOpenApiController;
use common\models\message\MsMessage;
use common\services\message\MessageService;
use common\helpers\TMessageHelper;
use common\viewmodels\api\ResponseModel;
use Yii;

class MessageController extends BaseOpenApiController
{
    public $modelClass = '';
    public $system_key;
    
    /**
     * 查检是否为post方式
     * 检查system_key
     */
    public function requestCheck()
    {
    	if (!Yii::$app->request->isPost) {
    		return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_003, '', '');
    	}
    	$queryParams = Yii::$app->request->getQueryParams();
    	if (!$this->systemKeyCheck)
    		return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'system_key', '\'system_key\' can not be empty');
    	$this->system_key = $queryParams['system_key'];
    	if (!parent::checkSystemKey($this->system_key))
    		return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'system_key', '\'system_key\' can not be empty');
    
    	return null;
    }
    
    /**
     * 对请求的post包进行包密
     * @return Ambigous <multitype:, multitype:NULL unknown Ambigous <string, NULL> >
     */
    public function DecryptRequestParam(&$httpRequestParam)
    {
    	$errorCode = null;
    	$errorMessage = null;
    	$rawBody = Yii::$app->request->getRawBody();
    	$rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);
    
    	if (!empty($errorCode)) {
    		return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_004, $errorCode, $errorMessage);
    	}
    	$httpRequestParam = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object
    	return null;
    }
    
    /**
     * action执行前进行检查,解密
     * @param unknown $httpRequestParam
     * @return \api\modules\v2\controllers\Ambigous|Ambigous <NULL, multitype:, multitype:NULL unknown Ambigous <string, NULL> >
     */
    public function preExecuteAction(&$httpRequestParam)
    {
    	$result = $this->requestCheck();
    	if ($result == null)
    		return $this->DecryptRequestParam($httpRequestParam);
    	return $result;
    }
    
    public function actionMessageCount()
    {
        $code = "messageMunu";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $lastLoadAt = null;


        $userId = Yii::$app->user->getId();
        $company_id = Yii::$app->user->identity->company_id;
        $createdTime = Yii::$app->user->identity->created_at;

        $service = new MessageService();
        $courseMessageCount = $service->getMessageCountByUid($userId, MsMessage::TYPE_TODO);
        $qaMessageCount = $service->getMessageCountByUid($userId, MsMessage::TYPE_QA);
        $newsMessageCount = $service->getNewsMessageCountByUid($userId,$company_id,$createdTime);
        $socialMessageCount = $service->getMessageCountByUid($userId, MsMessage::TYPE_SOCIAL);
        $count = $courseMessageCount + $qaMessageCount + $newsMessageCount + $socialMessageCount;




       $reuslt = ['courseMessageCount' => $courseMessageCount,
           'qaMessageCount' => $qaMessageCount,
           'newsMessageCount' => $newsMessageCount,
            'socialMessageCount' => $socialMessageCount,
            'count' => $count];

        return TMessageHelper::ResultBuild($this->systemKey, $code = 'OK', $name, $message, $reuslt);
    }


    public function actionPopMessageCourse()
    {
    	$req_param = null;
    	$err = $this->preExecuteAction($req_param);
    	if ($err != null) {
    		return $err;
    	}
    	 
    	$page = $req_param['page'];
    	if (empty($page))
    		$page=1;
    	
        $code = "messageCourse";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $userId = $this->user->kid;

        $msgService = new MessageService();
        $size = 10;
        $data = $msgService->getMessageAndPage($userId, MsMessage::TYPE_TODO, $size, ((int)$page - 1) * $size);

        $jsonArray = array();
        $i = 0;



        foreach ($data['data'] as $val )
        {
            $jsonArray[$i]['receive_status'] = $val->receive_status;
            $jsonArray[$i]['sender'] = $val->sender;
            $jsonArray[$i]['kid'] = $val->kid;
            $jsonArray[$i]['task_id'] = $val->task_id;
            $jsonArray[$i]['sender_id'] = $val->sender_id;
            $jsonArray[$i]['object_id'] = $val->object_id;
            $jsonArray[$i]['object_type'] = $val->object_type;
            $jsonArray[$i]['title'] = $val->title;
            $jsonArray[$i]['content'] = $val->content;
            $jsonArray[$i]['end_time'] = $val->end_time;
            $jsonArray[$i]['msg_status'] = $val->msg_status;
            $jsonArray[$i]['message_type'] = $val->message_type;
            $jsonArray[$i]['created_by'] = $val->created_by;
            $jsonArray[$i]['created_at'] = $val->created_at;
            $jsonArray[$i]['updated_by'] = $val->updated_by;
            $jsonArray[$i]['updated_at'] = $val->updated_at;
            $jsonArray[$i]['is_deleted'] = $val->is_deleted;
            $i++;
        }
        return TMessageHelper::ResultBuild($this->systemKey, $code = 'OK', $name, $message,$jsonArray);
    }


    public function actionPopMessageQuestion()
    {
    	$req_param = null;
    	$err = $this->preExecuteAction($req_param);
    	if ($err != null) {
    		return $err;
    	}
    	 
    	$page = $req_param['page'];
    	if (empty($page))
    		$page=1;
    	
        $code = "messageCourse";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $userId = $this->user->kid;

        $msgService = new MessageService();
        $size = 10;
        $data = $msgService->getMessageAndPage($userId, MsMessage::TYPE_QA, $size, ((int)$page - 1) * $size);

        $jsonArray = array();
        $i = 0;



        foreach ($data['data'] as $val )
        {
            $jsonArray[$i]['receive_status'] = $val->receive_status;
            $jsonArray[$i]['sender'] = $val->sender;
            $jsonArray[$i]['kid'] = $val->kid;
            $jsonArray[$i]['task_id'] = $val->task_id;
            $jsonArray[$i]['sender_id'] = $val->sender_id;
            $jsonArray[$i]['object_id'] = $val->object_id;
            $jsonArray[$i]['object_type'] = $val->object_type;
            $jsonArray[$i]['title'] = $val->title;
            $jsonArray[$i]['content'] = $val->content;
            $jsonArray[$i]['end_time'] = $val->end_time;
            $jsonArray[$i]['msg_status'] = $val->msg_status;
            $jsonArray[$i]['message_type'] = $val->message_type;
            $jsonArray[$i]['created_by'] = $val->created_by;
            $jsonArray[$i]['created_at'] = $val->created_at;
            $jsonArray[$i]['updated_by'] = $val->updated_by;
            $jsonArray[$i]['updated_at'] = $val->updated_at;
            $jsonArray[$i]['is_deleted'] = $val->is_deleted;
            $i++;
        }
        return TMessageHelper::ResultBuild($this->systemKey, $code = 'OK', $name, $message,$jsonArray);
    }

    public function actionPopMessageNews()
    {
    	$req_param = null;
    	$err = $this->preExecuteAction($req_param);
    	if ($err != null) {
    		return $err;
    	}
    	 
    	$page = $req_param['page'];
    	if (empty($page))
    		$page=1;
    	
        $code = "messageCourse";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $userId = $this->user->kid;
        $companyId = Yii::$app->user->identity->company_id;

        $createdTime = $this->user->created_at;

        $msgService = new MessageService();
        $size = 10;
        $data = $msgService->getNewsMessageByUid($userId, $companyId, $createdTime, $size, ((int)$page - 1) * $size);
        $jsonArray = array();
        $i = 0;

        foreach ($data['data'] as $val )
        {
//            var_dump($val);
            $jsonArray[$i] = $val;
            $i++;
        }
        return TMessageHelper::ResultBuild($this->systemKey, $code = 'OK', $name, $message,$jsonArray);
    }

    public function actionPopMessageSocial()
    {
    	$req_param = null;
    	$err = $this->preExecuteAction($req_param);
    	if ($err != null) {
    		return $err;
    	}
    	 
    	$page = $req_param['page'];
    	if (empty($page))
    		$page=1;
    	
        $code = "messageCourse";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $userId = $this->user->kid;

        $msgService = new MessageService();
        $size = 10;
        $data = $msgService->getMessageAndPage($userId, MsMessage::TYPE_SOCIAL, $size, ((int)$page - 1) * $size);

        $jsonArray = array();
        $i = 0;

        foreach ($data['data'] as $val )
        {
            $jsonArray[$i]['receive_status'] = $val->receive_status;
            $jsonArray[$i]['sender'] = $val->sender;
            $jsonArray[$i]['kid'] = $val->kid;
            $jsonArray[$i]['task_id'] = $val->task_id;
            $jsonArray[$i]['sender_id'] = $val->sender_id;
            $jsonArray[$i]['object_id'] = $val->object_id;
            $jsonArray[$i]['object_type'] = $val->object_type;
            $jsonArray[$i]['title'] = $val->title;
            $jsonArray[$i]['content'] = $val->content;
            $jsonArray[$i]['end_time'] = $val->end_time;
            $jsonArray[$i]['msg_status'] = $val->msg_status;
            $jsonArray[$i]['message_type'] = $val->message_type;
            $jsonArray[$i]['created_by'] = $val->created_by;
            $jsonArray[$i]['created_at'] = $val->created_at;
            $jsonArray[$i]['updated_by'] = $val->updated_by;
            $jsonArray[$i]['updated_at'] = $val->updated_at;
            $jsonArray[$i]['is_deleted'] = $val->is_deleted;
            $i++;
        }
        return TMessageHelper::ResultBuild($this->systemKey, $code = 'OK', $name, $message,$jsonArray);
    }
    
    /**
     * 消息标为已读  待完成任务,问答录,社交圈
     * @return multitype:string
     */
    public function actionMarkRead()
    {
    	$req_param = null;
    	$err = $this->preExecuteAction($req_param);
    	if ($err != null) {
    		return $err;
    	}
    	 
    	$uid = $this->user->kid;
    	$id  = $req_param['id'];
    	if (empty($id))
    		return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'id', '\'id\' can not be empty');
    
    	$ids = explode(',', $id);
    	// var_dump($ids);
    	$service = new MessageService();
    
    	if ($service->markRead($uid, $ids)) {
    		$sessionKey = "MessageCountData";
    		Yii::$app->session->remove($sessionKey);
    		return ResponseModel::wrapResponseObject(['result' => 'success'],$this->system_key);
    	} else {
    		return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'id', $id.' 记录不存在!');
    	}
    }
    
    /**
     * 新鲜事标为已读
     * @return multitype:string
     */
    public function actionNewsMarkRead()
    {
    	$req_param = null;
    	$err = $this->preExecuteAction($req_param);
    	if ($err != null) {
    		return $err;
    	}
    	 
    	$uid = $this->user->kid;
    	$id  = $req_param['id'];
    	if (empty($id))
    		return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'id', '\'id\' can not be empty');
    
    	$ids = explode(',', $id);
    
    	$service = new MessageService();
    
    	if ($service->NewsMarkRead($uid, $ids)) {
    		$sessionKey = "MessageCountData";
    		Yii::$app->session->remove($sessionKey);
    		return ResponseModel::wrapResponseObject(['result' => 'success'],$this->system_key);
    	} else {
    		return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'id', $id.'记录不存在!');
    	}
    }
}