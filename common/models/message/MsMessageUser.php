<?php

namespace common\models\message;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ms_message_user}}".
 *
 * @property string $kid
 * @property string $user_id
 * @property string $msg_id
 * @property string $receive_status
 * @property string $msg_type
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class MsMessageUser extends BaseActiveRecord
{
    const STATUS_UNRECEIVE = '0';
    const STATUS_RECEIVE = '1';

    const TYPE_NORMAL = '0';
    const TYPE_SPECIAL = '1';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_message_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'msg_id'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'user_id', 'msg_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['receive_status'], 'string', 'max' => 1],
            [['receive_status'], 'default', 'value' => self::STATUS_UNRECEIVE],
            [['receive_status'], 'in', 'range' => [self::STATUS_UNRECEIVE, self::STATUS_RECEIVE]],

            [['msg_type'], 'string', 'max' => 1],
            [['msg_type'], 'default', 'value' => self::TYPE_NORMAL],
            [['msg_type'], 'in', 'range' => [self::TYPE_NORMAL, self::TYPE_SPECIAL]],

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
            'msg_id' => Yii::t('frontend', 'msg_id'),
            'receive_status' => Yii::t('frontend', 'receive_status'),
            'msg_type' => Yii::t('frontend', 'msg_type'),
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMsMessage()
    {
        return $this->hasOne(MsMessage::className(), ['kid' => 'msg_id'])
            ->onCondition([MsMessage::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO,
                MsMessageUser::realTableName() . '.msg_type' => MsMessageUser::TYPE_NORMAL
            ]);
    }
}
