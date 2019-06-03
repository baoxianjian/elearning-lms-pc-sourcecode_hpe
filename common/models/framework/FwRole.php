<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_role}}".
 *
 * @property string $kid
 * @property string $role_code
 * @property string $role_name
 * @property string $company_id
 * @property string $description
 * @property string $share_flag
 * @property string $limitation
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
 * @property FwRolePermission[] $fwRolePermissions
 * @property FwUserRole[] $fwUserRoles
 */
class FwRole extends BaseActiveRecord
{
    public $role_display_name;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_role}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['role_code', 'role_name', 'limitation'], 'required', 'on' => 'manage'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'company_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['role_code', 'role_name'], 'string', 'max' => 50],
            [['description'], 'string'],
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
            'kid' => Yii::t('common', 'role_id'),
            'role_code' => Yii::t('common', 'role_code'),
            'role_name' => Yii::t('common', 'role_name'),
            'company_id' => Yii::t('common', 'company_id'),
            'description' => Yii::t('common', 'description'),
            'share_flag' => Yii::t('common', 'share_flag'),
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
    public function getFwRolePermissions()
    {
        return $this->hasMany(FwRolePermission::className(), ['role_id' => 'kid'])
            ->onCondition([FwRolePermission::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwUserRoles()
    {
        return $this->hasMany(FwUserRole::className(), ['role_id' => 'kid'])
            ->onCondition([FwUserRole::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
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
