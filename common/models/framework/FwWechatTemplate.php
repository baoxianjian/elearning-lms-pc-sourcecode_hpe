<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_wechat_template}}".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $template_code
 * @property string $template_name
 * @property string $wechat_template_id
 * @property string $wechat_template_id_short
 * @property string $wechat_template_content
 * @property string $description
 * @property string $status
 * @property integer $sequence_number
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
class FwWechatTemplate extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_wechat_template}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_code', 'template_name', 'sequence_number'], 'required', 'on' => 'manage'],
            [['description'], 'string'],
            [['sequence_number', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'company_id', 'template_code', 'template_name', 'wechat_template_id', 'wechat_template_id_short',
                'created_by', 'created_from', 'updated_by', 'updated_from'], 'string', 'max' => 50],
            [['wechat_template_content'], 'string', 'max' => 500],

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
            'template_code' => Yii::t('common', 'template_code'),
            'template_name' => Yii::t('common', 'template_name'),
            'wechat_template_id' => Yii::t('common', 'wechat_template_id'),
            'wechat_template_id_short' => Yii::t('common', 'wechat_template_id_short'),
            'wechat_template_content' => Yii::t('common', 'wechat_template_content'),
            'description' => Yii::t('common', 'description'),
            'status' => Yii::t('common', 'status'),
            'sequence_number' => Yii::t('common', 'sequence_number'),
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

    public function getCompanyName()
    {
        $companyModel = FwCompany::findOne($this->company_id);

        if ($companyModel != null)
            return $companyModel->company_name;
        else
            return "";
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwCompany()
    {
        return $this->hasOne(FwCompany::className(), ['kid' => 'company_id'])
            ->onCondition([FwCompany::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
