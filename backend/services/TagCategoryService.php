<?php


namespace backend\services;

use common\base\BaseActiveRecord;
use Yii;
use common\models\framework\FwTagCategory;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

class TagCategoryService extends FwTagCategory{

    /**
     * 搜索字典分类数据
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = FwTagCategory::find(false);

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
        $model = new FwTagCategory();
//        $Year = date("Y");
        $condition = [];
//        $condition = ['year(FROM_UNIXTIME(created_at))' => $Year];
        $maxSequenceNumber = $model->find(false)->andWhere($condition)->max(BaseActiveRecord::getQuoteColumnName("sequence_number"));
//            ->where('year(CREATE_DATE) = :year',[':year'=>$Year])
//            ->andWhere();


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
                $model = new FwTagCategory();
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
                $model = new FwTagCategory();
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
            $model = new FwTagCategory();
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
    public function isExistSameTagCategoryCode($kid, $cateCode)
    {
        $model = new FwTagCategory();
        $query = $model->find(false);


        $count = $query->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'cate_code', $cateCode])
            ->count(1);


        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 获取全部字典分类
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAllTagCategory()
    {
        $model = FwTagCategory::find(false);

        $query = $model
            ->andFilterWhere(['<>','limitation',FwTagCategory::LIMITATION_HIDDEN])
            ->addOrderBy(['sequence_number' => SORT_ASC])
            ->all();

        return $query;
    }
}