<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_dictionary}}".
 *
 * @property string $kid
 * @property string $dictionary_category_id
 * @property string $parent_dictionary_id
 * @property string $company_id
 * @property string $dictionary_code
 * @property string $dictionary_name
 * @property string $dictionary_value
 * @property string $i18n_flag
 * @property string $status
 * @property string $description
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
 * @property FwDictionaryCategory $fwDictionaryCategory
 */
class FwDictionary extends BaseActiveRecord
{
    public $dictionary_name_i18n;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_dictionary}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dictionary_category_id', 'dictionary_code', 'dictionary_name', 'dictionary_value', 'sequence_number'], 'required', 'on' => 'manage'],
            [['sequence_number', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'parent_dictionary_id', 'dictionary_category_id', 'i18n_flag', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['dictionary_code', 'dictionary_name', 'company_id'], 'string', 'max' => 50],
            [['dictionary_value'], 'string', 'max' => 500],
            [['description'], 'string'],
            [['created_from', 'updated_from'], 'string', 'max' => 50],

            [['sequence_number'], 'integer', 'min' => 1, 'max' => 2147483647],

            [['version'], 'number'],
            [['version'], 'default', 'value' => 1],

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
            'kid' => Yii::t('common', 'dictionary_id'),
            'dictionary_category_id' => Yii::t('common', 'dictionary_category_id'),
            'parent_dictionary_id' => Yii::t('common', 'parent_dictionary_id'),
            'dictionary_code' => Yii::t('common', 'dictionary_code'),
            'dictionary_name' => Yii::t('common', 'dictionary_name'),
            'dictionary_value' => Yii::t('common', 'dictionary_value'),
            'i18n_flag' => Yii::t('common', 'i18n_flag'),
            'company_id' => Yii::t('common', 'company_id'),
            'status' => Yii::t('common', 'status'),
            'description' => Yii::t('common', 'description'),
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

    public function attributeHints()
    {
        return [

        ];
    }

    public function getDictionaryCategoryName()
    {
        $dictionaryCategoryModel = FwDictionaryCategory::findOne($this->dictionary_category_id);

        if ($dictionaryCategoryModel != null)
            return $dictionaryCategoryModel->cate_name;
        else
            return "";
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwDictionaryCategory()
    {
        return $this->hasOne(FwDictionaryCategory::className(), ['kid' => 'dictionary_category_id'])
            ->onCondition([FwDictionaryCategory::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwCompany()
    {
        return $this->hasOne(FwCompany::className(), ['kid' => 'company_id'])
            ->onCondition([FwCompany::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    public function getCompanyName()
    {
        $companyModel = FwCompany::findOne($this->company_id);

        if ($companyModel != null)
            return $companyModel->company_name;
        else
            return "";
    }

    public function getFwDomainWorkplace()
    {
        return $this->hasMany(FwDomainWorkplace::className(), ['workplace_id' => 'kid'])
            ->onCondition([FwDomainWorkplace::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO,
                FwDomainWorkplace::realTableName() . '.status' => self::STATUS_FLAG_NORMAL]);
    }
}
