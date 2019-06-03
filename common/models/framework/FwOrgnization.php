<?php

namespace common\models\framework;

use common\models\treemanager\FwTreeNode;
use common\services\framework\DictionaryService;
use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_orgnization}}".
 *
 * @property string $kid
 * @property string $tree_node_id
 * @property string $parent_orgnization_id
 * @property string $company_id
 * @property string $domain_id
 * @property string $orgnization_name
 * @property string $orgnization_code
 * @property string $orgnization_level
 * @property string $orgnization_manager_id
 * @property string $is_make_org
 * @property string $is_service_site
 * @property string $description
 * @property string $data_from
 * @property string $is_default_orgnization
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
 * @property FwTreeNode $fwTreeNode
 */
class FwOrgnization extends BaseActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_orgnization}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tree_node_id','company_id','domain_id'], 'required', 'on' => 'manage'],
            [['company_id','orgnization_code', 'orgnization_name'], 'required', 'on' => 'api-manage-add'],
//            [['orgnization_code', 'company_id', 'is_deleted'], 'unique', 'on' => 'api-manage-add','message'=>Yii::t('common','unique_constraint_error')],
            [['company_id','orgnization_code', 'orgnization_name'], 'required', 'on' => 'api-manage-update'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'tree_node_id', 'parent_orgnization_id', 'company_id', 'domain_id',
                'orgnization_code', 'orgnization_name','created_by', 'updated_by', 'data_from','orgnization_level', 'orgnization_manager_id'], 'string', 'max' => 50],
            [['description'], 'string'],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['is_default_orgnization'], 'string', 'max' => 1],
            [['is_default_orgnization'], 'in', 'range' => [self::NO, self::YES]],
            [['is_default_orgnization'], 'default', 'value' => self::YES],

            [['is_make_org'], 'string', 'max' => 1],
            [['is_make_org'], 'in', 'range' => [self::NO, self::YES]],
            [['is_make_org'], 'default', 'value' => self::YES],

            [['is_service_site'], 'string', 'max' => 1],
            [['is_service_site'], 'in', 'range' => [self::NO, self::YES]],
            [['is_service_site'], 'default', 'value' => self::YES],

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
            'kid' => Yii::t('common', 'orgnization_id'),
            'tree_node_id' => Yii::t('common', 'tree_node_id'),
            'parent_orgnization_id' => Yii::t('common', 'parent_orgnization_id'),
            'company_id' => Yii::t('common', 'company_id'),
            'domain_id' => Yii::t('common', 'domain_id'),
            'orgnization_code' => Yii::t('common', 'orgnization_code'),
            'orgnization_name' => Yii::t('common', 'orgnization_name'),
            'description' => Yii::t('common', 'description'),
            'orgnization_manager_id' => Yii::t('common', 'orgnization_manager_id'),
            'is_make_org' => Yii::t('common', 'is_make_org'),
            'is_service_site' => Yii::t('common', 'is_service_site'),
            'is_default_orgnization' => Yii::t('common', 'is_default_orgnization'),
            'orgnization_level' => Yii::t('common', 'orgnization_level'),
            'data_from' => Yii::t('common', 'data_from'),
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
    public function getFwTreeNode()
    {
        return $this->hasOne(FwTreeNode::className(), ['kid' => 'tree_node_id'])
            ->onCondition([FwTreeNode::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }


    public function getCompanyName()
    {
        $companyModel = FwCompany::findOne($this->company_id);

        if ($companyModel != null)
            return $companyModel->company_name;
        else
            return "";
    }

    public function getDomainName()
    {
        $domainModel = FwDomain::findOne($this->domain_id);

        if ($domainModel != null)
            return $domainModel->domain_name;
        else
            return "";
    }

    public function getIsDefaultOrgnizationText()
    {
        if ($this->is_default_orgnization == self::NO)
            return Yii::t('common', 'no');
        else
            return Yii::t('common', 'yes');
    }

    public function getIsMakeOrgText()
    {
        if ($this->is_make_org == self::NO)
            return Yii::t('common', 'no');
        else
            return Yii::t('common', 'yes');
    }

    public function getIsServiceSiteText()
    {
        if ($this->is_service_site == self::NO)
            return Yii::t('common', 'no');
        else
            return Yii::t('common', 'yes');
    }

    public function getOrgnizationLevelName()
    {
        $dictionaryService = new DictionaryService();
        $orgLevel = $dictionaryService->getDictionaryNameByValue("orgnization_level",$this->orgnization_level,$this->company_id, false);
        if (!empty($orgLevel)) {
            return $orgLevel;
        }
        else {
            return $this->orgnization_level;
        }
    }

    public function getOrgnizationManagerName()
    {
        if (!empty($this->orgnization_manager_id)) {
            $orgnizationMangerModel = FwUser::findOne($this->orgnization_manager_id);
            if ($orgnizationMangerModel) {
                $managerName = $orgnizationMangerModel->getDisplayName();
                return $managerName;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
