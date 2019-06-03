<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_scorm_seq_mapinfo}}".
 *
 * @property string $kid
 * @property string $objective_id
 * @property string $scorm_sco_id
 * @property string $scorm_id
 * @property string $target_objective_id
 * @property string $read_satisfied_status
 * @property string $read_normalized_measure
 * @property string $write_satisfied_status
 * @property string $write_normalized_measure
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnScormSeqObjective $lnScormSeqObjective
 */
class LnScormSeqMapinfo extends BaseActiveRecord
{
    const READ_SATISFIED_STATUS_NO = "0";
    const READ_SATISFIED_STATUS_YES = "1";

    const READ_NORMALIZED_MEASURE_NO = "0";
    const READ_NORMALIZED_MEASURE_YES = "1";

    const WRITE_SATISFIED_STATUS_NO = "0";
    const WRITE_SATISFIED_STATUS_YES = "1";

    const WRITE_NORMALIZED_MEASURE_NO = "0";
    const WRITE_NORMALIZED_MEASURE_YES = "1";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_scorm_seq_mapinfo}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['objective_id', 'scorm_sco_id', 'scorm_id'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'objective_id', 'scorm_sco_id', 'scorm_id', 'target_objective_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['read_satisfied_status', 'read_normalized_measure', 'write_satisfied_status', 'write_normalized_measure'], 'string', 'max' => 1],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['read_satisfied_status'], 'in', 'range' => [self::READ_SATISFIED_STATUS_NO, self::READ_SATISFIED_STATUS_YES]],
            [['read_satisfied_status'], 'default', 'value'=> self::READ_SATISFIED_STATUS_YES],

            [['read_normalized_measure'], 'in', 'range' => [self::READ_NORMALIZED_MEASURE_NO, self::READ_NORMALIZED_MEASURE_YES]],
            [['read_normalized_measure'], 'default', 'value'=> self::READ_NORMALIZED_MEASURE_YES],

            [['write_satisfied_status'], 'in', 'range' => [self::WRITE_SATISFIED_STATUS_NO, self::WRITE_SATISFIED_STATUS_YES]],
            [['write_satisfied_status'], 'default', 'value'=> self::WRITE_SATISFIED_STATUS_NO],

            [['write_normalized_measure'], 'in', 'range' => [self::WRITE_NORMALIZED_MEASURE_NO, self::WRITE_NORMALIZED_MEASURE_YES]],
            [['write_normalized_measure'], 'default', 'value'=> self::WRITE_NORMALIZED_MEASURE_NO],

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
            'objective_id' => Yii::t('common', 'objective_id'),
            'scorm_sco_id' => Yii::t('common', 'scorm_sco_id'),
            'scorm_id' => Yii::t('common', 'scorm_id'),
            'target_objective_id' => Yii::t('common', 'target_objective_id'),
            'read_satisfied_status' => Yii::t('common', 'read_satisfied_status'),
            'read_normalized_measure' => Yii::t('common', 'read_normalized_measure'),
            'write_satisfied_status' => Yii::t('common', 'write_satisfied_status'),
            'write_normalized_measure' => Yii::t('common', 'write_normalized_measure'),
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
    public function getLnScormSeqObjective()
    {
        return $this->hasOne(LnScormSeqObjective::className(), ['kid' => 'objective_id'])
            ->onCondition([LnScormSeqObjective::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
