<?php

namespace common\models\message;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ms_push_result_temp}}".
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
 */
class MsPushResultTemp extends BaseActiveRecord
{
    public $object_name;
    public $org_name;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_push_result_temp}}';
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
            [['created_from','updated_from'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('frontend', 'kid'),
            'task_id' => Yii::t('frontend', 'task_id'),
            'user_id' => Yii::t('frontend', 'user_id'),
            'push_status' => Yii::t('frontend', 'push_status'),
            'error_msg_code' => Yii::t('frontend', 'error_msg_code'),
            'error_msg_title' => Yii::t('frontend', 'error_msg_title'),
            'error_msg_content' => Yii::t('frontend', 'error_msg_content'),
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
