<?php

namespace common\models\framework;

use common\services\framework\DictionaryService;
use Yii;
use common\models\treemanager\FwTreeNode;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%fw_company}}".
 *
 * @property string $kid
 * @property string $tree_node_id
 * @property string $parent_company_id
 * @property string $org_certificate_code
 * @property string $company_name
 * @property string $company_code
 * @property string $representative
 * @property string $description
 * @property string $site_url
 * @property string $resource_url
 * @property string $status
 * @property string $reporting_model
 * @property string $default_portal
 * @property string $second_level_domain
 * @property string $theme
 * @property string $language
 * @property string $logo_url
 * @property string $is_self_register
 * @property string $register_mode
 * @property string $is_default_company
 * @property string $limited_user_number
 * @property string $limited_domain_number
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property FwTreeNode $fwTreeNode
 * @property FwDomain[] $fwDomains
 * @property FwOrgnization[] $fwOrgnizations
 * @property FwPosition[] $fwPositions
 * @property FwRole[] $fwRoles
 * @property FwTag[] $fwTags
 */
class FwCompany extends BaseActiveRecord
{
    const REPORTING_MODEL_LINE_MANAGER = "0";
    const REPORTING_MODEL_DEPART_MANAGER = "1";

    const USER_PORTAL = "0";
    const COMPANY_PORTAL = "1";

    const REGISTER_MODE_MAIL = "0";
    const REGISTER_MODE_MANUAL = "1";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_company}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tree_node_id'], 'required', 'on' => 'manage'],
//            [['company_code', 'is_deleted'], 'unique', 'on' => 'api-manage-add','message'=>Yii::t('common','unique_constraint_error')],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'tree_node_id', 'parent_company_id', 'company_code', 'company_name', 'created_by', 'updated_by', 'org_certificate_code', 'representative'], 'string', 'max' => 50],
            [['site_url', 'resource_url', 'logo_url', 'second_level_domain'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['theme','language'], 'string', 'max' => 30],
            [['limited_user_number','limited_domain_number'], 'integer', 'min' => 0],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['is_self_register'], 'string', 'max' => 1],
            [['is_self_register'], 'in', 'range' => [self::NO, self::YES]],
            [['is_self_register'], 'default', 'value' => self::YES],

            [['register_mode'], 'string', 'max' => 1],
            [['register_mode'], 'in', 'range' => [self::REGISTER_MODE_MAIL, self::REGISTER_MODE_MANUAL]],
            [['register_mode'], 'default', 'value' => self::REGISTER_MODE_MAIL],

            [['is_default_company'], 'string', 'max' => 1],
            [['is_default_company'], 'in', 'range' => [self::NO, self::YES]],
            [['is_default_company'], 'default', 'value' => self::YES],

            [['default_portal'], 'string', 'max' => 1],
            [['default_portal'], 'in', 'range' => [self::USER_PORTAL, self::COMPANY_PORTAL]],
            [['default_portal'], 'default', 'value' => self::USER_PORTAL],

            [['reporting_model'], 'string', 'max' => 1],
            [['reporting_model'], 'in', 'range' => [self::REPORTING_MODEL_LINE_MANAGER, self::REPORTING_MODEL_DEPART_MANAGER]],
            [['reporting_model'], 'default', 'value' => self::REPORTING_MODEL_LINE_MANAGER],

            [['version'], 'number'],
            [['version'], 'default', 'value' => 1],

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
            'kid' => Yii::t('common', 'company_id'),
            'tree_node_id' => Yii::t('common', 'tree_node_id'),
            'parent_company_id' => Yii::t('common', 'parent_company_id'),
            'company_code' => Yii::t('common', 'company_code'),
            'company_name' => Yii::t('common', 'company_name'),
            'org_certificate_code' => Yii::t('common', 'org_certificate_code'),
            'representative' => Yii::t('common', 'representative'),
            'site_url' => Yii::t('common', 'site_url'),
            'resource_url' => Yii::t('common', 'resource_url'),
            'description' => Yii::t('common', 'description'),
            'reporting_model' => Yii::t('common', 'reporting_model'),
            'default_portal' => Yii::t('common', 'default_portal'),
            'theme' => Yii::t('common', 'default_theme'),
            'language' => Yii::t('common', 'default_language'),
            'second_level_domain' => Yii::t('common', 'second_level_domain'),
            'logo_url' => Yii::t('common', 'logo_url'),
            'is_self_register' => Yii::t('common', 'is_self_register'),
            'is_default_company' => Yii::t('common', 'is_default_company'),
            'limited_user_number' => Yii::t('common', 'limited_user_number'),
            'limited_domain_number' => Yii::t('common', 'limited_domain_number'),
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

    public function getReportingModelName()
    {
        if ($this->reporting_model == self::REPORTING_MODEL_LINE_MANAGER)
            return Yii::t('common', 'reporting_model_line_manager');
        else
            return Yii::t('common', 'reporting_model_depart_manager');
    }

    public function getDefaultPortalName()
    {
        if ($this->default_portal == self::COMPANY_PORTAL)
            return Yii::t('common', 'company_portal');
        else
            return Yii::t('common', 'user_portal');
    }

    public function getThemeName()
    {
        $theme = $this->theme;

        if ($theme != null && $theme != "") {
            $service = new DictionaryService();
            $name = $service->getDictionaryNameByValue('theme',$theme);

            return $name;
        }
        else
            return "";
    }

    public function getLanguageName()
    {
        $language = $this->language;

        if ($language != null && $language != "") {
            $service = new DictionaryService();
            $name = $service->getDictionaryNameByValue('language',$language);

            return $name;
        }
        else
            return "";
    }

    public function getIsSelfRegisterText()
    {
        if ($this->is_self_register == self::NO)
            return Yii::t('common', 'no');
        else
            return Yii::t('common', 'yes');
    }

    public function getIsDefaultCompanyText()
    {
        if ($this->is_default_company == self::NO)
            return Yii::t('common', 'no');
        else
            return Yii::t('common', 'yes');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwTreeNode()
    {
        return $this->hasOne(FwTreeNode::className(), ['kid' => 'tree_node_id'])
            ->onCondition([FwTreeNode::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwDomains()
    {
        return $this->hasMany(FwDomain::className(), ['company_id' => 'kid'])
            ->onCondition([FwDomain::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwOrgnizations()
    {
        return $this->hasMany(FwOrgnization::className(), ['company_id' => 'kid'])
            ->onCondition([FwOrgnization::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwPositions()
    {
        return $this->hasMany(FwPosition::className(), ['company_id' => 'kid'])
            ->onCondition([FwPosition::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwRoles()
    {
        return $this->hasMany(FwRole::className(), ['company_id' => 'kid'])
            ->onCondition([FwRole::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwTags()
    {
        return $this->hasMany(FwTag::className(), ['company_id' => 'kid'])
            ->onCondition([FwTag::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
