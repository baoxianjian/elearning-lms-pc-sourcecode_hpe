<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/8
 * Time: 17:22
 */
namespace frontend\controllers;

use common\helpers\TArrayHelper;
use common\helpers\TFileUploadHelper;
use common\models\framework\FwUser;
use common\models\learning\LnCertification;
use common\models\learning\LnRecord;
use common\models\learning\LnUserCertification;
use common\models\social\SoQuestion;
use common\models\social\SoRecord;
use common\models\social\SoShare;
use common\services\framework\CompanyMenuService;
use common\services\framework\DictionaryService;
use common\services\framework\PointRuleService;
use common\services\framework\TagService;
use common\services\framework\UserDomainService;
use common\services\framework\UserService;
use common\services\framework\WechatService;
use common\services\learning\CertificationService;
use common\services\learning\CourseService;
use common\services\learning\RecordService;
use common\services\message\MessageService;
use common\services\message\TimelineService;
use common\services\social\CollectService;
use common\services\social\QuestionService;
use common\services\social\ShareService;
use common\services\social\UserAttentionService;
use frontend\base\BaseFrontController;
use Yii;
use yii\db;
use yii\web\Response;


/**
 * Student controller
 */
class StudentController extends BaseFrontController
{
    public $layout = 'frame';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['except'] = ['login'];

