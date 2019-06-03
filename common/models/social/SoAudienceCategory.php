<?php

namespace common\models\social;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%so_audience_category}}".
 *
 * @property string $kid
 * @property string $tree_node_id
 * @property string $parent_category_id
 * @property string $company_id
 * @property string $owner_id
 * @property string $category_code
 * @property string $category_name
 * @property string $description
 * @property string $status
 * @property integer $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $created_ip
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $updated_ip
 * @property string $is_deleted
 *
 * @property SoAudience[] $soAudiences
 */
class SoAudienceCategory extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%so_audience_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tree_node_id', 'owner_id', 'category_code', 'category_name'], 'required'],
            [['description'], 'string'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['tree_node_id', 'parent_category_id', 'company_id', 'owner_id', 'category_code', 'category_name', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['status', 'is_deleted'], 'string', 'max' => 1]
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
            'owner_id' => Yii::t('common', 'owner_id'),
            'category_code' => Yii::t('common', 'category_code'),
            'category_name' => Yii::t('common', 'category_name'),
            'description' => Yii::t('common', 'description'),
            'status' => Yii::t('common', 'status'),
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'created_ip' => Yii::t('common', 'created_ip'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'updated_ip' => Yii::t('common', 'updated_ip'),
            'is_deleted' => Yii::t('common', 'is_deleted'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSoAudiences()
    {
        return $this->hasMany(SoAudience::className(), ['category_id' => 'kid']);
    }
}
