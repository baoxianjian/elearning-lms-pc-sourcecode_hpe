<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 8/22/2015
 * Time: 10:23 PM
 */

namespace backend\services;


use common\models\framework\FwCompany;
use common\models\framework\FwCompanySystem;
use common\models\framework\FwExternalSystem;
use common\base\BaseActiveRecord;
use yii\data\ActiveDataProvider;

class ExternalSystemService extends FwExternalSystem
{
    /**
     * 搜索树类型数据
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = FwExternalSystem::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query
            ->andFilterWhere(['like', 'system_code',  trim(urldecode($this->system_code))])
            ->andFilterWhere(['like', 'system_name',  trim(urldecode($this->system_name))]);

        $dataProvider->setSort(false);
        $query->addOrderBy(['kid' => SORT_ASC]);

        return $dataProvider;
    }

    /**
     * @param null $companyId
     * @return array|FwExternalSystem
     */
    public function getExternalSystemByCompanyId($companyId = null) {

        if (empty($companyId))
        {
            $model = new FwExternalSystem();
            $result = $model->find(false)
                ->all();
        }
        else {
            $model = new FwExternalSystem();
            $result = $model->find(false)
                ->innerJoinWith('fwCompanySystems')
                ->andFilterWhere(['=','company_id',$companyId])
                ->andFilterWhere(['=',FwCompanySystem::realTableName() . '.status', FwCompanySystem::STATUS_FLAG_NORMAL])
                ->all();
        }

        return $result;
    }

    public function getCompanyListBySystemId($systemId) {

        $model = new FwCompanySystem();
        $result = $model->find(false)
            ->innerJoinWith('fwCompany')
            ->andFilterWhere(['=','system_id',$systemId])
            ->andFilterWhere(['=',FwCompany::realTableName() . '.status',FwCompany::STATUS_FLAG_NORMAL])
            ->all();

        return $result;
    }



    /**
     * 启用指定的企业外部系统
     * @param FwCompanySystem $targetModel
     */
    public function startRelationship(FwCompanySystem $targetModel)
    {
        if (isset($targetModel) &&  $targetModel != null)
        {
            $companySystemModel = new FwCompanySystem();
            $companySystemModel->company_id = $targetModel->company_id;
            $companySystemModel->system_id = $targetModel->system_id;
            $companySystemModel->status = self::STATUS_FLAG_NORMAL;
            $companySystemModel->start_at = time();

            if (!$this->isRelationshipExist($targetModel)) {
                $companySystemModel->save();
            }
        }
    }

    /**
     * 批量启用关系
     */
    public function batchStartRelationship($targetModels)
    {
        if (isset($targetModels) &&  $targetModels != null && count($targetModels) > 0)
        {
            BaseActiveRecord::batchInsertSqlArray($targetModels);
        }
    }

    /**
     * 根据列表批量停用关系
     */
    public function stopRelationshipBySystemId($systemId)
    {
        if (!empty($systemIds))
        {
            $sourceMode = new FwCompanySystem();

            $params = [
                ':system_id'=>$systemId,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("system_id") . ' = :system_id';

            $attributes = [
                'status' => self::STATUS_FLAG_STOP,
                'end_at' => time(),
            ];

//            if ($this->IsRelationshipExist($targetModel)) {
            $sourceMode->updateAll($attributes,$condition,$params);
//            }
        }
    }

    /**
     * 判断企业外部系统关系是否存在
     * @param FwCompanySystem $targetModel
     * @return bool
     */
    public function isRelationshipExist(FwCompanySystem $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $condition = [
                'status' => self::STATUS_FLAG_NORMAL,
                'company_id' => $targetModel->company_id,
                'system_id' => $targetModel->system_id
            ];
            $model = FwCompanySystem::findOne($condition);

            if ($model != null)
                return true;
            else
                return false;
        } else {
            return true;
        }
    }
}