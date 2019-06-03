<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/6/27
 * Time: 10:27
 */

namespace mobile\controllers\wechat;
use mobile\base\BaseMobileController;

use common\services\framework\WechatJsSdkService;
use common\services\framework\WechatService;
use Yii;

class ConfigController extends BaseMobileController {
    
    public function actionIndex() {
        $wechatService = new WechatService();
        $cfg = $wechatService->getCompanyActiveWechat($this->companyId);
        $sdk = new WechatJsSdkService($this->companyId,$cfg->app_id,$cfg->app_secret,Yii::$app->request->getQueryParam('debug',null) != null);
        Yii::$app->response->format = 'json';
        return $sdk->config();
    }
}