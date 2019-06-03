<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_scorm_seq_rulecond}}".
 *
 * @property string $kid
 * @property string $rule_conds_id
 * @property string $scorm_sco_id
 * @property string $scorm_id
 * @property string $referenced_objective
 * @property double $measure_threshold
 * @property string $operator
 * @property string $cond
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnScormSeqRuleconds $lnScormSeqRuleconds
 */
class LnScormSeqRulecond extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_scorm_seq_rulecond}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rule_conds_id', 'scorm_sco_id', 'scorm_id'], 'required'],
            [['measure_threshold'], 'number'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'rule_conds_id', 'scorm_sco_id', 'scorm_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['referenced_objective'], 'string', 'max' => 255],
            [['operator'], 'string', 'max' => 5],
            [['cond'], 'string', 'max' => 30],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['measure_threshold'], 'default', 'value'=> 0.000],
            [['operator'], 'default', 'value'=> 'noOp'],
            [['cond'], 'default', 'value'=> 'always'],

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
            'rule_conds_id' => Yii::t('common', 'rule_conds_id'),
            'scorm_sco_id' => Yii::t('common', 'scorm_sco_id'),
            'scorm_id' => Yii::t('common', 'scorm_id'),
            'referenced_objective' => Yii::t('common', 'referenced_objective'),
            'measure_threshold' => Yii::t('common', 'measure_threshold'),
            'operator' => Yii::t('common', 'operator'),
            'cond' => Yii::t('common', 'cond'),
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
    public function getLnScormSeqRuleconds()
    {
        return $this->hasOne(LnScormSeqRuleconds::className(), ['kid' => 'rule_conds_id'])
            ->onCondition([LnScormSeqRuleconds::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
