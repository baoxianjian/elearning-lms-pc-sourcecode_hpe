<?php
/**
 * Created by PhpStorm.
 * User: t62539
 * Date: 3/3/2016
 * Time: 10:17 PM
 */


// deprecated by GROOT at 2016.04.28
namespace api\services;


use common\models\framework\FwDictionary;
use common\models\framework\FwDictionaryCategory;

class DictionaryService extends FwDictionaryCategory{

    /**
     * 获取所有字典类型
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getDictionaryCategory() {
        $model = new FwDictionaryCategory();
        $result = $model->find(false)
            ->addOrderBy(['sequence_number' => SORT_ASC])
            ->all();

        return $result;
    }

    /**
     * 根据字典分类代码获取字典列表
     * @param $categoryCode
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getDictionariesByCategory($categoryCode)
    {
        $dictionaryCategoryId = $this->getDictionaryCateIdByCateCode($categoryCode);

        if (!empty($dictionaryCategoryId)) {
            $query = FwDictionary::find(false);

            $result = $query
                ->andFilterWhere(['=', 'dictionary_category_id', $dictionaryCategoryId])
                ->addOrderBy(['sequence_number' => SORT_ASC])
                ->all();
            return $result;
        }
        else {
            return null;
        }
    }

    /**
     * 根据字典分类代码获取字典分类ID
     * @param $categoryCode
     * @return mixed|null
     */
    public function getDictionaryCateIdByCateCode($categoryCode){
        $model = FwDictionaryCategory::find(false);

        $result = $model
            ->andFilterWhere(['=','cate_code', $categoryCode])
            ->one();

        if (!empty($result))
            return $result->kid;
        else
            return null;
    }
}