<?php

namespace frontend\controllers;

use common\models\framework\FwExternalSystem;
use common\models\framework\FwUser;
use common\services\framework\UserService;
use common\services\framework\WechatService;
use common\helpers\TClientHelper;
use frontend\base\BaseFrontController;
use Yii;
use yii\filters\AccessControl;

class SystemTransferController extends BaseFrontController
{
    public $layout = 'none';
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['except'] = ['login', 'wechat-index','wechat-login'];

        return $behaviors;
    }

    public function actionLogin($system_key, $access_token, $transferUrl = null)
    {
        $success = false;
        $errorMessage = Yii::t('frontend', 'login_failed');
        $systemKey = trim($system_key);
        $accessToken = trim($access_token);
        if (!empty($transferUrl))
            $transferUrl = trim($transferUrl);

        if (empty(Yii::$app->user->identity)) {
            $model = FwExternalSystem::findOne(['system_key' => $systemKey], false);
            if (!empty($model)) {
                $extenalSystemId = $model->kid;

                $userId = FwUser::getUserIdByAccessToken($extenalSystemId, $accessToken);

                $userModel = FwUser::findOne($userId);

                if (!empty($userModel) && $userModel->status == self::STATUS_FLAG_NORMAL) {
                    $success = true;
                }
            }

            if (!$success) {
                return $this->render('login', [
                    'errorMessage' => $errorMessage,
                ]);
            } else {
                $loginResult = Yii::$app->user->login($userModel, 0);

                if ($loginResult) {
                    $userId = Yii::$app->user->getId();
                    $commonUserService = new UserService();
                    $commonUserService->login($userId);

                    Yii::$app->session->set("system_key", $systemKey);
                    Yii::$app->session->set("system_id", $extenalSystemId);

                    $browserName = null;
                    $clientService = new TClientHelper();
                    $isMobile = $clientService->isMobile($browserName);
                    if (!Yii::$app->session->has("isMobile")) {
                        Yii::$app->session->set("isMobile", $isMobile);
                        Yii::$app->session->set("browserName", $browserName);
                    }

                    if (!empty($transferUrl))
                        return Yii::$app->getResponse()->redirect($transferUrl);
                    else
                        return Yii::$app->getResponse()->redirect(['/student/index']);
                } else {
                    return $this->render('login', [
                        'errorMessage' => $errorMessage,
                    ]);
                }
            }
        } else {
            //如果已经登录了，则直接跳转
            if (!empty($transferUrl))
                return Yii::$app->getResponse()->redirect($transferUrl);
            else
                return Yii::$app->getResponse()->redirect(['/student/index']);
        }
    }

    public function actionWechatIndex()
    {
        $transferUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?";

        header("content-type:text/html;charset=UTF-8");
        if (!empty(Yii::$app->request->getQueryParam("appid"))) {
            $appId = Yii::$app->request->getQueryParam("appid");
            $transferUrl .= "appid=" . $appId;
            $wechatService = new WechatService();
            $wechatModel = $wechatService->getCompanyActiveWechatByAppId($appId);
            if (!empty($wechatModel)) {
                $companyId = $wechatModel->company_id;
            }
            else {
                $errMessage = Yii::t('common', 'company_no_active_wechat');
                echo $errMessage;
                exit();
            }
        } else if (!empty(Yii::$app->request->getQueryParam("companyId"))) {
            $companyId = Yii::$app->request->getQueryParam("companyId");
            $wechatService = new WechatService();
            $wechatModel = $wechatService->getCompanyActiveWechat($companyId);

            if (empty($wechatModel) || empty($wechatModel->app_id) || empty($wechatModel->app_secret)) {
                $errMessage = Yii::t('common', 'company_no_active_wechat');
                echo $errMessage;
                exit();
            }
            else {
                $transferUrl .= "appid=" . $wechatModel->app_id;
            }
        } else {
            echo Yii::t('frontend', 'params_wrong_need_appid_and_companyid');
            exit();
        }

        $redirectUri = urlencode(Yii::$app->urlManager->createAbsoluteUrl(["system-transfer/wechat-login",'companyId'=>$companyId]));

        $transferUrl .= "&redirect_uri=" . $redirectUri . "&response_type=code&scope=snsapi_base&state=123#wechat_redirect";
        //第一步：用户同意授权，获取code
        //如果用户同意授权，页面将跳转至 redirect_uri/?code=CODE&state=STATE。
        //若用户禁止授权，则重定向后不会带上code参数，仅会带上state参数redirect_uri?state=STATE

        return Yii::$app->getResponse()->redirect($transferUrl);
    }

    public function actionWechatLogin($companyId)
    {
        //第二步：通过code换取网页授权access_token
        $transferUrl = Yii::$app->urlManager->createAbsoluteUrl("site/login");
        $code = Yii::$app->request->getQueryParam("code");
        $state = Yii::$app->request->getQueryParam("state");
//        $errorMessage = '登录失败';
        $success = false;
        if (!empty($code)) {
//            echo "code:".$code;
//            echo "state:".$state;
//            echo "companyId:".$companyId;
            $wechatService = new WechatService();
            if ($wechatService->getLoginOpenId($companyId,$code,$openId,$errMessage)) {

                $model = $wechatService->findWechatAccountByOpenId($companyId,$openId);
                if (!empty($model)) {
                    $userId = $model->user_id;
                    $userModel = FwUser::findOne($userId);

                    if (!empty($userModel) && $userModel->status == self::STATUS_FLAG_NORMAL) {
                        $success = true;
                    }
                }
            }
            else {
                echo $errMessage;
                exit();
            }
        }
//        echo "success:".$success;
//        echo "openId:".$openId;
//        echo "userId:".$userId;
        if ($success) {
            $loginResult = Yii::$app->user->login($userModel, 0);

            if ($loginResult) {
                $userId = Yii::$app->user->getId();
                $commonUserService = new UserService();
                $commonUserService->login($userId);

                $externalSystemModel = FwExternalSystem::findOne($wechatService->systemId);
                Yii::$app->session->set("system_key", $externalSystemModel->system_key);
                Yii::$app->session->set("system_id", $wechatService->systemId);

                $browserName = null;
                $clientService = new TClientHelper();
                $isMobile = $clientService->isMobile($browserName);
                if (!Yii::$app->session->has("isMobile")) {
                    Yii::$app->session->set("isMobile", $isMobile);
                    Yii::$app->session->set("browserName", $browserName);
                }

                $transferUrl = Yii::$app->urlManager->createAbsoluteUrl("student/index");

            }
        }


        return Yii::$app->getResponse()->redirect($transferUrl);
    }
}
?>
