<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_service}}".
 *
 * @property string $kid
 * @property string $service_code
 * @property string $service_name
 * @property string $service_status
 * @property string $is_log
 * @property string $is_allow_restart
 * @property string $restart_cycle
 * @property string $service_type
 * @property string $run_at
 * @property string $description
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property FwServiceLog[] $fwServiceLogs
 */
class FwService extends BaseActiveRecord
{
    const SERVICE_STATUS_STOP = "0";
    const SERVICE_STATUS_RUNNING = "1";

    const SERVICE_TYPE_NORMAL = "0";
    const SERVICE_TYPE_REPORT = "1";

    const RESTART_CYCLE_NONE = "0";
    const RESTART_CYCLE_YEAR = "1";
    const RESTART_CYCLE_MONTH = "2";
    const RESTART_CYCLE_DAY = "3";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_service}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_code', 'service_name'], 'required', 'on' => 'manage'],
            [['kid', 'service_code', 'service_name', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['run_at'], 'string', 'max' => 20],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['created_at', 'updated_at'], 'integer'],
            [['description'], 'string'],

            [['service_status'], 'string', 'max' => 1],
            [['service_status'], 'in', 'range' => [self::SERVICE_STATUS_STOP, self::SERVICE_STATUS_RUNNING]],
            [['service_status'], 'default', 'value'=> self::SERVICE_STATUS_STOP],

            [['is_log'], 'string', 'max' => 1],
            [['is_log'], 'in', 'range' => [self::NO, self::YES]],
            [['is_log'], 'default', 'value'=> self::NO],

            [['is_allow_restart'], 'string', 'max' => 1],
            [['is_allow_restart'], 'in', 'range' => [self::NO, self::YES]],
            [['is_allow_restart'], 'default', 'value'=> self::NO],

            [['restart_cycle'], 'string', 'max' => 1],
            [['restart_cycle'], 'in', 'range' => [self::RESTART_CYCLE_NONE, self::RESTART_CYCLE_YEAR,
                self::RESTART_CYCLE_MONTH, self::RESTART_CYCLE_DAY]],
            [['restart_cycle'], 'default', 'value'=> self::RESTART_CYCLE_NONE],

            [['service_type'], 'string', 'max' => 1],
            [['service_type'], 'in', 'range' => [self::SERVICE_TYPE_NORMAL, self::SERVICE_TYPE_REPORT]],
            [['service_type'], 'default', 'value'=> self::SERVICE_TYPE_NORMAL],

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
            'service_code' => Yii::t('common', 'service_code'),
            'service_name' => Yii::t('common', 'service_name'),
            'service_status' => Yii::t('common', 'service_status'),
            'is_log' => Yii::t('common', 'is_log'),
            'is_allow_restart' => Yii::t('common', 'is_allow_restart'),
            'restart_cycle' => Yii::t('common', 'restart_cycle'),
            'service_type' => Yii::t('common', 'service_type'),
            'run_at' => Yii::t('common', 'service_run_at'),
            'description' => Yii::t('common', 'description'),
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

    public function getServiceStatusText()
    {
        $status = $this->service_status;
        if ($status == self::SERVICE_STATUS_STOP)
            return Yii::t('common', 'change_status_stop');
        else if ($status == self::SERVICE_STATUS_RUNNING)
            return Yii::t('common', 'change_status_start');
    }

    public function getIsLogText()
    {
        $status = $this->is_log;
        if ($status == self::NO)
            return Yii::t('common', 'no');
        else if ($status == self::YES)
            return Yii::t('common', 'yes');
    }

    public function getIsAllowRestartText()
    {
        $value = $this->is_allow_restart;
        if ($value == self::NO)
            return Yii::t('common', 'no');
        else if ($value == self::YES)
            return Yii::t('common', 'yes');
    }

    public function getRestartCycleText()
    {
        $value = $this->restart_cycle;
        if ($value == self::RESTART_CYCLE_NONE)
            return Yii::t('common', 'restart_cycle_none');
        else if ($value == self::RESTART_CYCLE_YEAR)
            return Yii::t('common', 'restart_cycle_year');
        else if ($value == self::RESTART_CYCLE_MONTH)
            return Yii::t('common', 'restart_cycle_month');
        else if ($value == self::RESTART_CYCLE_DAY)
            return Yii::t('common', 'restart_cycle_day');
    }

    public function getServiceTypeText()
    {
        $value = $this->service_type;
        if ($value == self::SERVICE_TYPE_NORMAL)
            return Yii::t('common', 'service_type_normal');
        else if ($value == self::SERVICE_TYPE_REPORT)
            return Yii::t('common', 'service_type_report');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwServiceLogs()
    {
        return $this->hasMany(FwServiceLog::className(), ['service_id' => 'kid'])
            ->onCondition([FwServiceLog::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
    
    public function getAllowRestart(){
    	$status = $this->is_allow_restart;
    	if ($status == self::NO){
    		return Yii::t('common', 'no');
    	}	
    	else if ($status == self::YES){
    		return Yii::t('common', 'yes');
    	}
    		
    }
}
