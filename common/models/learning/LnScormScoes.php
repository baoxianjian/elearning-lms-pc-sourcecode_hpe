<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_scorm_scoes}}".
 *
 * @property string $kid
 * @property string $scorm_id
 * @property string $title
 * @property string $manifest
 * @property string $organization
 * @property string $parent
 * @property string $identifier
 * @property string $launch
 * @property string $scorm_type
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
 * @property LnCoursewareScorm $lnCoursewareScorm
 * @property LnScormScoesData[] $lnScormScoesDatas
 * @property LnScormScoesTrack[] $lnScormScoesTracks
 * @property LnScormSeqObjective[] $lnScormSeqObjectives
 * @property LnScormSeqRollru[] $lnScormSeqRollrus
 * @property LnScormSeqRuleconds[] $lnScormSeqRuleconds
 */
class LnScormScoes extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_scorm_scoes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scorm_id',  'sequence_number'], 'required'],
            [['sequence_number', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'scorm_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['title', 'manifest', 'organization', 'parent', 'identifier', 'launch', 'scorm_type'], 'string', 'max' => 255],
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
            'scorm_id' => Yii::t('common', 'scorm_id'),
            'title' => Yii::t('common', 'title'),
            'manifest' => Yii::t('common', 'manifest'),
            'organization' => Yii::t('common', 'organization'),
            'parent' => Yii::t('common', 'parent'),
            'identifier' => Yii::t('common', 'identifier'),
            'launch' => Yii::t('common', 'launch'),
            'scorm_type' => Yii::t('common', 'scorm_type'),
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
    public function getLnCoursewareScorm()
    {
        return $this->hasOne(LnCoursewareScorm::className(), ['kid' => 'scorm_id'])
            ->onCondition([LnCoursewareScorm::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnScormScoesDatas()
    {
        return $this->hasMany(LnScormScoesData::className(), ['scorm_sco_id' => 'kid'])
            ->onCondition([LnScormScoesData::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnScormScoesTracks()
    {
        return $this->hasMany(LnScormScoesTrack::className(), ['scorm_sco_id' => 'kid'])
            ->onCondition([LnScormScoesTrack::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnScormSeqObjectives()
    {
        return $this->hasMany(LnScormSeqObjective::className(), ['scorm_sco_id' => 'kid'])
            ->onCondition([LnScormSeqObjective::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnScormSeqRollrus()
    {
        return $this->hasMany(LnScormSeqRollru::className(), ['scorm_sco_id' => 'kid'])
            ->onCondition([LnScormSeqRollru::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnScormSeqRuleconds()
    {
        return $this->hasMany(LnScormSeqRuleconds::className(), ['scorm_sco_id' => 'kid'])
            ->onCondition([LnScormSeqRuleconds::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
