<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_dictionary_category}}".
 *
 * @property string $kid
 * @property string $cate_code
 * @property string $cate_name
 * @property string $limitation
 * @property string $cate_type
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
 * @property FwDictionary[] $fwDictionaries
 */
class FwDictionaryCategory extends BaseActiveRecord
{
    const CATE_TYPE_SYSTEM = "0";
    const CATE_TYPE_COMPANY = "1";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_dictionary_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cate_code', 'cate_name', 'sequence_number'], 'required', 'on' => 'manage'],
            [['sequence_number', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['cate_code', 'cate_name'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['cate_type'], 'string', 'max' => 1],
            [['cate_type'], 'in', 'range' => [self::CATE_TYPE_SYSTEM, self::CATE_TYPE_COMPANY]],

            [['sequence_number'], 'integer', 'min' => 1, 'max'=> 2147483647],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]],

            [['limitation'], 'string', 'max' => 1],
            [['limitation'], 'in', 'range' => [self::LIMITATION_NONE, self::LIMITATION_READONLY,
                self::LIMITATION_HIDDEN, self::LIMITATION_ONLYNAME]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'dictionary_category_id'),
            'cate_code' => Yii::t('common', 'dictionary_cate_code'),
            'cate_name' => Yii::t('common', 'dictionary_cate_name'),
            'cate_type' => Yii::t('common', 'dictionary_cate_type'),
            'limitation' => Yii::t('common', 'limitation'),
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

    /**
     * 获取限制文字
     * @return string
     */
    public function getCateTypeText()
    {
        if (isset($this->cate_type)) {
            $cateType = $this->cate_type;
            if ($cateType == self::CATE_TYPE_SYSTEM)
                return Yii::t('common', 'dictionary_cate_type_system');
            else if ($cateType == self::CATE_TYPE_COMPANY)
                return Yii::t('common', 'dictionary_cate_type_company');
        } else {
            return "";
        }
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwDictionaries()
    {
        return $this->hasMany(FwDictionary::className(), ['dictionary_category_id' => 'kid'])
            ->onCondition([FwDictionary::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
