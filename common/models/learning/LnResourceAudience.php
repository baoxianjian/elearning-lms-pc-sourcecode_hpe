<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;

use Yii;

/**
 * This is the model class for table "{{%ln_resource_audience}}".
 *
 * @property string $kid
 * @property string $resource_id
 * @property string $audience_id
 * @property string $company_id
 * @property string $resource_type
 * @property string $status
 * @property integer $start_at
 * @property integer $end_at
 * @property integer $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $created_ip
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $updated_ip
 * @property string $is_deleted
 */
class LnResourceAudience extends BaseActiveRecord
{

    //资源分类
    const RESOURCE_TYPE_COURSEWARE = '0';//课件
    const RESOURCE_TYPE_COURSE = '1';//课程
    const RESOURCE_TYPE_TEACHER = '2';//讲师
    const RESOURCE_TYPE_CERTIFICATE = '3';//证书

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_resource_audience}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['resource_id', 'audience_id', 'company_id', 'start_at'], 'required'],
            [['start_at', 'end_at', 'version', 'created_at', 'updated_at'], 'integer'],
            [['resource_id', 'audience_id', 'company_id', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['resource_type', 'status', 'is_deleted'], 'string', 'max' => 1],
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
            'audience_id' => Yii::t('common', 'audience_id'),
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
}
