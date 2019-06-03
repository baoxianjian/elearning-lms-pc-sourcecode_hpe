<?php


namespace common\services\framework;

use common\models\framework\FwDictionaryCategory;
use common\base\BaseActiveRecord;
use Yii;
use common\models\framework\FwDictionary;
use yii\data\ActiveDataProvider;

class DictionaryService extends FwDictionary{

    /**
     * 根据字典分类代码获取字典分类ID
     * @param $categoryCode
     * @return string
     */
    public function getDictionaryCateIdByCateCode($categoryCode, $withCache = true)
    {
        $cacheKey = "DictionaryCategory_Code_" . $categoryCode;

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $model = FwDictionaryCategory::find(false);

            $result = $model
                ->andFilterWhere(['=','cate_code', $categoryCode])
                ->one();

//            $dependencySql = "SELECT * FROM " . self::calculateTableName(FwActionLogFilter::tableName()) . " WHERE filter_code = '" . $filterCode . "' and is_deleted ='0'";

            self::saveToCache($cacheKey, $result);
        }

        if (!empty($result))
            return $result->kid;
        else
            return null;
    }

    /**
     * 根据字典分类代码获取字典列表
     * @param $categoryCode
     * @param $companyId
     * @param $withCache
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getDictionariesByCategory($categoryCode, $companyId = null, $withCache = true)
    {
        $dictionaryCategoryId = $this->getDictionaryCateIdByCateCode($categoryCode);
        $cacheKey = "DictionaryList_CateId_" . $dictionaryCategoryId . '_ComId_' . $companyId;

        $result = BaseActiveRecord::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            if (isset($dictionaryCategoryId) && $dictionaryCategoryId != null) {
                $query = FwDictionary::find(false);

                $result = $query->andWhere(['=', 'status', self::STATUS_FLAG_NORMAL])
                    ->andFilterWhere(['=', 'dictionary_category_id', $dictionaryCategoryId])
                    ->andFilterWhere(['=', 'company_id', $companyId])
                    ->addOrderBy(['sequence_number' => SORT_ASC])
                    ->all();

                self::saveToCache($cacheKey, $result);
            }
            else {
                $result = null;
            }
        }

        if (!empty($result) && count($result) > 0) {
            foreach ($result as $single) {
                if (!empty($single->i18n_flag)) {
                    $single->dictionary_name_i18n = Yii::t('data', $single->i18n_flag);
                }
                else {
                    $single->dictionary_name_i18n = $single->dictionary_name;
                }

            }
        }
        return $result;
    }


    /**
     * 根据字典代码获取字典值
     * @param $categoryCode
     * @param $dictionaryCode
     * @return mixed|null
     */
    public function getDictionaryValueByCode($categoryCode,$dictionaryCode, $withCache = true)
    {
        $dictionaryCategoryId = $this->getDictionaryCateIdByCateCode($categoryCode);
        $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicCode_" . $dictionaryCode;

        $result = BaseActiveRecord::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $dictionaryCategoryId = $this->getDictionaryCateIdByCateCode($categoryCode);

            $result = null;
            if (isset($dictionaryCategoryId) && $dictionaryCategoryId != null) {

                $query = FwDictionary::find(false);

                $result = $query->andWhere(['=', 'status', self::STATUS_FLAG_NORMAL])
                    ->andFilterWhere(['=', 'dictionary_category_id', $dictionaryCategoryId])
                    ->andFilterWhere(['=', 'dictionary_code', $dictionaryCode])
                    ->one();
            }

            BaseActiveRecord::saveToCache($cacheKey, $result);
        }


        if (!empty($result)) {
            return $result->dictionary_value;
        }
        else {
            return null;
        }   
    }


    /**
     * 根据字典代码获取字典名
     * @param $categoryCode
     * @param $dictionaryCode
     * @return mixed|null
     */
    public function getDictionaryNameByCode($categoryCode,$dictionaryCode,$i18n = true, $withCache = true)
    {
        if (empty($dictionaryCode)) {
            return null;
        }
        else {
            $dictionaryCategoryId = $this->getDictionaryCateIdByCateCode($categoryCode);
            $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicCode_" . $dictionaryCode;

            $result = BaseActiveRecord::loadFromCache($cacheKey, $withCache, $hasCache);

            if (empty($result) && !$hasCache) {

                $result = null;
                if (isset($dictionaryCategoryId) && $dictionaryCategoryId != null) {
                    $query = FwDictionary::find(false);

                    $result = $query->andWhere(['=', 'status', self::STATUS_FLAG_NORMAL])
                        ->andFilterWhere(['=', 'dictionary_category_id', $dictionaryCategoryId])
                        ->andFilterWhere(['=', 'dictionary_code', $dictionaryCode])
                        ->one();

                }
                BaseActiveRecord::saveToCache($cacheKey, $result);
            }

            if (!empty($result)) {
                if ($i18n) {
                    if (!empty($result->i18n_flag)) {
                        return Yii::t('data', $result->i18n_flag);
                    } else {
                        return $result->dictionary_name;
                    }
                } else {
                    return $result->dictionary_name;
                }
            } else {
                return null;
            }
        }
    }

    /**
     * 根据字典代码获取字典Id
     * @param $categoryCode
     * @param $dictionaryCode
     * @return mixed|null
     */
    public function getDictionaryIdByCode($categoryCode,$dictionaryCode, $withCache = true)
    {
        $dictionaryCategoryId = $this->getDictionaryCateIdByCateCode($categoryCode);
        $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicCode_" . $dictionaryCode;

        $result = BaseActiveRecord::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $dictionaryCategoryId = $this->getDictionaryCateIdByCateCode($categoryCode);

            $result = null;
            if (isset($dictionaryCategoryId) && $dictionaryCategoryId != null) {

                $query = FwDictionary::find(false);

                $result = $query->andWhere(['=', 'status', self::STATUS_FLAG_NORMAL])
                    ->andFilterWhere(['=', 'dictionary_category_id', $dictionaryCategoryId])
                    ->andFilterWhere(['=', 'dictionary_code', $dictionaryCode])
                    ->one();
            }

            BaseActiveRecord::saveToCache($cacheKey, $result);
        }


        if (!empty($result)) {
            return $result->kid;
        }
        else {
            return null;
        }
    }

    /**
     * 根据字典值获取字典名
     * @param $categoryCode
     * @param $dictionaryValue
     * @param $companyId
     * @param $i18n
     * @param $withCache
     * @return mixed|null
     */
    public function getDictionaryNameByValue($categoryCode, $dictionaryValue, $companyId = null, $i18n = true, $withCache = true)
    {
        if (empty($dictionaryValue)) {
            return null;
        }
        else {
            $dictionaryCategoryId = $this->getDictionaryCateIdByCateCode($categoryCode);
            $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicValue_" . $dictionaryValue.'_ComId_'.$companyId;

            $result = BaseActiveRecord::loadFromCache($cacheKey, $withCache, $hasCache);

            if (empty($result) && !$hasCache) {

                $result = null;
                if (isset($dictionaryCategoryId) && $dictionaryCategoryId != null) {

                    $query = FwDictionary::find(false);

                    $result = $query->andWhere(['=', 'status', self::STATUS_FLAG_NORMAL])
                        ->andFilterWhere(['=', 'dictionary_category_id', $dictionaryCategoryId])
                        ->andFilterWhere(['=', 'dictionary_value', $dictionaryValue])
                        ->andFilterWhere(['=', 'company_id', $companyId])
                        ->one();
                }
                BaseActiveRecord::saveToCache($cacheKey, $result);
            }

            if (!empty($result)) {
                if ($i18n) {
                    if (!empty($result->i18n_flag)) {
                        return Yii::t('data', $result->i18n_flag);
                    } else {
                        return $result->dictionary_name;
                    }
                } else {
                    return $result->dictionary_name;
                }
            } else {
                return null;
            }
        }
    }

    /**
     * 根据字典值获取字典ID
     * @param $categoryCode
     * @param $dictionaryValue
     * @param $withCache
     * @return mixed|null
     */
    public function getDictionaryIdByValue($categoryCode,$dictionaryValue, $withCache = true)
    {
        if (empty($dictionaryValue)) {
            return null;
        }
        else {
            $dictionaryCategoryId = $this->getDictionaryCateIdByCateCode($categoryCode);
            $cacheKey = "Dictionary_CateId_" . $dictionaryCategoryId . "_DicValue_" . $dictionaryValue;

            $result = BaseActiveRecord::loadFromCache($cacheKey, $withCache, $hasCache);

            if (empty($result) && !$hasCache) {

                $result = null;
                if (isset($dictionaryCategoryId) && $dictionaryCategoryId != null) {

                    $query = FwDictionary::find(false);

                    $result = $query->andWhere(['=', 'status', self::STATUS_FLAG_NORMAL])
                        ->andFilterWhere(['=', 'dictionary_category_id', $dictionaryCategoryId])
                        ->andFilterWhere(['=', 'dictionary_value', $dictionaryValue])
                        ->one();
                }
                BaseActiveRecord::saveToCache($cacheKey, $result);
            }

            if (!empty($result)) {
                return $result->kid;
            } else {
                return null;
            }
        }
    }

    /*根据分类代码生成数组*/
    public function getDictionaryArray($categoryCode){
        $list = $this->getDictionariesByCategory($categoryCode);
        $array = array();
        if (!empty($list)){
            foreach ($list as $val){
                $array[$val->dictionary_value] = $val->dictionary_name_i18n;
            }
        }
        return $array;
    }
}