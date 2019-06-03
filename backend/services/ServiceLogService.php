<?php


namespace backend\services;


use common\models\framework\FwServiceLog;
use Yii;
use yii\data\ActiveDataProvider;

class ServiceLogService extends FwServiceLog
{

    public $service_id;
    public $service_log;
    public $action_status;
    public $action_start_at;
    public $action_end_at;

    public function rules()
    {
        return [
            [['service_log'], 'string'],
            [['service_id'], 'string', 'max' => 50],
            [['action_status'], 'string', 'max' => 1],
            [['action_start_at', 'action_end_at'], 'date'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'service_id' => Yii::t('common', 'service_name'),
            'service_log' => Yii::t('common', 'service_log'),
            'action_status' => Yii::t('common', 'action_status'),
            'action_start_at' => Yii::t('common', 'action_start_at'),
            'action_end_at' => Yii::t('common', 'action_end_at'),
        ];
    }

    /**
     * 搜索日志数据
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = FwServiceLog::find(false);


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query
            ->joinWith('fwService')
            ->andFilterWhere(['like', 'service_log', trim(urldecode($this->service_log))])
            ->andFilterWhere(['=', 'service_id', $this->service_id])
            ->andFilterWhere(['=', 'action_status', $this->action_status]);

        if ($this->action_start_at) {
            $query->andFilterWhere(['>=', FwServiceLog::realTableName() . '.created_at', strtotime($this->action_start_at)]);
        }
        if ($this->action_end_at) {
            $query->andFilterWhere(['<=', FwServiceLog::realTableName() . '.created_at', strtotime($this->action_end_at . ' 23:59:59')]);
        }

        $dataProvider->setSort(false);

        $query->addOrderBy([FwServiceLog::realTableName() . '.created_at' => SORT_DESC]);

        return $dataProvider;
    }


}