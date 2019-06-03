<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_course_mark_summary}}".
 *
 * @property string $kid
 * @property string $course_id
 * @property integer $year
 * @property integer $month
 * @property integer $course_mark
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
class LnCourseMarkSummary extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_course_mark_summary}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['kid', 'course_id', 'year', 'month', 'course_mark', 'created_by', 'created_at'], 'required'],
            [['year', 'month', 'course_mark', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'course_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
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
            'course_id' => Yii::t('common', 'course_id'),
            'year' => Yii::t('common', 'year'),
            'month' => Yii::t('common', 'month'),
            'course_mark' => Yii::t('common', 'course_mark'),
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
        return $this->hasOne(LnCourse::className(), ['kid' => 'course_id']);
    }
}
