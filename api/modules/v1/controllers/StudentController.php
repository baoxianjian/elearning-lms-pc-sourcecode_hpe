<?php


namespace api\modules\v1\controllers;

use api\base\BaseNormalApiController;
use common\models\framework\FwUser;
use api\services\UserService;
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
class StudentController extends BaseNormalApiController{


    public $modelClass = '';
    
    /**
     * 对问题回答点赞接口
     * @return mixed
     */
    public function actionAnswerPraise($id)
    {	//$uid = Yii::$app->user->getId();
    	$result=SoAnswer::addFieldNumber($id,"praise_num");/*增加点赞数据*/
    	return  ResponseModel::wrapResponseObject($result);
    }
    
    /**
     * 学习历程->课程
     * @param $page
     * @param null $time
     * @return string
     */
    public function  actionMyCoursePath($page=1, $time=null)
    {
    
    	$id = Yii::$app->user->getId();
    
    	$size = 10;
    	$service = new CourseService();
    	$data = $service->getAllRegCourseByUid($id, $time, $size, $page);
    
    	return  ResponseModel::wrapResponseObject($data);
    }
    
    /**
     * 学习历程->问答
     * @param number $page
     * @param int $time
     * @param number $type 1－最新　2-最热　　3－回复最多
     * @return
     */
    public function actionGetQuestion($page=1, $time=null,$type=0)
    {
    	$id = Yii::$app->user->getId();
    
    	$size = 10;
    	$service = new QuestionService();
    	$question = $service->getQuestionPageDataByType($id, $time, $size, $page,$type);
    
    	return  ResponseModel::wrapResponseObject($question);
    }
    
    /**
     * 我提的问题列表
     * @param number $page
     * @param int $time
     * @return
     */
    public function actionGetMyQuestion($page=1)
    {
    	$id = Yii::$app->user->getId();
    
    	$size = 10;
    	$service = new QuestionService();
    	$question = $service->getQuestionListById($id,$size, $page);
    
    	return  ResponseModel::wrapResponseObject($question);
    }
    
   /**
     * 通过用户ID获取所有回复列表
     * @param number $page
     * @param int $time
     * @return
     */
    public function actionGetMyAnswer()
    {
    	$id = Yii::$app->user->getId();
    
    	$service = new QuestionService();
    	$question = $service->getAnswerById($id);
    
    	return  ResponseModel::wrapResponseObject($question);
    } 
    
    
    /**
     * 学习历程->分享
     */
    public function actionGetShare($page=1, $time=null)
    {
    	$id = Yii::$app->user->getId();
    
    	$service = new ShareService();
    	$size = 10;
    
    	$share = $service->getSharePageDataById($id, $time, $size, $page);
    
    	return  ResponseModel::wrapResponseObject($share);
    }
    
    /**
     * 学习历程->记录
     */
    public function actionGetRecord($page=1, $time=null)
    {
    	$id = Yii::$app->user->getId();
    
    	$service = new RecordService();
    	$size = 10;
    
    	$record = $service->getRecordPageDataById($id, $time, $size, $page);
    
    	return  ResponseModel::wrapResponseObject($record);
    }

   
}