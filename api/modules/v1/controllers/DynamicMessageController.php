<?php

namespace api\modules\v1\controllers;

use api\base\BaseNormalApiController;
use common\models\framework\FwUser;
use api\services\UserService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\message\MsMessage;
use common\services\message\MessageService;
use yii\web\Response;

class DynamicMessageController extends BaseNormalApiController{


    public $modelClass = 'api\services\UserService';

	//http://elearning/api/v1/dynamic-message/get-course-message.html?access-token=aacb1c6927cf882206e35b3df67eb923&type=1
	/**
     * 获取待处理信息
     * @param unknown $page
     * @param string $time
     * @return Ambigous <string, string>|NULL
     */
    public function actionGetCourseMessage($type=MsMessage::TYPE_TODO,$page=1, $time = null)
    {
    	Yii::$app->response->format = Response::FORMAT_JSON;
    	$this->layout = 'none';
    	$id = Yii::$app->user->getId();
    	$size = 10;
    	$view = 'tab-student-course';
    
    	$msgService = new MessageService();
    	$data = $msgService->getMessageByUid($id, $type, $time, $size, $size < 1 ? 0 : ((int)$page - 1) * $size);
    	
    	return $data?$data['data']:'';
    }
  
}