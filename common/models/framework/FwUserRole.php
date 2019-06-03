<?php

namespace common\models\framework;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%fw_user_role}}".
 *
 * @property string $kid
 * @property string $user_id
 * @property string $role_id
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
 * @property string $role_display_name
 * @property string $role_name
 * @property string $role_code
 *
 * @property FwRole $fwRole
 * @property FwUser $fwUser
 */
class FwUserRole extends BaseActiveRecord
{
    public $role_display_name;
    public $role_name;
    public $role_code;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_user_role}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'role_id', 'start_at'], 'required'],
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'user_id', 'role_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
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
            'kid' => Yii::t('common', 'user_role_id'),
            'user_id' => Yii::t('common', 'user_id'),
            'role_id' => Yii::t('common', 'role_id'),
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
    public function getFwRole()
    {
        return $this->hasOne(FwRole::className(), ['kid' => 'role_id'])
            ->onCondition([FwRole::realTableName() . '.' . BaseActiveRecord::getQuoteColumnName("is_deleted") => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwUser()
    {
        return $this->hasOne(FwUser::className(), ['kid' => 'user_id'])
            ->onCondition([FwUser::realTableName() . '.' . BaseActiveRecord::getQuoteColumnName("is_deleted") => self::DELETE_FLAG_NO]);
    }
}
