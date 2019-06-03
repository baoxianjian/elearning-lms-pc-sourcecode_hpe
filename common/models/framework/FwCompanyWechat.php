<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_company_wechat}}".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $mp_type
 * @property string $is_authenticated
 * @property string $mp_name
 * @property string $original_id
 * @property string $wechat_name
 * @property string $app_id
 * @property string $app_secret
 * @property string $action_token
 * @property string $server_url
 * @property string $encoding_aes_key
 * @property string $security_mode
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
 * @property FwCompany $fwCompany
 */
class FwCompanyWechat extends BaseActiveRecord
{
    const SECURITY_MODE_PLAIN = "0";
    const SECURITY_MODE_COMPATIBLE = "1";
    const SECURITY_MODE_ENCRYPT = "2";

    const MP_TYPE_SUBSCRIBE = "0";
    const MP_TYPE_SERVICE = "1";
    const MP_TYPE_COMPANY = "2";

    public $server_url;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_company_wechat}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'mp_name', 'original_id', 'action_token','app_id','app_secret'], 'required'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'company_id', 'mp_name', 'original_id', 'wechat_name',
                'encoding_aes_key','app_id','app_secret'], 'string', 'max' => 50],

            [['action_token'], 'string', 'min' => 3, 'max' => 32],

            [['mp_type'], 'string', 'max' => 1],
            [['mp_type'], 'in', 'range' => [self::MP_TYPE_SUBSCRIBE, self::MP_TYPE_SERVICE, self::MP_TYPE_COMPANY]],

            [['security_mode'], 'string', 'max' => 1],
            [['security_mode'], 'in', 'range' => [self::SECURITY_MODE_PLAIN, self::SECURITY_MODE_COMPATIBLE, self::SECURITY_MODE_ENCRYPT]],

            [['is_authenticated'], 'string', 'max' => 1],
            [['is_authenticated'], 'in', 'range' => [self::NO, self::YES]],

            [['created_by','created_from', 'updated_by', 'updated_from'], 'string', 'max' => 50],

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
            'company_id' => Yii::t('common', 'company_id'),
            'mp_type' => Yii::t('common', 'mp_type'),
            'is_authenticated' => Yii::t('common', 'is_authenticated'),
            'mp_name' => Yii::t('common', 'mp_name'),
            'original_id' => Yii::t('common', 'original_id'),
            'wechat_name' => Yii::t('common', 'wechat_name'),
            'app_id' => Yii::t('common', 'app_id'),
            'app_secret' => Yii::t('common', 'app_secret'),
            'action_token' => Yii::t('common', 'action_token'),
            'server_url' => Yii::t('common', 'server_url'),
            'encoding_aes_key' => Yii::t('common', 'encoding_aes_key'),
            'security_mode' => Yii::t('common', 'security_mode'),
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwCompany()
    {
        return $this->hasOne(FwCompany::className(), ['kid' => 'company_id'])
            ->onCondition([FwCompany::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }


    /**
     * 公众号类型
     * @return string
     */
    public function getMpTypeText()
    {
        if (isset($this->mp_type)) {
            $mpType = $this->mp_type;
            if ($mpType == self::MP_TYPE_COMPANY)
                return Yii::t('common', 'mp_type_subscribe');
            else if ($mpType == self::MP_TYPE_SERVICE)
                return Yii::t('common', 'mp_type_service');
            else if ($mpType == self::MP_TYPE_COMPANY)
                return Yii::t('common', 'mp_type_company');
        }
        else {
            return "";
        }
    }

    /**
     * 是否认证
     * @return string
     */
    public function getIsAuthenticatedText()
    {
        if (isset($this->is_authenticated)) {
            $isAuthenticated = $this->is_authenticated;
            if ($isAuthenticated == self::NO)
                return Yii::t('common', 'no');
            else
                return Yii::t('common', 'yes');
        }
        else {
            return Yii::t('common', 'no');
        }
    }

    /**
     * 消息加解密方式
     * @return string
     */
    public function getSecurityModeText()
    {
        if (isset($this->security_mode)) {
            $securityMode = $this->security_mode;
            if ($securityMode == self::SECURITY_MODE_COMPATIBLE)
                return Yii::t('common', 'security_mode_compatible');
            else if ($securityMode == self::SECURITY_MODE_PLAIN)
                return Yii::t('common', 'security_mode_plain');
            else
                return Yii::t('common', 'security_mode_encrypt');
        }
        else {
            return "";
        }
    }
}
