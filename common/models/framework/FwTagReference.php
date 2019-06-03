<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_tag_reference}}".
 *
 * @property string $kid
 * @property string $tag_id
 * @property string $tag_category_id
 * @property string $tag_value
 * @property string $subject_id
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
 * @property FwTag $fwTag
 */
class FwTagReference extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_tag_reference}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'tag_category_id', 'tag_value', 'subject_id', 'start_at'], 'required'],
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'tag_id', 'tag_category_id', 'subject_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['tag_value'], 'string', 'max' => 500],
            [['created_from', 'updated_from'], 'string', 'max' => 50],

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
            'kid' => Yii::t('common', 'kid'),
            'tag_id' => Yii::t('common', 'tag_id'),
            'tag_category_id' => Yii::t('common', 'tag_category_id'),
            'tag_value' => Yii::t('common', 'tag_value'),
            'subject_id' => Yii::t('common', 'subject_id'),
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
    public function getFwTag()
    {
        return $this->hasOne(FwTag::className(), ['kid' => 'tag_id'])
            ->onCondition([FwTag::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
