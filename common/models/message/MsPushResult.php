<?php

namespace common\models\message;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ms_push_result}}".
 *
 * @property string $kid
 * @property string $task_id
 * @property string $user_id
 * @property string $push_status
 * @property string $error_msg_code
 * @property string $error_msg_title
 * @property string $error_msg_content
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 */
class MsPushResult extends BaseActiveRecord
{
    const PUSH_STATUS_TODO = '0';
    const PUSH_STATUS_SUCCESS = '1';
    const PUSH_STATUS_FAIL = '2';

    public $object_name;
    public $org_name;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_push_result}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'user_id'], 'required'],
            [['error_msg_content'], 'string'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'task_id', 'user_id', 'error_msg_code', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['push_status', 'is_deleted'], 'string', 'max' => 1],
            [['error_msg_title'], 'string', 'max' => 500],
            [['created_from', 'updated_from'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('frontend', 'kid'),
            'task_id' => Yii::t('frontend', '任务ID'),
            'user_id' => Yii::t('frontend', '用户ID'),
            'push_status' => Yii::t('frontend', '推送状态；0：待推送，1：推送成功，2：推送失败'),
            'error_msg_code' => Yii::t('frontend', '错误消息代码'),
            'error_msg_title' => Yii::t('frontend', '错误消息标题'),
            'error_msg_content' => Yii::t('frontend', '错误消息内容'),
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'is_deleted' => Yii::t('common', 'is_deleted'),
        ];
    }
}
