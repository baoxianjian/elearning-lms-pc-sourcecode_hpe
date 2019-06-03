<?php

namespace common\models\framework;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%fw_growth}}".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $stage_name
 * @property string $level_name
 * @property integer $sequence_number
 * @property string $require_point
 * @property string $is_template
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
 *
 * @property FwUserPointSummary[] $fwUserPointSummaries
 */
class FwGrowth extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_growth}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['stage_name', 'level_name', 'sequence_number', 'require_point'], 'required'],
            [['sequence_number', 'version', 'created_at', 'updated_at'], 'integer'],
            [['require_point'], 'number'],
            [['description'], 'string'],
            [['company_id', 'stage_name', 'level_name', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['is_template', 'is_deleted'], 'string', 'max' => 1]
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
            'stage_name' => Yii::t('common', 'stage_name'),
            'level_name' => Yii::t('common', 'level_name'),
            'sequence_number' => Yii::t('common', 'sequence_number'),
            'require_point' => Yii::t('common', 'require_point'),
            'is_template' => Yii::t('common', 'is_template'),
            'description' => Yii::t('common', 'description'),
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwUserPointSummaries()
    {
        return $this->hasMany(FwUserPointSummary::className(), ['growth_system_id' => 'kid']);
    }
}
