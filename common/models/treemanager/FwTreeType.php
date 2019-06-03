<?php

namespace common\models\treemanager;

use Yii;
use common\base\BaseActiveRecord;
/**
 * This is the model class for table "{{%fw_tree_type}}".
 *
 * @property string $kid
 * @property string $tree_type_code
 * @property string $tree_type_name
 * @property string $code_gen_way
 * @property string $code_prefix
 * @property string $limitation
 * @property integer $max_level
 * @property integer $sequence_number
 * @property string $i18n_flag
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property FwTreeNode[] $fwTreeNodes
 */
class FwTreeType extends BaseActiveRecord
{
    const CODE_GEN_WAY_SYSTEM = "0";
    const CODE_GEN_WAY_MANUAL = "1";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_tree_type}}';
    }


    public function getCodeGenWayText()
    {
        $codeGenWay = $this->code_gen_way;
        if ($codeGenWay == self::CODE_GEN_WAY_SYSTEM)
            return Yii::t('common', 'code_gen_way_system');

        return Yii::t('common', 'code_gen_way_manual');
    }



    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tree_type_code', 'tree_type_name', 'limitation', 'code_gen_way', 'sequence_number'], 'required', 'on' => 'manage'],
            [['max_level', 'sequence_number', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'tree_type_code', 'code_prefix', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['tree_type_name','i18n_flag'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['sequence_number'], 'integer', 'min' => 1, 'max'=> 2147483647],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]],

            [['limitation'], 'string', 'max' => 1],
            [['limitation'], 'in', 'range' => [self::LIMITATION_NONE, self::LIMITATION_READONLY,
                self::LIMITATION_HIDDEN, self::LIMITATION_ONLYNAME]],

            [['code_gen_way'], 'string', 'max' => 1],
            [['code_gen_way'], 'in', 'range' => [self::CODE_GEN_WAY_SYSTEM, self::CODE_GEN_WAY_MANUAL]],

        ];
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'tree_type_id'),
            'tree_type_code' => Yii::t('common', 'tree_type_code'),
            'tree_type_name' => Yii::t('common', 'tree_type_name'),
            'code_gen_way' => Yii::t('common', 'code_gen_way'),
            'code_prefix' => Yii::t('common', 'code_prefix'),
            'limitation' => Yii::t('common', 'limitation'),
            'max_level' => Yii::t('common', 'max_level'),
            'sequence_number' => Yii::t('common', 'sequence_number'),
            'i18n_flag' => Yii::t('common', 'i18n_flag'),
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
     * 根据多国语言对应的名字
     * @return string
     */
    public function getI18nName()
    {
        if (!empty($this->i18n_flag)) {
            return Yii::t('data',$this->i18n_flag);
        }
        else {
            $this->tree_type_name;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwTreeNodes()
    {
        return $this->hasMany(FwTreeNode::className(), ['tree_type_id' => 'kid'])
            ->onCondition([FwTreeNode::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
