<?php


namespace backend\services;


use common\models\framework\FwCompany;
use common\models\framework\FwWechatTemplate;
use common\models\treemanager\FwTreeNode;
use common\services\framework\RbacService;
use common\services\framework\UserCompanyService;
use common\base\BaseActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class WechatTemplateService extends FwWechatTemplate{

    /**
     * 搜索数据列表
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$parentNodeId,$includeSubNode)
    {
        $query = FwWechatTemplate::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->innerJoin(FwCompany::realTableName(),
                FwWechatTemplate::tableName() . "." . self::getQuoteColumnName("company_id") . " = " . FwCompany::tableName() . "." . self::getQuoteColumnName("kid") )
            ->innerJoin(FwTreeNode::realTableName(),
                FwCompany::tableName() . "." . self::getQuoteColumnName("tree_node_id") . " = " . FwTreeNode::tableName() . "." . self::getQuoteColumnName("kid") )
            ->andFilterWhere(['like', 'template_code', trim(urldecode($this->template_code))])
            ->andFilterWhere(['like', 'template_name', trim(urldecode($this->template_name))])
            ->andFilterWhere(['=', FwWechatTemplate::realTableName(). '.status', $this->status]);

        if ($includeSubNode == '1') {
            if ($parentNodeId != '') {
                $treeNodeModel = FwTreeNode::findOne($parentNodeId);
                $nodeIdPath = $treeNodeModel->node_id_path . $parentNodeId . "/%";

                $condition = ['or',
                    BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'",
                    ['=', FwCompany::realTableName() . '.tree_node_id', $parentNodeId]];
            } else {
                $condition = ['or',
                    BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '/%'",
                    BaseActiveRecord::getQuoteColumnName("company_id") . ' is null'];
            }

//            $treeNodeService = new TreeNodeService();
//            $treeTypeId = $treeNodeService->getTreeTypeId('company');
//            $query->andFilterWhere(['=','tree_type_id',$treeTypeId]);

            $query->andFilterWhere($condition);
//            $query->andWhere(BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'");
        }
        else
        {
            if ($parentNodeId == '') {
                $query->andWhere(BaseActiveRecord::getQuoteColumnName("company_id") . ' is null');
            } else {
                $query->andFilterWhere(['=', FwCompany::realTableName() . '.tree_node_id', $parentNodeId]);
            }
        }
        
//        if (is_array($treeNodeIdList)) {
//            if (in_array('', $treeNodeIdList)) {
////                $condition = ;
//
//                $condition = ['or',
//                    [ 'in',FwCompany::tableName() . '.tree_node_id',$treeNodeIdList],
//                    'company_id is null'];
//                $query->andFilterWhere($condition);
//            }
//            else
//            {
//                $condition = [ 'in',FwCompany::tableName() . '.tree_node_id',$treeNodeIdList];
//                $query->andFilterWhere($condition);
//            }
//        }
//        else {
//            if ($treeNodeIdList == '') {
//                $query->andWhere('company_id is null');
//            } else {
//                $query->andFilterWhere(['=', FwCompany::tableName() . '.tree_node_id', $treeNodeIdList]);
//            }
//        }

        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();

            $rbacService = new RbacService();

            if (!$rbacService->isSpecialUser($userId)) {
                $userCompanyService = new UserCompanyService();

                $selectedResult = $userCompanyService->getManagedListByUserId($userId, null, false);

                if (isset($selectedResult) && $selectedResult != null) {
                    $selectedList = ArrayHelper::map($selectedResult, 'kid', 'kid');

                    $companyIdList = array_keys($selectedList);
                }
                else {
                    $companyIdList = null;
                }

                $condition = ['or',
                    ['in', FwWechatTemplate::realTableName() . '.company_id', $companyIdList],
                    FwWechatTemplate::realTableName() .'.company_id is null'];
                $query->andFilterWhere($condition);
//                $query->andFilterWhere(['in', FwPosition::tableName() . '.company_id', $companyIdList]);
            }

        }

//            ->andFilterWhere(['like', 'limitation', $this->limitation])
//            ->andFilterWhere(['like', 'code_gen_way', $this->code_gen_way])
//            ->andFilterWhere(['like', 'code_prefix', $this->code_prefix]);
//        $sort->attributes=['LPT_NAME'=> [
//            'asc' => ['LPT_NAME' => SORT_ASC],
//            'desc' => ['LPT_NAME' => SORT_DESC]]];
        $dataProvider->setSort(false);

        $query->addOrderBy([FwTreeNode::realTableName() . '.tree_level' => SORT_ASC]);
        $query->addOrderBy([FwTreeNode::realTableName() . '.parent_node_id' => SORT_ASC]);
        $query->addOrderBy([FwTreeNode::realTableName() . '.sequence_number' => SORT_ASC]);
        $query->addOrderBy([FwWechatTemplate::realTableName() .'.sequence_number' => SORT_ASC]);
        $query->addOrderBy([FwWechatTemplate::realTableName() .'.created_at' => SORT_DESC]);

        return $dataProvider;
    }


    /**
     * 判断是否存在相同模板代码
     * @param $kid
     * @param $companyId
     * @param $templateCode
     * @return bool
     */
    public function isExistSameTemplateCode($kid, $companyId, $templateCode)
    {
        $model = new FwWechatTemplate();
        $query = $model->find(false);

        $query->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'template_code', $templateCode]);

        if ($companyId == null || $companyId == "")
            $query->andWhere(BaseActiveRecord::getQuoteColumnName("company_id") . ' is null');
        else
            $query->andFilterWhere(['=', 'company_id', $companyId]);

        $count = $query->count(1);

        if ($count > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * 改变数据相关状态
     * @param $kids
     */
    public function changeStatusByKidList($kids,$status)
    {
        if (!empty($kids)) {
            $sourceMode = new FwWechatTemplate();


            $attributes = [
                'status' => $status,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $kids . ')';


            $sourceMode->updateAll($attributes, $condition);
        }
    }


    /**
     * 移动数据
     * @param $userId
     */
    public function moveDataByKidList($kids,$companyId)
    {
        if (!empty($kids)) {
            $sourceMode = new FwWechatTemplate();

            $attributes = [
                'company_id' => $companyId,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $kids . ')';


            $sourceMode->updateAll($attributes, $condition);
        }
    }
}