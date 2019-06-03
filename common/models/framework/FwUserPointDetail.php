<?php

namespace common\models\framework;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%fw_user_point_detail}}".
 *
 * @property string $kid
 * @property string $user_id
 * @property string $point_rule_id
 * @property string $company_id
 * @property string $reason
 * @property string $get_from
 * @property string $get_from_id
 * @property string $point
 * @property string $point_type
 * @property integer $get_at
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
 * @property FwPointRule $pointRule
 * @property FwUser $user
 */
class FwUserPointDetail extends BaseActiveRecord
{

    const POINT_TYPE_IN = '0'; //转入
    const POINT_TYPE_OUT = '1'; //转出
    const POINT_TYPE_GET = '2'; //获得
    const POINT_TYPE_MIN = '3'; //扣除

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_user_point_detail}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'point_rule_id', 'company_id', 'point', 'get_at'], 'required'],
            [['point'], 'number'],
            [['get_at', 'version', 'created_at', 'updated_at'], 'integer'],
            [['user_id', 'point_rule_id', 'company_id', 'get_from', 'get_from_id', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['reason'], 'string', 'max' => 500],
            [['point_type', 'is_deleted'], 'string', 'max' => 1]
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
            'point_rule_id' => Yii::t('common', 'point_rule_id'),
            'company_id' => Yii::t('common', 'company_id'),
            'reason' => Yii::t('common', 'reason'),
            'get_from' => Yii::t('common', 'get_from'),
            'get_from_id' => Yii::t('common', 'get_from_id'),
            'point' => Yii::t('common', 'point'),
            'point_type' => Yii::t('common', 'point_type'),
            'get_at' => Yii::t('common', 'get_at'),
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
    public function getPointRule()
    {
        return $this->hasOne(FwPointRule::className(), ['kid' => 'point_rule_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(FwUser::className(), ['kid' => 'user_id']);
    }
    

}
