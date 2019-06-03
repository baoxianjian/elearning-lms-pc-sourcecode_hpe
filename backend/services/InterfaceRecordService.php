<?php


namespace backend\services;


use Yii;

use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use common\models\boe\BoeInterfaceRecord;

class InterfaceRecordService extends BoeInterfaceRecord{

    /**
     * 搜索接口调用记录
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = BoeInterfaceRecord::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        
       $this->bo_type=$params['InterfaceRecordService']['bo_type'];
       $this->change_type=$params['InterfaceRecordService']['change_type'];
       $this->handle_result=$params['InterfaceRecordService']['handle_result'];
       $this->operate_time=$params['InterfaceRecordService']['operate_time'];
       $this->kid=$params['InterfaceRecordService']['kid'];
       // $this->load($params);
        
        $query->andFilterWhere(['=', 'bo_type', $this->bo_type])
           // ->andFilterWhere(['like', 'service_code',  trim(urldecode($this->service_code))])
       		->andFilterWhere(['=', 'kid',$this->kid])
       	    ->andFilterWhere(['=', 'handle_result',$this->handle_result])
       	    ->andFilterWhere(['=', 'operate_time', $this->operate_time])
            ->andFilterWhere(['=', 'change_type', $this->change_type]);
        
        $dataProvider->setSort(false);
        $query->addOrderBy(['created_at' => SORT_DESC]);
        return $dataProvider;
    }
   

}