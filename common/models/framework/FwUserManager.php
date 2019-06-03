<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_user_manager}}".
 *
 * @property string $kid
 * @property string $user_id
 * @property string $manager_id
 * @property string $reporting_model
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
 *
 * @property FwUser $fwUser
 */
class FwUserManager extends BaseActiveRecord
{
    const REPORTING_MODEL_LINE_MANAGER = "0";
    const REPORTING_MODEL_DEPART_MANAGER = "1";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_user_manager}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'manager_id'], 'required'],
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'user_id', 'manager_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['reporting_model'], 'string', 'max' => 1],
            [['reporting_model'], 'in', 'range' => [self::REPORTING_MODEL_LINE_MANAGER, self::REPORTING_MODEL_DEPART_MANAGER]],

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
            'user_id' => Yii::t('app', 'user_id'),
            'manager_id' => Yii::t('app', 'manager_id'),
            'reporting_model' => Yii::t('app', 'reporting_model'),
            'status' => Yii::t('app', 'status'),
            'start_at' => Yii::t('app', 'start_at'),
            'end_at' => Yii::t('app', 'end_at'),
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
}
