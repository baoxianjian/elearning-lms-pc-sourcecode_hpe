<?php


namespace backend\services;


use common\models\framework\FwCompany;
use common\models\framework\FwPosition;
use common\models\framework\FwUser;
use common\models\framework\FwUserPosition;
use common\models\treemanager\FwTreeNode;
use common\services\framework\RbacService;
use common\services\framework\TreeNodeService;
use common\services\framework\UserCompanyService;
use common\services\framework\UserPositionService;
use common\base\BaseActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class PositionService extends FwPosition{

    /**
     * 搜索岗位数据列表
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$parentNodeId,$includeSubNode)
    {
        $query = FwPosition::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->joinWith('fwCompany.fwTreeNode')
            ->andFilterWhere(['like', 'position_code', trim(urldecode($this->position_code))])
            ->andFilterWhere(['like', 'position_name', trim(urldecode($this->position_name))])
            ->andFilterWhere(['<>', 'limitation', FwPosition::LIMITATION_HIDDEN])
            ->andFilterWhere(['=', FwPosition::realTableName(). '.status', $this->status]);


//        if (is_array($treeNodeIdList)) {
//            if (in_array('', $treeNodeIdList)) {
////                $condition = ;
//
//                $condition = ['or',
//                    [ 'in',FwCompany::realTableName() . '.tree_node_id',$treeNodeIdList],
//                    BaseActiveRecord::getQuoteColumnName("company_id") . ' is null'];
//                $query->andFilterWhere($condition);
//            }
//            else
//            {
//                $condition = [ 'in',FwCompany::realTableName() . '.tree_node_id',$treeNodeIdList];
//                $query->andFilterWhere($condition);
//            }
//        }
//        else {
//            if ($treeNodeIdList == '') {
//                $query->andWhere(BaseActiveRecord::getQuoteColumnName("company_id") . ' is null');
//            } else {
//                $query->andFilterWhere(['=', FwCompany::realTableName() . '.tree_node_id', $treeNodeIdList]);
//            }
//        }


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
                    ['in', 'company_id', $companyIdList],
                    BaseActiveRecord::getQuoteColumnName("company_id") . ' is null'];
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
        $query->addOrderBy([FwPosition::realTableName() .'.created_at' => SORT_DESC]);

        return $dataProvider;
    }


    /**
     * 判断岗位代码是否重复
     * @param $kid
     * @param $companyId
     * @param $positionCode
     * @return bool
     */
    public function isExistSamePositionCode($kid, $companyId, $positionCode)
    {
        $userPositionService = new UserPositionService();
        return $userPositionService->isExistSamePositionCode($kid, $companyId, $positionCode);
    }


    /**
     * 获取当前企业可用岗位（含共享的岗位）
     * @param $companyId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAvailablePositionByCompanyId($companyId)
    {
        $query = FwPosition::find(false);

        $query->andFilterWhere(['=', 'status', self::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['<>', 'limitation', self::LIMITATION_HIDDEN]);

        $condition = ['or',
            [ '=','company_id', $companyId],
            [ '=','share_flag',self::SHARE_FLAG_SHARE],
        ];

        $query->andFilterWhere($condition);

        $query->addOrderBy(['company_id' => SORT_ASC]);
        $query->addOrderBy(['created_at' => SORT_DESC]);



        $result =  $query->all();

        if (!empty($result) && count($result) > 0) {
            foreach ($result as $single) {
                $single->position_display_name = $single->position_name . "(" . $single->position_code . ")";
            }
        }

        return $result;
    }


    /**
     * 改变列表相关状态
     * @param $kids
     */
    public function changeStatusByKidList($kids,$status)
    {
        if (!empty($kids)) {
            $sourceMode = new FwPosition();

            $attributes = [
                'status' => $status,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $kids . ')';


            $sourceMode->updateAll($attributes, $condition);
        }
    }

    /**
     * 移动证书列表
     * @param $userId
     */
    public function moveDataByKidList($kids,$companyId)
    {
        if (!empty($kids)) {
            $sourceMode = new FwPosition();

            $attributes = [
                'company_id' => $companyId,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $kids . ')';


            $sourceMode->updateAll($attributes, $condition);
        }
    }
}