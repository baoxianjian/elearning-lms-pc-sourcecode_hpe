<?php

namespace common\models\learning;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ln_record}}".
 *
 * @property string $kid
 * @property string $user_id
 * @property string $object_id
 * @property string $object_type
 * @property string $push_user_id
 * @property string $push_user_name
 * @property string $record_category
 * @property string $learning_verb
 * @property string $learning_acivity
 * @property string $learning_result
 * @property string $record_type
 * @property string $start_at
 * @property string $end_at
 * @property integer $duration
 * @property string $data_from
 * @property string $record_format
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class LnRecord extends BaseActiveRecord
{
    const OBJECT_TYPE_COURSE = 'course';
    const OBJECT_TYPE_EXAM = 'exam';
    const OBJECT_TYPE_SURVEY = 'survey';

    const OBJECT_TYPE_QUESTION = 'question';

    const OBJECT_TYPE_RECORD = 'record';

    const OBJECT_TYPE_CERT = 'certification';

    const RECORD_CATEGORY_QUESTION = '问题';
    const RECORD_CATEGORY_COURSE = '课程';
    const RECORD_CATEGORY_EXAM = '考试';
    const RECORD_CATEGORY_SURVEY = '调查';
    const RECORD_CATEGORY_WEB = '网页';
    const RECORD_CATEGORY_EVENT = '事件';
    const RECORD_CATEGORY_BOOK = '书籍';
    const RECORD_CATEGORY_EXP = '经验';
    const RECORD_CATEGORY_CERT = '证书';


    const RECORD_TYPE_RECORD = '0';//学习记录

    const RECORD_TYPE_PROCESS = '1';//学习历程

    const DATA_FROM_LOCAL = 'local';//本系统

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'record_category', 'learning_verb', 'learning_acivity', 'record_type', 'record_format'], 'required'],
            [['duration', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'user_id', 'object_id', 'object_type', 'push_user_id', 'record_category', 'learning_verb', 'data_from', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['learning_acivity', 'learning_result'], 'string', 'max' => 500],
            [['record_type', 'record_format'], 'string', 'max' => 1],
            [['start_at', 'end_at'], 'string', 'max' => 20],
            [['push_user_name'], 'string', 'max' => 255],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['version'], 'number'],
            [['version'], 'default', 'value' => 1],

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
            'kid' => Yii::t('common', 'kid'),
            'user_id' => Yii::t('common', 'user_id'),
            'object_id' => Yii::t('common', 'object_id'),
            'object_type' => Yii::t('common', 'object_type'),
            'push_user_id' => Yii::t('common', 'push_user_id'),
            'push_user_name' => Yii::t('common', 'push_user_name'),
            'record_category' => Yii::t('common', 'record_category'),
            'learning_verb' => Yii::t('common', 'learning_verb'),
            'learning_acivity' => Yii::t('common', 'learning_acivity'),
            'learning_result' => Yii::t('common', 'learning_result'),
            'record_type' => Yii::t('common', 'record_type'),
            'start_at' => Yii::t('common', 'start_at'),
            'end_at' => Yii::t('common', 'end_at'),
            'duration' => Yii::t('common', 'duration'),
            'data_from' => Yii::t('common', 'data_from'),
            'record_format' => Yii::t('common', 'record_format'),
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
