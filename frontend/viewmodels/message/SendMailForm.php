<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2016/5/17
 * Time: 13:52
 */
namespace frontend\viewmodels\message;

use yii\base\Model;
use Yii;

class SendMailForm extends Model
{
    public $title;
    public $content;
    public $sendMethod = '0';
    public $sendUsers;
    public $ccEmail;
    public $ccSelf = '0';
    public $ccManager = '0';
    public $sendSMS = '0';
    public $objectId;
    public $scenes;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content'], 'required'],
            [['title'], 'string', 'max' => 500],
            [['content', 'sendUsers', 'ccEmail', 'objectId', 'scenes'], 'string'],
            [['sendMethod'], 'string', 'max' => 1],
            [['ccSelf', 'ccManager', 'sendSMS'], 'boolean'],
        ];
    }
}