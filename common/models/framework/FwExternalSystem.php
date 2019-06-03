<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_external_system}}".
 *
 * @property string $kid
 * @property string $system_code
 * @property string $system_name
 * @property string $system_key
 * @property string $system_key_is_single
 * @property string $encoding_key
 * @property string $security_mode
 * @property string $encrypt_mode
 * @property string $api_address
 * @property string $service_mode
 * @property string $token_expire
 * @property string $duration
 * @property string $limit_count
 * @property string $memo1
 * @property string $memo2
 * @property string $status
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property FwCompanySystem[] $fwCompanySystems
 * @property FwExternalSystemValue[] $fwExternalSystemValues
 */
class FwExternalSystem extends BaseActiveRecord
{
    const SECURITY_MODE_PLAIN = "0";
    const SECURITY_MODE_ENCRYPT = "1";

    const ENCRYPT_MODE_NONE = "0";
    const ENCRYPT_MODE_AES = "1";
    const ENCRYPT_MODE_DES = "2";

    const SERVICE_MODE_SERVER = "0";
    const SERVICE_MODE_CLIENT = "1";
    const SERVICE_MODE_BOTH = "2";


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_external_system}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['system_code', 'system_name', 'system_key','duration','limit_count'], 'required', 'on' => 'manage'],
            [['duration', 'limit_count', 'created_at', 'updated_at','token_expire'], 'integer'],
            [['kid', 'system_code', 'system_name', 'system_key', 'encoding_key', 'memo1', 'memo2', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['api_address'], 'string', 'max' => 255],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['security_mode'], 'string', 'max' => 1],
            [['security_mode'], 'in', 'range' => [self::SECURITY_MODE_PLAIN, self::SECURITY_MODE_ENCRYPT]],

            [['encrypt_mode'], 'string', 'max' => 1],
            [['encrypt_mode'], 'in', 'range' => [self::ENCRYPT_MODE_NONE, self::ENCRYPT_MODE_AES, self::ENCRYPT_MODE_DES]],

            [['service_mode'], 'string', 'max' => 1],
            [['service_mode'], 'in', 'range' => [self::SERVICE_MODE_SERVER, self::SERVICE_MODE_CLIENT, self::SERVICE_MODE_BOTH]],


            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

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
            'system_code' => Yii::t('common', 'system_code'),
            'system_name' => Yii::t('common', 'system_name'),
            'system_key' => Yii::t('common', 'system_key'),
            'system_key_is_single' => Yii::t('common', 'system_key_is_single'),
            'encoding_key' => Yii::t('common', 'encoding_key'),
            'security_mode' => Yii::t('common', 'security_mode'),
            'encrypt_mode' => Yii::t('common', 'encrypt_mode'),
            'api_address' => Yii::t('common', 'api_address'),
            'service_mode' => Yii::t('common', 'service_mode'),
            'duration' => Yii::t('common', 'duration'),
            'token_expire' => Yii::t('common', 'token_expire'),
            'limit_count' => Yii::t('common', 'limit_count'),
            'user_name' => Yii::t('common', 'user_name'),
            'password' => Yii::t('common', 'password'),
            'memo1' => Yii::t('common', 'memo1'),
            'memo2' => Yii::t('common', 'memo2'),
            'memo3' => Yii::t('common', 'memo3'),
            'status' => Yii::t('common', 'status'),
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

    public function getSystemKeyIsSingleText()
    {
        $single = $this->system_key_is_single;
        if ($single == self::NO)
            return Yii::t('common', 'no');
        else
            return Yii::t('common', 'yes');
    }

    public function getSecurityModeText()
    {
        $securityMode = $this->security_mode;
        if ($securityMode == self::SECURITY_MODE_PLAIN)
            return Yii::t('common', 'security_mode_plain');
        else
            return Yii::t('common', 'security_mode_encrypt');
    }

    public function getEncryptModeText()
    {
        $encryptMode = $this->encrypt_mode;
        if ($encryptMode == self::ENCRYPT_MODE_NONE)
            return Yii::t('common', 'encrypt_mode_none');
        else  if ($encryptMode == self::ENCRYPT_MODE_AES)
            return Yii::t('common', 'encrypt_mode_aes');
        else
            return Yii::t('common', 'encrypt_mode_des');
    }

    public function getServiceModeText()
    {
        $serviceMode = $this->service_mode;
        if ($serviceMode == self::SERVICE_MODE_SERVER)
            return Yii::t('common', 'service_mode_server');
        else  if ($serviceMode == self::SERVICE_MODE_CLIENT)
            return Yii::t('common', 'service_mode_client');
        else
            return Yii::t('common', 'service_mode_both');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwCompanySystems()
    {
        return $this->hasMany(FwCompanySystem::className(), ['system_id' => 'kid'])
            ->onCondition([FwCompanySystem::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwExternalSystemValues()
    {
        return $this->hasMany(FwExternalSystemValue::className(), ['system_id' => 'kid'])
            ->onCondition([FwExternalSystemValue::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
