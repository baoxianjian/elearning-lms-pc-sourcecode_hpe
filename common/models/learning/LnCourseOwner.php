<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;
use \common\models\framework\FwUser;

/**
 * This is the model class for table "{{%ln_course_owner}}".
 *
 * @property string $kid
 * @property string $course_id
 * @property string $user_id
 * @property string $owner_type
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
class LnCourseOwner extends BaseActiveRecord
{

    const OWNER_TYPE_ALL = '0';//完全所有者
    const OWNER_TYPE_TEACHER = '1';//讲师
    const OWNER_TYPE_HELPTEACHER = '2';//助教

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_course_owner}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['course_id', 'user_id'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'course_id', 'user_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['owner_type'], 'string', 'max' => 1],
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
            'user_id' => Yii::t('common', 'user_id'),
            'owner_type' => Yii::t('common', 'owner_type'),
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
    public function getFwUser()
    {
        return $this->hasOne(FwUser::className(), ['kid' => 'user_id'])
            ->onCondition([FwUser::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * 添加课程讲师关系表
     */
    public function addRelationship(LnCourse $course, $user_id, $owner_type){
        $find = $this->findOne(['course_id'=>$course->kid, 'user_id'=>$user_id, 'owner_type' => $owner_type],false);
        if (!$find){
            $model = new LnCourseOwner();
            $model->course_id = $course->kid;
            $model->user_id = $user_id;
            $model->owner_type = $owner_type;
            $model->save();
        }
    }
}
