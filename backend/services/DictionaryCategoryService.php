<?php


namespace backend\services;

use common\base\BaseActiveRecord;
use Yii;
use common\models\framework\FwDictionaryCategory;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

class DictionaryCategoryService extends FwDictionaryCategory{

    /**
     * 获取字典分类数据
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = FwDictionaryCategory::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }



        $query->andFilterWhere(['like', 'cate_code',  trim(urldecode($this->cate_code))])
            ->andFilterWhere(['like', 'cate_name',  trim(urldecode($this->cate_name))]);
//            ->andFilterWhere(['like', 'limitation', $this->limitation])
//            ->andFilterWhere(['like', 'code_gen_way', $this->code_gen_way])
//            ->andFilterWhere(['like', 'code_prefix', $this->code_prefix]);
//        $sort->attributes=['LPT_NAME'=> [
//            'asc' => ['LPT_NAME' => SORT_ASC],
//            'desc' => ['LPT_NAME' => SORT_DESC]]];
        $dataProvider->setSort(false);
        $query->addOrderBy(['sequence_number' => SORT_ASC]);
//        $query->addOrderBy(['created_at' => SORT_DESC]);

        return $dataProvider;
    }


    /**
     * 获取最大序列号
     * @return int|mixed
     */
    public function findMaxSequenceNumber()
    {
        $model = new FwDictionaryCategory();
//        $Year = date("Y");
        $condition = [];
//        $condition = ['year(FROM_UNIXTIME(created_at))' => $Year];
        $maxSequenceNumber = $model->find(false)->andWhere($condition)->max(BaseActiveRecord::getQuoteColumnName("sequence_number"));
//            ->where('year(CREATE_DATE) = :year',[':year'=>$Year])
//            ->andWhere();


        if ($maxSequenceNumber != null)
        {
            $maxSequenceNumber = $maxSequenceNumber + 1;
        }
        else
        {
            $maxSequenceNumber = 1;
        }


        return $maxSequenceNumber;
    }


    /**
     * 更新序列号（自动排序）
     * @param $dictionaryId
     * @param $oldSequenceNumber
     * @param $newSequenceNumber
     * @param $flag
     */
    public function updateSequenceNumber($dictionaryId,$oldSequenceNumber,$newSequenceNumber,$flag)
    {
        if ($flag == '0') {
            //增加或更新时用
            if ($oldSequenceNumber == $newSequenceNumber) {
                return;
            } else if ($newSequenceNumber < $oldSequenceNumber) {
                $model = new FwDictionaryCategory();
                $query = $model->find(false);

                $query->andFilterWhere(['<>', 'kid', $dictionaryId])
                    ->andFilterWhere(['>=', 'sequence_number', $newSequenceNumber])
//                ->andFilterWhere(['<=', 'sequence_number', $newSequenceNumber])
                    ->addOrderBy(['sequence_number' => SORT_ASC]);

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
                $model = new FwDictionaryCategory();
                $query = $model->find(false);


                $query->andFilterWhere(['<>', 'kid', $dictionaryId])
                    ->andFilterWhere(['<=', 'sequence_number', $newSequenceNumber])
                    ->andFilterWhere(['>=', 'sequence_number', $oldSequenceNumber])
                    ->addOrderBy(['sequence_number' => SORT_ASC]);

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
        }
        else
        {
            //删除时用
            $model = new FwDictionaryCategory();
            $query = $model->find(false);


            $query->andFilterWhere(['<>', 'kid', $dictionaryId])
                ->andFilterWhere(['>=', 'sequence_number', $newSequenceNumber])
//                ->andFilterWhere(['>=', 'sequence_number', $oldSequenceNumber])
                ->addOrderBy(['sequence_number' => SORT_ASC]);

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
     * 判断是否存在相同的字典分类代码
     * @param $kid
     * @param $cateCode
     * @return bool
     */
    public function isExistSameDictionaryCategoryCode($kid, $cateCode)
    {
        $model = new FwDictionaryCategory();
        $query = $model->find(false);


        $count = $query->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'cate_code', $cateCode])
            ->count();


        if ($count > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }


    /**
     * 获取所有字典分类
     * @param string $type 字典分类类型 | null：所有；0：系统字典；1：企业字典
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAllDictionaryCategory($type = null)
    {
        $model = FwDictionaryCategory::find(false);

        if ($type !== null) {
            $model->andFilterWhere(['=', 'cate_type', $type]);
        }

        $query = $model
            ->andFilterWhere(['<>', 'limitation', FwDictionaryCategory::LIMITATION_HIDDEN])
            ->addOrderBy(['sequence_number' => SORT_ASC])
            ->all();

        return $query;
    }
}