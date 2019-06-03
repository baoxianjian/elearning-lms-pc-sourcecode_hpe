<?php

namespace common\models\framework;

use common\models\framework\FwCompany;
use common\services\framework\TagService;
use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%fw_tag}}".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $tag_category_id
 * @property string $tag_value
 * @property integer $reference_count
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property FwTagCategory $fwTagCategory
 * @property FwCompany $fwCompany
 * @property FwTagReference[] $fwTagReferences
 */
class FwTag extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_tag}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_category_id', 'tag_value'], 'required', 'on' => 'super_manage'],
            [['tag_category_id', 'company_id', 'tag_value'], 'required', 'on' => 'manage'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'company_id', 'tag_category_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['tag_value'], 'string', 'max' => 100],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['version','reference_count'], 'number'],
            [['version'], 'default', 'value'=> 1],
            [['reference_count'], 'default', 'value'=> 0],

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
            'kid' => Yii::t('common', 'tag_id'),
            'company_id' => Yii::t('common', 'company_id'),
            'tag_category_id' => Yii::t('common', 'tag_category_id'),
            'tag_value' => Yii::t('common', 'tag_value'),
            'reference_count' => Yii::t('common', 'reference_count'),
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

    public function getTagCategoryName()
    {
        $tagCategorModel = FwTagCategory::findOne($this->tag_category_id);

        if ($tagCategorModel != null)
            return $tagCategorModel->cate_name;
        else
            return "";
    }

    public function getCompanyName(){
        $companyModel = FwCompany::findOne($this->company_id);

        if ($companyModel != null)
            return $companyModel->company_name;
        else
            return "";
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwTagCategory()
    {
        return $this->hasOne(FwTagCategory::className(), ['kid' => 'tag_category_id'])
            ->onCondition([FwTagCategory::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
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
    public function getFwTagReferences()
    {
        return $this->hasMany(FwTagReference::className(), ['tag_id' => 'kid'])
            ->onCondition([FwTagReference::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /*课程问答复制课程标签*/
    public function setCopyTag($courseId, $questionId){
        $tagService = new TagService();
        $tagService->setCopyTag($courseId, $questionId);
    }

}
