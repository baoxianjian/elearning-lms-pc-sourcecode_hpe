<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/4/1
 * Time: 16:59
 */

namespace mobile\controllers\api;

use mobile\base\BaseApiController;
use yii\helpers\Json;
use mobile\events\WechatUserInfo;

class UserController extends BaseApiController {
    
    const EVENT_WECHAT_AUTH_SUCCESS = 'WECHAT_AUTH_SUCCESS';

    public function actionIndex() {
        $this->on(self::EVENT_WECHAT_AUTH_SUCCESS,[new WechatUserInfo(),'call']);

        $this->trigger(self::EVENT_WECHAT_AUTH_SUCCESS);
        return Json::encode([]);
    }


}