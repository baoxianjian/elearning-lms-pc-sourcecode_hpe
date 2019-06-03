<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/2
 * Time: 13:52
 */
namespace frontend\viewmodels;

use yii\base\Model;
use Yii;

class TaskPushForm extends Model
{
    const IS_TEMP_YES = 'yes';

    const IS_TEMP_NO = 'no';

    public $task_id;
    public $domain;
    public $items;
    public $objects;
    public $time_push;
    public $push_prepare_at;
    public $is_temp;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id'], 'string', 'max' => 50],
            [['items', 'objects', 'domain'], 'required', 'message' => '{attribute}不能为空！'],
            [['time_push'], 'string', 'max' => 1],
            [['is_temp'], 'string'],
            [['push_prepare_at'], 'integer'],
        ];
    }
}