<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_position}}".
 *
 * @property string $kid
 * @property string $position_code
 * @property string $position_name
 * @property string $position_type
 * @property string $position_level
 * @property string $company_id
 * @property string $responsibilities
 * @property string $capabilities
 * @property string $description
 * @property string $share_flag
 * @property string $limitation
 * @property string $data_from
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
 * @property FwCompany $fwCompany
 * @property FwUserPosition[] $fwUserPositions
 */
class FwPosition extends BaseActiveRecord
{


    public $position_display_name;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_position}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['position_code', 'position_name', 'limitation'], 'required', 'on' => 'manage'],
            [['company_id','position_code', 'position_name', 'limitation'], 'required', 'on' => 'api-manage'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'company_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['position_code', 'position_name','position_type','position_level','data_from'], 'string', 'max' => 50],
            [['description','responsibilities','capabilities'], 'string'],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

            [['limitation'], 'string', 'max' => 1],
            [['limitation'], 'in', 'range' => [self::LIMITATION_NONE, self::LIMITATION_READONLY,
                self::LIMITATION_HIDDEN, self::LIMITATION_ONLYNAME]],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]],

            [['share_flag'], 'string', 'max' => 1],
            [['share_flag'], 'in', 'range' => [self::SHARE_FLAG_EXCLUSIVE, self::SHARE_FLAG_SHARE]],
        ];
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'position_id'),
            'position_code' => Yii::t('common', 'position_code'),
            'position_name' => Yii::t('common', 'position_name'),
            'company_id' => Yii::t('common', 'company_id'),
            'position_type' => Yii::t('common', 'position_type'),
            'position_level' => Yii::t('common', 'position_level'),
            'responsibilities' => Yii::t('common', 'responsibilities'),
            'capabilities' => Yii::t('common', 'capabilities'),
            'description' => Yii::t('common', 'description'),
            'share_flag' => Yii::t('common', 'share_flag'),
            'data_from' => Yii::t('common', 'data_from'),
            'limitation' => Yii::t('common', 'limitation'),
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
    public function getFwCompany()
    {
        return $this->hasOne(FwCompany::className(), ['kid' => 'company_id'])
            ->onCondition([FwCompany::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwUserPositions()
    {
        return $this->hasMany(FwUserPosition::className(), ['position_id' => 'kid'])
            ->onCondition([FwUserPosition::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    public function getCompanyName()
    {
        $companyModel = FwCompany::findOne($this->company_id);

        if ($companyModel != null)
            return $companyModel->company_name;
        else
            return "";
    }
}
