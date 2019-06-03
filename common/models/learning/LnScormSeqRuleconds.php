<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_scorm_seq_ruleconds}}".
 *
 * @property string $kid
 * @property string $scorm_sco_id
 * @property string $scorm_id
 * @property string $condition_combination
 * @property string $rule_type
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
 * @property LnScormSeqRulecond[] $lnScormSeqRuleconds
 * @property LnScormScoes $lnScormScoes
 */
class LnScormSeqRuleconds extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_scorm_seq_ruleconds}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scorm_sco_id', 'scorm_id', 'action'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'scorm_sco_id', 'scorm_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['condition_combination'], 'string', 'max' => 3],
            [['rule_type'], 'string', 'max' => 2],
            [['action'], 'string', 'max' => 25],
            [['created_from','updated_from'], 'string', 'max' => 50],

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
            'condition_combination' => Yii::t('common', 'condition_combination'),
            'rule_type' => Yii::t('common', 'rule_type'),
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
    public function getLnScormSeqRuleconds()
    {
        return $this->hasMany(LnScormSeqRulecond::className(), ['rule_conds_id' => 'kid'])
            ->onCondition([LnScormSeqRulecond::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnScormScoes()
    {
        return $this->hasOne(LnScormScoes::className(), ['kid' => 'scorm_sco_id'])
            ->onCondition([LnScormScoes::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
