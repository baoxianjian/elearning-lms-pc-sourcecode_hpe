<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/8/20
 * Time: 10:22
 */
namespace frontend\controllers;

use common\models\framework\FwDomain;
use common\models\message\MsPushObject;
use common\models\message\MsTask;
use common\models\message\MsTaskItem;
use common\services\framework\ExternalSystemService;
use common\services\framework\UserDomainService;
use common\helpers\TNetworkHelper;
use frontend\base\BaseFrontController;
use common\services\common\SearchService;
use common\services\message\TaskService;
use frontend\viewmodels\TaskPushForm;
use Yii;
use yii\data\Pagination;
use yii\db;
use yii\helpers\ArrayHelper;
use yii\web\Response;


/**
 * Task controller
 */
class TaskController extends BaseFrontController
{

    public $layout = 'frame';

    public function actionIndex()
    {
        $uid = Yii::$app->user->getId();

        /*当前用户要查询的域*/
        $userDomainService = new UserDomainService();
        $domain_list = $userDomainService->getManagedListByUserId($uid, FwDomain::STATUS_FLAG_NORMAL, false, FwDomain::SHARE_FLAG_EXCLUSIVE);

        return $this->render('index', [
            'domain_list' => $domain_list,
        ]);
    }

    public function actionList($search_key = null, $search_date = null, $search_type = null)
    {
        $this->layout = 'list';

        $uid = Yii::$app->user->getId();

        $service = new TaskService();

        $size = $this->defaultPageSize;

        $result = $service->getTaskList($uid, $size, $search_key, $search_date, $search_type);

        return $this->render('list', $result);
    }

    public function actionCreate()
    {
        $model = new MsTask();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $uid = Yii::$app->user->getId();

            $model->task_sponsor_id = $uid;

            if ($model->save()) {

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
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionSearchItem()
    {
        $user_id = Yii::$app->user->getId();
        $domain_id = Yii::$app->request->getQueryParam('domain');
        $keyword = Yii::$app->request->getQueryParam('keyword');
        $selected = Yii::$app->request->getQueryParam('selected');

        $company_id = Yii::$app->user->identity->company_id;

        $domain_ids = array();
        $domain_ids[] = $domain_id;

        $domain = FwDomain::findOne($domain_id);

        $domainService = new UserDomainService();
        $domain_list = $domainService->getShareDomain($company_id, $domain->parent_domain_id);

        if ($domain_list !== null && count($domain_list) > 0) {
            foreach ($domain_list as $val) {
                $domain_ids[] = $val->kid;
            }
        }

        $service = new SearchService();
        $result = $service->SearchCourseByKeyword($user_id, $domain_ids, $keyword, $selected);

        return $this->renderAjax('search-item', [
            'data' => $result['data'],
            'page_id' => 'page1',
            'pages' => $result['page'],
        ]);
    }

    public function actionSearchExam()
    {
        $this->layout = 'none';

        $user_id = Yii::$app->user->getId();
        $keyword = Yii::$app->request->getQueryParam('keyword');
        $selected = Yii::$app->request->getQueryParam('selected');

        $company_id = Yii::$app->user->identity->company_id;

        $service = new SearchService();
        $result = $service->SearchExamByKeyword($company_id, $keyword, $selected);

        return $this->render('search-exam', [
            'data' => $result['data'],
            'pages' => $result['page'],
        ]);
    }

    public function actionSearchSurvey()
    {
        $this->layout = 'none';

        $user_id = Yii::$app->user->getId();
        $keyword = Yii::$app->request->getQueryParam('keyword');
        $selected = Yii::$app->request->getQueryParam('selected');

        $company_id = Yii::$app->user->identity->company_id;

        $service = new SearchService();
        $result = $service->SearchSurveyByKeyword($company_id, $keyword, $selected);

        return $this->render('search-survey', [
            'data' => $result['data'],
            'pages' => $result['page'],
        ]);
    }

    public function actionSearchObject()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $uid = Yii::$app->user->getId();
        $key = Yii::$app->request->post('key');
        $domain = Yii::$app->request->post('domain');

        $service = new SearchService();
        $data = $service->SearchObjectByName($key, $domain);//任务推送应该是能选到自己的

        return $data;
    }

    public function actionTaskPush()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $uid = Yii::$app->user->getId();
        $company_id = Yii::$app->user->identity->company_id;

        $model = new TaskPushForm();
        $model->load(Yii::$app->request->post());

        if ($model->push_prepare_at) {
            $model->push_prepare_at = strtotime($model->push_prepare_at);
        }

        $item_list = [];
        $object_list = [];

        for ($i = 0; $i < count($model['items']['item_id']); $i++) {
            $task_item = new MsTaskItem();
            $task_item->item_id = $model['items']['item_id'][$i];
            $task_item->item_type = $model['items']['item_type'][$i];
            $task_item->item_title = $model['items']['item_title'][$i];
            if ($model['items']['plan_complete_at'][$i] != '') {
                $task_item->plan_complete_at = strtotime($model['items']['plan_complete_at'][$i]);
            }
            $task_item->sequence_number = $i + 1;
            $item_list[$i] = $task_item;
        }

        for ($i = 0; $i < count($model['objects']); $i++) {
            $temp = explode(',', $model['objects'][$i]);
            $task_object = new MsPushObject();
            $task_object->obj_type = $temp[0];
            $task_object->obj_id = $temp[1];
            $object_list[$i] = $task_object;
        }

        $service = new TaskService();
        $result = $service->pushTask($uid, $model->task_id, $model->domain, $company_id, MsTask::TASK_TYPE_ADMIN, $item_list, $object_list, $model->time_push, $model->push_prepare_at, $model->is_temp);
        if ($result) {
            if ($model->time_push || $model->is_temp === TaskPushForm::IS_TEMP_YES) {
                return ['result' => 'success'];
            }
            $externalSystemService = new ExternalSystemService();
            $admin_push_url = $externalSystemService->getExternalSystemInfoByExternalSystemCode("elearning-task-service")->api_address;
            $admin_push_url .= "text";
            $response = TNetworkHelper::HttpPost($admin_push_url, ['task_id' => $result, 'sponsor_id' => $uid, 'domain_ids' => [$model->domain]]);
            if ($response['code'] == '200') {
                return ['result' => 'success'];
            } else {
                return ['result' => 'other', 'message' => Yii::t('frontend', 'push_service_ask_failed')];
            }
        } else {
            return ['result' => 'other', 'message' => Yii::t('frontend', 'task_push_failed')];
        }
    }

