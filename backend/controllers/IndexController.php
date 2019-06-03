<?php
namespace backend\controllers;


use backend\services\CourseService;
use backend\services\InvestigationService;
use backend\services\MenuService;
use backend\services\PermissionService;
use backend\services\QuestionService;
use common\models\framework\FwSystemInfo;
use common\services\framework\RbacService;
use common\services\framework\SystemInfoService;
use common\services\framework\UserService;
use common\models\framework\FwUser;
use common\viewmodels\framework\Menu;
use common\viewmodels\framework\PasswordResetRequestForm;
use common\viewmodels\framework\ResetPasswordForm;
use common\base\BaseActiveRecord;
use common\helpers\TClientHelper;
use common\helpers\TNetworkHelper;
use common\helpers\TURLHelper;
use components\widgets\ActiveForm;
use Yii;
use backend\base\BaseBackController;
use common\viewmodels\framework\LoginForm;
use yii\base\Exception;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\User;


class IndexController extends BaseBackController
{
    public $layout = 'frame';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['except'] = ['login', 'error', 'page', 'captcha', 'offline', 'signup', 'no-authority', 'request-password-reset', 'reset-password'];

        return $behaviors;
    }

    /**
     * @return string 后台默认页面
     */
    public function actionIndex()
    {
//        $this->layout = 'frame';
        $userId = Yii::$app->user->getId();
        $companyId = null;

        $commonUserServce = new UserService();

        $rbacService = new RbacService();
        $isSpecial = $rbacService->isSpecialUser($userId);

        if (!$isSpecial) {
            $companyId = Yii::$app->user->identity->company_id;
        }

        $user_count = $commonUserServce->getUserCount($companyId,$isSpecial);

        $course = new CourseService();
        $course_count = $course->getCourseCount($userId,$isSpecial);

        $question = new QuestionService();
        $question_count = $question->getQuestionCount($userId,$isSpecial);

        $investigation = new InvestigationService();
        $investigation_count = $investigation->getInvestigationCount($userId,$isSpecial);
//
//        $test =  $commonUserServce->GetOnlineUserList();

//        $userId = Yii::$app->user->getId();
//        $a = $commonUserServce->GetCompanyStringByUserId($userId);
//        $b = $commonUserServce->GetOrgnizationStringByUserId($userId);
//        $c = $commonUserServce->getReportingManagerStringByUserId($userId);
//        $d = $commonUserServce->GetDomainStringByUserId($userId);

        return $this->render('index', [
            'user_count' => $user_count,
            'course_count'=>$course_count,
            'investigation_count'=>$investigation_count,
            'question_count'=>$question_count,
            'isSpecial'=>$isSpecial
        ]);
    }

    public function actionUserCountChart()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userService = new \backend\services\UserService();
        $rbacService = new RbacService();
        $userId = Yii::$app->user->getId();
        $isSpecial = $rbacService->isSpecialUser($userId);
        $jsonData = [];
        if ($isSpecial) {
            $year = intval(date("Y", time()));
            $month = intval(date("m", time()));


            for ($i = 0; $i < 12; $i++) {
                if ($month == 0) {
                    $year = $year - 1;
                    $month = 12;
                }

                $firstday = date('Y-m-01', strtotime(strval($year) . '-' . strval($month) . '-01'));
                $currentTime = date('Y-m', strtotime(strval($year) . '-' . strval($month) . '-01'));
                $lastday = date('Y-m-d', strtotime("$firstday + 1 month -1 day"));

//                $startTime = $firstday . ' 00:00:00';
                $endTime = $lastday . ' 23:59:59';

                $userCount = $userService->getTotalUserCount(strtotime($endTime));
                $timeStr = Yii::t('backend','time');
                $useNoStr = Yii::t('backend','user_number');
                $newItem = [
                    "$timeStr" => $currentTime,
                    "$useNoStr" => $userCount
                ];

                array_push($jsonData, $newItem);
                $month = $month - 1;
            }

        }
        return $jsonData;
    }

    public function actionHome()
    {
        return $this->render('home');
    }

    public function actionNoAuthority()
    {
        return $this->render('no-authority');
    }

    public function actionOffline($message = null)
    {
        $this->layout = false;
        return $this->render('offline', ['message' => $message]);
    }


    /**
     * @return string|\yii\web\Response 用户登录
     */

    public function actionLogin()
    {
        $model = new LoginForm();
        $commonUserService = new UserService();
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && $commonUserService->loginCheck($model)) {
            $browserName = null;
            $clientService = new TClientHelper();
            $isMobile = $clientService->isMobile($browserName);
            if (!Yii::$app->session->has("isMobile"))
            {
                Yii::$app->session->set("isMobile",$isMobile);
                Yii::$app->session->set("browserName",$browserName);
            }

            $returnUrl = Yii::$app->user->getReturnUrl();
            if (empty($returnUrl) || strpos($returnUrl,"/backend/?XDEBUG_SESSION_START=") !== false || $returnUrl == "/backend/") {
                $url = TURLHelper::createUrl('index/index');
                return Yii::$app->getResponse()->redirect($url);
            }
            else {
                return Yii::$app->getResponse()->redirect($returnUrl);
            }
        } else {
            $this->layout = 'login';

            $clientService = new TClientHelper();
            if (!$clientService->isSupported()) {
                return Yii::$app->getResponse()->redirect(['../site/upgrade-browser']);
            }

            if (!Yii::$app->user->getIsGuest()) {
                $userId = Yii::$app->user->getId();
                $commonUserService = new UserService();
                $commonUserService->keepOffline($userId);

                Yii::$app->user->logout();
            }

            return $this->render('login', [
                'model' => $model,
            ]);
        }
