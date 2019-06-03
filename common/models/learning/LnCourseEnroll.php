<?php

namespace common\models\learning;

use Yii;
use \common\base\BaseActiveRecord;
use common\models\framework\FwUser;

/**
 * This is the model class for table "{{%ln_course_enroll}}".
 *
 * @property string $kid
 * @property string $course_id
 * @property string $user_id
 * @property string $enroll_type
 * @property integer $enroll_user_id
 * @property string $enroll_time
 * @property string $enroll_method
 * @property string $approved_state
 * @property string $approved_by
 * @property string $approved_at
 * @property string $approved_reason
 * @property string $cancel_state
 * @property string $cancel_by
 * @property string $cancel_at
 * @property string $cancel_reason
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
class LnCourseEnroll extends BaseActiveRecord
{
    const ENROLL_METHOD_SELF = 'self';
    const ENROLL_METHOD_ADMIN = 'admin';
    const ENROLL_METHOD_MANAGER = 'manager';
    const ENROLL_METHOD_MANUAL = 'manual';

    const ENROLL_TYPE_REG = '0'; /*注册*/
    const ENROLL_TYPE_ALLOW = '1'; /*成功*/
    const ENROLL_TYPE_ALTERNATE = '2'; /*候补*/
    const ENROLL_TYPE_DISALLOW = '3'; /*拒绝*/

    const APPROVED_STATE_APPLING = '0'; /*申请中*/
    const APPROVED_STATE_APPROVED = '1'; /*审批同意*/
    const APPROVED_STATE_REJECTED = '2'; /*审批不同意*/
    const APPROVED_STATE_CANCELED = '3'; /*作废*/

    const CANCEL_STATE_APPLING = '0'; /*申请中*/
    const CANCEL_STATE_APPROVED = '1'; /*审批同意*/
    const CANCEL_STATE_REJECTED = '2'; /*审批不同意*/
    const CANCEL_STATE_CANCELED = '3'; /*作废*/

    public $real_name;
    public $orgnization_name;
    public $sign_time;
    public $location;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_course_enroll}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['course_id', 'user_id', 'enroll_time', 'enroll_method', 'enroll_type', 'enroll_type'], 'required'],
            [['enroll_time', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'course_id', 'user_id', 'enroll_user_id', 'enroll_method', 'approved_by', 'cancel_by', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from', 'updated_from'], 'string', 'max' => 50],

            [['enroll_type'], 'string', 'max' => 1],
            [['enroll_type'], 'in', 'range' => [self::ENROLL_TYPE_REG, self::ENROLL_TYPE_ALLOW, self::ENROLL_TYPE_ALTERNATE, self::ENROLL_TYPE_DISALLOW]],
            [['enroll_type'], 'default', 'value' => self::ENROLL_TYPE_REG],

            [['approved_state'], 'string', 'max' => 1],
            [['approved_state'], 'in', 'range' => [self::APPROVED_STATE_APPLING, self::APPROVED_STATE_APPROVED, self::APPROVED_STATE_REJECTED, self::APPROVED_STATE_CANCELED]],
            [['approved_state'], 'default', 'value' => self::APPROVED_STATE_APPROVED],

            [['cancel_state'], 'string', 'max' => 1],
            [['cancel_state'], 'in', 'range' => [self::CANCEL_STATE_APPLING, self::CANCEL_STATE_APPROVED, self::CANCEL_STATE_REJECTED, self::CANCEL_STATE_CANCELED]],

            [['approved_reason', 'cancel_reason'], 'string'],

            [['version'], 'number'],
            [['version'], 'default', 'value' => 1],

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
            'enroll_type' => Yii::t('common', 'enroll_type'),
            'enroll_user_id' => Yii::t('common', 'enroll_user_id'),
            'enroll_time' => Yii::t('common', 'enroll_time'),
            'enroll_method' => Yii::t('common', 'enroll_method'),
            'approved_state' => Yii::t('common', 'approved_state'),
            'approved_by' => Yii::t('common', 'approved_by'),
            'approved_at' => Yii::t('common', 'approved_at'),
            'approved_reason' => Yii::t('common', 'approved_reason'),
            'cancel_state' => Yii::t('common', 'cancel_state'),
            'cancel_by' => Yii::t('common', 'cancel_by'),
            'cancel_at' => Yii::t('common', 'cancel_at'),
            'cancel_reason' => Yii::t('common', 'cancel_reason'),
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
}
