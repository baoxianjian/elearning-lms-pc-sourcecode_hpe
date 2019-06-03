<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_action_log_filter}}".
 *
 * @property string $kid
 * @property string $filter_code
 * @property string $filter_name
 * @property string $controller_id
 * @property string $action_id
 * @property string $system_flag
 * @property string $status
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property FwActionLog[] $fwActionLogs
 */
class FwActionLogFilter extends BaseActiveRecord
{
    const SYSTEM_FLG_ELN_BACKEND = "eln_backend";
    const SYSTEM_FLG_ELN_FRONTEND = "eln_frontend";
    const SYSTEM_FLG_ELN_APP = "eln_app";
    const SYSTEM_FLG_ELN_API = "eln_api";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_action_log_filter}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['filter_code', 'filter_name', 'controller_id', 'action_id'], 'required', 'on' => 'manage'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'filter_code', 'filter_name', 'controller_id', 'action_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['system_flag'], 'string', 'max' => 30],
            [['created_from','updated_from'], 'string', 'max' => 50],

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
            'kid' => Yii::t('common', 'action_filter_id'),
            'filter_code' => Yii::t('common', 'filter_code'),
            'filter_name' => Yii::t('common', 'filter_name'),
            'controller_id' => Yii::t('common', 'controller_id'),
            'action_id' => Yii::t('common', 'action_id'),
            'system_flag' => Yii::t('common', 'system_flag'),
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
    public function getFwActionLogs()
    {
        return $this->hasMany(FwActionLog::className(), ['action_filter_id' => 'kid'])
            ->onCondition([FwActionLog::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    public function getRelateActionFilter($controllerId, $actionId, $systemFlag, $withCache = true)
    {
        $cacheKey = "RelateActionFilter_SystemFlag_" . $systemFlag . "_ActionId_" . $actionId . "_ControllerId_" . $controllerId;

        $result = BaseActiveRecord::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $model = new FwActionLogFilter();

            $result = $model->findOne([
                'controller_id' => $controllerId,
                'action_id' => $actionId,
                'system_flag' => $systemFlag,
                'status' => self::STATUS_FLAG_NORMAL,
            ]);

            BaseActiveRecord::saveToCache($cacheKey, $result, null, BaseActiveRecord::DURATION_DAY, $withCache);
        }

        return $result;
    }
}
