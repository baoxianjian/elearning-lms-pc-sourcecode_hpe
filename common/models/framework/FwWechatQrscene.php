<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_wechat_qrscene}}".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $qrscene_type
 * @property string $qrscene_id
 * @property string $qrscene_value
 * @property string $qrscene_action
 * @property integer $start_at
 * @property integer $end_at
 * @property string $ticket
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property FwCompany $fwCompany
 */
class FwWechatQrscene extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_wechat_qrscene}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'qrscene_id', 'qrscene_value', 'qrscene_action', 'start_at', 'ticket'], 'required'],
            [['start_at', 'end_at', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'company_id', 'qrscene_id', 'qrscene_value', 'qrscene_action',
                'created_by', 'created_from', 'updated_by', 'updated_from'], 'string', 'max' => 50],
            [['qrscene_type'], 'string', 'max' => 30],
            [['ticket'], 'string', 'max' => 100],

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
            'company_id' => Yii::t('common', 'company_id'),
            'qrscene_type' => Yii::t('common', 'qrscene_type'),
            'qrscene_id' => Yii::t('common', 'qrscene_id'),
            'qrscene_value' => Yii::t('common', 'qrscene_value'),
            'qrscene_action' => Yii::t('common', 'qrscene_action'),
            'start_at' => Yii::t('common', 'start_at'),
            'end_at' => Yii::t('common', 'end_at'),
            'ticket' => Yii::t('common', 'ticket'),
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
    public function getFwCompany()
    {
        return $this->hasOne(FwCompany::className(), ['kid' => 'company_id'])
            ->onCondition([FwCompany::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
