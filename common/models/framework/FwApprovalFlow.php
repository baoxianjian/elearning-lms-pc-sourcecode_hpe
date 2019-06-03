<?php

namespace common\models\framework;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%fw_approval_flow}}".
 *
 * @property string $kid
 * @property string $flow_batch_no
 * @property string $event_id
 * @property string $event_type
 * @property string $applier_id
 * @property integer $applier_at
 * @property string $approval_status
 * @property integer $flow_number
 * @property string $preflow_id
 * @property string $root_flow_id
 * @property string $approval_rule
 * @property string $approved_by
 * @property integer $approved_at
 * @property string $version
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
class FwApprovalFlow extends BaseActiveRecord
{
	const EVENT_TYPE_COURSE_APPLY = '0';//事项类别：0 课程申请
    const EVENT_TYPE_COURSE_CANCEL = '1';//事项类别：1 课程取消

    const APPROVAL_STATUS_APPLING = '0'; /*申请中*/
    const APPROVAL_STATUS_APPROVED = '1'; /*审批同意*/
    const APPROVAL_STATUS_REJECTED = '2'; /*审批不同意*/
    const APPROVAL_STATUS_CANCELED = '3'; /*作废*/
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_approval_flow}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['event_id', 'applier_id', 'applier_at', 'approval_rule', 'approved_by'], 'required'],
            [['applier_at', 'flow_number', 'approved_at', 'version', 'created_at', 'updated_at'], 'integer'],
            [['flow_batch_no','event_id', 'applier_id', 'preflow_id', 'root_flow_id', 'approved_by', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['event_type', 'approval_status', 'is_deleted'], 'string', 'max' => 1],
            [['approval_rule'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'kid'),
            'flow_batch_no' => Yii::t('common', 'flow_batch_no'),
            'event_id' => Yii::t('common', 'event_id'),
            'event_type' => Yii::t('common', 'event_type'),
            'applier_id' => Yii::t('common', 'applier_id'),
            'applier_at' => Yii::t('common', 'applier_at'),
            'approval_status' => Yii::t('common', 'approval_status'),
            'flow_number' => Yii::t('common', 'flow_number'),
            'preflow_id' => Yii::t('common', 'preflow_id'),
            'root_flow_id' => Yii::t('common', 'root_flow_id'),
            'approval_rule' => Yii::t('common', 'approval_rule'),
            'approved_by' => Yii::t('common', 'approved_by'),
            'approved_at' => Yii::t('common', 'approved_at'),
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'created_ip' => Yii::t('common', 'created_ip'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'updated_ip' => Yii::t('common', 'updated_ip'),
            'is_deleted' => Yii::t('common', 'is_deleted'),
        ];
    }
}