        return $behaviors;
    }

    public function actionIndex()
    {
        $notShowSetTag = true;

        if (Yii::$app->user->identity->login_number == 1) {
            $sessionKey = 'Not_Show_Set_Tag';
            if (Yii::$app->session->has($sessionKey)) {
                $tempFlag = Yii::$app->session->get($sessionKey);
            }
            if (!isset($tempFlag)) {
                $userId = Yii::$app->user->getId();
                $companyId = Yii::$app->user->identity->company_id;
                $service = new TagService();
                // 初始化用户标签，将用户岗位作为初始标签
                $service->initUserInterestTag($userId, $companyId);

                Yii::$app->session->set($sessionKey, true);
                $notShowSetTag = false;
            }
        }

        return $this->render('index', ['notShowSetTag' => $notShowSetTag]);
    }

    /**
     * 我的收藏
     * @return string
     */
    public function actionCollect()
    {
        return $this->render('collect');
    }

    public function actionCertificationPreview($id)
    {
        $this->layout = 'none';
        $service = new CertificationService();
        $model = LnUserCertification::findOne($id);

        $template = LnCertification::findOne($model->certification_id);
        if (!empty($template)) {
            $printOrientation = $template->print_orientation;
        }
        if ($model->status == LnUserCertification::STATUS_FLAG_STOP) {
            $message = Yii::t('frontend', 'certifi_cancel');
            $html = null;
        } else {

            $html = $service->GetUserCertificationContent($model);
            if (empty($html)) {
                $message = Yii::t('frontend', 'certifi_message');
            } else {
                $message = null;
            }
        }
        return $this->render('/common/certification-preview', [
            'message' => $message,
            'html' => $html,
            'printOrientation' => $printOrientation,
        ]);
    }

    /**
     * 我的收藏->列表view
     * @param int $type
     * @param null $time
     * @param int $page
     * @return string
     */
    public function actionCollectList($current_time, $type = 1, $time = null, $page = 1)
    {
        $this->layout = 'none';
        $id = Yii::$app->user->getId();

        $size = 10;

        $service = new CollectService();
        $collect = $service->getPageDataByUserId($id, $type, $time, $size, $page, $current_time);

        return $this->render('collect-list', [
            'data' => $collect,
        ]);
    }

    public function actionCollectOne($current_time, $type = 1, $page = 1, $time = null)
    {
        $this->layout = 'none';
        $id = Yii::$app->user->getId();

        $size = 10;

        $service = new CollectService();
        $collect = $service->getCollectOneByUserId($id, $type, $time, $size, $page, $current_time);

        return $this->render('collect-list', [
            'data' => $collect,
        ]);
    }

    /**
     * @param string $cate_code
     * @param string $format
     * @return array|null|string|db\ActiveRecord[]
     * 更新 2015-11-19 @author adophper
     */
    public function actionGetTag($cate_code = 'conversation', $format = '', $companyId = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (empty($companyId)) {
            $companyId = Yii::$app->user->identity->company_id;
        }

        $tagService = new TagService();
        if (!empty($format)) {
            $word = trim(Yii::$app->request->get('q'));
        } else {
            $word = trim(Yii::$app->request->post('val'));
        }

        if (empty($word)) {
            return '';
        }

        $tags = $tagService->getLikeTagByValue($companyId, $cate_code, $word);
        if (!empty($format) && !empty($tags)) {
            $new_array = array();
            foreach ($tags as $item) {
                $new_array[] = array('kid' => $item->kid, 'title' => $item->tag_value);
            }
            $tags = array('results' => $new_array);
        }
        return $tags ? $tags : '';
    }

    /**
     * 个人信息
     * @return string
     */
    public function actionPersonInfo()
    {
        $userId = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;
        $wechatService = new WechatService();
        $model = $wechatService->getCompanyActiveWechat($companyId);

        $duration = 5; //5秒
        $hasWechat = "0";
        $wechatFunction = "STOP";
        if (!empty($model)) {
            $wechatFunction = "START";

            $wechatModel = $wechatService->getWechatAccount($userId,null,false);
            if (!empty($wechatModel) && !empty($wechatModel->open_id)) {
                $hasWechat = "1";
            }
        }

        return $this->render('person-info', [
            'wechatFunction' => $wechatFunction,
            'hasWechat' => $hasWechat,
            'duration' => $duration,
        ]);
    }

    public function actionGetWechatStatus()
    {
        $userId = Yii::$app->user->getId();
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $hasWechat = "0";
            $wechatService = new WechatService();
            $wechatModel = $wechatService->getWechatAccount($userId,null,false);

            if (!empty($wechatModel) && !empty($wechatModel->open_id)) {
                $hasWechat = "1";
            }

            return ['hasWechat' => $hasWechat];
        }
    }

    /**
     * 个人信息->基础信息
     * @return string
     */
    public function actionBasicInfo()
    {
        $id = Yii::$app->user->getId();
        $model = FwUser::findOne($id);
        $model->scenario = 'info';
        $oldEmail = $model->email;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->validate()) {
                if ($oldEmail != $model->email) {
                    $model->email = $oldEmail;
                }
                if ($model->save()) {
                    setcookie("LangCookie",$model->language);
                    $sessionLanguageKey = "Language_" . $id;
                    $sessionThemeKey = "Theme_" . $id;
                    Yii::$app->session->set($sessionLanguageKey, $model->language);
                    Yii::$app->session->set($sessionThemeKey, $model->theme);
                    $companyMenuService = new CompanyMenuService();
                    $companyMenuService->clearCompanyMenuSession();
                    return ['result' => 'success'];
                } else {
                    return ['result' => 'failure'];
                }
            } else {
                $errors = array_values($model->getErrors());
                $message = '';
                for ($i = 0; $i < count($errors); $i++) {
                    $message .= $errors[$i][0] . '<br />';
                }

                return ['result' => 'other', 'message' => $message];
            }
        } else {
            $userService = new UserService();
            $reporting_manager = $userService->getReportingManagerStringByUserId($id);
            $org = $userService->getOrgnizationStringByUserId($id);
            $domain = $userService->getDomainStringByUserId($id);
            $company = $userService->getCompanyStringByUserId($id);
            $position = $userService->getPositionListStringByUserId($id);

            $dictionaryService = new DictionaryService();
            $languageModel = $dictionaryService->getDictionariesByCategory('language');
            $themeModel = $dictionaryService->getDictionariesByCategory('theme');
            $timezoneModel = $dictionaryService->getDictionariesByCategory('timezone');
            $genderModel = $dictionaryService->getDictionariesByCategory('gender');
            $locationModel = $dictionaryService->getDictionariesByCategory('location');

            $model->email_repeat = $model->email;

            $userDomainService = new UserDomainService();
            $searched_domain = $userDomainService->getSearchedListStringByUserId($id);

            $tagService = new TagService();
            $userTagList = $tagService->getTagListBySubjectId($model->company_id, 'interest', $id);

            $userTags = TArrayHelper::get_array_key($userTagList, 'tag_value');
            $userTags = implode(', ', $userTags);

            return $this->renderAjax('person-info-basic-info', [
                'model' => $model,
                'reporting_manager' => $reporting_manager,
                'searched_domain' => $searched_domain,
                'position' => $position,
                'org' => $org,
                'domain' => $domain,
                'company' => $company,
                'genderModel' => $genderModel,
                'languageModel' => $languageModel,
                'themeModel' => $themeModel,
                'timezoneModel' => $timezoneModel,
                'locationModel' => $locationModel,
                'userTags' => $userTags,
            ]);
        }
    }

    /**
     * 个人信息->修改密码
     * @return string
     */
    public function actionChangePassword()
    {
        $id = Yii::$app->user->getId();
        $model = FwUser::findOne($id);
        $model->setScenario("change-password");
        $oldPasswordHash = $model->password_hash;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->validate()) {
                $checkOldPassword = Yii::$app->security->validatePassword($model->password_old, $oldPasswordHash);

                if (!$checkOldPassword) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'password_old_error')];
                }

                if ($model->password_hash != $model->password_repeat) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'password_repeat_error')];
                }

                $model->setPassword($model->password_hash);
                $model->last_pwd_change_at = time();
                $model->last_pwd_change_reason = FwUser::PASSWORD_CHANGE_REASON_CHANGE;

                if ($model->save()) {
                    return ['result' => 'success'];
                } else {
                    return ['result' => 'failure'];
                }
            } else {
                $errors = array_values($model->getErrors());
                $message = '';
                for ($i = 0; $i < count($errors); $i++) {
                    $message .= $errors[$i][0] . '<br />';
                }

                return ['result' => 'other', 'message' => $message];
//                return ['result' => 'failure'];
            }
        } else {
            $model->password_hash = '';
            $model->password_repeat = '';
            $model->password_old = '';

            return $this->renderAjax('person-info-change-password', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 个人信息->设置头像
     * @return string
     */
    public function actionSetThumb()
    {
        $id = Yii::$app->user->getId();
        $model = FwUser::findOne($id);

        return $this->renderAjax('person-info-set-thumb', [
            'model' => $model,
        ]);
    }

    public function actionSetWechat()
    {
        $userId = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;

        $wechatService = new WechatService();
        if (Yii::$app->request->isPost && !empty(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $openId = Yii::$app->request->post('openId');

            if ($wechatService->unBindWechatAccount($userId, $companyId, $openId)) {
                return ['result' => 'success'];
            } else {
                return ['result' => 'failure'];
            }
        } else {
            $expireSeconds = 60 * 120; //120分钟

            $duration = 5; //5秒

            $wechatModel = $wechatService->getWechatAccount($userId,null,true);

            if (!empty($wechatModel) && isset($wechatModel[0]) && !empty($wechatModel[0]->open_id)) {
                return $this->renderAjax('person-info-set-wechat', [
                    'wechatModels' => $wechatModel,
                    'hasWechat' => "1",
                    'duration' => $duration,
                    'userId' => $userId
                ]);

            } else {
                if ($wechatService->createQRCode($companyId, WechatService::QR_CODE_TEMP, $expireSeconds, WechatService::QR_SCENE_ACTION_BIND_USER, $userId, $result, $errMessage)) {
                    $ticketUrl = $wechatService->getQRCodeSrcUrlByTicket($result);
                    return $this->renderAjax('person-info-set-wechat', [
                        'hasWechat' => "0",
                        'ticketUrl' => $ticketUrl,
                        'duration' => $duration,
                        'userId' => $userId
                    ]);
                } else {
                    return $this->renderAjax('person-info-set-wechat', [
                        'hasWechat' => "0",
                        'ticketUrl' => null,
                        'duration' => null,
                        'userId' => $userId,
                        'errMessage' => $errMessage
                    ]);
                }
            }
        }
    }

    /**
     * 学习历程
     * @return string
     */
    public function actionMyPath()
    {
        return $this->render('my-path');
    }

    /**
     * 学习历程tab
     * @param $page
     * @param $type
     * @return string
     */
    public function actionMyPathList($current_time, $page = 1, $type)
    {
        $this->layout = 'none';

        $id = Yii::$app->user->getId();

        $size = 10;
        $service = new RecordService();

        $view = '';
        switch ($type) {
            case LnRecord::RECORD_CATEGORY_COURSE:
                $data = $service->getRecordByUserId($id, $type, $size, $page, $current_time);
                $view = '/common/tab-path-course';
                break;
            case LnRecord::RECORD_CATEGORY_EXAM:
                $data = $service->getRecordByUserId($id, $type, $size, $page, $current_time);
                $view = '/common/tab-path-exam';
                break;
            case LnRecord::RECORD_CATEGORY_SURVEY:
                $data = $service->getRecordByUserId($id, $type, $size, $page, $current_time);
                $view = '/common/tab-path-survey';
                break;
            case LnRecord::RECORD_CATEGORY_QUESTION:
                $data = $service->getRecordByUserId($id, $type, $size, $page, $current_time);
                $view = '/common/tab-path-question';
                break;
            case LnRecord::RECORD_CATEGORY_WEB:
                $data = $service->getRecordAndDataByUserId($id, $type, $size, $page, $current_time);
                $view = '/common/tab-path-web';
                break;
            case LnRecord::RECORD_CATEGORY_EVENT:
                $data = $service->getRecordAndDataByUserId($id, $type, $size, $page, $current_time);
                $view = '/common/tab-path-event';
                break;
            case LnRecord::RECORD_CATEGORY_BOOK:
                $data = $service->getRecordAndDataByUserId($id, $type, $size, $page, $current_time);
                $view = '/common/tab-path-book';
                break;
            case LnRecord::RECORD_CATEGORY_EXP:
                $data = $service->getRecordAndDataByUserId($id, $type, $size, $page, $current_time);
                $view = '/common/tab-path-exp';
                break;
            case LnRecord::RECORD_CATEGORY_CERT:
                $data = $service->getCertAndDataByUserId($id, $type, $size, $page, $current_time);
                $view = '/common/tab-path-cert';
                break;
        }

        return $this->render($view, [
            'data' => $data
        ]);
    }

    /**
     * 学习历程->课程
     * @param $page
     * @param null $time
     * @return string
     */
    public function actionMyCoursePath($page, $time = null)
    {
        $this->layout = 'none';

        $id = Yii::$app->user->getId();

        $size = 10;
        $service = new CourseService();
        $data = $service->getAllRegCourseByUid($id, $time, $size, $page);

        return $this->render('/common/tab-path-course', [
            'data' => $data
        ]);
    }

    /**
     * 学习历程->问答
     */
    public function actionGetQuestion($page, $time = null)
    {
        $this->layout = 'none';
        $id = Yii::$app->user->getId();

        $size = 10;
        $service = new QuestionService();
        $question = $service->getQuestionPageDataById($id, $time, $size, $page);

        return $this->render('/common/tab-path-question', [
            'data' => $question,
        ]);
    }

    /**
     * 学习历程->分享
     */
    public function actionGetShare($page, $time = null)
    {
        $this->layout = 'none';
        $id = Yii::$app->user->getId();

        $service = new ShareService();
        $size = 10;

        $share = $service->getSharePageDataById($id, $time, $size, $page);

        return $this->render('/common/tab-path-share', [
            'data' => $share,
        ]);
    }

    /**
     * 学习历程->记录
     */
    public function actionGetRecord($page, $time)
    {
        $this->layout = 'none';
        $id = Yii::$app->user->getId();

        $service = new RecordService();
        $size = 10;

        $record = $service->getRecordPageDataById($id, $time, $size, $page);

        return $this->render('/common/tab-path-record', [
            'data' => $record,
        ]);
    }

    /**
     * 学员首页->我要提问
     * @return array|string
     */
    public function actionIndexTabQuestion()
    {
        $model = new SoQuestion();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $uid = Yii::$app->user->getId();
            $company_id = Yii::$app->user->identity->company_id;
            $user = FwUser::findOne($uid);
            $service = new QuestionService();
            $model->user_id = $uid;
            $model->company_id = $company_id;
            $model->tags = Yii::$app->request->post('tags');
            $at_users = Yii::$app->request->post('select_value');

            if ($at_users == '') {
                $at_users = null;
            } else {
                $at_users = explode('|', $at_users);
            }

            if ($service->CreateQuestion($model, $user)) {
                $recordService = new RecordService();
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
            return $this->renderAjax('index-tab-question', [
                'data' => $model,
            ]);
        }
    }

    /**
     * 学员首页->记录网页
     * @return array|string
     */
    public function actionIndexTabWeb()
    {
        $model = new SoRecord();
        $model->setScenario('web');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $uid = Yii::$app->user->getId();
            $is_share = Yii::$app->request->post('is_share');

            $model->user_id = $uid;
            $model->needReturnKey = true;
            if ($model->save()) {
                /*增加积分*/
                $pointRuleService = new PointRuleService();
                $pointResult = $pointRuleService->curUserCheckActionForPoint('Publish-Page', 'Learning-Portal');

                $recordService = new RecordService();
                $recordService->addByRecord($uid, $model->kid, LnRecord::RECORD_CATEGORY_WEB);
                if ($is_share == '1') {
                    $service = new UserAttentionService();
                    //获取所有关注对象
                    $user_attention = $service->getAllUserId($uid);

                    // 保存share操作
                    $share = new SoShare();
                    $share->title = $model->title;
                    $share->content = $model->content;
                    $share->type = SoShare::SHARE_TYPE_RECORD;
                    $share->obj_id = $model->kid;
                    $share->user_id = $uid;
                    $share->needReturnKey = true;
                    if ($share->save()) {
                        if (isset($user_attention) && $user_attention != null && count($user_attention) > 0) {
                            $user_array = TArrayHelper::get_array_key($user_attention, 'user_id');

                            ShareService::ShareUserSave($share, $user_array);

                            $timelineService = new TimelineService();
                            // 推送动态
                            $timelineService->PushTimelineByShare($uid, $user_attention, $model);

                            $messageService = new MessageService();
                            // 推送消息
                            $messageService->PushMessageByShare($uid, $user_attention, $model);
                        }
                    }
                }
                return ['result' => 'success', 'pointResult' => $pointResult];

            } else {
                $errors = array_values($model->getErrors());
                $message = '';
                for ($i = 0; $i < count($errors); $i++) {
                    $message .= $errors[$i][0] . '<br />';
                }

                return ['result' => 'other', 'message' => $message];
            }
        } else {
            return $this->renderAjax('index-tab-web', [
                'data' => $model,
            ]);
        }
    }

    /**
     * 学员首页->记录事件
     * @return array|string
     */
    public function actionIndexTabEvent()
    {
        $model = new SoRecord();
        $model->setScenario('event');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $uid = Yii::$app->user->getId();
            $is_share = Yii::$app->request->post('is_share');

            $model->user_id = $uid;
            $model->needReturnKey = true;
            if ($model->save()) {
                /*增加积分*/
                $pointRuleService = new PointRuleService();
                $pointResult = $pointRuleService->curUserCheckActionForPoint('Publish-Event', 'Learning-Portal');

                $recordService = new RecordService();
                $recordService->addByRecord($uid, $model->kid, LnRecord::RECORD_CATEGORY_EVENT);

                if ($is_share == '1') {
                    $service = new UserAttentionService();
                    //获取所有关注对象
                    $user_attention = $service->getAllUserId($uid);

                    // 保存share操作
                    $share = new SoShare();
                    $share->title = $model->title;
                    $share->content = $model->content;
                    $share->type = SoShare::SHARE_TYPE_RECORD;
                    $share->obj_id = $model->kid;
                    $share->user_id = $uid;
                    $share->needReturnKey = true;
                    if ($share->save()) {
                        if (isset($user_attention) && $user_attention != null && count($user_attention) > 0) {
                            $user_array = TArrayHelper::get_array_key($user_attention, 'user_id');

                            ShareService::ShareUserSave($share, $user_array);

                            $timelineService = new TimelineService();
                            // 推送动态
                            $timelineService->PushTimelineByShare($uid, $user_attention, $model);

                            $messageService = new MessageService();
                            // 推送消息
                            $messageService->PushMessageByShare($uid, $user_attention, $model);
                        }
                    }
                }

                return ['result' => 'success', 'pointResult' => $pointResult];

            } else {
                $errors = array_values($model->getErrors());
                $message = '';
                for ($i = 0; $i < count($errors); $i++) {
                    $message .= $errors[$i][0] . '<br />';
                }

                return ['result' => 'other', 'message' => $message];
            }
        } else {
            return $this->renderAjax('index-tab-event', [
                'data' => $model,
            ]);
        }
    }

    /**
     * 学员首页->记录书籍
     * @return array|string
     */
    public function actionIndexTabBook()
    {
        $model = new SoRecord();
        $model->setScenario('book');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $uid = Yii::$app->user->getId();
            $is_share = Yii::$app->request->post('is_share');
            $model->needReturnKey = true;
            $model->user_id = $uid;
            if ($model->save()) {
                /*增加积分*/
                $pointRuleService = new PointRuleService();
                $pointResult = $pointRuleService->curUserCheckActionForPoint('Publish-Book', 'Learning-Portal');

                $recordService = new RecordService();
                $recordService->addByRecord($uid, $model->kid, LnRecord::RECORD_CATEGORY_BOOK);
                if ($is_share == '1') {
                    $service = new UserAttentionService();
                    //获取所有关注对象
                    $user_attention = $service->getAllUserId($uid);

                    // 保存share操作
                    $share = new SoShare();
                    $share->title = $model->title;
                    $share->content = $model->content;
                    $share->type = SoShare::SHARE_TYPE_RECORD;
                    $share->obj_id = $model->kid;
                    $share->user_id = $uid;
                    $share->needReturnKey = true;
                    if ($share->save()) {
                        if (isset($user_attention) && $user_attention != null && count($user_attention) > 0) {
                            $user_array = TArrayHelper::get_array_key($user_attention, 'user_id');

                            ShareService::ShareUserSave($share, $user_array);

                            $timelineService = new TimelineService();
                            // 推送动态
                            $timelineService->PushTimelineByShare($uid, $user_attention, $model);

                            $messageService = new MessageService();
                            // 推送消息
                            $messageService->PushMessageByShare($uid, $user_attention, $model);
                        }
                    }
                }

                return ['result' => 'success', 'pointResult' => $pointResult];

            } else {
                $errors = array_values($model->getErrors());
                $message = '';
                for ($i = 0; $i < count($errors); $i++) {
                    $message .= $errors[$i][0] . '<br />';
                }

                return ['result' => 'other', 'message' => $message];
            }
        } else {
            return $this->renderAjax('index-tab-book', [
                'data' => $model,
            ]);
        }
    }

    /**
     * 学员首页->记录经验
     * @return array|string
     */
    public function actionIndexTabExp()
    {
        $model = new SoRecord();
        $model->setScenario('exp');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $uid = Yii::$app->user->getId();
            $is_share = Yii::$app->request->post('is_share');

            $model->user_id = $uid;
            $model->needReturnKey = true;
            if ($model->save()) {
                /*增加积分*/
                $pointRuleService = new PointRuleService();
                $pointResult = $pointRuleService->curUserCheckActionForPoint('Publish-Sharing', 'Learning-Portal');

                $recordService = new RecordService();
                $recordService->addByRecord($uid, $model->kid, LnRecord::RECORD_CATEGORY_EXP);
                if ($is_share == '1') {
                    $service = new UserAttentionService();
                    //获取所有关注对象
                    $user_attention = $service->getAllUserId($uid);

                    // 保存share操作
                    $share = new SoShare();
                    $share->title = $model->title;
                    $share->content = $model->content;
                    $share->type = SoShare::SHARE_TYPE_RECORD;
                    $share->obj_id = $model->kid;
                    $share->user_id = $uid;
                    $share->needReturnKey = true;
                    if ($share->save()) {
                        if (isset($user_attention) && $user_attention != null && count($user_attention) > 0) {
                            $user_array = TArrayHelper::get_array_key($user_attention, 'user_id');

                            ShareService::ShareUserSave($share, $user_array);

                            $timelineService = new TimelineService();
                            // 推送动态
                            $timelineService->PushTimelineByShare($uid, $user_attention, $model);

                            $messageService = new MessageService();
                            // 推送消息
                            $messageService->PushMessageByShare($uid, $user_attention, $model);
                        }
                    }
                }

                return ['result' => 'success', 'pointResult' => $pointResult];

            } else {
                $errors = array_values($model->getErrors());
                $message = '';
                for ($i = 0; $i < count($errors); $i++) {
                    $message .= $errors[$i][0] . '<br />';
                }

                return ['result' => 'other', 'message' => $message];
            }
        } else {
            return $this->renderAjax('index-tab-exp', [
                'data' => $model,
            ]);
        }
    }

    /**
     * 记录经验->附件上传
     * @return string
     */
    public function actionUpload()
    {
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
                $result = ['info' => Yii::t('common', 'upload_error')];
//            } else if (!in_array($type, $fileTypes)) {
//                $result = ['info' => Yii::t('common', 'file_type_error')];
            } else {
                $fileUpload = new TFileUploadHelper();
                $info = $fileUpload->UploadFile($_FILES["myfile"], 'recordattach/');
                if ($info['result'] == 'Completed') {
                    $result = ['info' => $info['file_path'], 'filename' => $fileName];
                } else {
                    $result = ['info' => Yii::t('common', 'upload_error')];
                }
            }
            echo json_encode($result);
        }
    }

    public function actionAttention()
    {
        return $this->render('attention');
    }

    public function actionAttentionList($current_time, $page = 1, $filter = null, $time = null)
    {
        $this->layout = 'none';
        $id = Yii::$app->user->getId();

        $filter = $filter ? $filter : 3;

        $size = 10;

        $service = new UserService();
        $data = $service->getAttentionByUid($id, $filter, $time, $size, $page, $current_time);
        return $this->render('attention-list', [
            'data' => $data,
        ]);
    }

    public function actionGetAttentionOne($current_time, $page = 1, $filter = null, $time = null)
    {
        $this->layout = 'none';
        $id = Yii::$app->user->getId();

        $filter = $filter ? $filter : 3;

        $size = 10;

        $service = new UserService();
        $data = $service->getAttentionOneByUid($id, $filter, $time, $size, $page, $current_time);
        return $this->render('attention-list', [
            'data' => $data,
        ]);
    }

    public function actionMyCourse()
    {
        $id = Yii::$app->user->getId();

        $service = new CourseService();

        $counts = $service->getCourseStatusCount($id);

        return $this->render('my-course', [
            'reg_count' => $counts[0],
            'done_count' => $counts[1],
            'doing_count' => $counts[0] - $counts[1],
        ]);
    }

    public function actionGetCourseList($current_time, $key = null, $type = 'all', $page = 1)
    {
        $uid = Yii::$app->user->getId();
        $size = 10;
        $service = new CourseService();

        $view = 'get-course-list';

        if ($type === 'finished') {
            $view = 'get-finished-course-list';
        }

        $data = $service->GetRegCourseByUserId($uid, $key, $type, $size, $size < 1 ? 0 : ((int)$page - 1) * $size, $current_time);

        return $this->renderAjax($view, $data);
    }

    /**
     * 用户积分详情
     * @author adophper 2016-03-08
     * @return string
     */
    public function actionIntegral()
    {
        $companyId = Yii::$app->user->identity->company_id;
        $userService = new UserService();
        $userId = Yii::$app->user->getId();
        $integralAvailable = $userService->getUserIntegral($userId, $companyId);
        $integralTotal = $userService->getUserIntegral($userId, $companyId, 'total');
        $integralYears = $userService->getUserIntegralDetail($userId, $companyId, 'year');
        $integralMonth = $userService->getUserIntegralDetail($userId, $companyId, 'month');
        return $this->render('integral', [
            'integralAvailable' => $integralAvailable,
            'integralTotal' => $integralTotal,
            'integralYears' => $integralYears,
            'integralMonth' => $integralMonth,
        ]);
    }

    /**
     * 积分记录
     * @return string
     */
    public function actionIntegralList()
    {
        $params = Yii::$app->request->getQueryParams();
        $params['defaultPageSize'] = $this->defaultPageSize;
        $userId = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;
        $userService = new UserService();
        $result = $userService->getUserIntegralList($userId, $companyId, $params);
        return $this->renderAjax('integral-list', [
            'params' => $params,
            'result' => $result,
        ]);
    }

    /**
     * 积分规则
     * @author adophper 2016-03-08
     * @return string
     */
    public function actionIntegralPointRule()
    {
        $userService = new UserService();
        $companyId = Yii::$app->user->identity->company_id;
        $params['defaultPageSize'] = $this->defaultPageSize;
        $result = $userService->getIntegralPointRule($companyId, $params);
        return $this->renderAjax('integral-point-rule', [
            'result' => $result,
        ]);
    }

    /**
     * 成长体系
     * @author adophper 2016-03-08
     * @return string
     */
    public function actionIntegralGrowth()
    {
        $userService = new UserService();
        $companyId = Yii::$app->user->identity->company_id;
        $params['defaultPageSize'] = $this->defaultPageSize;
        $result = $userService->getIntegralGrowth($companyId, $params);
        return $this->renderAjax('integral-growth', [
            'result' => $result,
        ]);
    }

    public function actionSetTag()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $userId = Yii::$app->user->getId();
            $companyId = Yii::$app->user->identity->company_id;

            $service = new TagService();

            Yii::$app->response->format = Response::FORMAT_JSON;

            $tagList = Yii::$app->request->post('tags');

            $service->saveUserInterestTags($userId, $companyId, $tagList);

            /*增加积分*/
            $pointRuleService = new PointRuleService();
            $pointResult = $pointRuleService->curUserCheckActionForPoint('Complete-Self-Info', 'Learning-Portal');

            return ['result' => 'success', 'pointResult' => $pointResult];
        }
    }
}