<?php


namespace backend\services;


use common\models\framework\FwActionLogFilter;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

class ActionLogFilterService extends FwActionLogFilter{

    /**
     * 搜索操作日志过滤器数据
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = FwActionLogFilter::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'filter_code',  trim(urldecode($this->filter_code))])
            ->andFilterWhere(['like', 'filter_name',  trim(urldecode($this->filter_name))]);
//            ->andFilterWhere(['like', 'limitation', $this->limitation])
        $dataProvider->setSort(false);
        $query->addOrderBy(['kid' => SORT_ASC]);
//        $query->addOrderBy(['created_at' => SORT_DESC]);

        return $dataProvider;
    }



    /**
     * 判断是否存在相同的过滤器代码
     * @param $kid
     * @param $treeTypeCode
     * @return bool
     */
    public function isExistSameFilterCode($kid, $filterCode)
    {
        $model = new FwActionLogFilter();
        $query = $model->find(false);


        $count = $query->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'filter_code', $filterCode])
            ->count(1);


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
     * 获取所有操作日志过滤器
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAllActionLogFilter()
    {
        $model = FwActionLogFilter::find(false);


        $query = $model
            ->andFilterWhere(['=','status',FwActionLogFilter::STATUS_FLAG_NORMAL])
            ->addOrderBy(['kid' => SORT_ASC])
            ->all();

        return $query;
    }

}