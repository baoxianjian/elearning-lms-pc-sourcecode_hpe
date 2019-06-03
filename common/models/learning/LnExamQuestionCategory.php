<?php

namespace common\models\learning;

use Yii;
use \common\base\BaseActiveRecord;
use common\models\treemanager\FwTreeNode;
use yii\caching\DbDependency;

/**
 * This is the model class for table "{{%ln_exam_question_category}}".
 *
 * @property string $kid
 * @property string $tree_node_id
 * @property string $parent_category_id
 * @property string $company_id
 * @property string $category_code
 * @property string $category_name
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
class LnExamQuestionCategory extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_exam_question_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tree_node_id', 'category_code', 'category_name'], 'required'],
            [['description'], 'string'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'tree_node_id', 'parent_category_id', 'company_id', 'category_code', 'category_name', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['status', 'is_deleted'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => 'Kid',
            'tree_node_id' => 'Tree Node ID',
            'parent_category_id' => 'Parent Category ID',
            'company_id' => 'Company ID',
            'category_code' => 'Category Code',
            'category_name' => 'Category Name',
            'description' => 'Description',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnExaminationQuestions()
    {
        return $this->hasMany(LnExaminationQuestion::className(), ['category_id' => 'kid'])
            ->onCondition([LnExaminationQuestion::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
