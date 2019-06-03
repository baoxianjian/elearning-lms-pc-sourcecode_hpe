<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_company_menu}}".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $parent_menu_id
 * @property string $menu_code
 * @property string $menu_name
 * @property string $menu_type
 * @property string $action_url
 * @property string $action_parameter
 * @property string $action_type
 * @property string $action_target
 * @property string $action_icon
 * @property string $action_class
 * @property string $action_tip
 * @property string $description
 * @property string $share_flag
 * @property string $limitation
 * @property string $i18n_flag
 * @property string $status
 * @property integer $sequence_number
 * @property integer $version
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
class FwCompanyMenu extends BaseActiveRecord
{
    const ACTION_TYPE_ACTION = "1";
    const ACTION_TYPE_URL = "0";

    const MENU_TYPE_PORTAL = "portal";
    const MENU_TYPE_REPORT = "report";
    const MENU_TYPE_TOOL_BOX = "tool-box";
    const MENU_TYPE_PORTAL_MENU = "portal-menu";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_company_menu}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['menu_code', 'menu_name', 'menu_type', 'action_url', 'sequence_number'], 'required' , 'on' => 'manage'],
            [['description'], 'string'],
            [['sequence_number', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'company_id', 'parent_menu_id', 'menu_code', 'menu_name', 'menu_type', 'action_target', 'action_icon',
                'action_class', 'action_tip', 'i18n_flag', 'created_by', 'created_from', 'updated_by', 'updated_from'], 'string', 'max' => 50],
            [['action_url', 'action_parameter'], 'string', 'max' => 500],

            [['limitation'], 'string', 'max' => 1],
            [['limitation'], 'in', 'range' => [self::LIMITATION_NONE, self::LIMITATION_READONLY,
                self::LIMITATION_HIDDEN, self::LIMITATION_ONLYNAME]],


            [['action_type'], 'string', 'max' => 1],
            [['action_type'], 'in', 'range' => [self::ACTION_TYPE_ACTION, self::ACTION_TYPE_URL]],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

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
            'kid' => Yii::t('common', 'kid'),
            'company_id' => Yii::t('common', 'company_id'),
            'parent_menu_id' => Yii::t('common', 'parent_menu_id'),
            'menu_code' => Yii::t('common', 'menu_code'),
            'menu_name' => Yii::t('common', 'menu_name'),
            'menu_type' => Yii::t('common', 'menu_type'),
            'action_url' => Yii::t('common', 'action_url'),
            'action_parameter' => Yii::t('common', 'action_parameter'),
            'action_type' => Yii::t('common', 'action_type'),
            'action_target' => Yii::t('common', 'action_target'),
            'action_icon' => Yii::t('common', 'action_icon'),
            'action_class' => Yii::t('common', 'action_class'),
            'action_tip' => Yii::t('common', 'action_tip'),
            'description' => Yii::t('common', 'description'),
            'share_flag' => Yii::t('common', 'share_flag'),
            'limitation' => Yii::t('common', 'limitation'),
            'i18n_flag' => Yii::t('common', 'i18n_flag'),
            'status' => Yii::t('common', 'status'),
            'sequence_number' => Yii::t('common', 'sequence_number'),
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

    public function getCompanyName()
    {
        $companyModel = FwCompany::findOne($this->company_id);

        if ($companyModel != null)
            return $companyModel->company_name;
        else
            return "";
    }

    public function getMenuTypeName()
    {
        if ($this->menu_type == self::MENU_TYPE_PORTAL)
            return Yii::t('common', 'menu_type_portal');
        else if ($this->menu_type == self::MENU_TYPE_REPORT)
            return Yii::t('common', 'menu_type_report');
        else if ($this->menu_type == self::MENU_TYPE_TOOL_BOX)
            return Yii::t('common', 'menu_type_tool_box');
        else if ($this->menu_type == self::MENU_TYPE_PORTAL_MENU)
            return Yii::t('common', 'menu_type_portal_menu');
    }

    public function getActionTypeName()
    {
        if ($this->action_type == self::ACTION_TYPE_URL)
            return Yii::t('common', 'action_type_url');
        else
            return Yii::t('common', 'action_type_action');
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
