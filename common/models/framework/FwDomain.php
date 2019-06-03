<?php

namespace common\models\framework;

use common\models\treemanager\FwTreeNode;
use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_domain}}".
 *
 * @property string $kid
 * @property string $tree_node_id
 * @property string $parent_domain_id
 * @property string $company_id
 * @property string $domain_name
 * @property string $domain_code
 * @property string $description
 * @property string $share_flag
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
 * @property FwTreeNode $fwTreeNode
 */
class FwDomain extends BaseActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_domain}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tree_node_id','company_id'], 'required', 'on' => 'manage'],
            [['domain_code', 'company_id'], 'required', 'on' => 'api-manage-add'],
//            [['domain_code', 'company_id', 'is_deleted'], 'unique', 'on' => 'api-manage-add','message'=>Yii::t('common','unique_constraint_error')],
            [['domain_code', 'company_id'], 'required', 'on' => 'api-manage-update'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'tree_node_id', 'parent_domain_id', 'company_id', 'data_from', 'domain_code', 'domain_name', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['description'], 'string'],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

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
            'kid' => Yii::t('common', 'domain_id'),
            'tree_node_id' => Yii::t('common', 'tree_node_id'),
            'parent_domain_id' => Yii::t('common', 'parent_domain_id'),
            'company_id' => Yii::t('common', 'company_id'),
            'domain_code' => Yii::t('common', 'domain_code'),
            'domain_name' => Yii::t('common', 'domain_name'),
            'description' => Yii::t('common', 'description'),
            'share_flag' => Yii::t('common', 'share_flag'),
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
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwCompany()
    {
        return $this->hasOne(FwCompany::className(), ['kid' => 'company_id'])
            ->onCondition([FwCompany::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
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
