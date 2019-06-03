<?php

namespace common\models\message;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ms_subscribe_setting}}".
 *
 * @property string $kid
 * @property string $type_id
 * @property string $user_id
 * @property string $status
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class MsSubscribeSetting extends BaseActiveRecord
{
    const STATUS_ON = '1';
    const STATUS_OFF = '0';
    public $type_code;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_subscribe_setting}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id', 'user_id'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'type_id', 'user_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_ON, self::STATUS_OFF]],

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
            'kid' => Yii::t('common', 'Kid'),
            'type_id' => Yii::t('common', 'Type ID'),
            'user_id' => Yii::t('common', 'User ID'),
            'status' => Yii::t('common', 'Status'),
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
