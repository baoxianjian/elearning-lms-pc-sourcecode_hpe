<?php

namespace common\models\framework;


use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%fw_user_point_summary}}".
 *
 * @property string $kid
 * @property string $user_id
 * @property string $growth_system_id
 * @property string $company_id
 * @property string $available_point
 * @property string $get_point
 * @property string $transfer_in_point
 * @property string $transfer_out_point
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
 * @property FwGrowth $growthSystem
 * @property FwUser $user
 */
class FwUserPointSummary extends BaseActiveRecord
{

    const GET_FROM_USER='user';
    const GET_FROM_ADMIN='admin';
    const GET_FROM_RULE='rule';

    const TRANS_TYPE_IN=0;  //转入
    const TRANS_TYPE_OUT=1;  //转出
    const TRANS_TYPE_GET=2;   //获得
    const TRANS_TYPE_DEDUCT=3;  //扣除

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_user_point_summary}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'user_id', 'growth_system_id', 'company_id'], 'required'],
            [['available_point', 'get_point', 'transfer_in_point', 'transfer_out_point'], 'number'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['user_id', 'growth_system_id', 'company_id', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
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
            'growth_system_id' => Yii::t('common', 'growth_system_id'),
            'company_id' => Yii::t('common', 'company_id'),
            'available_point' => Yii::t('common', 'available_point'),
            'get_point' => Yii::t('common', 'get_point'),
            'transfer_in_point' => Yii::t('common', 'transfer_in_point'),
            'transfer_out_point' => Yii::t('common', 'transfer_out_point'),
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
    public function getGrowthSystem()
    {
        return $this->hasOne(FwGrowth::className(), ['kid' => 'growth_system_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(FwUser::className(), ['kid' => 'user_id']);
    }
    

    
}
