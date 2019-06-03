<?php

namespace common\models\message;

use common\models\learning\LnCourse;
use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ms_task}}".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $domain_id
 * @property string $task_sponsor_id
 * @property string $task_code
 * @property string $task_type
 * @property string $task_status
 * @property string $complete_type
 * @property string $push_user_list
 * @property integer $push_user_count
 * @property string $error_msg_code
 * @property string $error_msg_title
 * @property string $error_msg_content
 * @property integer $push_prepare_at
 * @property integer $push_start_at
 * @property integer $push_end_at
 * @property string $status
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class MsTask extends BaseActiveRecord
{
    /**
     * 学习管理员推送
     */
    const TASK_TYPE_ADMIN = '0';
    /**
     * 直线经理指派
     */
    const TASK_TYPE_MANAGER = '1';

    /**
     * 待推送
     */
    const TASK_STATUS_TODO = '0';
    /**
     * 进行中
     */
    const TASK_STATUS_PROGRESS = '1';
    /**
     * 已完成
     */
    const TASK_STATUS_DONE = '2';

    /**
     * 未完成
     */
    const COMPLETE_TYPE_UNDONE = '0';
    /**
     * 全部成功
     */
    const COMPLETE_TYPE_ALL_SUCCESS = '1';
    /**
     * 全部失败
     */
    const COMPLETE_TYPE_ALL_FAIL = '2';
    /**
     * 部分成功
     */
    const COMPLETE_TYPE_PART_SUCCESS = '3';

    public $item_count;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_task}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'domain_id', 'task_sponsor_id', 'task_code', 'task_type', 'task_status', 'complete_type'], 'required'],
            [['complete_type', 'created_at', 'updated_at', 'push_user_count', 'push_prepare_at', 'push_start_at', 'push_end_at'], 'integer'],
            [['kid', 'company_id', 'domain_id', 'task_sponsor_id', 'task_code', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from', 'updated_from'], 'string', 'max' => 50],

            [['task_type'], 'string', 'max' => 1],
            [['task_type'], 'default', 'value' => self::TASK_TYPE_ADMIN],
            [['task_type'], 'in', 'range' => [self::TASK_TYPE_ADMIN, self::TASK_TYPE_MANAGER]],

            [['task_status'], 'string', 'max' => 1],
            [['task_status'], 'default', 'value' => self::TASK_STATUS_TODO],
            [['task_status'], 'in', 'range' => [self::TASK_STATUS_TODO, self::TASK_STATUS_PROGRESS, self::TASK_STATUS_DONE]],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

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
            'kid' => Yii::t('frontend', 'Kid'),
            'task_sponsor_id' => Yii::t('frontend', 'Task Sponsor ID'),
            'task_code' => Yii::t('frontend', 'Task Code'),
            'task_type' => Yii::t('frontend', 'Task Type'),
            'task_status' => Yii::t('frontend', 'Task Status'),
            'complete_type' => Yii::t('common', 'complete_type'),
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

    public function getTaskCode($companyId, $kid = "")
    {
        if (!empty($kid)) {
            $info = LnCourse::findOne($kid);
            return $info->task_code;
        }
        $start_at = strtotime(date('Y-m-d'));
        $end_at = $start_at + 86400;//1天
        $allCount = MsTask::find(false)
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['>=', 'created_at', $start_at])
            ->andFilterWhere(['<=', 'created_at', $end_at])
            ->count();
        $count = $allCount + 1;/*默认成1开始*/
        return date('Ymd') . sprintf("%03d", $count);
    }

}
