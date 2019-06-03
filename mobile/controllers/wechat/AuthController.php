<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/4/5
 * Time: 10:26
 */

namespace mobile\controllers\wechat;

use yii\web\Controller;
use yii\helpers\Json;
use Yii;
use yii\web\BadRequestHttpException;
use common\services\framework\WechatService;
use yii\web\Cookie;
use common\viewmodels\framework\LoginForm;
use common\services\framework\ExternalSystemService;
use api\services\UserService;
use common\viewmodels\framework\PasswordResetRequestForm;
use common\viewmodels\framework\ResetPasswordForm;
use mobile\services\WechatAuthBehavior;

class AuthController extends Controller {

    public function actionIndex() {
        $this->layout = false;
        $cookies = Yii::$app->request->cookies;
        Yii::$app->response->cookies->add(new Cookie([
            'name' => '_openid',
            'value' => 'om6iGxKjc7cyImR6LqpCnXjYDlMA',
            'expire' => time() + 3600 * 24 * 365
        ]));
        return $this->render('index',[
            'message' => $cookies->getValue('_openid','can not get openid from cookie'),
            'login' => Yii::$app->session->get('access_token')
        ]);
    }
    public function actionLogout() {
        Yii::$app->response->cookies->remove('_openid');
        Yii::$app->user->logout();
        Yii::$app->response->redirect(Yii::$app->urlManager->createUrl(['index/pending_list']))->send();
    }
    
    public function actionRefreshToken() {
        $this->attachBehavior('autoLogin',WechatAuthBehavior::className());
        $this->auth();
        $token = Yii::$app->session->get('access_token')['access_token'];
        Yii::$app->response->format = 'json';
        return [
            'token' => $token
        ];
    }
    
    public function actionLogin() {
        $input = Yii::$app->request->getQueryParams();
        $this->layout = 'login';
        $model = new LoginForm();
        $commonUserService = new \common\services\framework\UserService();
        if(Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && $commonUserService->loginCheck($model)) {
            $userService = new UserService();
            $post = Yii::$app->request->post();
            $result = $userService->openAuthLoginByUsername('lms-wechat', $this->action->id, $post['LoginForm']['user_name'], $post['LoginForm']['password']);
            Yii::$app->session->set('access_token',Json::decode($result['result']));

            $redirectUrl = Yii::$app->urlManager->createUrl(['index/pending_list']);
            if(isset($input['redirect_to']) && !empty($input['redirect_to'])) {
                $redirectUrl = $input['redirect_to'];
            }
            return $this->redirect($redirectUrl);
        }

        return $this->render('login',[
            'model' => $model
        ]);
    }

    public function actionRedirect() {
        $input = Yii::$app->request->getQueryParams();
        $wechat = new WechatService;
        $userId = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;
        $state = $input['state'];
        
        $cfg = $wechat->getCompanyActiveWechat($companyId);
        $token = $wechat->getWebAuthAccessToken($cfg->app_id,$cfg->app_secret,$input['code']);
        $token = json_decode($token,true);
        if(isset($token['errcode'])) {
            exit($token['errmsg']);
        }

        $cookies = Yii::$app->response->cookies;
        $cookies->add(new Cookie([
            'name' => '_openid',
            'value' => $token['openid'],
            'expire' => time() + 3600 * 24 * 365
        ]));

        $userinfo = $wechat->getWebAuthUserInfo($token['access_token'],$token['openid']);

        $ret = $wechat->bindWechatAccount($companyId,$userId,$token['openid'],$error,$userinfo,true);
        if(!$ret) {
            exit($error);
        }

        $redirectUrl = Yii::$app->session->get('state_'.$state,Yii::$app->getUrlManager()->createUrl(['index/pending_list']));
        Yii::$app->session->remove('state_'.$state);
        return Yii::$app->response->redirect($redirectUrl);
    }

    public function actionFindPassword() {
        $this->layout = 'login';
        $passwordResetModel = new PasswordResetRequestForm();
        if (Yii::$app->request->isPost && $passwordResetModel->load(Yii::$app->request->post()) && $passwordResetModel->validate()) {
            $userService = new \common\services\framework\UserService();
            if ($userService->isEmailRepeat($passwordResetModel->email)) {
                $passwordResetModel->addError("email", Yii::t('common','error_email_repeat'));
            }
            else {
                if ($passwordResetModel->sendEmail()) {
                    $passwordResetModel->addError("email", Yii::t('common', 'login_email_reset'));
                } else {
                    $passwordResetModel->addError("email", Yii::t('common', 'send_mail_error'));
                }
            }
        }
        return $this->render('find_password',[
            'model' => $passwordResetModel
        ]);
    }
}