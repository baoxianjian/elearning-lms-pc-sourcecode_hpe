<?php


namespace api\modules\v2\controllers;

use api\base\BaseOpenApiController;
use common\helpers\TMessageHelper;
use common\models\framework\FwUser;
use api\services\UserService;
use common\models\learning\LnCertification;
use common\models\learning\LnRecord;
use common\models\learning\LnUserCertification;
use common\models\social\SoRecord;
use common\models\social\SoUserAttention;
use common\helpers\TFileUploadHelper;
use common\services\message\MessageService;
use common\services\message\TimelineService;
use common\services\social\UserAttentionService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\viewmodels\api\ResponseModel;
use common\services\learning\CourseService;
use common\services\social\QuestionService;
use common\services\social\ShareService;
use common\services\learning\RecordService;
use common\models\social\SoAnswer;

/**
 * 
 * 学习者接口
 * @author ailan
 *
 */
class StudentController extends BaseOpenApiController
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

	/**
	 * 对问题回答点赞接口
	 * @return multitype:string
	 */
	public function actionAnswerPraise()
	{
		$req_param = null;
		$err = $this->preExecuteAction($req_param);
		if ($err != null) {
			return $err;
		}

		$id = $req_param['id'];
		if (empty($id))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'id', '\'id\' can not be empty');

		//$uid = Yii::$app->user->getId();
		$result = SoAnswer::addFieldNumber($id, "praise_num");/*增加点赞数据*/
		return ResponseModel::wrapResponseObject($result, $this->system_key);
	}

	/**
	 * 学习历程->课程
	 * @param $page
	 * @param null $time
	 * @return string
	 */
	public function  actionMyCoursePath($page = 1, $time = null)
	{
		$req_param = null;
		$err = $this->preExecuteAction($req_param);
		if ($err != null) {
			return $err;
		}

		$id = $this->user->kid;

		$size = 10;
		$service = new CourseService();
		$data = $service->getAllRegCourseByUid($id, $time, $size, $page);

		return ResponseModel::wrapResponseObject($data, $this->system_key);
	}

	/**
	 * 学习历程->问答
	 * @param number $page
	 * @param int $time
	 * @param number $type 1－最新　2-最热　　3－回复最多
	 * @return
	 */
	public function actionGetQuestion()
	{
		$req_param = null;
		$err = $this->preExecuteAction($req_param);
		if ($err != null) {
			return $err;
		}

		$current_time = $req_param['current_time'];
		if (empty($current_time))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'current_time', '\'current_time\' can not be empty');

		$page = $req_param['page'];
		$time = $req_param['time'];
		$type = $req_param['type'];

		if (empty($page))
			$page = 1;
		if (empty($time))
			$time = null;
		if (empty($type))
			$type = 0;

		$id = $this->user->kid;

		$size = 10;
		$service = new QuestionService();
		$question = $service->getQuestionPageDataByType($id, $time, $size, $page, $type);

		return ResponseModel::wrapResponseObject($question, $this->system_key);
	}

	/**
	 * 我收藏问题列表
	 * @param number $page
	 * @param int $time
	 * @return
	 */
	public function actionMyCollectQuestion()
	{
		$req_param = null;
		$err = $this->preExecuteAction($req_param);
		if ($err != null) {
			return $err;
		}

		$current_time = $req_param['current_time'];
		if (empty($current_time))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'current_time', '\'current_time\' can not be empty');

		$page = $req_param['page'];
		if (empty($page))
			$page = 1;

		$is_resolved = $req_param['is_resolved'];
		if (empty($is_resolved))
			$is_resolved = null;

		$id = $this->user->kid;

		$size = 10;
		$service = new QuestionService();
		$question = $service->getMyCollectQuestion($id, $is_resolved, $size, $page,$current_time);

		return ResponseModel::wrapResponseObject($question, $this->system_key);
	}


	/**
	 * 我关注的问题列表
	 * @param number $page
	 * @param int $time
	 * @return
	 */
	public function actionMyCareQuestion()
	{
		$req_param = null;
		$err = $this->preExecuteAction($req_param);
		if ($err != null) {
			return $err;
		}

		$current_time = $req_param['current_time'];
		if (empty($current_time))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'current_time', '\'current_time\' can not be empty');

		$page = $req_param['page'];
		if (empty($page))
			$page = 1;

		$is_resolved = $req_param['is_resolved'];
		if (empty($is_resolved))
			$is_resolved = null;

		$id = $this->user->kid;
		$company_id = $this->user->company_id;

		$size = 10;
		$service = new QuestionService();
		$question = $service->getMycareQuestion($company_id, null, $id, $is_resolved, $size, $page, $current_time);

		return ResponseModel::wrapResponseObject($question, $this->system_key);
	}

	/**
	 * 获取@我的问题列表
	 * @param number $page
	 * @param int $time
	 * @return
	 */
	public function actionAtMyQuestion()
	{
		$req_param = null;
		$err = $this->preExecuteAction($req_param);
		if ($err != null) {
			return $err;
		}

		$current_time = $req_param['current_time'];
		if (empty($current_time))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'current_time', '\'current_time\' can not be empty');

		$page = $req_param['page'];
		if (empty($page))
			$page = 1;

		$is_resolved = $req_param['is_resolved'];
		if (empty($is_resolved))
			$is_resolved = null;

		$id = $this->user->kid;
		$company_id = $this->user->company_id;

		$size = 10;
		$service = new QuestionService();
		$question = $service->getAtmeQuestion($company_id, null, $id, $is_resolved, $size, $page, $current_time);

		return ResponseModel::wrapResponseObject($question, $this->system_key);
	}

	/**
	 * 获取我回复过的问题列表
	 * @param number $page
	 * @param int $time
	 * @return
	 */
	public function actionMyAnswerQuestion()
	{
		$req_param = null;
		$err = $this->preExecuteAction($req_param);
		if ($err != null) {
			return $err;
		}

		$current_time = $req_param['current_time'];
		if (empty($current_time))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'current_time', '\'current_time\' can not be empty');

		$page = $req_param['page'];
		if (empty($page))
			$page = 1;

		$is_resolved = $req_param['is_resolved'];
		if (empty($is_resolved))
			$is_resolved = null;

		$id = $this->user->kid;
		$company_id = $this->user->company_id;

		$size = 10;
		$service = new QuestionService();
		$question = $service->getMyAnswerQuestion($company_id, null, $id, $is_resolved, $size, $page, $current_time);

		return ResponseModel::wrapResponseObject($question, $this->system_key);
	}


	/**
	 * 我提的问题列表
	 * @param number $page
	 * @param int $time
	 * @return
	 */
	public function actionGetMyQuestion()
	{
		$req_param = null;
		$err = $this->preExecuteAction($req_param);
		if ($err != null) {
			return $err;
		}

		$current_time = $req_param['current_time'];
		if (empty($current_time))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'current_time', '\'current_time\' can not be empty');

		$page = $req_param['page'];
		if (empty($page))
			$page = 1;

		$id = $this->user->kid;

		$size = 10;
		$service = new QuestionService();
		$question = $service->getQuestionListById($id, $size, $page);

		return ResponseModel::wrapResponseObject($question, $this->system_key);
	}

	/**
	 * 通过用户ID获取所有回复列表
	 * @param number $page
	 * @param int $time
	 * @return
	 */
	public function actionGetMyAnswer()
	{
		$req_param = null;
		$err = $this->preExecuteAction($req_param);
		if ($err != null) {
			return $err;
		}

		$current_time = $req_param['current_time'];
		if (empty($current_time))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'current_time', '\'current_time\' can not be empty');

		$id = $this->user->kid;

		$service = new QuestionService();
		$question = $service->getAnswerById($id);

		return ResponseModel::wrapResponseObject($question, $this->system_key);
	}


	/**
	 * 学习历程->分享
	 */
	public function actionGetShare($page = 1, $time = null)
	{
		$req_param = null;
		$err = $this->preExecuteAction($req_param);
		if ($err != null) {
			return $err;
		}

		$id = $this->user->kid;

		$service = new ShareService();
		$size = 10;

		$share = $service->getSharePageDataById($id, $time, $size, $page);

		return ResponseModel::wrapResponseObject($share, $this->system_key);
	}

	/**
	 * 学习历程->记录
	 */
	public function actionGetRecord($page = 1, $time = null)
	{
		$req_param = null;
		$err = $this->preExecuteAction($req_param);
		if ($err != null) {
			return $err;
		}

		$id = $this->user->kid;

		$service = new RecordService();
		$size = 10;

		$record = $service->getRecordPageDataById($id, $time, $size, $page);

		return ResponseModel::wrapResponseObject($record, $this->system_key);
	}


	/**
	 * 学员首页->记录网页
	 * @return array|string
	 */
	public function actionIndexTabWeb()
	{
		$code = "addWeb";
		$name = null;
		$message = null;

		$errorCode = null;
		$errorMessage = null;

		$model = new SoRecord();


		if (Yii::$app->request->isGet) {
			$number = "003";
			$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
			$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
			return $result;
		} else {
			$rawBody = Yii::$app->request->getRawBody();
			$rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

			if (!empty($errorCode)) {
				$number = "004";
				$errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
				$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
				return $result;
			} else {
				$bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object

				if (isset($bodyParams['title']) && $bodyParams['title'] != "") {
					$title = $bodyParams['title'];
				} else {
					$number = "001";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "title");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}

				if (isset($bodyParams['content']) && $bodyParams['content'] != "") {
					$content = $bodyParams['content'];
				} else {
					$number = "002";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "content");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}
				if (isset($bodyParams['url']) && $bodyParams['url'] != "") {
					$url = $bodyParams['url'];
				} else {
					$number = "003";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "url");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}


				if (isset($bodyParams['isShare']) && $bodyParams['isShare'] != "") {
					$is_Share = $bodyParams['isShare'];
				} else {
					$number = "004";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "isShare");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}

			}
		}


		$model->setScenario('web');
		$uid = $this->user->kid;
		$model->record_type = "0";
		$model->user_id = $uid;
		$model->created_by = $uid;
		$model->title = $title;
		$model->content = $content;
		$model->url = $url;


		if (isset($bodyParams['duration']) && $bodyParams['duration'] != "") {

			$model->duration = (int)($bodyParams['duration']);
		}
		$model->systemKey = $this->system_key;
		$model->needReturnKey = true;
		if ($model->save()) {
			$recordService = new \common\services\learning\RecordService();
			$recordService->addByRecord($uid, $model->kid, LnRecord::RECORD_CATEGORY_WEB);
			//添加积分
			$this->curUserCheckActionForPoint('Publish-Page',/* Learning-Portal */$this->systemKey,$model->kid);
			
			if ($is_Share == '1') {
				$service = new UserAttentionService();
				//获取所有关注对象
				$user_attention = $service->getAllUserId($uid);

				$timelineService = new TimelineService();
				// 推送动态
				$timelineService->PushTimelineByShare($uid, $user_attention, $model);

				$messageService = new MessageService();
				// 推送消息
				$messageService->PushMessageByShare($uid, $user_attention, $model);
			}

			$result = TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name, $message, ['result' => 'success']);
			return $result;
		} else {
			$result = TMessageHelper::resultBuild($this->systemKey, $code = 'NO', $name, $message = $model->getErrors(), ['result' => 'Save failed']);
			return $result;
		}
	}


	/**
	 * 学员首页->记录事件
	 * @return array|string
	 */
	public function actionIndexTabEvent()
	{
		$code = "addEvent";
		$name = null;
		$message = null;

		$errorCode = null;
		$errorMessage = null;


		$model = new SoRecord();
		$model->setScenario('event');


		if (Yii::$app->request->isGet) {
			$number = "003";
			$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
			$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
			return $result;
		} else {
			$rawBody = Yii::$app->request->getRawBody();
			$rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

			if (!empty($errorCode)) {
				$number = "004";
				$errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
				$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
				return $result;
			} else {
				$bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object

				if (isset($bodyParams['title']) && $bodyParams['title'] != "") {
					$title = $bodyParams['title'];
				} else {
					$number = "001";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "title");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}

				if (isset($bodyParams['content']) && $bodyParams['content'] != "") {
					$content = $bodyParams['content'];
				} else {
					$number = "001";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "content");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}

				if (isset($bodyParams['isShare']) && $bodyParams['isShare'] != "") {
					$is_Share = $bodyParams['isShare'];
				} else {
					$number = "001";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "isShare");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}

			}

			$uid = $this->user->kid;
			$model->user_id = $uid;
			$model->title = $title;
			$model->content = $content;
			$model->record_type = "1";
			if (isset($bodyParams["url"]) && $bodyParams["url"] != "")
				$model->url = $bodyParams["url"];

			if (isset($bodyParams['duration']) && $bodyParams['duration'] != "") {
				$model->duration = (int)($bodyParams['duration']);
			}
			if (isset($bodyParams['start_at']) && $bodyParams['start_at'] != "") {
				$model->start_at = $bodyParams['start_at'];
			} else {

				$model->start_at = "";
			}
			$model->systemKey = $this->system_key;
			$model->needReturnKey = true;
			if ($model->save()) {
				$recordService = new \common\services\learning\RecordService();
				$recordService->addByRecord($uid, $model->kid, LnRecord::RECORD_CATEGORY_EVENT);
				//添加积分
				$this->curUserCheckActionForPoint('Publish-Event',/* Learning-Portal */$this->systemKey,$model->kid);
					
				if ($is_Share == '1') {
					$service = new UserAttentionService();
					//获取所有关注对象
					$user_attention = $service->getAllUserId($uid);

					$timelineService = new TimelineService();
					// 推送动态
					$timelineService->PushTimelineByShare($uid, $user_attention, $model);

					$messageService = new MessageService();
					// 推送消息
					$messageService->PushMessageByShare($uid, $user_attention, $model);
				}
				$result = TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name, $message, ['result' => 'success']);
				return $result;
			} else {
				$result = TMessageHelper::resultBuild($this->systemKey, $code = 'NO', $name, $message = $model->getErrors(), ['result' => 'failed']);
				return $result;
			}
		}
	}


	/**
	 * 学员首页->记录书籍
	 * @return array|string
	 */
	public function actionIndexTabBook()
	{
		$code = "addBook";
		$name = null;
		$message = null;

		$errorCode = null;
		$errorMessage = null;


		$model = new SoRecord();
		$model->setScenario('book');


		if (Yii::$app->request->isGet) {
			$number = "003";
			$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
			$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
			return $result;
		} else {
			$rawBody = Yii::$app->request->getRawBody();
			$rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

			if (!empty($errorCode)) {
				$number = "004";
				$errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
				$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
				return $result;
			} else {
				$bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object

				if (isset($bodyParams['title']) && $bodyParams['title'] != "") {
					$title = $bodyParams['title'];
				} else {
					$number = "001";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "title");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}

				if (isset($bodyParams['content']) && $bodyParams['content'] != "") {
					$content = $bodyParams['content'];
				} else {
					$number = "001";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "content");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}


				if (isset($bodyParams['isShare']) && $bodyParams['isShare'] != "") {
					$is_Share = $bodyParams['isShare'];
				} else {
					$number = "001";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "isShare");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}

			}

			$uid = $this->user->kid;
			$model->user_id = $uid;
			$model->record_type = "2";
			$model->title = $title;
			$model->content = $content;
			if (isset($bodyParams["url"]) && $bodyParams["url"] != "")
				$model->url = $bodyParams["url"];

			if (isset($bodyParams['duration']) && $bodyParams['duration'] != "") {

				$model->duration = (int)($bodyParams['duration']);
			}
			$model->systemKey = $this->system_key;
			$model->needReturnKey = true;
			if ($model->save()) {
				$recordService = new \common\services\learning\RecordService();
				$recordService->addByRecord($uid, $model->kid, LnRecord::RECORD_CATEGORY_BOOK);
				//添加积分
				$this->curUserCheckActionForPoint('Publish-Book',/* Learning-Portal */$this->systemKey,$model->kid);
					
				if ($is_Share == '1') {
					$service = new UserAttentionService();
					//获取所有关注对象
					$user_attention = $service->getAllUserId($uid);

					$timelineService = new TimelineService();
					// 推送动态
					$timelineService->PushTimelineByShare($uid, $user_attention, $model);

					$messageService = new MessageService();
					// 推送消息
					$messageService->PushMessageByShare($uid, $user_attention, $model);
				}
				$result = TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name, $message, ['result' => 'success']);
				return $result;
			} else {
				$result = TMessageHelper::resultBuild($this->systemKey, $code = 'NO', $name, $message = $model->getErrors(), ['result' => 'Save failed']);
				return $result;
			}
		}
	}


	/**
	 * 学员首页->记录经验
	 * @return array|string
	 */
	public function actionIndexTabExp()
	{
		$code = "addexp";
		$name = null;
		$message = null;

		$errorCode = null;
		$errorMessage = null;


		$model = new SoRecord();
		$model->setScenario('exp');


		if (Yii::$app->request->isGet) {
			$number = "003";
			$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
			$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
			return $result;
		} else {
			$rawBody = Yii::$app->request->getRawBody();
			$rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

			if (!empty($errorCode)) {
				$number = "004";
				$errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
				$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
				return $result;
			} else {
				$bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object

				if (isset($bodyParams['title']) && $bodyParams['title'] != "") {
					$title = $bodyParams['title'];
				} else {
					$number = "001";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "title");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}

				if (isset($bodyParams['content']) && $bodyParams['content'] != "") {
					$content = $bodyParams['content'];
				} else {
					$number = "001";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "content");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}

				if (isset($bodyParams['isShare']) && $bodyParams['isShare'] != "") {
					$is_Share = $bodyParams['isShare'];
				} else {
					$number = "001";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "isShare");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}

			}

			$uid = $this->user->kid;
			$model->user_id = $uid;
			$model->title = $title;
			$model->record_type = "3";
			$model->content = $content;
			if (isset($bodyParams['attach_url']) && $bodyParams['attach_url'] != "") {
				$model->attach_url = $bodyParams['attach_url'];
			}

			if (isset($bodyParams['file_name']) && $bodyParams['file_name'] != "") {
				$model->attach_original_filename = $bodyParams['file_name'];
			}


			$model->systemKey = $this->system_key;
			$model->needReturnKey = true;
			if ($model->save()) {
				$recordService = new \common\services\learning\RecordService();
				$recordService->addByRecord($uid, $model->kid, LnRecord::RECORD_CATEGORY_EXP);
				//添加积分
				$this->curUserCheckActionForPoint('Publish-Sharing',/* Learning-Portal */$this->systemKey,$model->kid);
					
				if ($is_Share == '1') {
					$service = new UserAttentionService();
					//获取所有关注对象
					$user_attention = $service->getAllUserId($uid);

					$timelineService = new TimelineService();
					// 推送动态
					$timelineService->PushTimelineByShare($uid, $user_attention, $model);

					$messageService = new MessageService();
					// 推送消息
					$messageService->PushMessageByShare($uid, $user_attention, $model);
				}
				$result = TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name, $message, ['result' => 'success']);
				return $result;
			} else {
				$result = TMessageHelper::resultBuild($this->systemKey, $code = 'NO', $name, $message = $model->getErrors(), ['result' => 'Save failed']);
				return $result;
			}
		}
	}

	/**
	 * 记录经验->附件上传
	 * @return string
	 */
	public function actionUpload()
	{
		$name = "uploadFile";
		$message = null;

		if (!empty($_FILES)) {
			//得到上传的临时文件流
			$tempFile = $_FILES['myfile']['tmp_name'];
			$type = $_FILES['myfile']["type"];
			//得到文件原名
			$fileName = $_FILES["myfile"]["name"];
			$fileError = $_FILES["myfile"]["error"];
			$fileSize = $_FILES["myfile"]["size"];

			//允许的文件后缀
			$fileTypes = array(
					'image/jpg',
					'image/jpeg',
					'image/png',
					'image/pjpeg',
					'image/gif',
					'image/bmp',
					'image/x-png');

			if ($fileError) {
				return TMessageHelper::resultBuild($this->systemKey, $code = 'NO', $name, $message, ["result" => "Upload_Error"]);
			} else {
				$fileUpload = new TFileUploadHelper();
				$info = $fileUpload->UploadFile($_FILES["myfile"], 'recordattach/');
				if ($info['result'] == 'Completed') {
					$result = ['info' => $info['file_path'], 'filename' => $fileName];
					return TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name, $message, $result);

				} else {

					return TMessageHelper::resultBuild($this->systemKey, $code = 'NO', $name, $message, ["result" => "Upload_Error"]);
				}
			}
			echo json_encode($result);
		}
	}


	/**
	 * 学习历程tab
	 * @param $page
	 * @param $type
	 * @return string
	 */
	public function  actionMyPathList()
	{

		$code = null;
		$name = "myPathList";
		$message = null;

		$userId = $this->user->kid;

		$size = 10;
		$service = new \common\services\learning\RecordService();


		if (Yii::$app->request->isGet) {
			$number = "003";
			$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
			$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
			return $result;
		} else {

			$rawBody = Yii::$app->request->getRawBody();
			$rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

			if (!empty($errorCode)) {
				$number = "004";
				$errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
				$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
				return $result;
			} else {
				$bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object

				if (isset($bodyParams['current_time']) && $bodyParams['current_time'] != "") {
					$current_time = $bodyParams['current_time'];
				} else {
					$number = "001";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "current_time");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}


				if (isset($bodyParams['type']) && $bodyParams['type'] != "") {
					$type = $bodyParams['type'];
				} else {
					$number = "001";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "type");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}

				if (isset($bodyParams['page']) && $bodyParams['page'] != "") {
					$page = $bodyParams['page'];
				} else {
					$number = "001";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "page");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}
			}

		}


		switch ($type) {
			case LnRecord::RECORD_CATEGORY_COURSE:
				$data = $service->getRecordByUserId($userId, LnRecord::RECORD_CATEGORY_COURSE, $size, $page, $current_time);
				break;
			case LnRecord::RECORD_CATEGORY_EXAM:
				$data = $service->getRecordByUserId($userId, LnRecord::RECORD_CATEGORY_EXAM, $size, $page, $current_time);
				break;
			case LnRecord::RECORD_CATEGORY_SURVEY:
				$data = $service->getRecordByUserId($userId, LnRecord::RECORD_CATEGORY_SURVEY, $size, $page, $current_time);
				break;
			case LnRecord::RECORD_CATEGORY_QUESTION:
				$data = $service->getRecordByUserId($userId, LnRecord::RECORD_CATEGORY_QUESTION, $size, $page, $current_time);
				break;
			case LnRecord::RECORD_CATEGORY_WEB:
				$data = $service->getRecordAndDataByUserId($userId, LnRecord::RECORD_CATEGORY_WEB, $size, $page, $current_time);
				break;
			case LnRecord::RECORD_CATEGORY_EVENT:
				$data = $service->getRecordAndDataByUserId($userId, LnRecord::RECORD_CATEGORY_EVENT, $size, $page, $current_time);
				break;
			case LnRecord::RECORD_CATEGORY_BOOK:
				$data = $service->getRecordAndDataByUserId($userId, LnRecord::RECORD_CATEGORY_BOOK, $size, $page, $current_time);
				break;
			case LnRecord::RECORD_CATEGORY_EXP:
				$data = $service->getRecordAndDataByUserId($userId, LnRecord::RECORD_CATEGORY_EXP, $size, $page, $current_time);
				break;
			case LnRecord::RECORD_CATEGORY_CERT:
				$data = $service->getCertAndDataByUserId($userId, LnRecord::RECORD_CATEGORY_CERT, $size, $page, $current_time);
				break;
		}

