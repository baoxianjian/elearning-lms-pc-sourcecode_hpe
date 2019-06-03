<?php

/**
 * ailan
 */
namespace api\modules\v2\controllers;

use api\base\BaseOpenApiController;
use common\helpers\TMessageHelper;
use common\models\framework\FwUser;
use api\services\UserService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\learning\LnCourse;
use common\models\social\SoAnswer;
use common\models\social\SoAnswerComment;
use common\models\social\SoCollect;
use common\models\social\SoQuestion;
use common\models\social\SoQuestionAnswer;
use common\models\social\SoQuestionCare;
use common\models\social\SoShare;
use common\models\social\SoUserAttention;
use common\models\framework\FwTag;
use common\services\framework\TagService;
use common\services\message\MessageService;
use common\services\social\QuestionService;
use common\services\social\UserAttentionService;
use yii\helpers\ArrayHelper;
use api\models\QuestionInfo;
use common\helpers\TStringHelper;
use common\viewmodels\api\ResponseModel;
use api\models\AnswerInfo;
use common\services\common\SearchService;
use common\services\social\QuestionCareService;
use common\services\message\TimelineService;
use common\services\learning\RecordService;
use common\models\message\MsTimeline;
use common\services\learning\CourseService;
use api\models\TagInfo;
use Exception;

/**
 * 问答接口
 * @author ailan
 *
 */
class QuestionController extends BaseOpenApiController {
	public $modelClass = '';
	public $system_key=null;
	
