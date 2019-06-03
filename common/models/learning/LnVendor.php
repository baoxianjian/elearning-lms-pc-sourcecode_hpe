<?php

namespace common\models\learning;
use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "eln_ln_vendor".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $vendor_name
 * @property string $vendor_code
 * @property string $description
 * @property string $version
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
class LnVendor extends BaseActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'eln_ln_vendor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'vendor_name'], 'required'],
            [['description'], 'string'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'company_id', 'vendor_name','vendor_code', 'created_by', 'created_ip', 'updated_by', 'updated_ip'], 'string', 'max' => 50],

            [['created_from','updated_from'], 'string', 'max' => 50],

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
            'vendor_name' => Yii::t('common', 'address_name'),
            'description' => Yii::t('common', 'description'),
            'status' => Yii::t('common', 'status'),
            'version' => Yii::t('common', 'version'),
            'vendor_code' => Yii::t('common', 'address_code'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'created_ip' => Yii::t('common', 'created_ip'),
            'updated_ip' => Yii::t('common', 'updated_ip'),
            'is_deleted' => Yii::t('common', 'is_deleted'),
        ];
    }
}
