<?php

namespace common\models\treemanager;

use Yii;
use common\base\BaseActiveRecord;
/**
 * This is the model class for table "{{%fw_tree_node}}".
 *
 * @property string $kid
 * @property string $tree_type_id
 * @property string $tree_node_code
 * @property string $tree_node_name
 * @property string $node_id_path
 * @property string $node_name_path
 * @property string $node_code_path
 * @property string $parent_node_id
 * @property string $root_node_id
 * @property integer $tree_level
 * @property string $status
 * @property integer $sequence_number
 * @property integer $display_number
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property FwTreeType $fwTreeType
 */
class FwTreeNode extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_tree_node}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tree_type_id', 'tree_node_code', 'tree_node_name', 'sequence_number'], 'required', 'on' => 'manage'],
            [['tree_level', 'sequence_number', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'tree_type_id', 'tree_node_code', 'parent_node_id', 'root_node_id', 'created_by', 'updated_by',
                'tree_node_name'], 'string', 'max' => 50],
            [['node_id_path', 'node_name_path', 'node_code_path'], 'string', 'max' => 5000],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['sequence_number'], 'integer', 'min' => 1, 'max'=> 2147483647],
            ['display_number', 'integer'],

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
        if (!empty($this->tree_type_id)) {
            $treeTypeModel = FwTreeType::findOne($this->tree_type_id);
        }
        else {
            $treeTypeModel = null;
        }

        if ($treeTypeModel != null)
        {
            $treeNodeCode = Yii::t('common', '{value}_tree_node_code',['value'=>Yii::t('backend',$treeTypeModel->tree_type_code)]);
            $treeNodeName = Yii::t('common', '{value}_tree_node_name',['value'=>Yii::t('backend',$treeTypeModel->tree_type_code)]);
        }
        else
        {
            $treeNodeCode = Yii::t('common', 'tree_node_code');
            $treeNodeName = Yii::t('common', 'tree_node_name');
        }

        return [
            'kid' => Yii::t('common', 'tree_node_id'),
            'tree_type_id' => Yii::t('common', 'tree_type_id'),
            'tree_node_code' => $treeNodeCode,
            'tree_node_name' => $treeNodeName,
            'node_id_path' => Yii::t('common', 'node_id_path'),
            'node_name_path' => Yii::t('common', 'node_name_path'),
            'node_code_path' => Yii::t('common', 'node_code_path'),
            'parent_node_id' => Yii::t('common', 'parent_node_id'),
            'root_node_id' => Yii::t('common', 'root_node_id'),
            'tree_level' => Yii::t('common', 'tree_level'),
            'status' => Yii::t('common', 'status'),
            'display_number' => Yii::t('common', 'display_number'),
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



    public function getParentNodeText()
    {
        $parentNodeId = $this->parent_node_id;
        if ($parentNodeId == null || $parentNodeId == '')
            return "";
        else
        {
            return $this->findOne($parentNodeId)->tree_node_name;
        }
    }

    public function getRootNodeText()
    {
        $rootNodeId = $this->root_node_id;
        if ($rootNodeId == null || $rootNodeId == '')
            return "";
        else
        {
            return $this->findOne($rootNodeId)->tree_node_name;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwTreeType()
    {
        return $this->hasOne(FwTreeType::className(), ['kid' => 'tree_type_id'])
            ->onCondition([FwTreeType::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

}