	/**
	 * 查检是否为post方式
	 * 检查system_key
	 */
	public function requestCheck(){
		if(!Yii::$app->request->isPost){
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_003, '', '');
		}
		$queryParams = Yii::$app->request->getQueryParams();
		if(!$this->systemKeyCheck)
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'system_key', '\'system_key\' can not be empty');
		$this->system_key=$queryParams['system_key'];
		if(!parent::checkSystemKey($this->system_key))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'system_key', '\'system_key\' can not be empty');
	
		return null;
	}
	
	/**
	 * 对请求的post包进行包密
	 * @return Ambigous <multitype:, multitype:NULL unknown Ambigous <string, NULL> >
	 */
	public function DecryptRequestParam(&$httpRequestParam){
		$errorCode=null;
		$errorMessage=null;
        $rawBody = Yii::$app->request->getRawBody();
        $rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);
        
        if (!empty($errorCode)) {
        	return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_004,$errorCode,$errorMessage);
        } 
        $httpRequestParam = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object
        return null;        
	}
	
	/**
	 * action执行前进行检查,解密
	 * @param unknown $httpRequestParam
	 * @return \api\modules\v2\controllers\Ambigous|Ambigous <NULL, multitype:, multitype:NULL unknown Ambigous <string, NULL> >
	 */
	public function preExecuteAction(&$httpRequestParam){
		$result=$this->requestCheck();
		if($result ==null)
			return $this->DecryptRequestParam($httpRequestParam);
		return $result;
	}
	
	
	
	
	/**
	 * 获取话题TAG
	 * 
	 * @return Ambigous <string, unknown>
	 */
	public function actionGetTag() {
		
		$req_param=null;
		$err=$this->preExecuteAction($req_param);
		if($err!=null){
			return $err;
		}
		
		if(!isset($req_param['word']) || empty($req_param['word']))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'word', '\'word\' can not be empty');
		$word=$req_param['word'];
		
		$companyId = $this->user->company_id;
		
		$tagService = new TagService ();
		$tags = $tagService->getTagValueListWithTagValue($companyId, 'conversation', $word );
		
		$i=0;
		foreach ($tags as $v){
			$result[$i]=new TagInfo();
			$result[$i]->tag_value=$v->tag_value;
			$i++;
		}
		
		$resp=ResponseModel::wrapResponseObject($result,$this->system_key);
		return $resp;
	}
	
	
	/**
	 * 我要提问
	 * @return array|string
	 */
	public function actionCreateQuestion()
	{
		$req_param=null;
		$err=$this->preExecuteAction($req_param);
		if($err!=null){
			return $err;
		}
		
		$model = new SoQuestion();
		
		if ($model->load($req_param,'')) {
			$uid = $this->user->kid;
			$company_id = Yii::$app->user->identity->company_id;
			$user = FwUser::findOne($uid);
			$service = new QuestionService();
			$model->user_id = $uid;
			$model->company_id = $company_id;
			$model->tags = $req_param['tags'];
//			$model->created_from=SoQuestion::CREATED_FROM_MOBILE;
//			$model->updated_from=SoQuestion::CREATED_FROM_MOBILE;
			$model->systemKey = $this->systemKey;
			$at_users = $req_param['select_value'];
			
			if ($at_users == '') {
				$at_users = null;
			} else {
				$at_users = explode('|', substr($at_users, 1));
	
				if ($at_users != null && count($at_users) === 1 && $at_users[0] === 'all') {
					$searchService = new SearchService();
					$at_users = $searchService->GetPeopleByCompanyId($company_id, true);
					if (isset($at_users) && $at_users != null) {
						$at_users = ArrayHelper::map($at_users, 'kid', 'kid');
						$at_users = array_keys($at_users);
					}
				}
			}
	
			if ($service->CreateQuestion($model, $user)) {
				$recordService = new \common\services\learning\RecordService();
				$timelineService = new TimelineService();
	
				if ($at_users == null || count($at_users) == 0) {
					// 学习记录添加
					$recordService->addBySubQuestion($uid, $model);
	
					// 时间树 推送
					$timelineService->pushBySubQuestion($uid, $model);
				} else {
					$service->saveAtUser($model->kid, $at_users);
	
					// 学习记录添加
					$recordService->addByQuestionAt($uid, $model->kid, $at_users);
	
					// 时间树 推送
					$timelineService->pushBySubQuestion($uid, $model);
	
					// 时间树 推送
					$timelineService->pushByQuestionAt($uid, $model, $at_users);
	
					// 消息推送
					$messageService = new MessageService();
					$messageService->pushByQuestionAt($uid, $model, $at_users);
				}
				return ResponseModel::wrapResponseObject(['result' => 'success'],$this->system_key);
			} else {
				$errors = array_values($model->getErrors());
				$message = '';
				for ($i = 0; $i < count($errors); $i++) {
					$message .= $errors[$i][0] . '\n';
				}
	
				return ResponseModel::getErrorResponse($this->systemKey,'other', $message, $message);
			}
		} else {
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, '', 'request param is error');
		}
	}	
	
	
	//http://elearning/api/v1/question/answer?access-token=aacb1c6927cf882206e35b3df67eb923&question_id=A071AF67-934D-9A9A-B30A-99CFC6E6571C&answer_content=测试
	/**
	 * 问题回答
	 * @return multitype:string
	 */
	public function actionAnswer()
	{
		$req_param=null;
		$err=$this->preExecuteAction($req_param);
		if($err!=null){
			return $err;
		}
		
		if(empty($req_param['question_id']))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'question_id','\'question_id\' can not be empty');
		if(empty($req_param['answer_content']))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'answer_content','\'answer_content\' can not be empty');
		
		$id = $this->user->kid;
		$model = new SoAnswer();
		$model->user_id = $id;
		$model->systemKey = $this->system_key;
		$model->needReturnKey = true;
		if ($model->load($req_param,'') && $model->save()) {
			SoQuestion::addFieldNumber($model->question_id, "answer_num");
			$messageService = new MessageService();
			// 向提问者推消息
			$messageService->QuestionAnswerToSub($model);
			// 向关注问题者推消息
			$messageService->QuestionAnswerToCare($model);
			
			// 学习历程添加
			$recordService = new RecordService();
			$recordService->addByAnswerQuestion($id, $model->question_id);
			
			//添加积分
			$question = SoQuestion::findOne($model->question_id);
			if($question->obj_id){//课程问答回复
				$this->curUserCheckActionForPoint('Reply-Course-Question', $this->system_key, $model->question_id);
			}else{
				$this->curUserCheckActionForPoint('Reply-Common-Question', $this->system_key, $model->question_id);
			}
			
            return ResponseModel::wrapResponseObject(['kid' => $model->kid],$this->system_key);
        } else {
            $errors = array_values($model->getErrors());
            $message = '';
            for ($i = 0; $i < count($errors); $i++) {
                $message .= $errors[$i][0] . '\n';
            }
			return ResponseModel::getErrorResponse($this->systemKey,'other', $message, $message);
        }
    }
    
    /**
     * 对问题回复进行评论
     * @return multitype:string
     */
    public function actionAnswerComment()
    {
    	$req_param=null;
    	$err=$this->preExecuteAction($req_param);
    	if($err!=null){
    		return $err;
    	}
    	
    	if(empty($req_param['answer_id']))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'answer_id','\'answer_id\' can not be empty');
    	if(empty($req_param['comment_content']))
    		return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'comment_content','\'comment_content\' can not be empty');
    	
    	
    	$id = $this->user->kid;
    	$model = new SoAnswerComment();
    	$model->user_id = $id;
    
    	if ($model->load($req_param,'') && $model->SubAnswerComment()) {
    		$messageService = new MessageService();
    		// 向回答者推消息
    		$messageService->AnswerComment($model);
    		
    		$answerModel = SoAnswer::findOne($model->answer_id);
    		//添加积分
    		$question = SoQuestion::findOne($answerModel->question_id);
    		if($question->obj_id){//课程问答回复
	    		$this->curUserCheckActionForPoint('Comment-Course-Question', $this->system_key, $answerModel->question_id);
    		}else{
	    		$this->curUserCheckActionForPoint('Comment-Common-Question', $this->system_key, $answerModel->question_id);
    		}
    		
    		return ResponseModel::wrapResponseObject(['result' => 'success'],$this->system_key);
    	} else {
    		$errors = array_values($model->getErrors());
    		$message = '';
    		for ($i = 0; $i < count($errors); $i++) {
    			$message .= $errors[$i][0] . '\n';
    		}
    
    		return ResponseModel::getErrorResponse($this->systemKey,'other', $message, $message);
    	}
    }
    
    /**
     * 根据回复ID获取评论列表
     * @return unknown
     */
    public function  actionAnswerCommentList()
    {
    	$req_param=null;
    	$err=$this->preExecuteAction($req_param);
    	if($err!=null){
    		return $err;
    	}
    	
    	$current_time = $req_param['current_time'];
    	if(empty($current_time))
    		return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'current_time', '\'current_time\' can not be empty');
    	
    	if(empty($req_param['answer_id']))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'answer_id','\'answer_id\' can not be empty');
    
    	$answer_id=$req_param['answer_id'];
    	
    	try {
    		$service = new QuestionService();
    		$comment_list = $service->getAnswerCommentByID($answer_id);
    	} catch (Exception $e) {
    		return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_000, $e->getMessage(), $e->getMessage());
    	}
    	return  ResponseModel::wrapResponseObject($comment_list,$this->system_key);
    }
    
	
	
	// http://elearning/api/v1/question/detail?access-token=aacb1c6927cf882206e35b3df67eb923&id=1D0C9C49-1C4B-CBD9-BB2E-DD2289BA07D1
	/**
	 * 问题明细
	 * @return Ambigous <string, string>
	 */
	public function actionDetail()
	{
		$req_param=null;
		$err=$this->preExecuteAction($req_param);
		if($err!=null){
			return $err;
		}
		
		if(!isset($req_param['id']) || empty($req_param['id']))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'id', '\'id\' can not be empty');
		
		$question_id = $req_param['id'];
		$uid = $this->user->kid;
		$companyId = $this->user->company_id;
		SoQuestion::addFieldNumber($question_id,"browse_num");/*增加浏览数据*/
		$service = new QuestionService();
		$question = $service->getQuestionById($question_id);
		
		$canOperate = $service->canOperate($uid, $question_id);
		$isCollect = $service->isCollect($uid, $question_id);
		$isCare = $service->isCare($uid, $question_id);
		$isShare=$service->isShare($uid, $question_id);
		
		$tagService = new TagService();
		$tags = $tagService->getTagListBySubjectId($companyId, 'conversation', $question_id);
		$tag_values = null;
		foreach ($tags as $v){
			if(empty($tag_values)){
				$tag_values=$v->tag_value;
				continue;
			}
			$tag_values=$tag_values.'|'.$v->tag_value;
		}
		
		$questionUser = FwUser::findIdentity($question->user_id);
		$questionInfo=new QuestionInfo();
		$questionInfo->loadWithObject($question);
		$questionInfo->real_name=$questionUser->real_name;
		$questionInfo->thumb=TStringHelper::Thumb($questionUser->thumb,$questionUser->gender);
		$questionInfo->gender=$questionUser->gender;
		$questionInfo->tags=$tag_values;
		$questionInfo->canOperate=$canOperate;
		$questionInfo->isCare=$isCare;
		$questionInfo->isCollect=$isCollect;
		$questionInfo->isShare=$isShare;
		
		$attentionService = new UserAttentionService();
		$attentionUser = $attentionService->getAllAttentionUserId($uid);
		$attentionUser = ArrayHelper::map($attentionUser, 'attention_id', 'attention_id');
		$attentionUser = array_keys($attentionUser);
		
		if($uid !=$questionInfo->user_id && in_array($questionInfo->user_id,$attentionUser))
			$questionInfo->isUserCared=true;
		else 
			$questionInfo->isUserCared=false;
		
		if(!empty($questionInfo->obj_id) && $questionInfo->question_type==SoQuestion::QUESTION_TYPE_COURSE){
			$courseModel = CourseService::findOne($questionInfo->obj_id);
			if(!empty($courseModel))
				$questionInfo->obj_title=$courseModel->course_name;
		}
		
		return ResponseModel::wrapResponseObject($questionInfo,$this->system_key);
		
	}
	
	// http://elearning/api/v1/question/answer-list?access-token=aacb1c6927cf882206e35b3df67eb923&id=EC604D7F-5DEB-DF5A-2E4F-691764B90C9B
	/**
	 * 问题回答列表接口
	* @return Ambigous <string, string>
	*/
	public function actionAnswerList()
	{
		$req_param=null;
		$err=$this->preExecuteAction($req_param);
		if($err!=null){
			return $err;
		}
		
		$question_id = $req_param['id'];
		if(empty($question_id))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'id', '\'id\' can not be empty');
		
		$current_time = $req_param['current_time'];
		if(empty($current_time))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'current_time', '\'current_time\' can not be empty');
		
		$uid = $this->user->kid;
		$companyId = $this->user->company_id;
	
		$attentionService = new UserAttentionService();
		$attentionUser = $attentionService->getAllAttentionUserId($uid);
		$attentionUser = ArrayHelper::map($attentionUser, 'attention_id', 'attention_id');
		$attentionUser = array_keys($attentionUser);
		
		$service = new QuestionService();
		$answer_list = $service->getAnswerByQAId($question_id);
		$i=0;
		foreach ($answer_list as $v){
			$rep[$i]=new AnswerInfo();
			$rep[$i]->loadWithArray($v);
			//$rep[$i]->thumb=TStringHelper::Thumb($rep[$i]->thumb);
			if(in_array($rep[$i]->user_id,$attentionUser))
				$rep[$i]->isCare=true;
			else 
				$rep[$i]->isCare=false;
			$i++;
		}
		
		return ResponseModel::wrapResponseObject($rep,$this->system_key);
	}
	
	/**
	 * 设置问题答案
	 * @return multitype:string
	 */
	public function actionSetRightAnswer()
	{
		$req_param=null;
		$err=$this->preExecuteAction($req_param);
		if($err!=null){
			return $err;
		}
		
		$question_id = $req_param['qid'];
		$answer_id=$req_param['aid'];
		if(empty($question_id))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'qid', '\'qid\' can not be empty');
		if(empty($answer_id))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'aid', '\'aid\' can not be empty');
		
		$id = Yii::$app->user->getId();
		$service = new QuestionService();
	
		if (!$service->canOperate($id, $question_id)) {
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_OTHER, '无权操作', '无权操作');
		}
	
		$model = new SoQuestionAnswer();
		$model->question_id = $question_id;
		$model->answer_id = $answer_id;
	
		if ($service->setRightAnswers($model)) {/*同时将问题设置为已解答*/
			return ResponseModel::wrapResponseObject(['result' => 'success'],$this->system_key);
		} else {
			$errors = array_values($model->getErrors());
			$message = '';
			for ($i = 0; $i < count($errors); $i++) {
				$message .= $errors[$i][0] . '';
			}
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_OTHER, $message, $message);
		}
	}
	
	
	/*提交课程问题*/
	public function actionCreateCourseQuestion(){
		$req_param=null;
		$err=$this->preExecuteAction($req_param);
		if($err!=null){
			return $err;
		}
		
		if (empty($req_param['obj_id']))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'obj_id', '\'obj_id\' can not be empty');
		
		$id=$req_param['obj_id'];
		$model = new SoQuestion();
