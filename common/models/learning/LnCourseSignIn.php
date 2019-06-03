<?php

namespace common\models\learning;
use Yii;
use \common\base\BaseActiveRecord;
/**
 * This is the model class for table "eln_ln_course_sign_in".
 *
 * @property string $kid
 * @property string $course_id
 * @property string $sign_in_setting_id
 * @property string $user_id
 * @property string $sign_user_id
 * @property integer $sign_time
 * @property string $sign_system
 * @property string $sign_type
 * @property string $sign_flag
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
class LnCourseSignIn extends BaseActiveRecord
{
	//签到方式
	const SIGN_TYPE_SELF = 'self' ;
	const SIGN_TYPE_ADMIN = 'admin' ;
	const SIGN_TYPE_TEACHER = 'teacher' ;

    const SIGN_FLAG_SIGN_IN = 1;  //签到
    const SIGN_FLAG_LEAVE = 2;  //请假
	
	// 签到系统
	const SIGN_SYSTEM_PC = '1' ;
	const SIGN_SYSTEM_APP = '0' ;

    const SIGN_STATUS_NO_SIGN_IN=1; //未签到
    const SIGN_STATUS_SIGNED_IN=2; //已签到
    const SIGN_STATUS_LEFT=3; //已请假



	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'eln_ln_course_sign_in';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'course_id', 'user_id', 'sign_user_id', 'sign_time', 'sign_type','sign_flag'], 'required'],
            [['sign_time', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'course_id', 'user_id', 'sign_user_id', 'sign_type', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['sign_system', 'is_deleted'], 'string', 'max' => 1]
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
            'sign_user_id' => Yii::t('common', 'sign_user_id'),
            'sign_time' => Yii::t('common', 'sign_time'),
            'sign_system' => Yii::t('common', 'sign_system'),
            'sign_type' => Yii::t('common', 'sign_type'),
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

    function getSignInStatuses($id=0)
    {
        $a=array('1'=>Yii::t('frontend', 'signin_not_data'),'2'=>Yii::t('frontend', 'signin_has_data'),'3'=>Yii::t('frontend', 'sign_in_left'));
        if(!$id){return $a;}
        return $a[$id];
    }
}