//        var_dump($data);
		$jsonReuslt = array();

		if ($type == LnRecord::RECORD_CATEGORY_COURSE || $type == LnRecord::RECORD_CATEGORY_QUESTION) {

			$i = 0;
			foreach ($data as $courseModel) {
				$jsonReuslt[$i] = $courseModel->attributes;
				$i++;
			}

			$result = TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name, $message, $jsonReuslt);
			return $result;
		}
		else if($type == LnRecord::RECORD_CATEGORY_CERT){
			$i = 0;
			$jsonReuslt = array();
			foreach($data as $v){
				$jsonReuslt[$i] = $v;
				$model = LnUserCertification::findOne($v["object_id"]);
				$template = LnCertification::findOne($model->certification_id);
				$jsonReuslt[$i]["certification_img_url"] = $template->file_path ? $template->file_path . "preview.png" : "";
				$i++;
			}

			$result = TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name, $message="证书", $jsonReuslt);
			return $result;

		}
		else if($type == LnRecord::RECORD_CATEGORY_EXAM){

			$jsonReuslt = array();
			$i = 0;
			foreach ($data as $v){
				$jsonReuslt[$i] = $v->attributes;
				$i++;
			}

			$result = TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name, $message="考试", $jsonReuslt);
			return $result;

		}else if($type == LnRecord::RECORD_CATEGORY_SURVEY){

			$jsonReuslt = array();
			$i = 0;
			foreach ($data as $v){
				$jsonReuslt[$i] = $v->attributes;
				$i++;
			}

			$result = TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name, $message=LnRecord::RECORD_CATEGORY_SURVEY, $jsonReuslt);
			return $result;

		}
		
		else{

			$result = TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name, $message, $data);
			return $result;
		}
	}

	public function actionFollowUserList()
	{

		$code = null;
		$name = "followUserList";
		$message = null;

		$userId = $this->user->kid;

		if (Yii::$app->request->isGet) {
			$number = "003";
			$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
			$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
			return $result;
		} else {

			$rawBody = Yii::$app->request->getRawBody();
			$rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

			if (!empty($errorCode)) {
				$number = "004";
				$errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
				$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
				return $result;
			} else {
				$bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object

				if (isset($bodyParams['current_time']) && $bodyParams['current_time'] != "") {
					$current_time = $bodyParams['current_time'];
				} else {
					$number = "002";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "current_time");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}

				if (isset($bodyParams['page']) && $bodyParams['page'] != "") {
					$page = $bodyParams['page'];
				} else {
					$number = "003";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "page");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}
			}

		}


		$size = 10;

		$service = new \common\services\framework\UserService();
		$data = $service->getAttentionByUid($userId, $filter = 1, $time = null, $size, $page, $current_time);
