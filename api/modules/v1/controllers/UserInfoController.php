<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 8/18/15
 * Time: 12:03 PM
 **/

namespace api\modules\v1\controllers;


use api\base\BaseApiController;

class UserInfoController extends BaseApiController
{
    public $modelClass = "common/models/framework/FwUser";

    //获取用户基本信息
    public function actionInfo()
    {
        echo "info";

    }

    //修改密码
    public function actionChangePassword()
    {
        echo "change-password";

    }
}