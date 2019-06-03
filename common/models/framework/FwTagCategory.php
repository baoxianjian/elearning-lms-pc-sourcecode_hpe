<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_tag_category}}".
 *
 * @property string $kid
 * @property string $cate_code
 * @property string $cate_name
 * @property string $limitation
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
 * @property FwTag[] $fwTags
 */
class FwTagCategory extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_tag_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cate_code', 'cate_name', 'sequence_number'], 'required', 'on' => 'manage'],
            [['sequence_number', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'cate_code', 'cate_name', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

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
            'kid' => Yii::t('common', 'tag_category_id'),
            'cate_code' => Yii::t('common', 'tag_cate_code'),
            'cate_name' => Yii::t('common', 'tag_cate_name'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getFwTags()
    {
        return $this->hasMany(FwTag::className(), ['tag_category_id' => 'kid'])
            ->onCondition([FwTag::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }


}
