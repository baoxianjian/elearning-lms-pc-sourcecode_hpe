<?php


namespace backend\services;


use common\models\framework\FwActionLog;
use common\models\framework\FwActionLogFilter;
use common\models\framework\FwOrgnization;
use common\models\framework\FwUser;
use common\models\treemanager\FwTreeNode;
use common\services\framework\RbacService;
use common\services\framework\UserOrgnizationService;
use common\base\BaseActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class ActionLogService extends FwActionLog{

    public $user_name;
    public $real_name;
    public $action_start_at;
    public $action_end_at;

    public function rules()
    {
        return [
            [['user_name', 'real_name'], 'string', 'max' => 255],
            [['action_filter_id'], 'string', 'max' => 50],
            [['action_start_at','action_end_at'], 'date'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_name' => Yii::t('common', 'user_name'),
            'real_name' => Yii::t('common', 'real_name'),
            'action_filter_id' => Yii::t('common', 'action_name'),
            'action_start_at' => Yii::t('common', 'action_start_at'),
            'action_end_at' => Yii::t('common', 'action_end_at'),
        ];
    }

    /**
     * 搜索操作用户操作日志数据
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$parentNodeId,$includeSubNode)
    {
        $query = FwActionLog::find(false);


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query
            ->innerJoin(FwUser::realTableName(),
                FwActionLog::tableName() . "." . self::getQuoteColumnName("user_id") . " = " . FwUser::tableName() . "." . self::getQuoteColumnName("kid") )
            ->leftJoin(FwOrgnization::realTableName(),
                FwUser::tableName() . "." . self::getQuoteColumnName("orgnization_id") . " = " . FwOrgnization::tableName() . "." . self::getQuoteColumnName("kid") )
            ->leftJoin(FwTreeNode::realTableName(),
                FwOrgnization::tableName() . "." . self::getQuoteColumnName("tree_node_id") . " = " . FwTreeNode::tableName() . "." . self::getQuoteColumnName("kid") )

            ->andFilterWhere(['like', 'real_name', trim(urldecode($this->real_name))])
            ->andFilterWhere(['like', 'user_name', trim(urldecode($this->user_name))])
            ->andFilterWhere(['like', 'action_filter_id', $this->action_filter_id]);

        if ($this->action_start_at) {
            $query->andFilterWhere(['>=', FwActionLog::tableName() . '.created_at', strtotime($this->action_start_at)]);
        }
        if ($this->action_end_at) {
            $query->andFilterWhere(['<=', FwActionLog::tableName() . '.created_at', strtotime($this->action_end_at . ' 23:59:59')]);
        }

        if ($includeSubNode == '1') {
            if ($parentNodeId != '') {
                $treeNodeModel = FwTreeNode::findOne($parentNodeId);
                $nodeIdPath = $treeNodeModel->node_id_path . $parentNodeId . "/%";

                $condition = ['or',
                    BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'",
                    ['=', FwOrgnization::realTableName() . '.tree_node_id', $parentNodeId]];
                $query->andFilterWhere($condition);
//                $query->andWhere(BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'");
            }
            else {
                $condition = ['or',
                    BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '/%'",
                    BaseActiveRecord::getQuoteColumnName("orgnization_id") . ' is null'];
                $query->andFilterWhere($condition);
            }

//            $treeNodeService = new TreeNodeService();
//            $treeTypeId = $treeNodeService->getTreeTypeId('orgnization');
//            $query->andFilterWhere(['=','tree_type_id',$treeTypeId]);

        }
        else
        {
            if ($parentNodeId == '') {
                $query->andWhere(BaseActiveRecord::getQuoteColumnName("orgnization_id") . ' is null');
            } else {
                $query->andFilterWhere(['=', FwOrgnization::realTableName() . '.tree_node_id', $parentNodeId]);
            }
        }

//        if (is_array($treeNodeIdList)) {
//            if (in_array('', $treeNodeIdList)) {
////                $condition = ;
//
//                $condition = ['or',
//                    [ 'in',FwOrgnization::tableName() . '.tree_node_id',$treeNodeIdList],
//                    FwUser::tableName() . '.orgnization_id is null'
//                ];
//                //$condition[] = ['in', Orgnization::tableName() . '.tree_node_id', $treeNodeIdList];
//                $query->andFilterWhere($condition);
//            }
//            else
//            {
//                $condition = [ 'in',FwOrgnization::tableName() . '.tree_node_id',$treeNodeIdList];
//                //$condition[] = ['in', Orgnization::tableName() . '.tree_node_id', $treeNodeIdList];
//                $query->andFilterWhere($condition);
//            }
//        }
//        else {
//            if ($treeNodeIdList == '') {
//                $query->andWhere(FwUser::tableName() . '.orgnization_id is null');
//            } else {
//                $query->andFilterWhere(['=', FwOrgnization::tableName() . '.tree_node_id', $treeNodeIdList]);
//            }
//        }

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

                $query->andFilterWhere(['in', FwUser::realTableName() . '.orgnization_id', $orgnizationIdList]);
            }

        }


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
        $query->addOrderBy([FwActionLog::realTableName() .'.created_at' => SORT_DESC]);

        return $dataProvider;
    }

}