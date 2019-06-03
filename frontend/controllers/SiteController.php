<?php

namespace frontend\controllers;


use common\models\framework\FwCompany;
use common\models\framework\FwUser;
use common\models\social\SoCollect;
use common\services\common\ApiService;
use common\services\framework\CompanyMenuService;
use common\services\framework\CompanyService;
use common\services\framework\DictionaryService;
use common\services\framework\ExternalSystemService;
use common\services\framework\PointRuleService;
use common\services\framework\UserDomainService;
use common\services\framework\UserService;
use common\services\interfaces\service\ToolInterface;
use common\services\learning\CourseService;
use common\services\social\CollectService;
use common\services\social\QuestionCareService;
use common\services\social\QuestionService;
use common\services\social\ShareService;
use common\services\social\UserAttentionService;
use common\viewmodels\framework\LoginForm;
use common\viewmodels\framework\PasswordResetRequestForm;
use common\viewmodels\framework\ResetPasswordForm;
use common\base\BaseActiveRecord;
use common\helpers\TBaseHelper;
use common\helpers\TClientHelper;
use common\helpers\TFileHelper;
use common\helpers\TNetworkHelper;
use common\helpers\TStringHelper;
use common\helpers\TURLHelper;
use frontend\base\BaseFrontController;
use frontend\viewmodels\SignupForm;
use Yii;
use yii\base\InvalidParamException;
use yii\db;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;

/**
 * Site controller
 */
class SiteController extends BaseFrontController
{
    public $layout = 'frame';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['except'] = ['login', 'license', 'error', 'captcha', 'offline', 'signup', 'no-authority',
            'reset-password', 'qr-code','change-lang',
            'signup-active', 'signup-reminder', 'upgrade-browser'];

