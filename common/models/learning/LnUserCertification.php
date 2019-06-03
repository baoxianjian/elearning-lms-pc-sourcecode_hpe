<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_user_certification}}".
 *
 * @property string $kid
 * @property string $certification_id
 * @property string $user_id
 * @property string $course_id
 * @property string $certification_from
 * @property string $serial_number
 * @property integer $issued_at
 * @property string $issued_by
 * @property integer $start_at
 * @property integer $end_at
 * @property string $complete_score
 * @property string $complete_grade
 * @property string $certification_name
 * @property string $certification_type
 * @property string $certification_img_url
 * @property string $created_channel
 * @property string $status
 * @property string $valid_status
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnCertification $lnCertification
 */
class LnUserCertification extends BaseActiveRecord
{
    const CERTIFICATION_TYPE_SYSTEM = "0";
    const CERTIFICATION_TYPE_EXTERNAL = "1";

    const CREATED_CHANNEL_MANUAL = "0";
    const CREATED_CHANNEL_COURSE = "1";

    const VALID_STATUS_HISTORY = "0";
    const VALID_STATUS_CURRENT = "1";

    //证书是否颁发
    const IS_ISSUE_YES = '1'; //是
    const IS_ISSUE_NO = '0';//否

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_user_certification}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['certification_id', 'user_id', 'serial_number', 'issued_at', 'issued_by', 'start_at'], 'required'],
            [['issued_at', 'start_at', 'end_at', 'created_at', 'updated_at'], 'integer'],
            [['complete_score', 'complete_grade'], 'number'],
            [['kid', 'certification_id', 'course_id', 'user_id', 'serial_number', 'issued_by', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['certification_img_url'], 'string', 'max' => 500],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['certification_name','certification_from'], 'string', 'max' => 100],

            [['certification_type'], 'string', 'max' => 1],
            [['certification_type'], 'in', 'range' => [self::CERTIFICATION_TYPE_SYSTEM, self::CERTIFICATION_TYPE_EXTERNAL]],
            [['certification_type'], 'default', 'value'=> self::CERTIFICATION_TYPE_SYSTEM],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],


            [['created_channel'], 'string', 'max' => 1],
            [['created_channel'], 'in', 'range' => [self::CREATED_CHANNEL_MANUAL, self::CREATED_CHANNEL_COURSE]],
            [['created_channel'], 'default', 'value'=> self::CREATED_CHANNEL_MANUAL],

            [['valid_status'], 'string', 'max' => 1],
            [['valid_status'], 'in', 'range' => [self::VALID_STATUS_HISTORY, self::VALID_STATUS_CURRENT]],
            [['valid_status'], 'default', 'value'=> self::VALID_STATUS_CURRENT],

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
            'certification_id' => Yii::t('common', 'certification_id'),
            'user_id' => Yii::t('common', 'user_id'),
            'course_id' => Yii::t('common', 'course_id'),
            'serial_number' => Yii::t('common', 'serial_number'),
            'issued_at' => Yii::t('common', 'issued_at'),
            'issued_by' => Yii::t('common', 'issued_by'),
            'start_at' => Yii::t('common', 'start_at'),
            'end_at' => Yii::t('common', 'end_at'),
            'complete_score' => Yii::t('common', 'complete_score'),
            'complete_grade' => Yii::t('common', 'complete_grade'),
            'certification_name' => Yii::t('common', 'certification_name'),
            'certification_type' => Yii::t('common', 'certification_type'),
            'certification_img_url' => Yii::t('common', 'certification_img_url'),
            'status' => Yii::t('common', 'status'),
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
    public function getLnCertification()
    {
        return $this->hasOne(LnCertification::className(), ['kid' => 'certification_id'])
            ->onCondition([LnCertification::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