//        }
    }

    public function actionLogout()
    {
        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();
            $commonUserService = new UserService();
            $commonUserService->keepOffline($userId);

            Yii::$app->user->logout();
        }
        $language = Yii::$app->language ;

        $cacheKey = "Common_Language";

        $commonlanguage = BaseActiveRecord::loadFromCache($cacheKey, true, $hasCache);
        $action = parent::getStartupAction();

        if ($hasCache && $commonlanguage == $language) {
            $homeUrl = Yii::$app->urlManager->createUrl($action);
        }
        else {
            if (!is_array($action)) {
                $arr = [];
                array_push($arr,$action);
                $params = array_merge($arr, ["lang" => $language]);
            }
            else {
                $params = array_merge($action, ["lang" => $language]);
            }

            $homeUrl = Yii::$app->urlManager->createUrl($params);
        }
        return Yii::$app->getResponse()->redirect($homeUrl);
    }

    public function actionRequestPasswordReset()
    {
        $this->layout = 'login';

        $model = new PasswordResetRequestForm();
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && $model->validate()) {
//            Yii::$app->response->format = Response::FORMAT_JSON;
            $userService = new UserService();
            if ($userService->isEmailRepeat($model->email)) {
                $model->addError("email", Yii::t('common','error_email_repeat'));
            }
            else {
                if ($model->sendEmail()) {
//                return ['result'=>'success'];
                    $model->addError("email", Yii::t('common', 'login_email_reset'));
//                    return $this->goHome();
                } else {
                    $model->addError("email", Yii::t('common', 'send_mail_error'));
//                    $model->error_message = Yii::t('common', 'send_mail_error');


//                throw new InvalidCallException(Yii::t('common','send_mail_error'));
//                return ['result'=>'failure'];
                }
            }

            return $this->render('requestPasswordResetToken', [
                'model' => $model,
            ]);

        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }


    public function actionResetPassword($token)
    {
        $this->layout = 'login';

        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
//            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->validate() && $model->resetPassword())
            {
                return $this->goHome();
            } else {
                $model->error_message = Yii::t('common','reset_password_error');
                return $this->render('resetPassword', [
                    'model' => $model,
                ]);
//                throw new InvalidCallException(Yii::t('common','reset_password_error'));
            }
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
}