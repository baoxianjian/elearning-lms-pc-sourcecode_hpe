<?php


namespace backend\services;

use common\models\framework\FwDictionary;
use common\models\framework\FwDictionaryCategory;
use common\base\BaseActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

class DictionaryService extends FwDictionary
{

    /**
     * 搜索字典数据
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = FwDictionary::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        $query
            ->innerJoinWith('fwDictionaryCategory')
            ->andFilterWhere(['like', 'dictionary_code', trim(urldecode($this->dictionary_code))])
            ->andFilterWhere(['like', 'dictionary_name', trim(urldecode($this->dictionary_name))])
            ->andFilterWhere(['like', 'dictionary_category_id', $this->dictionary_category_id])
            ->andFilterWhere(['=', 'cate_type', FwDictionaryCategory::CATE_TYPE_SYSTEM]);
//            ->andFilterWhere(['like', 'code_gen_way', $this->code_gen_way])
//            ->andFilterWhere(['like', 'code_prefix', $this->code_prefix]);
//        $sort->attributes=['LPT_NAME'=> [
//            'asc' => ['LPT_NAME' => SORT_ASC],
//            'desc' => ['LPT_NAME' => SORT_DESC]]];
        $dataProvider->setSort(false);
        $query->addOrderBy([FwDictionaryCategory::realTableName() . '.sequence_number' => SORT_ASC]);
        $query->addOrderBy([FwDictionary::realTableName() . '.sequence_number' => SORT_ASC]);
//        $query->addOrderBy(['created_at' => SORT_DESC]);

        return $dataProvider;
    }


    /**
     * 获取最大序列号
     * @param $dictionaryCategoryId
     * @param $companyId
     * @return int|mixed
     */
    public function findMaxSequenceNumber($dictionaryCategoryId, $companyId = null)
    {
        $query = FwDictionary::find(false);
        $query->andFilterWhere(['=', 'dictionary_category_id', $dictionaryCategoryId]);

        if ($companyId) {
            $query->andFilterWhere(['=', 'company_id', $companyId]);
        } else {
            $query->andWhere('company_id is null');
        }

        $maxSequenceNumber = $query->max(BaseActiveRecord::getQuoteColumnName("sequence_number"));

        if ($maxSequenceNumber != null) {
            $maxSequenceNumber = $maxSequenceNumber + 1;
        } else {
            $maxSequenceNumber = 1;
        }

        return $maxSequenceNumber;
    }


