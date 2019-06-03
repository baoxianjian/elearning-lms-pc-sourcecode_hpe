<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/7
 * Time: 13:00
 */

namespace frontend\controllers;

use common\models\framework\FwDomain;
use common\models\framework\FwUser;
use common\models\learning\LnCourse;
use common\models\learning\LnInvestigation;
use common\models\message\MsTaskItem;
use common\services\common\SearchService;
use common\services\framework\UserDomainService;
use common\services\framework\UserService;
use common\services\learning\CourseService;
use common\services\learning\InvestigationResultService;
use common\services\learning\InvestigationService;
use common\services\message\MessageService;
use common\services\message\TaskService;
use common\services\social\AnswerService;
use common\services\social\ShareService;
use common\helpers\TStringHelper;
use frontend\base\BaseFrontController;
use frontend\viewmodels\TaskPushForm;
use Yii;
use yii\db;
use yii\web\Response;

class ManagerController extends BaseFrontController
{
    public $layout = 'frame';

    public function actionMyTeam()
    {
        $uid = Yii::$app->user->getId();

        $service = new UserService();
        $courseService = new CourseService();
        $team_users = $service->getUserByReportManager($uid);
        $team_users = $courseService->getCourseCompleteStat($team_users);

        /*当前用户要查询的域*/
        $userDomainService = new UserDomainService();
        $domain_list = $userDomainService->getManagedListByUserId($uid);

        // 取得课程完成总数排行
        $courseTop = $courseService->getCourseCompleteTop($uid);

        // 取得回答次数排行
        $answerService = new AnswerService();
        $answerTop = $answerService->getAnswerTop($uid);

        // 取得分享次数排行
        $shareService = new ShareService();
        $shareTop = $shareService->getShareTop($uid);

        return $this->render('my-team', [
            'uid'=>$uid,
            'team_users' => $team_users,
            'answerTop' => $answerTop,
            'shareTop' => $shareTop,
            'courseTop' => $courseTop,
            'domain_list' => $domain_list,
        ]);
    }

    public function actionTaskList($owner = 'manager', $type = 'all', $key = null)
    {
        $uid = Yii::$app->user->getId();

        $taskService = new TaskService();
        if ($owner === 'manager') {
            $result = $taskService->getManagerTask($uid, $type, $key);
        } elseif ($owner === 'admin') {
            $result = $taskService->getAdminTask($uid, $type, $key);
        }
        $task = $result['task'];
        $page = $result['pages'];
        return $this->renderAjax('task-list', [
            'task' => $task,
            'page' => $page,
        ]);
    }

    public function actionSendRemind()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $item_id = Yii::$app->request->post('cid');
        $item_type = Yii::$app->request->post('type');
        $plan_complete_at = Yii::$app->request->post('plan_complete_at');
        $ids = Yii::$app->request->post('ids');

        $service = new MessageService();

        $service->sendTaskRemindByManager($item_id, $item_type, $plan_complete_at, $ids);

        return ['result' => 'success'];
    }

    public function actionPanelTaskPush()
    {
        $uid = Yii::$app->user->getId();
        $company_id = Yii::$app->user->identity->company_id;

        $model = new TaskPushForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->validate()) {
                $task = new TaskService();

                for ($i = 0; $i < count($model['items']['item_id']); $i++) {
                    $task_item = new MsTaskItem();
                    $task_item->item_id = $model['items']['item_id'][$i];
                    $task_item->item_type = $model['items']['item_type'][$i];
                    $task_item->item_title = $model['items']['item_title'][$i];
                    if ($model['items']['plan_complete_at'][$i] != '') {
                        $task_item->plan_complete_at = strtotime($model['items']['plan_complete_at'][$i]);
                    }
                    $task_item->sequence_number = 1;
                    $task->SaveManagerTask($uid, $task_item, $model->objects, $model->domain, $company_id);
                }
                return ['result' => 'success'];

            } else {
                $errors = array_values($model->getErrors());
                $message = '';
                for ($i = 0; $i < count($errors); $i++) {
                    $message .= $errors[$i][0] . '<br />';
                }

                return ['result' => 'other', 'message' => $message];
            }
        } else {
            $service = new UserService();
            $team_users = $service->getUserByReportManager($uid);

            /*当前用户要查询的域*/
            $userDomainService = new UserDomainService();
            $domain_list = $userDomainService->getManagedListByUserId($uid, FwDomain::STATUS_FLAG_NORMAL, false, FwDomain::SHARE_FLAG_EXCLUSIVE);

            $defaultDomainId = "";
            $defaultDomainName = "";
            if (!empty($domain_list) && count($domain_list) == 1) {
                $defaultDomainId = $domain_list[0]->kid;
                $defaultDomainName = $domain_list[0]->domain_name;
            }

            return $this->renderAjax('panel-task-push', [
                'users' => $team_users,
                'domain_list' => $domain_list,
                'defaultDomainId' => $defaultDomainId,
                'defaultDomainName' => $defaultDomainName,
            ]);
        }
    }

    public function actionSearchItem()
    {
        $this->layout = 'none';

        $user_id = Yii::$app->user->getId();
        $domain_id = Yii::$app->request->getQueryParam('domain');
        $keyword = Yii::$app->request->getQueryParam('keyword');
        $selected = Yii::$app->request->getQueryParam('selected');

        $service = new SearchService();

        $result = $service->SearchCourseByKeyword($user_id, $domain_id, $keyword, $selected, LnCourse::COURSE_TYPE_ONLINE);

        return $this->render('search-item', [
            'data' => $result['data'],
            'page_id' => 'page1',
            'pages' => $result['page'],
        ]);
    }

    public function actionSearchObject()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $uid = Yii::$app->user->getId();
        $key = Yii::$app->request->post('key');