        return $behaviors;
    }

    public function actionNoAuthority()
    {
        return $this->render('no-authority');
    }

    public function actionIndex()
    {
        $isMobile = false;
        if (Yii::$app->session->has("isMobile")) {
            $isMobile = Yii::$app->session->get("isMobile");
        }
        $size = Yii::$app->params['index_list_size'];

        $companyId = Yii::$app->user->identity->company_id;
        $companyMenuService = new CompanyMenuService();
        $portalMenu = $companyMenuService->getCompanyPortalMenu($companyId);

        //用户课程统计
        $lessonsInfo = [];
        //用户已加入的选课
        $user_lesson = [];
        //考试统计
        $testsInfo = [];

        //热门课程
        $course = new CourseService();
        $HotCourses = $course->getNewCourses($size, 0, $isMobile);
        $service = new QuestionService();
        /*热门问答*/
        $HotQuestion = $service->getQaPageDataById(null, $size, 1);
        //学习社区
        $forum_data = [];

        //积分榜
        $TopPoints = null;//UserPointService::getTopUserPoints(9);

        $userTests = [];
        return $this->render('index', [
            'HotCourses' => $HotCourses,
            'HotQuestion' => $HotQuestion,
            'forums' => $forum_data,
            'lessonsInfo' => $lessonsInfo,
            'testsInfo' => $testsInfo,
            'TopPoints' => $TopPoints,
            'user_lesson' => $user_lesson,
            'userTests' => $userTests,
            'portalMenu' => $portalMenu,
        ]);
    }

    public function actionLicense()
    {
        if (Yii::$app->request->isPost) {
//            $machine_code = Yii::$app->request->post('machine_code');
            $reg_code = Yii::$app->request->post('reg_code');
            if (!TBaseHelper::checkLicense($reg_code, $errMessage)) {
                $errMsg = Yii::t('common', 'reg_code_error');
            } else {
                /*写入配置文件*/
                TFileHelper::updateLicense($reg_code);
                $this->redirect(Url::toRoute(['site/login']));
                Yii::$app->end();
            }
        }

        $hostUrl = Yii::$app->request->getHostInfo();
        $hostName = str_replace(['http:', 'https:', '/'], ['', '', ''], $hostUrl);
        $position = strpos($hostName, ":");
        if ($position == false || $position == 0) {
            //不包含端口号，不做处理
        } else {
            $hostName = substr($hostName, 0, $position);
        }

        $isDevEnvironment = in_array($hostName, TBaseHelper::$devEnvironmentSites);
        /*验证通过直接跳转到登录页面*/

        $license = null;
        if (isset(Yii::$app->params['license'])) {
            $license = Yii::$app->params['license'];
        }

        if ($isDevEnvironment || TBaseHelper::checkLicense($license, $errMessage)) {
            $this->redirect(Url::toRoute(['site/login']));
            Yii::$app->end();
        }

        $companyModel = null;

        $secondLevelDomainOpen = false;//二级域名功能启用开关
        $isTopLevelDomain = false;//是否主站域名

        if (isset(Yii::$app->params['main_site_url'])) {
            $main_site_url = Yii::$app->params['main_site_url'];
        }

        if (isset(Yii::$app->params['cachedSites'])) {
            $cachedSites = Yii::$app->params['cachedSites'];
        }

        if (isset($main_site_url) && !empty($main_site_url)) {
            if (is_array($main_site_url)) {//主站域名配置如果是数组
                $isTopLevelDomain = in_array($hostName, $main_site_url);
            } else if ($main_site_url == $hostName) {
                $isTopLevelDomain = true;
            }
        } else {
            //如果没有设置main_site_url，任何站点都是主站
            $isTopLevelDomain = true;
        }

        if (!$isTopLevelDomain && isset($cachedSites) && count($cachedSites) > 0) {
            $secondLevelDomainOpen = in_array($hostName, $cachedSites);
        } else {
            //如果没有设置cachedSites，任何站点都是主站
            $isTopLevelDomain = true;
        }

        if (!$isTopLevelDomain) {
            if ($secondLevelDomainOpen) {
                $companyModel = FwCompany::findOne(['second_level_domain' => $hostName, 'status' => FwCompany::STATUS_FLAG_NORMAL]);

                if ($companyModel == null) {
//                        $isTopLevelDomain = true;
                    //如果没找到域名对应的企业，则认为是主站点
                }
            } else {
//                    $this->redirect('error');
                //如果没找到配置的域名，则认为是主站点
            }
        }

        $this->layout = 'login';
        $errMsg = empty($errMsg) ? null : $errMsg;
        $macAddress = TBaseHelper::getMacAddress();
        if (!TBaseHelper::getMachineCode($macAddress, $machineCode, $errMessage)) {
            $errMsg = Yii::t('system', 'frontend_name') . Yii::t('common', 'license_error');
        }


        return $this->render('license', [
            'machineCode' => $machineCode,
            'company' => $companyModel,
            'errMsg' => $errMsg,
        ]);


    }

    public function actionLogin()
    {
        $model = new LoginForm();
        $model->system_flag = "frontend";
        $commonUserService = new UserService();
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && $commonUserService->loginCheck($model)) {
            $showLogo = Yii::$app->params['show_logo'];
            if ($showLogo) {
                $company = Yii::$app->getUser()->getIdentity()->fwCompany;
                if ($company->logo_url) {
                    Yii::$app->session->set('logo_url', $company->logo_url);
                }
            }
            // 增加积分
            $pointRuleService = new PointRuleService();
            $pointResult = $pointRuleService->curUserCheckActionForPoint('Login');
            if ($pointResult['result'] === '1') {
                Yii::$app->session->set('LoginPoint', $pointResult);
            }

            if (Yii::$app->user->identity->need_pwd_change === FwUser::NEED_PWD_CHANGE_YES) {
                $url = TURLHelper::createUrl('site/change-password');
                return Yii::$app->getResponse()->redirect($url);
            } else {
                $browserName = null;
                $clientService = new TClientHelper();
                $isMobile = $clientService->isMobile($browserName);
                if (!Yii::$app->session->has("isMobile")) {
                    Yii::$app->session->set("isMobile", $isMobile);
                    Yii::$app->session->set("browserName", $browserName);
                }

                $returnUrl = Yii::$app->user->getReturnUrl();
                if (empty($returnUrl) || $returnUrl == "/" 
                    || strpos($returnUrl,"/?XDEBUG_SESSION_START=") !== false 
                    || strpos($returnUrl, "site/change-password") > 0) {
                    //注如果上一个页面是修改密码，则需要忽略，否则会无限循环
                    $homePage = TBaseHelper::getHomePage();
                    return Yii::$app->getResponse()->redirect($homePage);
                } else {
                    return Yii::$app->getResponse()->redirect($returnUrl);
                }
            }
        } else {
            $clientService = new TClientHelper();
            if (!$clientService->isSupported() && !YII_DEBUG) {
                echo "<script>if(document.documentMode < 10) window.location.href='/site/upgrade-browser.html';</script>";
            }

            if (!Yii::$app->user->getIsGuest()) {
                $userId = Yii::$app->user->getId();
                $commonUserService = new UserService();
                $commonUserService->keepOffline($userId);

                Yii::$app->user->logout();
            }

            $hostUrl = Yii::$app->request->getHostInfo();
            $hostName = str_replace(['http:', 'https:', '/'], ['', '', ''], $hostUrl);
            $position = strpos($hostName, ":");
            if ($position == false || $position == 0) {
                //不包含端口号，不做处理
            } else {
                $hostName = substr($hostName, 0, $position);
            }

            $isDevEnvironment = in_array($hostName, TBaseHelper::$devEnvironmentSites);

            $license = null;
            if (isset(Yii::$app->params['license'])) {
                $license = Yii::$app->params['license'];
            }
            
            if (!$isDevEnvironment && !TBaseHelper::checkLicense($license, $errMessage)) {
                //echo $errMessage;
                $this->redirect(Url::toRoute(['site/license']));
                Yii::$app->end();
            }
//            Yii::getLogger()->log("start Login 3", Logger::LEVEL_ERROR);
            $companyModel = null;

            $secondLevelDomainOpen = false;//二级域名功能启用开关
            $isTopLevelDomain = false;//是否主站域名

            if (isset(Yii::$app->params['main_site_url'])) {
                $main_site_url = Yii::$app->params['main_site_url'];
            }

            if (isset(Yii::$app->params['cachedSites'])) {
                $cachedSites = Yii::$app->params['cachedSites'];
            }

            if (isset($main_site_url) && !empty($main_site_url)) {
                if (is_array($main_site_url)) {//主站域名配置如果是数组
                    $isTopLevelDomain = in_array($hostName, $main_site_url);
                } else if ($main_site_url == $hostName) {
                    $isTopLevelDomain = true;
                }
            } else {
                //如果没有设置main_site_url，任何站点都是主站
                $isTopLevelDomain = true;
            }

            if (!$isTopLevelDomain && isset($cachedSites) && count($cachedSites) > 0) {
                $secondLevelDomainOpen = in_array($hostName, $cachedSites);
            } else {
                //如果没有设置cachedSites，任何站点都是主站
                $isTopLevelDomain = true;
            }

            if (!$isTopLevelDomain) {
                if ($secondLevelDomainOpen) {
                    $companyModel = FwCompany::findOne(['second_level_domain' => $hostName, 'status' => FwCompany::STATUS_FLAG_NORMAL]);

                    if ($companyModel == null) {
//                        $isTopLevelDomain = true;
                        //如果没找到域名对应的企业，则认为是主站点
                    }
                } else {
//                    $this->redirect('error');
                    //如果没找到配置的域名，则认为是主站点
                }
            }


            $this->layout = 'login';

            $passwordResetModel = new PasswordResetRequestForm();
            if (Yii::$app->request->isPost && $passwordResetModel->load(Yii::$app->request->post()) && $passwordResetModel->validate()) {
//            Yii::$app->response->format = Response::FORMAT_JSON;
                $userService = new UserService();
                if ($userService->isEmailRepeat($passwordResetModel->email)) {
                    $passwordResetModel->addError("email", Yii::t('common','error_email_repeat'));
                }
                else {
                    if ($passwordResetModel->sendEmail()) {
//                return ['result'=>'success'];
//                    return $this->goHome();
                        $passwordResetModel->addError("email", Yii::t('common', 'login_email_reset'));
//                    $PasswordResetmodel->error_message = Yii::t('common', 'send_mail_error');
//                    return $this->render('login', [
//                        'mailon' => 0,
//                        'passwordresetmodel' => $PasswordResetmodel,
//                    ]);
                    } else {
                        $passwordResetModel->addError("email", Yii::t('common', 'send_mail_error'));
//                    $PasswordResetmodel->error_message = Yii::t('common', 'send_mail_error');
//                    return $this->render('login', [
//                        'mailon' => 0,
//                        'passwordresetmodel' => $PasswordResetmodel,
//                    ]);
//                throw new InvalidCallException(Yii::t('common','send_mail_error'));
//                return ['result'=>'failure'];
                    }
                }
            }
            $mailon = 0;
            if (isset($_REQUEST['PasswordResetRequestForm']['email'])) {
                $mailon = 1;
            }

            if (!empty($companyModel)) {
                $enableRegister = $companyModel->is_self_register;
            } else {
                $dictionaryService = new DictionaryService();
                $defaultIsSelfRegister = $dictionaryService->getDictionaryValueByCode("system", "is_self_register");

                if (!empty($defaultIsSelfRegister)) {
                    $enableRegister = $defaultIsSelfRegister;
                } else {
                    $enableRegister = FwCompany::NO;
                }
            }
            $dictionaryService = new DictionaryService();
            $languageModel = $dictionaryService->getDictionariesByCategory('language');

            return $this->render('login', [
                'model' => $model,
                'languageModel' => $languageModel,
                'mailon' => $mailon,
                'hostUrl' => $hostUrl,
                'hostName' => $hostName,
                'passwordresetmodel' => $passwordResetModel,
                'company' => $companyModel,
                'enableRegister' => $enableRegister
            ]);
        }
    }

    public function actionQrCode($url)
    {
        $this->layout = 'frame-nologin';
        header('Content-type: image/png');
        $url = base64_decode($url);
        ToolInterface::genQRCode($url);
    }
    //切换语言setcookie
    public function actionChangeLang(){
        $lang = isset($_REQUEST['lang'])?$_REQUEST['lang']:'';
        $paramurl = isset($_REQUEST['url'])?$_REQUEST['url']:'';
        $isGuest = Yii::$app->user->isGuest;
        if($isGuest){
            if(!empty($lang)){
                setcookie("LangCookie",$lang);
                $url =  $paramurl.'?lang='.$lang;
                $this->redirect($url);
                Yii::$app->end();
             }else{
                if(!empty($_COOKIE['LangCookie'])){
                    $url =  $paramurl.'?lang='.$_COOKIE['LangCookie'];
                    $this->redirect($url);
                    Yii::$app->end();
                }else{
                    $this->redirect($paramurl);
                    Yii::$app->end();
                }
            }
        }else{
            if(!empty($lang)){
                setcookie("LangCookie",$lang);
                $url =  $paramurl.'?lang='.$lang;
                $id = Yii::$app->user->getId();
                $model = FwUser::findOne($id);
                $model->language = $lang;
                if ($model->validate()) {
                   if ($model->save()) {
                       $sessionLanguageKey = "Language_" . $id;
                       Yii::$app->session->set($sessionLanguageKey, $model->language);
                       $this->redirect($url);
                       Yii::$app->end();
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
            }else{
                $this->redirect($paramurl);
                Yii::$app->end();
            }
        }

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
            if (TStringHelper::CheckPasswordStrength($model->password_new) < 2) {
                $model->error_message = Yii::t('common', 'password_strength_less');
                return $this->render('resetPassword', [
                    'model' => $model,
                ]);
            }
            if ($model->validate() && $model->resetPassword()) {
                return $this->goHome();
            } else {
                $model->error_message = Yii::t('common', 'reset_password_error');
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

    public function actionSignup()
    {
        $model = new SignupForm();
        $dictionaryService = new DictionaryService();
        $genderModel = $dictionaryService->getDictionariesByCategory('gender');

        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if ($model->sendEmail($user->account_active_token)) {
                    $url = Yii::$app->urlManager->createUrl(['site/signup-reminder', 'token' => $user->account_active_token]);
                    return Yii::$app->getResponse()->redirect($url);
                }
            }
        } else {
            $hostUrl = Yii::$app->request->getHostInfo();
            $hostName = str_replace(['http:', 'https:', '/'], ['', '', ''], $hostUrl);
            $position = strpos($hostName, ":");
            if ($position == false || $position == 0) {
                //不包含端口号，不做处理
            } else {
                $hostName = substr($hostName, 0, $position);
            }
            $companyModel = null;

            $secondLevelDomainOpen = false;//二级域名功能启用开关
            $isTopLevelDomain = false;//是否主站域名

            if (isset(Yii::$app->params['main_site_url'])) {
                $main_site_url = Yii::$app->params['main_site_url'];
            }

            if (isset(Yii::$app->params['cachedSites'])) {
                $cachedSites = Yii::$app->params['cachedSites'];
            }

            if (isset($main_site_url) && !empty($main_site_url)) {
                if (is_array($main_site_url)) {//主站域名配置如果是数组
                    $isTopLevelDomain = in_array($hostName, $main_site_url);
                } else if ($main_site_url == $hostName) {
                    $isTopLevelDomain = true;
                }
            } else {
                //如果没有设置main_site_url，任何站点都是主站
                $isTopLevelDomain = true;
            }

            if (!$isTopLevelDomain && isset($cachedSites) && count($cachedSites) > 0) {
                $secondLevelDomainOpen = in_array($hostName, $cachedSites);
            } else {
                //如果没有设置cachedSites，任何站点都是主站
                $isTopLevelDomain = true;
            }

            if (!$isTopLevelDomain) {
                if ($secondLevelDomainOpen) {
                    $companyModel = FwCompany::findOne(['second_level_domain' => $hostName, 'status' => FwCompany::STATUS_FLAG_NORMAL]);

                    if ($companyModel == null) {
                        $isTopLevelDomain = true;
                    }
                }
            }

            if ($isTopLevelDomain) {
                $companyService = new CompanyService();
                $companyModel = $companyService->getDefaultRegisterCompanyList();
                $model->company_id = $companyModel->kid;
            } else {
                $model->company_id = $companyModel->kid;
            }
        }

        return $this->render('signup', [
            'model' => $model,
            'genderModel' => $genderModel
        ]);
    }

    public function actionSignupReminder($token)
    {
        $user = FwUser::findOne([
            'status' => FwUser::STATUS_FLAG_TEMP,
            'account_active_token' => $token,
        ]);

        return $this->render('signup-reminder', [
            'model' => $user,
        ]);
    }

    public function actionSignupActive($token)
    {
        $user = FwUser::findOne([
            'status' => FwUser::STATUS_FLAG_TEMP,
            'account_active_token' => $token,
        ]);

        if (!empty($user)) {
            $user->status = FwUser::STATUS_FLAG_NORMAL;
            $user->account_active_token = null;
            $user->save();
        }

        return $this->render('signup-active', [
            'model' => $user,
        ]);
    }

    public function actionAdmin()
    {
        return $this->redirect('/backend');
    }

    public function actionOffline($message = null)
    {
        $this->layout = false;
        return $this->render('offline', ['message' => $message]);
    }

    public function actionChangePassword()
    {
        $this->layout = 'login';

        $id = Yii::$app->user->getId();
        $model = FwUser::findOne($id);
        $model->setScenario('force-change-password');

        if ($model->need_pwd_change === FwUser::NEED_PWD_CHANGE_NO) {
            return $this->goBack();
        }

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->password_hash != $model->password_repeat) {
                    return $this->render('change-password', [
                        'model' => $model,
                        'error' => Yii::t('common', 'password_repeat_error')
                    ]);
                }

                if (TStringHelper::CheckPasswordStrength($model->password_hash) < 2) {
                    return $this->render('change-password', [
                        'model' => $model,
                        'error' => Yii::t('common', 'password_strength_less')
                    ]);
                }

                $model->setPassword($model->password_hash);
                $model->last_pwd_change_at = time();
                $model->last_pwd_change_reason = FwUser::PASSWORD_CHANGE_REASON_FORCE_CHANGE;
                $model->need_pwd_change = FwUser::NEED_PWD_CHANGE_NO;
//                $model->needReturnKey = true;
                if ($model->save()) {

                    Yii::$app->user->setIdentity($model);
                    $companyId = Yii::$app->user->identity->company_id;
                    $defaultPortal = FwCompany::USER_PORTAL;
                    if (!empty($companyId)) {
                        $companyModel = FwCompany::findOne($companyId);
                        $defaultPortal = $companyModel->default_portal;
                    }
                    if ($defaultPortal == FwCompany::COMPANY_PORTAL) {
                        return Yii::$app->getResponse()->redirect(['site/index']);
                    } else {
                        return Yii::$app->getResponse()->redirect(['student/index']);
                    }
                } else {
                    return $this->render('change-password', [
                        'model' => $model,
                        'error' => Yii::t('common', 'reset_password_error')
                    ]);
                }
            } else {
                $errors = array_values($model->getErrors());
                $message = '';
                for ($i = 0; $i < count($errors); $i++) {
                    $message .= $errors[$i][0] . '<br />';
                }

                return $this->render('change-password', [
                    'model' => $model,
                    'error' => $message,
                ]);
            }
        } else {
            $model->password_hash = '';

            return $this->render('change-password', [
                'model' => $model,
            ]);
        }
    }


    public function actionSearch($key)
    {
        $pointRuleService = new PointRuleService();
        $pointResult = $pointRuleService->curUserCheckActionForPoint('Search');

        $externalSystemService = new ExternalSystemService();
        $search_api_url = $externalSystemService->getExternalSystemInfoByExternalSystemCode("elearning-solr-service")->api_address;

        $count_course_api = 'course/search';
        $count_question_api = 'question/search';
        $count_person_api = 'user/search';
        $count_share_api = 'share/search';

        $user_id = Yii::$app->user->getId();
        $company_id = Yii::$app->user->identity->company_id;

        $userDomainService = new UserDomainService();
        $domainIds = $userDomainService->getSearchListByUserId($user_id);

        if (isset($domainIds) && $domainIds != null) {
            $domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');

            $domainIds = array_keys($domainIds);

            $domainIdStr = '';

            foreach ($domainIds as $dom) {
                $domainIdStr .= 'domain_id:' . $dom . ' ';
            }
            $domainIdStr = rtrim($domainIdStr);
        }

        // 取得课程记录条数
        $response = TNetworkHelper::HttpGet($search_api_url . $count_course_api, ['q' => $key, 'fq1' => $domainIdStr, 'fq2' => 'is_display_pc:1', 'start' => 0, 'rows' => 0]);
        $content = json_decode($response['content']);
        $course_count = $content->response->numFound;
        $course_time = $response['time'];

        // 取得问答记录条数
        $response = TNetworkHelper::HttpGet($search_api_url . $count_question_api, ['q' => $key, 'fq' => 'company_id:' . $company_id, 'start' => 0, 'rows' => 0]);

        $content = json_decode($response['content']);
        $question_count = $content->response->numFound;
        $question_time = $response['time'];

//        $blank_key = TStringHelper::StringAddBlank($key);
        // 取得人员记录条数
        //搜索相关度在0.1以上的
        $key_u="real_name:{$key} OR orgnization_name:{$key} OR position_name:{$key}";
        $fq2_u='{!frange l=0.1}query($q)'; 
        $response = TNetworkHelper::HttpGet($search_api_url . $count_person_api, ['q' => $key_u, 'fq1' => 'company_id:' . $company_id,'fq2'=>$fq2_u, 'start' => 0, 'rows' => 0]);

        $content = json_decode($response['content']);
        $person_count = $content->response->numFound;
        $person_time = $response['time'];

        // 取得分享记录条数
        $response = TNetworkHelper::HttpGet($search_api_url . $count_share_api, ['q' => $key, 'fq' => 'company_id:' . $company_id, 'start' => 0, 'rows' => 0]);
        $content = json_decode($response['content']);
        $share_count = $content->response->numFound;
        $share_time = $response['time'];

        return $this->render('search', [
            'key' => $key,
            'time' => $course_time + $question_time + $person_time + $share_time,
            'c_count' => $course_count ? $course_count : 0,
            'q_count' => $question_count ? $question_count : 0,
            'p_count' => $person_count ? $person_count : 0,
            's_count' => $share_count ? $share_count : 0,
            'pointResult' => $pointResult,
        ]);
    }

    /**
     * @return string
     */
