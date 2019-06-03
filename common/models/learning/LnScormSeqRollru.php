<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_scorm_seq_rollru}}".
 *
 * @property string $kid
 * @property string $scorm_sco_id
 * @property string $scorm_id
 * @property string $child_activity_set
 * @property integer $minimum_count
 * @property double $minimum_percent
 * @property string $condition_combination
 * @property string $action
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnScormScoes $lnScormScoes
 * @property LnScormSeqRollrucond[] $lnScormSeqRollruconds
 */
class LnScormSeqRollru extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_scorm_seq_rollru}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scorm_sco_id', 'scorm_id', 'child_activity_set', 'action'], 'required'],
            [['minimum_count', 'created_at', 'updated_at'], 'integer'],
            [['minimum_percent'], 'number'],
            [['kid', 'scorm_sco_id', 'scorm_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['child_activity_set'], 'string', 'max' => 15],
            [['condition_combination'], 'string', 'max' => 3],
            [['action'], 'string', 'max' => 25],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['minimum_count'], 'default', 'value'=> 0],
            [['minimum_percent'], 'default', 'value'=> 0.000],
            [['condition_combination'], 'default', 'value'=> 'all'],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

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
            'scorm_sco_id' => Yii::t('common', 'scorm_sco_id'),
            'scorm_id' => Yii::t('common', 'scorm_id'),
            'child_activity_set' => Yii::t('common', 'child_activity_set'),
            'minimum_count' => Yii::t('common', 'minimum_count'),
            'minimum_percent' => Yii::t('common', 'minimum_percent'),
            'condition_combination' => Yii::t('common', 'condition_combination'),
            'action' => Yii::t('common', 'action'),
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
    public function getLnScormScoes()
    {
        return $this->hasOne(LnScormScoes::className(), ['kid' => 'scorm_sco_id'])
            ->onCondition([LnScormScoes::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnScormSeqRollruconds()
    {
        return $this->hasMany(LnScormSeqRollrucond::className(), ['rollup_rule_id' => 'kid'])
            ->onCondition([LnScormSeqRollrucond::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
