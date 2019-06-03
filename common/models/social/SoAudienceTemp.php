<?php

namespace common\models\social;

use Yii;
use common\base\BaseActiveRecord;
/**
 * This is the model class for table "{{%so_audience_temp}}".
 *
 * @property string $kid
 * @property string $audience_batch
 * @property string $company_id
 * @property string $owner_id
 * @property string $user_id
 * @property string $user_name
 * @property string $real_name
 * @property string $orgnization
 * @property string $position
 * @property string $email
 * @property string $mobile_no
 * @property string $status
 * @property integer $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $created_ip
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $updated_ip
 * @property string $is_deleted
 */
class SoAudienceTemp extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%so_audience_temp}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['audience_batch', 'company_id', 'owner_id', 'user_id','user_name'], 'required'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['audience_batch', 'company_id', 'owner_id', 'user_id', 'user_name', 'real_name', 'orgnization', 'position', 'mobile_no', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 255],
            [['status', 'is_deleted'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'kid'),
            'audience_batch' => Yii::t('common', 'audience_batch'),
            'company_id' => Yii::t('common', 'company_id'),
            'owner_id' => Yii::t('common', 'owner_id'),
            'user_id' => Yii::t('common', 'user_id'),
            'user_name' => Yii::t('common', 'user_name'),
            'real_name' => Yii::t('common', 'real_name'),
            'orgnization' => Yii::t('common', 'orgnization'),
            'position' => Yii::t('common', 'position'),
            'email' => Yii::t('common', 'email'),
            'mobile_no' => Yii::t('common', 'mobile_no'),
            'status' => Yii::t('common', 'status'),
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'created_ip' => Yii::t('common', 'created_ip'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'updated_ip' => Yii::t('common', 'updated_ip'),
            'is_deleted' => Yii::t('common', 'is_deleted'),
        ];
    }
}