//		var_dump($data);
		$result = TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name, $message, $data);
		return $result;

	}

	public function actionUnfollow()
	{

		$code = null;
		$name = "unfollow";
		$message = null;

		$userId = $this->user->kid;

		if (Yii::$app->request->isGet) {
			$number = "003";
			$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
			$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
			return $result;
		} else {

			$rawBody = Yii::$app->request->getRawBody();
			$rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

			if (!empty($errorCode)) {
				$number = "004";
				$errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
				$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
				return $result;
			} else {
				$bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object

				if (isset($bodyParams['uid']) && $bodyParams['uid'] != "") {
					$uid = $bodyParams['uid'];
				} else {
					$number = "002";
					$errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "current_time");
					$result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
					return $result;
				}
			}

		}

		$model = new SoUserAttention();
		$model->user_id = $userId;
		$model->attention_id = $uid;

		$service = new UserAttentionService();

		if ($service->IsRelationshipExist($model)) {
			//判断该请求是否为关注用户
			$isFollow = $bodyParams['follow'];
			if(!empty($isFollow) && $isFollow=='follow'){
				$errmsg=Yii::t('api', 'err_followed');
				return ResponseModel::getErrorResponse(ResponseModel::ERR_CODE_OTHER, $errmsg, $errmsg);
			}
			
			$service->StopRelationship($model);
//			return ['result' => 'success', 'status' => 0];

			return $result = TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name, $message, ['result' => 'success', 'status' => 0]);
		} else {
			$service->startRelationship($model);

			$msgService = new MessageService();
			$msgService->SendByCarePerson($uid);
			//添加积分
			$this->curUserCheckActionForPoint('Attention-People', /* 'Learning-Portal' */$this->systemKey, $uid);

			return $result = TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name, $message, ['result' => 'success', 'status' => 1]);
		}

	}


	public function actionUploadHeadImage()
	{
		// $physicalPath = Yii::$app->basePath."/../upload/temp/";
		$physicalPath = rtrim(Yii::getAlias("@upload/thumb/"), '/\\') . "/";
		$logicalPath = "/upload/thumb/";
		if (!empty($_FILES)) {

			//得到上传的临时文件流
			$tempFile = $_FILES['myfile']['tmp_name'];

			$type = $_FILES['myfile']["type"];

			//得到文件原名
			$fileName = $_FILES["myfile"]["name"];
			$fileParts = pathinfo($_FILES['myfile']['name']);

			$fileError = $_FILES["myfile"]["error"];
			$fileSize = $_FILES["myfile"]["size"];

			//允许的文件后缀
			$fileTypes = array(
					'image/jpg',
					'image/jpeg',
					'image/png',
					'image/pjpeg',
					'image/gif',
					'image/bmp',
					'image/x-png');

			if ($fileError) {
				$info = Yii::t('common', 'upload_error');
//                $status=0;
//                $data='';
			} else if (!in_array($type, $fileTypes)) {
				$info = Yii::t('common', 'file_type_error');
//                $status=0;
//                $data='';
			} else {
				//最后保存服务器地址
				if (!is_dir($physicalPath)) {
					mkdir($physicalPath);
				}

				$extension = 'jpg';


				$newFileName = time() . "." . $extension;
				if (move_uploaded_file($tempFile, $physicalPath . $newFileName)) {
					$info = $logicalPath . $newFileName;
					$userId = $this->user->kid;
					$user = FwUser::findOne($userId);
					$user->thumb = $info;
					$user->systemKey = $this->system_key;
					$user->save();

					return TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name = "", $message = "", ["url" => $info]);

				} else {
					return TMessageHelper::resultBuild($this->systemKey, $code = 'NO', $name = "", $message = "上传失败", ["result" => "failed"]);
				}
			}
		}
	}

	/**
	 * @裁剪头像
	 */
	public function actionCutPic()
	{
		if (Yii::$app->request->isAjax) {
			Yii::$app->response->format = Response::FORMAT_JSON;

			$yiiBasePath = Yii::$app->basePath . "/..";
			$srcLogicalPath = "/upload/temp/";
			$destPhysicalPath = Yii::$app->basePath . "/../upload/thumb/";
			$destLogicalPath = "/upload/thumb/";

			$targ_w = $targ_h = 150;
			$jpeg_quality = 100;
			$src = Yii::$app->request->post('f');
			$src = $yiiBasePath . $src;//真实的图片路径

			$type = getimagesize($src)["mime"];

			$extension = "jpg";
			switch ($type) {
				case "image/jpg":
					$img_r = imagecreatefromjpeg($src);
					break;
				case "image/jpeg":
					$img_r = imagecreatefromjpeg($src);
					break;
				case "image/pjpeg":
					$img_r = imagecreatefromjpeg($src);
					break;
				case "image/x-png":
					$img_r = imagecreatefrompng($src);
					break;
				case "image/png":
					$img_r = imagecreatefrompng($src);
					break;
				case "image/gif":
					$img_r = imagecreatefromgif($src);
					break;
				case "image/bmp":
					$img_r = $this->ImageCreateFromBMP($src);
					break;
				case "image/x-ms-bmp":
					$img_r = $this->ImageCreateFromBMP($src);
					break;
				case "image/x-bmp":
					$img_r = $this->ImageCreateFromBMP($src);
					break;
				default:
					$img_r = imagecreatefromjpeg($src);
					break;
			}

			// $img_r = imagecreatefrompng($src);
			$ext = $destLogicalPath . time() . "." . $extension;//生成的引用路径
			$dst_r = ImageCreateTrueColor($targ_w, $targ_h);

			imagecopyresampled($dst_r, $img_r, 0, 0, Yii::$app->request->post('x'), Yii::$app->request->post('y'),
					$targ_w, $targ_h, Yii::$app->request->post('w'), Yii::$app->request->post('h'));

			$img = $yiiBasePath . $ext;//真实的图片路径

			if (imagejpeg($dst_r, $img, $jpeg_quality)) {
				$id = Yii::$app->user->getId();
				//更新用户头像
				$user = FwUser::findOne($id);
				$user->thumb = $ext;
				$user->systemKey = $this->system_key;
				$user->save();

				$arr['status'] = 1;
				$arr['data'] = $ext;
				$arr['info'] = Yii::t('common', 'crop_ok');
//                echo json_encode($arr);
				return $arr;
			} else {
				$arr['status'] = 0;
//                echo json_encode($arr);
				return $arr;
			}
		}
	}
	
	/**
	 * 用户积分详情
	 * @author ailan
	 */
	public function actionIntegral()
	{
		$req_param = null;
		$err = $this->preExecuteAction($req_param);
		if ($err != null) {
			return $err;
		}
		
		$companyId = $this->user->company_id;
		$userId = $this->user->kid;
		$userService = new \common\services\framework\UserService();
		$integralTotal = $userService->getUserIntegral($userId, $companyId);
		$integralYears = $userService->getUserIntegralDetail($userId, $companyId, 'year');
		$integralMonth = $userService->getUserIntegralDetail($userId, $companyId, 'year');
		
		$integral[integralTotal]=$integralTotal;
		$integral[integralYears]=$integralYears;
		$integral[integralMonth]=$integralMonth;
		
		return ResponseModel::wrapResponseObject($integral, $this->systemKey);		
	}
	
	/**
	 * 积分明细记录
	 * @author ailan
	 */
	public function actionIntegralList()
	{
		$req_param = null;
		$err = $this->preExecuteAction($req_param);
		if ($err != null) {
			return $err;
		}
		
		$current_time = $req_param['current_time'];
		if (empty($current_time))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'current_time', '\'current_time\' can not be empty');
		
		$start_time=$req_param['start_time'];
		$end_time=$req_param['end_time'];
		$page = $req_param['page'];
		
		if (empty($page))
			$page=1;
		
		$size=10;
		$params['start_time'] = $start_time;
		$params['end_time'] = $end_time;
		$params['defaultPageSize'] = $size;
		$companyId = $this->user->company_id;
		$userId = $this->user->kid;
		$userService = new \common\services\framework\UserService();
		$result = $userService->getUserIntegralList($userId, $companyId, $params, ((int)$page - 1) * $size);
		
		return ResponseModel::wrapResponseObject($result['data'], $this->systemKey);
	}
	
	/**
	 * 积分规则
	  * @author ailan
	 */
	public function actionIntegralPointRule()
	{
		$req_param = null;
		$err = $this->preExecuteAction($req_param);
		if ($err != null) {
			return $err;
		}
		
		$page = $req_param['page'];
		if (empty($page))
			$page=1;
		
		$userService = new \common\services\framework\UserService();
		$companyId = $this->user->company_id;
		$size=10;
		$params['defaultPageSize'] = $size;
		$result = $userService->getIntegralPointRule($companyId, $params, ((int)$page - 1) * $size);
		
		return ResponseModel::wrapResponseObject($result['data'], $this->systemKey);
	}
	
	/**
	 * 增加积分
	 * @author ailan
	 */
	public function actionAddIntegral()
	{
		$req_param = null;
		$err = $this->preExecuteAction($req_param);
		if ($err != null) {
			return $err;
		}
		
		//请求功能代码 
		$actionCode = $req_param['actionCode'];
		if (empty($actionCode))
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_001, 'actionCode', '\'actionCode\' can not be empty');
		//当前请求资源ID
		$id = $req_param['id'];
		if (empty($id))
			$id='';
		
		$result=$this->curUserCheckActionForPoint($actionCode,$this->systemKey,$id);
		if(empty($result)){
			return ResponseModel::getErrorResponse($this->systemKey,ResponseModel::ERR_CODE_000, 'addIntegral', '');
		}
		return ResponseModel::wrapResponseObject(['result' => 'success'], $this->systemKey);
	}
}
