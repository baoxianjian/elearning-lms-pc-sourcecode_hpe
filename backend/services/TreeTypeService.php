<?php


namespace backend\services;


use common\base\BaseActiveRecord;
use Yii;
use common\models\treemanager\FwTreeType;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

class TreeTypeService extends FwTreeType{

    /**
     * 搜索树类型数据
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = FwTreeType::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }



        $query->andFilterWhere(['like', 'tree_type_code',  trim(urldecode($this->tree_type_code))])
            ->andFilterWhere(['like', 'tree_type_name',  trim(urldecode($this->tree_type_name))])
            ->andWhere(['<>', 'limitation',  self::LIMITATION_HIDDEN]);
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
     * 获取最大的序列号
     * @return int|mixed
     */
    public function findMaxSequenceNumber()
    {
        $model = new FwTreeType();
//        $Year = date("Y");
        $condition = [];
//        $condition = ['year(FROM_UNIXTIME(created_at))' => $Year];
        $maxSequenceNumber = $model->find(false)
            ->max(BaseActiveRecord::getQuoteColumnName("sequence_number"));
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
     * @param $treeTypeId
     * @param $oldSequenceNumber
     * @param $newSequenceNumber
     * @param $flag
     */
    public function updateSequenceNumber($treeTypeId,$oldSequenceNumber,$newSequenceNumber,$flag)
    {
        if ($flag == '0') {
            //增加或更新时用
            if ($oldSequenceNumber == $newSequenceNumber) {
                return;
            } else if ($newSequenceNumber < $oldSequenceNumber) {
                $model = new FwTreeType();
                $query = $model->find(false);

                $query->andFilterWhere(['<>', 'kid', $treeTypeId])
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
                $model = new FwTreeType();
                $query = $model->find(false);


                $query->andFilterWhere(['<>', 'kid', $treeTypeId])
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
        } else {
            //删除时用
            $model = new FwTreeType();
            $query = $model->find(false);


            $query->andFilterWhere(['<>', 'kid', $treeTypeId])
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
     * 判断是否存在相同的树类型代码
     * @param $kid
     * @param $treeTypeCode
     * @return bool
     */
    public function isExistSameTreeTypeCode($kid, $treeTypeCode)
    {
        $model = new FwTreeType();
        $query = $model->find(false);


        $count = $query->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'tree_type_code', $treeTypeCode])
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
}