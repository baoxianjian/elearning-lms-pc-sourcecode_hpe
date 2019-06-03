<?php

namespace common\models\message;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ms_push_msg}}".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $sender_id
 * @property string $by_email
 * @property string $by_sms
 * @property string $title
 * @property string $content
 * @property string $send_method
 * @property string $send_status
 * @property string $complete_type
 * @property string $cc_manager
 * @property string $cc_self
 * @property string $send_user_list
 * @property integer $send_user_count
 * @property string $cc_user_list
 * @property integer $cc_user_count
 * @property string $bcc_user_list
 * @property integer $bcc_user_count
 * @property string $error_msg_code
 * @property string $error_msg_title
 * @property string $error_msg_content
 * @property integer $push_prepare_at
 * @property integer $push_start_at
 * @property integer $push_end_at
 * @property integer $event_id
 * @property integer $event_type
 * @property string $status
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
class MsPushMsg extends BaseActiveRecord
{
    const SEND_METHOD_BATCH = '0';
    const SEND_METHOD_SINGLE = '1';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_push_msg}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'sender_id',], 'required'],
            [['content', 'send_user_list', 'cc_user_list', 'bcc_user_list', 'error_msg_content'], 'string'],
            [['send_user_count', 'cc_user_count', 'bcc_user_count', 'push_prepare_at', 'push_start_at', 'push_end_at', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'company_id', 'sender_id', 'error_msg_code', 'event_id', 'event_type', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['by_email', 'by_sms', 'send_method', 'send_status', 'complete_type', 'cc_manager', 'cc_self', 'status'], 'string', 'max' => 1],
            [['title', 'error_msg_title'], 'string', 'max' => 500],

            [['by_email', 'by_sms', 'send_method', 'send_status', 'complete_type', 'cc_manager', 'cc_self'], 'default', 'value' => '0'],

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
            'company_id' => Yii::t('common', 'company_id'),
            'sender_id' => Yii::t('common', 'sender_id'),
            'by_email' => Yii::t('common', 'by_email'),
            'by_sms' => Yii::t('common', 'by_sms'),
            'title' => Yii::t('common', 'title'),
            'content' => Yii::t('common', 'content'),
            'send_method' => Yii::t('common', 'send_method'),
            'send_status' => Yii::t('common', 'send_status'),
            'complete_type' => Yii::t('common', 'complete_type'),
            'send_user_list' => Yii::t('common', 'send_user_list'),
            'send_user_count' => Yii::t('common', 'send_user_count'),
            'cc_user_list' => Yii::t('common', 'cc_user_list'),
            'cc_user_count' => Yii::t('common', 'cc_user_count'),
            'bcc_user_list' => Yii::t('common', 'bcc_user_list'),
            'bcc_user_count' => Yii::t('common', 'bcc_user_count'),
            'error_msg_code' => Yii::t('common', 'error_msg_code'),
            'error_msg_title' => Yii::t('common', 'error_msg_title'),
            'error_msg_content' => Yii::t('common', 'error_msg_content'),
            'push_prepare_at' => Yii::t('common', 'push_prepare_at'),
            'push_start_at' => Yii::t('common', 'push_start_at'),
            'push_end_at' => Yii::t('common', 'push_end_at'),
            'status' => Yii::t('common', 'status'),
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
