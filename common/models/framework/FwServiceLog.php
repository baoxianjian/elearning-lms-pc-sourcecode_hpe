<?php

namespace common\models\framework;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%fw_service_log}}".
 *
 * @property string $kid
 * @property string $service_id
 * @property string $action_status
 * @property string $service_log
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property FwService $fwService
 */
class FwServiceLog extends BaseActiveRecord
{
    const ACTION_STATUS_NORMAL = "0";
    const ACTION_STATUS_ERROR = "1";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_service_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_id', 'service_log'], 'required'],
            [['service_log'], 'string'],
            [['kid', 'service_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['action_status'], 'string', 'max' => 1],
            [['action_status'], 'in', 'range' => [self::ACTION_STATUS_NORMAL, self::ACTION_STATUS_ERROR]],
            [['action_status'], 'default', 'value' => self::ACTION_STATUS_NORMAL],

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
            'service_id' => Yii::t('common', 'service_id'),
            'action_status' => Yii::t('common', 'action_status'),
            'service_log' => Yii::t('common', 'service_log'),
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
    public function getFwService()
    {
        return $this->hasOne(FwService::className(), ['kid' => 'service_id'])
            ->onCondition([FwService::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    public function getActionStatusText()
    {
        if ($this->action_status === self::ACTION_STATUS_NORMAL) {
            return Yii::t('common', 'action_status_normal');
        } elseif ($this->action_status === self::ACTION_STATUS_ERROR) {
            return Yii::t('common', 'action_status_error');
        }
    }

    public function getServiceName()
    {
        $serviceModel = FwService::findOne($this->service_id);

        if ($serviceModel != null)
            return $serviceModel->service_name;
        else
            return "";
    }
}
