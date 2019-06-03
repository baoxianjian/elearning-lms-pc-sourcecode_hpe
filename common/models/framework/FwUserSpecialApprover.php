<?php

namespace common\models\framework;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%fw_user_special_approver}}".
 *
 * @property string $kid
 * @property string $user_id
 * @property string $approver_id
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
 *
 * @property FwUser $user
 */
class FwUserSpecialApprover extends BaseActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_user_special_approver}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'approver_id'], 'required'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'user_id', 'approver_id', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['is_deleted'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'kid'),
            'user_id' => Yii::t('common', 'user_id'),
            'approver_id' => Yii::t('common', 'approver_id'),
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(FwUser::className(), ['kid' => 'user_id']);
    }
}
