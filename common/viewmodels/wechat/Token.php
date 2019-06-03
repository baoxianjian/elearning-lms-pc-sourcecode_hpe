<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 12/16/2015
 * Time: 9:09 PM
 */

namespace common\viewmodels\wechat;


use yii\base\Model;

class Token extends Model
{
    public $accessToken;
    public $expiresIn;
    public $startAt;
    public $endAt;
    public $companyId;
    public $userId;
    public $wechatId;
}