    /**
     * 更新序列号（自动排序）
     * @param $dictionaryId
     * @param $dictionaryCategoryId
     * @param $oldSequenceNumber
     * @param $newSequenceNumber
     * @param $flag
     * @param $companyId
     */
    public function updateSequenceNumber($dictionaryId, $dictionaryCategoryId, $oldSequenceNumber, $newSequenceNumber, $flag, $companyId = null)
    {
        if ($flag == '0') {
            //增加或更新时用
            if ($oldSequenceNumber == $newSequenceNumber) {
                return;
            } else if ($newSequenceNumber < $oldSequenceNumber) {
                $model = new FwDictionary();
                $query = $model->find(false);

                $query->andFilterWhere(['<>', 'kid', $dictionaryId])
                    ->andFilterWhere(['=', 'dictionary_category_id', $dictionaryCategoryId])
                    ->andFilterWhere(['>=', 'sequence_number', $newSequenceNumber])
//                ->andFilterWhere(['<=', 'sequence_number', $newSequenceNumber])
                    ->addOrderBy(['sequence_number' => SORT_ASC]);

                if ($companyId) {
                    $query->andFilterWhere(['=', 'company_id', $companyId]);
                } else {
                    $query->andWhere('company_id is null');
                }
                $results = $query->all();

                $tempSequenceNumber = $newSequenceNumber + 1;
                if (isset($results) && count($results) > 0) {
                    $updateModels = [];
                    foreach ($results as $data) {
                        if ($data->sequence_number <> $tempSequenceNumber) {
                            $data->sequence_number = $tempSequenceNumber;
//                            $data->save();
                            array_push($updateModels, $data);
                        }
                        $tempSequenceNumber = $tempSequenceNumber + 1;
                    }

                    BaseActiveRecord::batchUpdateNormalMode($updateModels);
                }
            } else if ($newSequenceNumber > $oldSequenceNumber) {
                $model = new FwDictionary();
                $query = $model->find(false);


                $query->andFilterWhere(['<>', 'kid', $dictionaryId])
                    ->andFilterWhere(['=', 'dictionary_category_id', $dictionaryCategoryId])
                    ->andFilterWhere(['<=', 'sequence_number', $newSequenceNumber])
                    ->andFilterWhere(['>=', 'sequence_number', $oldSequenceNumber])
                    ->addOrderBy(['sequence_number' => SORT_ASC]);

                if ($companyId) {
                    $query->andFilterWhere(['=', 'company_id', $companyId]);
                } else {
                    $query->andWhere('company_id is null');
                }
                $results = $query->all();

                $tempSequenceNumber = $oldSequenceNumber;
                if (isset($results) && count($results) > 0) {
                    $updateModels = [];
                    foreach ($results as $data) {
                        if ($data->sequence_number <> $tempSequenceNumber) {
                            $data->sequence_number = $tempSequenceNumber;
//                            $data->save();
                            array_push($updateModels, $data);
                        }
                        $tempSequenceNumber = $tempSequenceNumber + 1;
                    }

                    BaseActiveRecord::batchUpdateNormalMode($updateModels);
                }
            }
        } else {
            //删除时用
            $model = new FwDictionary();
            $query = $model->find(false);


            $query->andFilterWhere(['<>', 'kid', $dictionaryId])
                ->andFilterWhere(['=', 'dictionary_category_id', $dictionaryCategoryId])
                ->andFilterWhere(['>=', 'sequence_number', $newSequenceNumber])
//                ->andFilterWhere(['>=', 'sequence_number', $oldSequenceNumber])
                ->addOrderBy(['sequence_number' => SORT_ASC]);

            if ($companyId) {
                $query->andFilterWhere(['=', 'company_id', $companyId]);
            } else {
                $query->andWhere('company_id is null');
            }
            $results = $query->all();


            $tempSequenceNumber = $newSequenceNumber;
            if (isset($results) && count($results) > 0) {
                $updateModels = [];
                foreach ($results as $data) {
                    if ($data->sequence_number <> $tempSequenceNumber) {
                        $data->sequence_number = $tempSequenceNumber;
//                        $data->save();
                        array_push($updateModels, $data);
                    }
                    $tempSequenceNumber = $tempSequenceNumber + 1;
                }

                BaseActiveRecord::batchUpdateNormalMode($updateModels);
            }
        }
    }


    /**
     * 判断是否存在相同的字典代码
     * @param $kid
     * @param $dictionaryCategoryId
     * @param $dictionaryCode
     * @param $companyId
     * @return bool
     */
    public function isExistSameDictionaryCode($kid, $dictionaryCategoryId, $dictionaryCode, $companyId = null)
    {
        $model = new FwDictionary();
        $query = $model->find(false);


        $query->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'dictionary_code', $dictionaryCode])
            ->andFilterWhere(['=', 'dictionary_category_id', $dictionaryCategoryId]);

        if ($companyId !== null) {
            $query->andFilterWhere(['=', 'company_id', $companyId]);
        }

        $count = $query->count();

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 改变列表相关状态
     * @param $kids
     */
    public function changeStatusByKidList($kids, $status)
    {
        if (!empty($kids)) {
            $sourceMode = new FwDictionary();


            $attributes = [
                'status' => $status,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $kids . ')';


            $sourceMode->updateAll($attributes, $condition);
        }
    }
}