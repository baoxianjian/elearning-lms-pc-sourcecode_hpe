<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/4/1
 * Time: 16:44
 */

namespace mobile\services;

use Yii;
use yii\base\Behavior;
use yii\web\Controller;
use common\services\framework\WechatService;
use common\models\framework\FwUserWechatAccount;
use common\models\framework\FwUser;
use common\services\framework\UserService;
use api\services\UserService as ApiUserService;
use yii\helpers\Json;

class WechatAuthBehavior extends Behavior {

    public function events() {
        return [
            Controller::EVENT_BEFORE_ACTION => 'auth'
        ];
    }

    public function auth() {
        $wechatService = new WechatService();
        $cookies = Yii::$app->request->cookies;
        $openid = $cookies->getValue('_openid',null);
        $isGuest = Yii::$app->user->isGuest;
        $state = time();
        Yii::$app->session->set('state_'.$state,Yii::$app->request->absoluteUrl);

        function _isWechat() {
            $ua = Yii::$app->request->getHeaders()->get('User-Agent');
            return !!preg_match("/MicroMessenger/i",$ua);
        }
        function _login($state) {
            Yii::$app->response->redirect(Yii::$app->urlManager->createUrl(['wechat/auth/login']).'?redirect_to='.Yii::$app->request->absoluteUrl.'&state='.$state)->send();
        }
        function _auth(&$service,$state) {
            $userId = Yii::$app->user->getId();
            $companyId = Yii::$app->user->identity->company_id;
            $cfg = $service->getCompanyActiveWechat($companyId);
            $url = $service->getWebAuthUrl($cfg->app_id,Yii::$app->urlManager->createAbsoluteUrl(['wechat/auth/redirect']),'snsapi_userinfo',$state);
            Yii::$app->response->redirect($url)->send();
        }

        if(!_isWechat()) {
            $openid = false;
        }
        //首次打开
        if(!$openid && $isGuest) {
            _login($state);
        }

        //未授权,已登录
        if(!$openid && !$isGuest && _isWechat()) {
            _auth($wechatService,$state);
        }

        //已授权,未登录
        if($openid && $isGuest) {
            $wechatModel = new FwUserWechatAccount();
            $userModel = new FwUser();
            $userService = new UserService();
            $apiUserService = new ApiUserService();

            $account = $wechatModel->findOne([
                'open_id' => $openid
            ]);
            if(empty($account)) {
                _auth($wechatService,$state);
            }

            $user = $userModel->findOne([
                'kid' => $account->user_id
            ]);
            
            Yii::$app->user->login($user);
            $userService->login($account->user_id);
            $result = $apiUserService->getAccessTokenByUserIdentity('lms-wechat', $user);
            Yii::$app->session->set('access_token',Json::decode($result['result']));
            Yii::$app->response->format = 'html';
        }
    }
}