<?php

namespace common\models\learning;

use Yii;
use \common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ln_course_mods}}".
 *
 * @property string $kid
 * @property string $mod_name
 * @property string $course_id
 * @property string $mod_desc
 * @property integer $mod_num
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
 * @property LnCourseQuestion[] $lnCourseQuestions
 * @property LnModRes[] $lnModRes
 */
class LnCourseMods extends BaseActiveRecord
{
    public $courseitems;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_course_mods}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mod_name', 'course_id'], 'required'],
            [['mod_desc'], 'string'],
            [['mod_num', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'mod_name', 'course_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
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
            'mod_name' => Yii::t('common', 'mod_name'),
            'course_id' => Yii::t('common', 'course_id'),
            'mod_desc' => Yii::t('common', 'mod_desc'),
            'mod_num' => Yii::t('common', 'mod_num'),
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
    public function getLnCourseQuestions()
    {
        return $this->hasMany(LnCourseQuestion::className(), ['module_id' => 'kid'])
            ->onCondition([LnCourseQuestion::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnModRes()
    {
        return $this->hasMany(LnModRes::className(), ['mod_id' => 'kid'])
            ->onCondition([LnModRes::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
