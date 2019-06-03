<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/17/15
 * Time: 1:13 PM
 */

namespace common\base;

use yii;
use common\helpers\TLoggerHelper;
use yii\base\ActionFilter;


class BaseFilter extends ActionFilter{

    private $startTime;

    public function beforeAction($action)
    {
        $this->startTime = microtime(true);
        return parent::beforeAction($action);
    }

    public function afterAction($action,$result)
    {
        $currentTime = microtime(true);
        $time = $currentTime - $this->startTime;

        /* @var $user \yii\web\User */
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        if ($user && ($identity = $user->getIdentity(false))) {
            $userID = $identity->getId();
        } else {
            $userID = '00000000-0000-0000-0000-000000000000';
        }

        TLoggerHelper::Access("User '{$userID}' spent $time second to access Action '{$action->uniqueId}'.");
        return parent::afterAction($action,$result);
    }
}