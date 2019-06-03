<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/22
 * Time: 10:18
 */

namespace frontend\controllers;

use common\models\framework\FwUser;
use common\models\message\MsTimeline;
use common\models\social\SoAnswer;
use common\models\social\SoAnswerComment;
use common\models\social\SoCollect;
use common\models\social\SoQuestion;
use common\models\social\SoQuestionAnswer;
use common\models\social\SoQuestionCare;
use common\models\social\SoShare;
use common\services\framework\PointRuleService;
use common\services\framework\TagService;
use common\services\learning\RecordService;
use common\services\learning\ResourceService;
use common\services\message\MessageService;
use common\services\message\TimelineService;
use common\services\social\CollectService;
use common\services\social\QuestionCareService;
use common\services\social\QuestionService;
use common\services\social\ShareService;
use common\services\social\UserAttentionService;
use frontend\base\BaseFrontController;
use Yii;
use yii\db;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class QuestionController extends BaseFrontController
{
    public $layout = 'frame';

    public function actionDetail()
    {
        $uid = Yii::$app->user->getId();
        $question_id = Yii::$app->request->get('id');
        $companyId = Yii::$app->user->identity->company_id;

        $service = new QuestionService();

        $tagService = new TagService();

        $question = $service->getQuestionById($question_id);

        if (empty($question)) {
            $this->redirect(['index']);
        }

        $tags = $tagService->getTagListBySubjectId($companyId, 'conversation', $question_id);

        // 根据问题话题取得相关问题
        $relationshipQuestionList = $service->getRelationshipQuestionByTags($tags, 8, $question_id);

        $isMobile = false;
        if (Yii::$app->session->has("isMobile")) {
            $isMobile = Yii::$app->session->get("isMobile");
        }

        $tagValues = [];
        foreach ($tags as $t) {
            $tagValues[] = $t->tag_value;
        }

        if (!empty($tagValues) && count($tagValues) > 0) {
            // 根据问题话题取得相关课程
            $courseTags = $tagService->getTagByValues($companyId, 'course', $tagValues);
            $relationshipCourseList = $service->getRelationshipCourseByTags($courseTags, 5, $isMobile);
        }
        $care_list = $service->getUserByCareQuestion($question_id);

        SoQuestion::addFieldNumber($question_id, "browse_num");

        $shareModel = new SoShare();

        $careService = new QuestionCareService();
        $careModel = new SoQuestionCare();
        $careModel->user_id = $uid;
        $careModel->question_id = $question_id;

        $isCare = $careService->IsRelationshipExist($careModel);

        $isCollect = $service->isCollect($uid, $question_id);

        $attentionService = new UserAttentionService();

        $attentionUser = $attentionService->getAllAttentionUserId($uid);

        $attentionUser = ArrayHelper::map($attentionUser, 'attention_id', 'attention_id');

        $attentionUser = array_keys($attentionUser);

        $questionUser = FwUser::findIdentity($question->user_id);

        return $this->render('detail', [
            'question' => $question,
            'tags' => $tags,
            'care_list' => $care_list,
            'relationship_question' => $relationshipQuestionList,
            'shareModel' => $shareModel,
            'isCare' => $isCare,
            'isCollect' => $isCollect,
            'attentionUser' => $attentionUser,
            'fwUser' => $questionUser,
            'relationship_course' => $relationshipCourseList,
        ]);
    }

    public function actionAnswerList($qid)
    {
        $this->layout = 'list';

        $uid = Yii::$app->user->getId();
        $question_id = $qid;

        $service = new QuestionService();

        $result = $service->getAnswerByQuestionId($question_id);

        $comment_list = $service->getAnswerCommentByAnswerList($result['data']);

        $answer = new SoAnswer();

        $canOperate = $service->canOperate($uid, $question_id);

        $attentionService = new UserAttentionService();

        $attentionUser = $attentionService->getAllAttentionUserId($uid);

        $attentionUser = ArrayHelper::map($attentionUser, 'attention_id', 'attention_id');

        $attentionUser = array_keys($attentionUser);

        return $this->render('answer-list', [
            'question_id' => $question_id,
            'answer' => $answer,
            'answer_list' => $result['data'],
            'comment_list' => $comment_list,
            'canOperate' => $canOperate,
            'attentionUser' => $attentionUser,
            'page' => $result['page'],
        ]);
    }

    public function actionCareUserList($qid)
    {
        $this->layout = 'none';
        $service = new QuestionService();
        $data = $service->getUserByCareQuestion($qid);

        $uid = Yii::$app->user->getId();

        $attentionService = new UserAttentionService();

        $attentionUser = $attentionService->getAllAttentionUserId($uid);

        $attentionUser = ArrayHelper::map($attentionUser, 'attention_id', 'attention_id');

        $attentionUser = array_keys($attentionUser);

        return $this->render('care-user-list', [
            'data' => $data,
            'attentionUser' => $attentionUser,
        ]);
    }

    public function actionAnswer()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->user->getId();
        $model = new SoAnswer();
        $model->user_id = $id;
        $model->needReturnKey = true;
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            SoQuestion::addFieldNumber($model->question_id, "answer_num");

            $messageService = new MessageService();
            // 向提问者推消息
            $messageService->QuestionAnswerToSub($model);
            // 向关注问题者推消息
            $messageService->QuestionAnswerToCare($model);

            // 学习历程添加
            $recordService = new RecordService();
            $recordService->addByAnswerQuestion($id, $model->question_id);

            $questionModel = SoQuestion::findOne($model->question_id);

            /*添加积分*/
            $pointRuleService = new PointRuleService();
            $code = 'Reply-Common-Question';
            if ($questionModel->question_type === SoQuestion::QUESTION_TYPE_COURSE) {
                $code = 'Reply-Course-Question';
            }
            $pointResult = $pointRuleService->curUserCheckActionForPoint($code, 'Community', $model->question_id);
            return ['result' => 'success', 'pointResult' => $pointResult];
        } else {
            $errors = array_values($model->getErrors());
            $message = '';
            for ($i = 0; $i < count($errors); $i++) {
                $message .= $errors[$i][0] . '<br />';
            }

            return ['result' => 'other', 'message' => $message, 'pointResult' =>false];
        }
    }

    public function actionAnswerComment()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->user->getId();
        $model = new SoAnswerComment();
        $model->user_id = $id;
        $model->needReturnKey = true;

        if ($model->load(Yii::$app->request->post()) && $model->SubAnswerComment()) {
            $messageService = new MessageService();
            // 向回答者推消息
            $messageService->AnswerComment($model);

            $answerModel = SoAnswer::findOne($model->answer_id);
            $questionModel = SoQuestion::findOne($answerModel->question_id);

            /*添加积分*/
            $pointRuleService = new PointRuleService();
            $code = 'Comment-Common-Question';
            if ($questionModel->question_type === SoQuestion::QUESTION_TYPE_COURSE) {
                $code = 'Comment-Course-Question';
            }
            $pointResult = $pointRuleService->curUserCheckActionForPoint($code, 'Community', $answerModel->question_id);
            return ['result' => 'success', 'pointResult' => $pointResult];
        } else {
            $errors = array_values($model->getErrors());
            $message = '';
            for ($i = 0; $i < count($errors); $i++) {
                $message .= $errors[$i][0] . '<br />';
            }

            return ['result' => 'other', 'message' => $message, 'pointResult' =>false ];
        }
    }

    public function actionCare()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $uid = Yii::$app->user->getId();
        $question_id = Yii::$app->request->post('qid');

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
            return ['result' => 'success', 'status' => 'cancel'];
        } else {
            // 增加积分
            $pointRuleService = new PointRuleService();
            $pointResult = $pointRuleService->curUserCheckActionForPoint('Attention-Question', 'Learning-Portal', $question_id);

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


            return ['result' => 'success', 'status' => 'success', 'pointResult' => $pointResult];

        }
    }

    public function actionCollect()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->user->getId();
        $questionId = Yii::$app->request->post('qid');

        $service = new CollectService();
        $collectModel = new SoCollect();
        $collectModel->object_id = $questionId;
        $collectModel->type = SoCollect::TYPE_QUESTION;
        $collectModel->user_id = $id;

        if ($service->IsRelationshipExist($collectModel)) {
            $service->StopRelationship($collectModel);
            SoQuestion::subFieldNumber($questionId, "collect_num");
            return ['result' => 'success', 'status' => 'cancel', 'pointResult' => false];
        } else {
            // 增加积分
            $pointRuleService = new PointRuleService();
            $pointResult = $pointRuleService->curUserCheckActionForPoint('Collect-Question', 'Learning-Portal', $questionId);

            $service->startRelationship($collectModel);
            SoQuestion::addFieldNumber($questionId, "collect_num");

            return ['result' => 'success', 'status' => 'success', 'pointResult' => $pointResult];

        }
    }

    public function actionShare()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $uid = Yii::$app->user->getId();
        $model = new SoShare();
        $model->user_id = $uid;
        $model->type = SoShare::SHARE_TYPE_QUESTION;
        $model->needReturnKey = true;
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            SoQuestion::addFieldNumber($model->obj_id, "share_num");

            $attentionService = new UserAttentionService();
            //获取所有关注对象
            $user_attention = $attentionService->getAllUserId($uid);
            if (isset($user_attention) && $user_attention != null) {
                $user_attention = ArrayHelper::map($user_attention, 'user_id', 'user_id');
                $user_attention = array_keys($user_attention);
            }

            ShareService::ShareUserSave($model, $user_attention);

            // 时间轴添加
            $timelineService = new TimelineService();
            $timelineService->pushByShareQuestion($uid, $user_attention, $model->obj_id, $model->content);

            // 消息推送
            $service = new MessageService();
            $service->pushMessageByQuestionShare($uid, $model, $user_attention);
            return ['result' => 'success'];
        } else {
            $errors = array_values($model->getErrors());
            $message = '';
            for ($i = 0; $i < count($errors); $i++) {
                $message .= $errors[$i][0] . '<br />';
            }

            return ['result' => 'other', 'message' => $message];
        }
    }

    public function actionSetRightAnswer()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->user->getId();
        $question_id = Yii::$app->request->post('qid');
        $answer_id = Yii::$app->request->post('aid');

        $service = new QuestionService();

        if (!$service->canOperate($id, $question_id)) {
            return ['result' => 'other', 'message' => Yii::t('frontend', 'operation_limited')];
        }

        $model = new SoQuestionAnswer();
        $model->question_id = $question_id;
        $model->answer_id = $answer_id;

        if ($service->setRightAnswers($model)) {/*同时将问题设置为已解答*/
            return ['result' => 'success'];
        } else {
            $errors = array_values($model->getErrors());
            $message = '';
            for ($i = 0; $i < count($errors); $i++) {
                $message .= $errors[$i][0] . '<br />';
            }

            return ['result' => 'other', 'message' => $message];
        }
    }
    /*-------------------------------------------------index页面修改--------------------------------------------------------------------------------------*/
    //主页面
    public function actionIndex()
    {
        $value = isset($_GET['value']) ? $_GET['value'] : null;
        $kid = isset($_GET['kid']) ? $_GET['kid'] : null;
        $shareModel = new SoShare();
        $timenow = time();
        return $this->render('index', ['shareModel' => $shareModel, 'kid' => $kid, 'value' => $value, 'timenow' => $timenow]);
    }

    //已解决
    public function actionSolved()
    {
        $userid = Yii::$app->user->getId();
        $page = isset($_GET['page']) ? $_GET['page'] : 0;
        $tag_id = isset($_GET['tag']) ? $_GET['tag'] : null;
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : null;
        $isresolved = isset($_GET['isresolved']) ? $_GET['isresolved'] : null;
        $qtype = isset($_GET['type']) ? $_GET['type'] : null;
        $btn = isset($_GET['btn']) ? $_GET['btn'] : null;
        $small = isset($_GET['small']) ? $_GET['small'] : false;
        $timenow = isset($_GET['timenow']) ? $_GET['timenow'] : time();
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $view = null;
        $companyId = Yii::$app->user->identity->company_id;
        $sbids = array();
        $service = new QuestionService();
        $size = $this->defaultPageSize;
        $key = $keyword;
        $keyword = urldecode($keyword);
        $keyword = trim($keyword);
        switch ($qtype) {
            case 'solved':
                $isresolved = 1;
                $data = $service->getPageListQuestionByCondition($companyId, $tag_id, $isresolved, $size, $page, null, $timenow);
                $view = 'solved';
                break;

            case 'unsolved':
                $isresolved = 0;
                $data = $service->getPageListQuestionByCondition($companyId, $tag_id, $isresolved, $size, $page, null, $timenow);
                $view = 'solved';
                break;

            case 'mine':
                $data = $service->getMyQuestion($companyId, $tag_id, $userid, $isresolved, $size, $page, null, $timenow);
                $view = 'mine';
                break;

            case 'care':
                $data = $service->getMycareQuestion($companyId, $tag_id, $userid, $isresolved, $size, $page, null, $timenow);
                $view = 'care';
                break;

            case 'atme':
                $data = $service->getAtmeQuestion($companyId, $tag_id, $userid, $isresolved, $size, $page, null, $timenow);
                $view = 'atme';
                break;

            case 'answer':
                $data = $service->getMyanswerQuestion($companyId, $tag_id, $userid, $isresolved, $size, $page, null, $timenow);
                $view = 'answer';
                break;

            case 'search':

                $data = $service->getListBykeyword($keyword, null, $size, $page, $timenow, $status, $tag_id);
                $view = 'search';
                break;

        }
        // $ressbid = $service->GetTags($companyId,$isresolved,100, 1);
        if ($small == TRUE) {
            $view = 'mine-small';
        }
        $ids = array();
        $co_ids = array();
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $ids[] = $v['kid'];
            };
        }

        // foreach($ressbid as $k => $v){
        //       $sbids["$v[kid]"][] = $v['tag_value'];
        //   }
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $isCollect = $service->isCollect($userid, $v['kid']);
                $isCare = $service->isCare($userid, $v['kid']);
                $data[$k]['isCare'] = $isCare;
                $data[$k]['isCollect'] = $isCollect;

                if (!empty($v['obj_id'])) $co_ids[] = $v['obj_id'];
                // $ressbid = $service->GetTags($companyId,$isresolved,100, 1);
                $data["$k"]['tag_value'] = $service->GetTagsbyQuestionKid($companyId, $isresolved, $v['kid']);
//                if (!empty($sbids["$v[kid]"])) {
                //           $data["$k"]['tag_value'] = $sbids["$v[kid]"];
                //           } else {
                //              $data["$k"]['tag_value'] = '';
                //           }
            }
        }
        $type = 'all';
        $order = 'new';
        $courseService = new ResourceService();
        if (!empty($co_ids)) {
            $course = $courseService->getResourcecourse($co_ids, $type, $size, 0, $order);
        }

        if (!empty($course)) {
            foreach ($course as $k => $v) {
                $coursefinal["$v[kid]"] = $v['course_name'];
            }
        } else {
            $coursefinal = array();
        }

        return $this->renderAjax('tablist/question-' . "$view", [
            'data' => $data,
            'coursefinal' => $coursefinal,
            'tagId' => $tag_id,
            'type' => $btn,
            'status' => $status,
            'key' => $key,
        ]);

    }

    public function actionSolvedCount()
    {
        $tag_id = isset($_REQUEST['tag']) ? $_REQUEST['tag'] : null;
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 1;
        $companyId = Yii::$app->user->identity->company_id;
        $service = new QuestionService();
        $result = $service->getPageListQuestionCount($companyId, $tag_id, $type);

        return $result;
    }
}