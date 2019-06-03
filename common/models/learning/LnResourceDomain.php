<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "eln_ln_resource_domain".
 *
 * @property string $kid
 * @property string $resource_id
 * @property string $domain_id
 * @property string $company_id
 * @property string $resource_type
 * @property string $status
 * @property integer $start_at
 * @property integer $end_at
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class LnResourceDomain extends BaseActiveRecord
{
    const RESOURCE_TYPE_COURSEWARE = "0";
    const RESOURCE_TYPE_COURSE = "1";
    const RESOURCE_TYPE_TEACHER = "2";
    const RESOURCE_TYPE_CERTIFICATION = "3";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_resource_domain}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['domain_id', 'start_at'], 'required'],
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'resource_id', 'domain_id', 'company_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['resource_type'], 'in', 'range' => [self::RESOURCE_TYPE_COURSEWARE, self::RESOURCE_TYPE_COURSE,
                self::RESOURCE_TYPE_TEACHER, self::RESOURCE_TYPE_CERTIFICATION]],
            [['resource_type'], 'default', 'value'=> self::RESOURCE_TYPE_COURSEWARE],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

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
            'resource_id' => Yii::t('common', 'resource_id'),
            'domain_id' => Yii::t('common', 'domain_id'),
            'company_id' => Yii::t('common', 'company_id'),
            'resource_type' => Yii::t('common', 'resource_type'),
            'status' => Yii::t('common', 'status'),
            'start_at' => Yii::t('common', 'start_at'),
            'end_at' => Yii::t('common', 'end_at'),
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
    public function getLnCourseware()
    {
        return $this->hasOne(LnCourseware::className(), ['kid' => 'resource_id'])
            ->onCondition([LnCourseware::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourse()
    {
        return $this->hasMany(LnCourse::className(), ['kid' => 'recourse_id'])
            ->onCondition([LnCourse::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }


}