    public function actionView($id)
    {
        $task = MsTask::findOne($id);
        $service = new TaskService();
        $task_items = $service->getTaskItemByTaskId($id);

        return $this->renderAjax('view', [
            'task' => $task,
            'task_items' => $task_items,
        ]);
    }

    public function actionRepush()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $task_id = Yii::$app->request->post('task_id');
        $sponsor_id = Yii::$app->request->post('sponsor_id');
        $domain_id = Yii::$app->request->post('domain_id');

        $service = new TaskService();

        if ($service->repushTask($sponsor_id, $task_id)) {
            $task = MsTask::findOne(['kid' => $task_id, 'task_sponsor_id' => $sponsor_id]);

            $externalSystemService = new ExternalSystemService();
            $url = $externalSystemService->getExternalSystemInfoByExternalSystemCode("elearning-task-service")->api_address;
            if ($task->complete_type === MsTask::COMPLETE_TYPE_ALL_FAIL) {
                $url .= "text";
            } elseif ($task->complete_type === MsTask::COMPLETE_TYPE_PART_SUCCESS) {
                $url .= "failure";
            }

            $response = TNetworkHelper::HttpPost($url, ['task_id' => $task_id, 'sponsor_id' => $sponsor_id, 'domain_ids' => [$domain_id]]);
            if ($response['code'] == '200') {
                return ['result' => 'success'];
            } else {
                return ['result' => 'other', 'message' => Yii::t('frontend', 'push_service_ask_failed')];
            }
        } else {
            return ['result' => 'failure'];
        }
    }

    public function actionImmediatelyPush()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $task_id = Yii::$app->request->post('task_id');
        $sponsor_id = Yii::$app->request->post('sponsor_id');
        $domain_id = Yii::$app->request->post('domain_id');

        $task = MsTask::findOne(['kid' => $task_id, 'task_sponsor_id' => $sponsor_id]);
        if (!empty($task)) {
            $push_prepare_at = $task->push_prepare_at;
        }
        $service = new TaskService();

        if ($service->immediatelyPushTask($task, $sponsor_id, $task_id)) {
            $externalSystemService = new ExternalSystemService();
            $admin_push_url = $externalSystemService->getExternalSystemInfoByExternalSystemCode("elearning-task-service")->api_address;
            $admin_push_url .= "text";

            $response = TNetworkHelper::HttpPost($admin_push_url, ['task_id' => $task_id, 'sponsor_id' => $sponsor_id, 'domain_ids' => [$domain_id]]);
            if ($response['code'] == '200') {
                return ['result' => 'success'];
            } else {
                $task->push_prepare_at = $push_prepare_at;
                $task->save();
                return ['result' => 'other', 'message' => Yii::t('frontend', 'push_service_ask_failed')];
            }
        } else {
            return ['result' => 'failure'];
        }
    }

    public function actionDelete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $task_id = Yii::$app->request->post('task_id');

        if ($task_id == null || $task_id == '') {
            return ['result' => 'failure'];
        }
        $service = new TaskService();

        if ($service->deleteTask($task_id)) {
            return ['result' => 'success'];
        } else {
            return ['result' => 'other', 'message' => Yii::t('frontend', 'task_del_failed')];
        }
    }

    public function actionViewFail($id, $key = null)
    {
        $service = new TaskService();
        $size = $this->defaultPageSize;
        $task_users_fail = $service->getTaskObjectByTaskId($id, $key, $size, true);

        return $this->renderAjax('view-fail', $task_users_fail);
    }

    public function actionViewSuccess($id, $key = null)
    {
        $service = new TaskService();
        $size = $this->defaultPageSize;
        $task_users = $service->getTaskObjectByTaskId($id, $key, $size);

        return $this->renderAjax('view-success', $task_users);
    }

    public function actionEdit($id)
    {
        $service = new TaskService();
        $data = $service->GetTaskDataByTaskID($id);

        return $this->renderAjax('edit', $data);
    }
}