//    public function actionNewsList()
//    {
//        $this->layout = 'list';
//
//        $service = new CommonService();
//
//        $pages = new TPagination(['defaultPageSize' => '4', 'totalCount' => $service->getNewsCount()]);
//
//        $data = $service->getNews($pages->offset, $pages->limit);
//        return $this->render('/common/newsList', [
//            'data' => $data,
//            'pages' => $pages,
//        ]);
//    }


    public function actionSearchCourse($key, $count)
    {
        $this->layout = 'list';

        $user_id = Yii::$app->user->getId();

        // 取得用户收藏课程，用于判断界面收藏按钮文字显示
        $collectService = new CollectService();
        $course_list = $collectService->getCourseByUserId($user_id);
        $course_list = ArrayHelper::map($course_list, 'object_id', 'object_id');
        $course_list = array_keys($course_list);

        $size = $this->defaultPageSize;

        $service = new ApiService();

        //是否为手机端判断
        $isMobile = false;
        if (Yii::$app->session->has("isMobile")) {
            $isMobile = Yii::$app->session->get("isMobile");
        }

        $result = $service->GetSearchCourseData($key, $user_id, $size, $count, $isMobile);

        return $this->render('search-course', [
            'data' => $result['data'],
            'pages' => $result['page'],
            'collect_courses' => $course_list,
        ]);
    }

    public function actionSearchQuestion($key, $count)
    {
        $this->layout = 'list';

        $user_id = Yii::$app->user->getId();
        $company_id = Yii::$app->user->identity->company_id;

        // 取得用户关注对象，用于判断界面关注按钮文字显示
        $careService = new QuestionCareService();
        $care_list = $careService->getAllCareUserId($user_id);
        $care_list = ArrayHelper::map($care_list, 'question_id', 'question_id');
        $care_list = array_keys($care_list);

        // 取得用户收藏对象，用于判断界面收藏按钮文字显示
        $collectService = new CollectService();
        $collect_list = $collectService->getAllCollectByUserId($user_id, SoCollect::TYPE_QUESTION);
        $collect_list = ArrayHelper::map($collect_list, 'object_id', 'object_id');
        $collect_list = array_keys($collect_list);

        $size = $this->defaultPageSize;

        $service = new ApiService();
        $result = $service->GetSearchQuestionData($key, $company_id, $size, $count);

        return $this->render('search-question', [
            'data' => $result['data'],
            'pages' => $result['page'],
            'care_questions' => $care_list,
            'collect_questions' => $collect_list,
        ]);
    }

    public function actionSearchPerson($key, $count)
    {
        $this->layout = 'list';

        $user_id = Yii::$app->user->getId();
        $company_id = Yii::$app->user->identity->company_id;

        // 取得用户关注对象，用于判断界面关注按钮文字显示
        $attentionService = new UserAttentionService();
        $attentionUser = $attentionService->getAllAttentionUserId($user_id);
        $attentionUser = ArrayHelper::map($attentionUser, 'attention_id', 'attention_id');
        $attentionUser = array_keys($attentionUser);

        $size = 15;

        $service = new ApiService();
        $result = $service->GetSearchPersonData($key, $company_id, $size, $count);

        return $this->render('search-person', [
            'data' => $result['data'],
            'pages' => $result['page'],
            'attention_users' => $attentionUser,
        ]);
    }

    public function actionSearchShare($key, $type = null, $count)
    {
        $this->layout = 'list';

        $user_id = Yii::$app->user->getId();
        $company_id = Yii::$app->user->identity->company_id;

        if ($type === 'all') {
            $type = null;
        }

        if ($type !== null) {
            $externalSystemService = new ExternalSystemService();
            $search_api_url = $externalSystemService->getExternalSystemInfoByExternalSystemCode("elearning-solr-service")->api_address;

            $count_share_api = 'share/search';

            // 取得分享记录条数
            $response = TNetworkHelper::HttpGet($search_api_url . $count_share_api, ['q' => $key, 'fq1' => 'record_type:' . $type, 'fq2' => 'company_id:' . $company_id, 'start' => 0, 'rows' => 0]);
            $content = json_decode($response['content']);
            $share_count = $content->response->numFound;
        }

        $size = 10;

        $service = new ApiService();
        $result = $service->GetSearchShareData($key, $type, $company_id, $size, $type !== null ? $share_count : $count);

        return $this->render('search-share', [
            'data' => $result['data'],
            'pages' => $result['page'],
        ]);
    }

    public function actionSearchHistory($uid, $page = 1)
    {
        $size = 10;
        $service = new ShareService();
        $data = $service->getShareByUid($uid, $size, $page);

        return $this->renderAjax('search-history', [
            'data' => $data,
        ]);
    }

    public function actionUpgradeBrowser()
    {
        $this->layout = false;
		
        $clientService = new TClientHelper();
        if ($clientService->isSupported()) {
            return $this->goHome();
        } else {
            echo "<script>if(document.documentMode >= 10) window.location.href='" . Yii::$app->getHomeUrl() . "';</script>";
        }

        return $this->render('upgrade-browser');
    }
}
