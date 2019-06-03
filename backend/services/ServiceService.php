<?php


namespace backend\services;

use common\models\framework\FwService;
use Yii;

use yii\data\ActiveDataProvider;
use yii\helpers\Html;

class ServiceService extends FwService{


    /**
     * 获取服务列表
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getServiceList()
    {
        $query = FwService::find(false);
        $result = $query->all();

        return $result;
    }

    /**
     * 搜索服务记录
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = FwService::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

  //      if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
      //      return $dataProvider;
    //    }

        $query
            ->andFilterWhere(['like', 'service_code',  trim(urldecode($this->service_code))])
            ->andFilterWhere(['like', 'service_name',  trim(urldecode($this->service_name))])
           // ->andFilterWhere(['like', 'service_code',  trim(urldecode($this->service_code))])
            ->andFilterWhere(['=', 'service_status',  trim(urldecode($this->service_status))]);
        $dataProvider->setSort(false);
        $query->addOrderBy(['created_at' => SORT_ASC]);
        return $dataProvider;
    }

    /**
     * 判断是否存在相同的服务代码
     * @param $kid
     * @param $serviceCode
     * @return bool
     */
    public function isExistSameServiceCode($kid, $serviceCode)
    {
        $model = new FwService();
        $query = $model->find(false);


        $count = $query->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'service_code', $serviceCode])
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

}