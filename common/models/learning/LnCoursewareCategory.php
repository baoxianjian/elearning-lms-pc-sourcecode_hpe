<?php

namespace common\models\learning;

use common\models\framework\FwCompany;
use common\models\treemanager\FwTreeNode;
use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_course_category}}".
 *
 * @property string $kid
 * @property string $tree_node_id
 * @property string $parent_category_id
 * @property string $company_id
 * @property string $category_code
 * @property string $category_name
 * @property string $share_flag
 * @property string $description
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
class LnCoursewareCategory extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_courseware_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tree_node_id', 'category_code', 'category_name'], 'required', 'on' => 'manage'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'tree_node_id', 'parent_category_id', 'company_id', 'category_code', 'category_name', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

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
            'kid' => Yii::t('common', 'category_id'),
            'tree_node_id' => Yii::t('common', 'tree_node_id'),
            'parent_category_id' => Yii::t('common', 'parent_category_id'),
            'company_id' => Yii::t('common', 'company_id'),
            'category_code' => Yii::t('common', 'category_code'),
            'category_name' => Yii::t('common', 'category_name'),
            'description' => Yii::t('common', 'description'),
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
    public function getFwTreeNode(){
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

}
