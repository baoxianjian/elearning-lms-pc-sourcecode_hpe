<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_scorm_seq_objective}}".
 *
 * @property string $kid
 * @property string $scorm_sco_id
 * @property string $scorm_id
 * @property string $primary_obj
 * @property string $objective_id
 * @property string $satisfied_by_measure
 * @property double $min_normalized_measure
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnScormSeqMapinfo[] $lnScormSeqMapinfos
 * @property LnScormScoes $lnScormScoes
 */
class LnScormSeqObjective extends BaseActiveRecord
{
    const PRIMARY_OBJ_NO = "0";
    const PRIMARY_OBJ_YES = "1";

    const SATISFIED_BY_MEASURE_NO = "0";
    const SATISFIED_BY_MEASURE_YES = "1";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_scorm_seq_objective}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scorm_sco_id', 'scorm_id'], 'required'],
            [['min_normalized_measure'], 'number'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'scorm_sco_id', 'scorm_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['primary_obj', 'satisfied_by_measure'], 'string', 'max' => 1],
            [['objective_id'], 'string', 'max' => 255],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['primary_obj'], 'in', 'range' => [self::PRIMARY_OBJ_NO, self::PRIMARY_OBJ_YES]],
            [['primary_obj'], 'default', 'value'=> self::PRIMARY_OBJ_NO],

            [['satisfied_by_measure'], 'in', 'range' => [self::SATISFIED_BY_MEASURE_NO, self::SATISFIED_BY_MEASURE_YES]],
            [['satisfied_by_measure'], 'default', 'value'=> self::SATISFIED_BY_MEASURE_YES],

            [['min_normalized_measure'], 'default', 'value'=> 0.000],

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
            'primary_obj' => Yii::t('common', 'primary_obj'),
            'objective_id' => Yii::t('common', 'objective_id'),
            'satisfied_by_measure' => Yii::t('common', 'satisfied_by_measure'),
            'min_normalized_measure' => Yii::t('common', 'min_normalized_measure'),
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
    public function getLnScormSeqMapinfos()
    {
        return $this->hasMany(LnScormSeqMapinfo::className(), ['objective_id' => 'kid'])
            ->onCondition([LnScormSeqMapinfo::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
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
