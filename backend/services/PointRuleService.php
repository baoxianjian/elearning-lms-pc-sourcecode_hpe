<?php


namespace backend\services;


use common\base\BaseActiveRecord;
use Yii;
use common\models\framework\FwPointRule;
use yii\data\ActiveDataProvider;
use common\services\framework\CompanyService;
use yii\db\Query;


class PointRuleService extends FwPointRule{

    /**
     * 搜索组件数据
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     
    public function Search($params)
    {
        $query = FwPointRule::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);



        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'point_name',  trim(urldecode($this->point_name))])
            ->andFilterWhere(['like', 'point_code',  trim(urldecode($this->point_code))]);



        $dataProvider->setSort(false);
      //  $query->addOrderBy(['sequence_number' => SORT_ASC]);

        return $dataProvider;
    }
    */
    
    /**
     * 搜索操作用户操作日志数据
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$parentNodeId)
    {
        $query =  FwPointRule::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        /*
        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        */



        if ($parentNodeId == '') {
            //$query->andWhere(BaseActiveRecord::getQuoteColumnName("company_id") . ' is null');
            $query->andFilterWhere(['=', 'company_id', 1]);   //点企业时不显示信息
            $companyId = null;
            return $dataProvider;
        } else {
            $companyService = new CompanyService();
            $companyId = $companyService->getCompanyIdByTreeNodeId($parentNodeId);

            $query->andFilterWhere(['=', 'company_id', $companyId]);
        }


             
        $query
            ->andFilterWhere(['like', 'point_code',  trim(urldecode($this->point_code))])
            ->andFilterWhere(['like', 'point_name',  trim(urldecode($this->point_name))])
            ->andFilterWhere(['=', 'status',  trim(urldecode($this->status))]);

//        $query->andFilterWhere(['=', '.company_id', $companyId]);

        if (!empty($companyId)) {
            if ($this->status != FwPointRule::STATUS_FLAG_STOP) {
                if ($query->count(1) == 0) {
                    $commonPointRuleService = new \common\services\framework\PointRuleService();
                    $commonPointRuleService->copyRuleAndScale($companyId);
                }
            }
        }
                
        
        /*
        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();

            $rbacService = new RbacService();

            if (!$rbacService->isSpecialUser($userId)) {
                $userOrgnizationService = new UserOrgnizationService();

                $selectedResult = $userOrgnizationService->getSearchListByUserId($userId);

                $orgnizationIdList = [];
                if (isset($selectedResult) && $selectedResult != null) {
                    $selectedList = ArrayHelper::map($selectedResult, 'kid', 'kid');

                    $orgnizationIdList = array_keys($selectedList);
                }

                $query->andFilterWhere(['in', FwUser::tableName() . '.orgnization_id', $orgnizationIdList]);
            }

        }
        */


//            ->andFilterWhere(['like', 'limitation', $this->limitation])
//            ->andFilterWhere(['like', 'code_gen_way', $this->code_gen_way])
//            ->andFilterWhere(['like', 'code_prefix', $this->code_prefix]);
//        $sort->attributes=['LPT_NAME'=> [
//            'asc' => ['LPT_NAME' => SORT_ASC],
//            'desc' => ['LPT_NAME' => SORT_DESC]]];

        $dataProvider->setSort(false);
//        $query->addOrderBy([FwTreeNode::tableName() . '.tree_level' => SORT_ASC]);
//        $query->addOrderBy([FwTreeNode::tableName() . '.parent_node_id' => SORT_ASC]);
//        $query->addOrderBy([FwTreeNode::tableName() . '.sequence_number' => SORT_ASC]);
        $query->addOrderBy([FwPointRule::tableName() .'.point_code' => SORT_ASC]);

        return $dataProvider;
    }
    
    
}