//        $domain = Yii::$app->request->post('domain');

        $service = new SearchService();
        $team_users = $service->getUserByReportManager($uid, $key);
        if ($team_users) {
            foreach ($team_users as &$u) {
                $u['position_name'] = TStringHelper::PositionName($u['position_name']);
            }
        }

        return json_encode($team_users);
    }

    public function actionMemberLesson($id)
    {
        $uid = Yii::$app->user->getId();

        $service = new UserService();
        $courseService = new CourseService();
        $team_users = $service->getUserByReportManager($uid);
        $team_users = $courseService->getCourseCompleteStat($team_users);

        // 取得课程完成总数排行
        $courseTop = $courseService->getCourseCompleteTop($uid);

        // 取得回答次数排行
        $answerService = new AnswerService();
        $answerTop = $answerService->getAnswerTop($uid);

        // 取得分享次数排行
        $shareService = new ShareService();
        $shareTop = $shareService->getShareTop($uid);

        $real_name = FwUser::findOne($id)->real_name;

        return $this->render('member-lesson', [
            'member_id' => $id,
            'real_name' => $real_name,
            'team_users' => $team_users,
            'answerTop' => $answerTop,
            'shareTop' => $shareTop,
            'courseTop' => $courseTop,
        ]);
    }

    public function actionMemberCourseList($current_time, $id, $type = 'all', $key = null, $page = 1)
    {
        $size = 10;
        $service = new CourseService();

        $view = 'member-course-list';

        if ($type === 'finished') {
            $view = 'member-finished-course-list';
        }

        $data = $service->GetRegCourseByUserId($id, $key, $type, $size, $size < 1 ? 0 : ((int)$page - 1) * $size, $current_time);

        return $this->renderAjax($view, $data);
    }

    public function actionMemberExamList($current_time, $id, $type = 'all', $key = null, $page = 1)
    {
        $size = 10;
        $service = new TaskService();

        $data = $service->getExamTaskByUserId($id, $type, $key, $size, $size < 1 ? 0 : ((int)$page - 1) * $size, $current_time);

        return $this->renderAjax('member-exam-list', ['data' => $data]);
    }

    public function actionMemberSurveyList($current_time, $id, $type = 'all', $status = 'all', $key = null, $page = 1)
    {
        $size = 10;
        $service = new TaskService();

        $data = $service->getSurveyTaskByUserId($id, $type, $status, $key, $size, $size < 1 ? 0 : ((int)$page - 1) * $size, $current_time);

        return $this->renderAjax('member-survey-list', ['data' => $data,
            'user_id' => $id,
        ]);
    }

    public function actionSurveyResult($id, $uid)
    {
        $model = LnInvestigation::findOne($id);

        $data = null;
        return $this->render('survey-result', [
            'data' => $data,
            'id' => $id,
            'uid' => $uid,
            'type' => $model->investigation_type,
        ]);
    }

    public function actionSurveyResultStatistical($id)
    {
        $params = Yii::$app->request->get();

        $params['is_content_flag'] = "true";
        $investigationService = new InvestigationService();
        $v_result = $investigationService->getSingleSurveyStResult($id, $params);

        if ($v_result['answer_type'] == Yii::t('frontend','questionnaire_real_name')) {
            return $this->renderAjax('survey-result-statistical', ['results' => $v_result, 'iid' => $id]);
        } else {
            return $this->renderAjax('survey-result-statistical', ['results' => $v_result, 'iid' => $id]);
        }
    }

    public function actionSurveyResultList($id, $uid, $type)
    {
        $params['size'] = 10;
        $investigationResultService = new InvestigationResultService();

        $pinfolist = $investigationResultService->getSingleVoteStUserInfoResultOne($id, $uid, $params);

        return $this->renderAjax('survey-result-list', array_merge($pinfolist, ['type' => $type]));
    }
}