<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_external_system_value}}".
 *
 * @property string $kid
 * @property string $system_id
 * @property string $object_id
 * @property string $object_type
 * @property string $value
 * @property string $value_type
 * @property string $status
 * @property integer $start_at
 * @property integer $end_at
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property FwExternalSystem $fwExternalSystem
 */
class FwExternalSystemValue extends BaseActiveRecord
{
    const OBJECT_TYPE_USER = "0";
    const OBJECT_TYPE_ORGNIZATION = "1";
    const OBJECT_TYPE_DOMAIN = "2";
    const OBJECT_TYPE_COMPANY = "3";
    const OBJECT_TYPE_POSITION = "4";

    const VALUE_TYPE_ACCESS_TOKEN = "access_token";
    const VALUE_TYPE_USER_KEY = "user_key";
    const VALUE_TYPE_ORGNIZATION_KEY = "orgnization_key";
    const VALUE_TYPE_DOMAIN_KEY = "domian_key";
    const VALUE_TYPE_POSITION_KEY = "position_key";
    const VALUE_TYPE_USER_WECHAT_OPENID = "user_wechat_openid";
    const VALUE_TYPE_USER_WECHAT_UNIONID = "user_wechat_unionid";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_external_system_value}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['system_id', 'object_id', 'value', 'start_at'], 'required'],
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'system_id', 'object_id', 'value_type', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['value'], 'string', 'max' => 200],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['object_type'], 'string', 'max' => 1],
            [['object_type'], 'in', 'range' => [self::OBJECT_TYPE_USER,self::OBJECT_TYPE_ORGNIZATION,self::OBJECT_TYPE_DOMAIN,self::OBJECT_TYPE_POSITION]],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

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
            'kid' => Yii::t('common', 'kid'),
            'system_id' => Yii::t('common', 'system_id'),
            'object_id' => Yii::t('common', 'object_id'),
            'object_type' => Yii::t('common', 'object_type'),
            'value' => Yii::t('common', 'value'),
            'value_type' => Yii::t('common', 'value_type'),
            'status' => Yii::t('common', 'status'),
            'start_at' => Yii::t('common', 'start_at'),
            'end_at' => Yii::t('common', 'end_at'),
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
    public function getFwExternalSystem()
    {
        return $this->hasOne(FwExternalSystem::className(), ['kid' => 'system_id'])
            ->onCondition([FwExternalSystem::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
