<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_action_log}}".
 *
 * @property string $kid
 * @property string $user_id
 * @property string $system_id
 * @property string $action_filter_id
 * @property string $controller_id
 * @property string $action_id
 * @property string $action_parameter_query
 * @property string $action_parameter_body
 * @property string $encrypt_mode
 * @property string $http_mode
 * @property string $system_flag
 * @property string $action_url
 * @property string $action_ip
 * @property string $start_at
 * @property string $end_at
 * @property string $duration_time
 * @property string $machine_label
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property FwUser $fwUser
 * @property FwActionLogFilter $fwActionFilter
 */
class FwActionLog extends BaseActiveRecord
{
    const ENCRYPT_MODE_NONE = "0";
    const ENCRYPT_MODE_AES = "1";
    const ENCRYPT_MODE_DES = "2";

    const HTTP_MODE_GET = "0";
    const HTTP_MODE_POST = "1";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_action_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'controller_id', 'action_id', 'action_url', 'system_flag', 'action_ip'], 'required', 'on' => 'manage'],
            [['created_at', 'updated_at'], 'integer'],
            [['start_at', 'end_at', 'duration_time'], 'number'],
            [['kid', 'user_id', 'system_id', 'action_filter_id', 'controller_id', 'action_id', 'created_by', 'updated_by','machine_label'], 'string', 'max' => 50],
            [['system_flag', 'action_ip'], 'string', 'max' => 30],
            [['action_parameter_query','action_parameter_body','action_url'], 'string'],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['encrypt_mode'], 'string', 'max' => 1],
            [['encrypt_mode'], 'in', 'range' => [self::ENCRYPT_MODE_NONE, self::ENCRYPT_MODE_AES, self::ENCRYPT_MODE_DES]],
            [['encrypt_mode'], 'default', 'value'=> self::ENCRYPT_MODE_NONE],

            [['http_mode'], 'string', 'max' => 1],
            [['http_mode'], 'in', 'range' => [self::HTTP_MODE_GET, self::HTTP_MODE_POST]],
            [['http_mode'], 'default', 'value'=> self::HTTP_MODE_GET],

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
            'kid' => Yii::t('common', 'action_log_id'),
            'user_id' => Yii::t('common', 'user_id'),
            'system_id' => Yii::t('common', 'system_id'),
            'action_filter_id' => Yii::t('common', 'action_filter_id'),
            'controller_id' => Yii::t('common', 'controller_id'),
            'action_id' => Yii::t('common', 'action_id'),
            'action_parameter_query' => Yii::t('common', 'action_parameter_query'),
            'action_parameter_body' => Yii::t('common', 'action_parameter_body'),
            'encrypt_mode' => Yii::t('common', 'encrypt_mode'),
            'http_mode' => Yii::t('common', 'http_mode'),
            'system_flag' => Yii::t('common', 'system_flag'),
            'action_url' => Yii::t('common', 'action_url'),
            'action_ip' => Yii::t('common', 'action_ip'),
            'start_at' => Yii::t('common', 'start_at'),
            'end_at' => Yii::t('common', 'end_at'),
            'duration_time' => Yii::t('common', 'duration_time'),
            'machine_label' => Yii::t('common', 'machine_label'),
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
    public function getFwUser()
    {
        return $this->hasOne(FwUser::className(), ['kid' => 'user_id'])
            ->onCondition([FwUser::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwActionFilter()
    {
        return $this->hasOne(FwActionLogFilter::className(), ['kid' => 'action_filter_id'])
            ->onCondition([FwActionLogFilter::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    public function getUserName()
    {
        $userModel = FwUser::findOne($this->user_id);

        if ($userModel != null)
            return $userModel->user_name;
        else
            return "";
    }


    public function getRealName()
    {
        $userModel = FwUser::findOne($this->user_id);

        if ($userModel != null)
            return $userModel->real_name;
        else
            return "";
    }

    public function getOrgnizationName()
    {
        $userModel = FwUser::findOne($this->user_id);

        if ($userModel != null){
            $orgizationId = $userModel->orgnization_id;

            $orgnizationModel = FwOrgnization::findOne($orgizationId);
            if ($orgnizationModel != null){
                return $orgnizationModel->orgnization_name;
            }
            else
                return "";
        }
        else
            return "";
    }

    public function getFilterName()
    {
        $actionLogFilterModel = FwActionLogFilter::findOne($this->action_filter_id);

        if ($actionLogFilterModel != null)
            return $actionLogFilterModel->filter_name;
        else
            return "";
    }

}
