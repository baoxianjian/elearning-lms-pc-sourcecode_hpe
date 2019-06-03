<?php

namespace common\models\learning;

use Yii;
use \common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ln_course_reg}}".
 *
 * @property string $kid
 * @property string $course_id
 * @property string $user_id
 * @property string $sponsor_id
 * @property integer $reg_time
 * @property string $reg_type
 * @property string $reg_state
 * @property integer $disable_time
 * @property string $approved_by
 * @property string $approver_at
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnCourse $lnCourse
 */
class LnCourseReg extends BaseActiveRecord
{
    const REG_TYPE_SELF = 'self';
    const REG_TYPE_POS = 'pos';
    const REG_TYPE_ORG = 'org';
    const REG_TYPE_MANAGER = 'manager';

    const REG_STATE_APPLING = '0';
    const REG_STATE_APPROVED = '1';
    const REG_STATE_REJECTED = '2';
    const REG_STATE_CANCELED = '3';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_course_reg}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['course_id', 'user_id', 'reg_time', 'reg_type', 'reg_state'], 'required'],
            [['reg_time', 'created_at', 'updated_at','approved_at'], 'integer'],
            [['kid', 'course_id', 'user_id', 'sponsor_id', 'reg_type', 'created_by', 'updated_by','approved_by'], 'string', 'max' => 50],
            [['reg_state', 'is_deleted'], 'string', 'max' => 1],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['reg_state'], 'string', 'max' => 1],
            [['reg_state'], 'in', 'range' => [self::REG_STATE_APPLING, self::REG_STATE_APPROVED, self::REG_STATE_REJECTED, self::REG_STATE_CANCELED]],
            [['reg_state'], 'default', 'value'=> self::REG_STATE_APPLING],

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
            'course_id' => Yii::t('common', 'course_id'),
            'user_id' => Yii::t('common', 'user_id'),
            'sponsor_id' => Yii::t('common', 'sponsor_id'),
            'reg_time' => Yii::t('common', 'reg_time'),
            'reg_type' => Yii::t('common', 'reg_type'),
            'reg_state' => Yii::t('common', 'reg_state'),
            'approved_by' => Yii::t('common', 'approved_by'),
            'approved_at' => Yii::t('common', 'approved_at'),
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
    public function getLnCourse()
    {
        return $this->hasOne(LnCourse::className(), ['kid' => 'course_id'])
            ->onCondition([LnCourse::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourseComplete()
    {
        return $this->hasOne(LnCourseComplete::className(), ['course_reg_id' => 'kid'])
            ->onCondition([
                LnCourseComplete::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO,
                LnCourseComplete::realTableName() . '.complete_type' => LnCourseComplete::COMPLETE_TYPE_FINAL
            ]);
    }
}
