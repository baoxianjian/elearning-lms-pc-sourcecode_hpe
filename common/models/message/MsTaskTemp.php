<?php

namespace common\models\message;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ms_task_temp}}".
 *
 * @property string $kid
 * @property string $object_id
 * @property string $task_type
 * @property string $push_status
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class MsTaskTemp extends BaseActiveRecord
{
    const PUSH_STATUS_ERROR = '0';
    const PUSH_STATUS_SUCCESS = '1';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_task_temp}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['kid', 'object_id', 'task_type', 'push_status', 'created_by', 'created_at'], 'required'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'task_type', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['object_id'], 'string', 'max' => 5000],
            [['push_status'], 'string', 'max' => 1],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['push_status'], 'string', 'max' => 1],
            [['push_status'], 'default', 'value' => self::PUSH_STATUS_ERROR],
            [['push_status'], 'in', 'range' => [self::PUSH_STATUS_ERROR, self::PUSH_STATUS_SUCCESS]],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => '任务ID',
            'object_id' => '多个用\",\" 隔开',
            'task_type' => '任务类型；Domain：域；Position：岗位；Organization：组织；Object：受众',
            'push_status' => '推送状态；0：失败；1：成功',
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