//		$model->created_from=SoQuestion::CREATED_FROM_MOBILE;
//		$model->updated_from=SoQuestion::CREATED_FROM_MOBILE;
		$model->systemKey = $this->systemKey;
		$uid = Yii::$app->user->getId();
		$model->user_id = $uid;
		$model->obj_id = $id;
		$companyId = Yii::$app->user->identity->company_id;
		$model->company_id = $companyId;
		$model->question_type = SoQuestion::QUESTION_TYPE_COURSE;
		$model->question_content = $req_param['question_content'];
		$model->title = $req_param['title'];
		$at_users = explode('|', substr($req_param['select_value'], 1));
		$tag = $req_param['tags'];
		$model->tags = explode(',', $tag);
		$questionService = new QuestionService();
		if ($questionService->CreateQuestion($model,FwUser::findOne($uid,false))) {
			/*复制课程标签*/
			$copyModel = new FwTag();
			$copyModel->setCopyTag($id, $model->kid);
			// 消息推送
			$messageService = new MessageService();
			/*$messageService->pushMessageByQuestion($user, $model, null);//问题分享*/
			$messageService->pushByQuestionAt($uid, $model, $at_users);
			// 学习记录添加
			$recordService = new RecordService();
			//$recordService->addBySubQuestion($uid, $model);
			// 时间树 推送
			$timelineService = new TimelineService();
			$timelineService->pushBySubQuestion($uid, $model);
			if (!empty($at_users)) {
				$timelineService->pushByQuestionAt($uid, $model, $at_users);
				$questionService->saveAtUser($model->kid,$at_users);
				$recordService->addByQuestionAt($uid, $model->kid, $at_users);
			}
			return ResponseModel::wrapResponseObject(['result' => 'success'],$this->system_key);
		} else {
			$errors = array_values($model->getErrors());
			$message = '';
			for ($i = 0; $i < count($errors); $i++) {
				$message .= $errors[$i][0] . '\n';
			}
			return ResponseModel::getErrorResponse($this->systemKey,'other', $message, $message);
		}
	}
	
	
	/*获取课程问题记录*/
	public function actionGetCourseQuestion(){
		$req_param=null;
		$err=$this->preExecuteAction($req_param);
		if($err!=null){
			return $err;
		}
		
		$id=$req_param['id'];
		$page=$req_param['page'];
		$keyword=$req_param['keyword'];
		
		if(empty($id))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'id', '\'id\' can not be empty');
		if(empty($page))
			$page=1;
		if(empty($keyword))
			$keyword=null;
		
		$size=10;
		$service = new QuestionService();
		$question = $service->getCourseQuestionWithID($id,$keyword,$size, $page);
	
		return ResponseModel::wrapResponseObject($question,$this->system_key);
	}
	
	
	/**
	 * 所有根所企业ＩＤ查询已解决　未解决　问题列表
	 * @param number $page
	 * @param int $time
	 * @return
	 */
	public function actionGetQuestion()
	{
		$req_param=null;
		$err=$this->preExecuteAction($req_param);
		if($err!=null){
			return $err;
		}
		
		$current_time = $req_param['current_time'];
		if(empty($current_time))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'current_time', '\'current_time\' can not be empty');

		$page=$req_param['page'];
		if(empty($page))
			$page=1;

		$is_resolved=$req_param['is_resolved'];
		if(empty($is_resolved) && $is_resolved !=0)
			$is_resolved=null;
		
		$is_hot=$req_param['is_hot'];
		if(empty($is_hot))
			$is_hot=false;

		$tag=$req_param['tag'];
		if(empty($tag))
			$tag=null;
		
		$keyword=$req_param['keyword'];
		if(empty($keyword))
			$keyword=null;


		$id = $this->user->kid;
		$company_id = $this->user->company_id;

		$size = 10;
		$service = new QuestionService();
		$question=null;
		if($is_hot){
			$question = $service->getHotQuestionList($company_id);
			$_tmp = [];
			foreach($question as $q) {
				$_tmp[] = is_callable([$q,'toArray']) ? $q->toArray() : $q;
			}
			$question=$_tmp;
		}else{
			$question = $service->getPageListQuestionByCondition($company_id,$tag,$is_resolved, $size, $page,$keyword,$current_time);
		}
		return  ResponseModel::wrapResponseObject($question,$this->system_key);
	}
	
	/**
	 * 问题关注
	 * 取消关注
	 * @return multitype:string
	 */
	public function actionCare()
	{
		$req_param=null;
		$err=$this->preExecuteAction($req_param);
		if($err!=null){
			return $err;
		}
		
		$uid = $this->user->kid;
		$question_id=$req_param['qid'];
		
		if(empty($question_id))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'qid', '\'qid\' can not be empty');
		
		$careService = new QuestionCareService();
	
		$careModel = new SoQuestionCare();
		$careModel->user_id = $uid;
		$careModel->question_id = $question_id;
	
		$service = new QuestionService();
		if ($careService->IsRelationshipExist($careModel)) {
			// 停止关注
			$careService->StopRelationship($careModel);
	
			// 删除时间树
			$timelineService = new TimelineService();
	
			$timelineModel = new MsTimeline();
			$timelineModel->owner_id = $uid;
			$timelineModel->object_id = $question_id;
			$timelineModel->object_type = MsTimeline::OBJECT_TYPE_QUESTION;
			$timelineModel->type_code = MsTimeline::TYPE_ATTENTION_QUESTION;
	
			$timelineService->deleteTimeline($timelineModel);
	
			SoQuestion::subFieldNumber($question_id, 'attention_num');
//			return ResponseModel::wrapResponseObject(['result' => 'success'],$this->system_key);
			return ResponseModel::wrapResponseObject(['result' => 'success','status'=>'cancel'],$this->system_key);
		} else {
			// 增加积分
			$this->curUserCheckActionForPoint('Attention-Question', $this->system_key, $question_id);
			
			// 添加关注关系
			$careService->startRelationship($careModel);
		
			// 增加关注统计值
			SoQuestion::addFieldNumber($question_id, 'attention_num');
	
			// 添加时间树
			$timelineService = new TimelineService();
			$timelineService->pushByCareQuestion($uid, $question_id);
	
			// 学习历程添加
			$recordService = new RecordService();
			$recordService->addByCareQuestion($uid, $question_id);
	
			return ResponseModel::wrapResponseObject(['result' => 'success','status'=>'care'],$this->system_key);
		}
	}
	
	/**
	 * 问题分享
	 * @return multitype:string
	 */
	public function actionShare()
	{
		$req_param=null;
		$err=$this->preExecuteAction($req_param);
		if($err!=null){
			return $err;
		}
		
		if(empty($req_param['obj_id']))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'obj_id', '\'obj_id\' can not be empty');
		
		if(empty($req_param['title']))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'title', '\'title\' can not be empty');
		
		if(empty($req_param['content']))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'content', '\'content\' can not be empty');
		
		$uid = $this->user->kid;
		$model = new SoShare();
		$model->user_id = $uid;
		$model->type = SoShare::SHARE_TYPE_QUESTION;
		$model->systemKey = $this->system_key;
		if ($model->load($req_param,'') && $model->save()) {
			SoQuestion::addFieldNumber($model->obj_id, "share_num");
	
			$attentionService = new UserAttentionService();
			//获取所有关注对象
			$user_attention = $attentionService->getAllUserId($uid);
			if (isset($user_attention) && $user_attention != null) {
				$user_attention = ArrayHelper::map($user_attention, 'user_id', 'user_id');
				$user_attention = array_keys($user_attention);
			}
			
			//添加积分
			//$this->curUserCheckActionForPoint('Publish-Sharing',$this->system_key,$req_param['obj_id']);
			
			// 时间轴添加
			$timelineService = new TimelineService();
			$timelineService->pushByShareQuestion($uid, $user_attention, $model->obj_id, $model->content);
	
			// 消息推送
			$service = new MessageService();
			$service->pushMessageByQuestionShare($uid, $model, $user_attention);
			return ResponseModel::wrapResponseObject(['result' => 'success'],$this->system_key);
		} else {
			$errors = array_values($model->getErrors());
			$message = '';
			for ($i = 0; $i < count($errors); $i++) {
				$message .= $errors[$i][0] . '';
			}
	
			return ResponseModel::getErrorResponse($this->systemKey,'other', $message, $message);
		}
	}
	
}