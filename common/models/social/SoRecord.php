<?php

namespace common\models\social;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%so_record}}".
 *
 * @property string $kid
 * @property string $user_id
 * @property string $title
 * @property string $content
 * @property string $start_at
 * @property string $end_at
 * @property string $url
 * @property integer $duration
 * @property string $record_type
 * @property string $attach_original_filename
 * @property string $attach_url
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class SoRecord extends BaseActiveRecord
{
    /*
     * 网页
     */
    const RECORD_TYPE_WEB = '0';
    /*
     * 事件
     */
    const RECORD_TYPE_EVENT = '1';
    /*
     * 书籍
     */
    const RECORD_TYPE_BOOK = '2';
    /*
     * 经验
     */
    const RECORD_TYPE_EXP = '3';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%so_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'title', 'content', 'url', 'record_type'], 'required', 'on' => 'web'],
            [['user_id', 'title', 'content', 'start_at', 'record_type'], 'required', 'on' => 'event'],
            [['user_id', 'title', 'content', 'record_type'], 'required', 'on' => 'book'],
            [['user_id', 'title', 'content', 'record_type'], 'required', 'on' => 'exp'],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['content', 'attach_original_filename', 'attach_url'], 'string'],
            [['end_at'], 'compare', 'compareAttribute' => 'start_at', 'operator' => '>='],
            [['duration', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'user_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 200],
            [['url'], 'url'],

            [['record_type'], 'string', 'max' => 1],
            [['record_type'], 'in', 'range' => [
                self::RECORD_TYPE_WEB, self::RECORD_TYPE_EVENT,
                self::RECORD_TYPE_BOOK, self::RECORD_TYPE_EXP
            ]],

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
            'kid' => Yii::t('frontend', 'kid'),
            'user_id' => Yii::t('frontend', 'user_id'),
            'title' => Yii::t('frontend', 'title'),
            'content' => Yii::t('frontend', 'content'),
            'start_at' => Yii::t('frontend', 'start_at'),
            'end_at' => Yii::t('frontend', 'end_at'),
            'url' => Yii::t('frontend', 'url'),
            'duration' => Yii::t('frontend', 'duration'),
            'record_type' => Yii::t('frontend', 'record_type'),
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
