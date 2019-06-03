<?php


namespace common\services\framework;


use common\base\BaseActiveRecord;
use common\helpers\TBaseHelper;
use common\models\framework\FwActionLog;
use common\models\framework\FwActionLogFilter;
use common\models\framework\FwActionLogMongo;
use common\models\framework\FwOrgnization;
use common\models\framework\FwUser;
use common\models\treemanager\FwTreeNode;
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
     * 检查指定日期日志是否存在
     * @param $action_filter_id
     * @param $userId
     * @param $startAt
     * @param $endAt
     * @return bool
     */
    public function checkDailyActionLogExist($action_filter_id, $userId, $startAt, $endAt) {
        if (!TBaseHelper::isUseMongoDB()) {
            $model = new FwActionLog();
        }
        else {
            $model = new FwActionLogMongo();
        }
        $result = $model->find(false)
            ->andFilterWhere(['=', 'user_id', $userId])
//            ->andFilterWhere(['=', 'system_id', $systemId])
            ->andFilterWhere(['=', 'action_filter_id', $action_filter_id])
            ->andFilterWhere(['>=', 'created_at', $startAt])
            ->andFilterWhere(['<=', 'created_at', $endAt])
            ->one();

        if (!empty($result)) {
            return true;
        }
        else {
            return false;
        }
    }


    /**
     * 插入操作日志
     * @param $systemId
     * @param $actionFilterId
     * @param $userId
     * @param $controllerId
     * @param $actionId
     * @param $paramterQuery
     * @param $paramterBody
     * @param $encryptMode
     * @param $httpMode
     * @param $actionUrl
     * @param $systemFlag
     * @param $actionIp
     * @param $startTime
     * @param $endTime
     * @param $durationTime
     * @param $machineLabel
     * @param string $systemKey
     * @return bool
     */
    public function insertActionLog($systemId, $actionFilterId, $userId, $controllerId, $actionId, $paramterQuery, $paramterBody,
                                    $encryptMode, $httpMode, $actionUrl, $systemFlag, $actionIp, $startTime, $endTime, $durationTime, $machineLabel, $systemKey = "PC")
    {
        if (!TBaseHelper::isUseMongoDB()) {
            $model = new FwActionLog();
        }
        else {
            $model = new FwActionLogMongo();

            $userModel = FwUser::findOne($userId);
            $orgModel = FwOrgnization::findOne($userModel->orgnization_id);
            $actionLogFileterModel = FwActionLogFilter::findOne($actionFilterId);
            
            $model->orgnization_id = $userModel->orgnization_id;
            $model->orgnization_tree_node_id = $orgModel->tree_node_id;
            $model->real_name = $userModel->real_name;
            $model->user_name = $userModel->user_name;
            $model->orgnization_name = $orgModel->orgnization_name;
            $model->filter_name = $actionLogFileterModel->filter_name;

        }

        $model->system_id = $systemId;
        $model->action_filter_id = $actionFilterId;
        $model->user_id = $userId;
        $model->controller_id = $controllerId;
        $model->action_id = $actionId;
        $model->action_parameter_query = $paramterQuery == "" ? null : $paramterQuery;
        $model->action_parameter_body = $paramterBody == "" ? null : $paramterBody;
        $model->encrypt_mode = $encryptMode;
        $model->http_mode = $httpMode;
        $model->action_url = $actionUrl;
        $model->system_flag = $systemFlag;
        $model->action_ip = $actionIp;
        $model->start_at = $startTime;
        $model->end_at = $endTime;
        $model->duration_time = $durationTime;
        $model->machine_label = $machineLabel;
        $model->systemKey = $systemKey;

        return $model->save();
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
        if (!TBaseHelper::isUseMongoDB()) {
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
                    FwActionLog::tableName() . "." . self::getQuoteColumnName("user_id") . " = " . FwUser::tableName() . "." . self::getQuoteColumnName("kid"))
                ->leftJoin(FwOrgnization::realTableName(),
                    FwUser::tableName() . "." . self::getQuoteColumnName("orgnization_id") . " = " . FwOrgnization::tableName() . "." . self::getQuoteColumnName("kid"))
                ->leftJoin(FwTreeNode::realTableName(),
                    FwOrgnization::tableName() . "." . self::getQuoteColumnName("tree_node_id") . " = " . FwTreeNode::tableName() . "." . self::getQuoteColumnName("kid"))
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
                } else {
                    $condition = ['or',
                        BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '/%'",
                        BaseActiveRecord::getQuoteColumnName("orgnization_id") . ' is null'];
                    $query->andFilterWhere($condition);
                }

//            $treeNodeService = new TreeNodeService();
//            $treeTypeId = $treeNodeService->getTreeTypeId('orgnization');
//            $query->andFilterWhere(['=','tree_type_id',$treeTypeId]);

            } else {
                if ($parentNodeId == '') {
                    $query->andWhere(BaseActiveRecord::getQuoteColumnName("orgnization_id") . ' is null');
                } else {
                    $query->andFilterWhere(['=', FwOrgnization::realTableName() . '.tree_node_id', $parentNodeId]);
                }
            }

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
            $query->addOrderBy([FwActionLog::realTableName() . '.created_at' => SORT_DESC]);

            return $dataProvider;
        }
        else {
            $query = FwActionLogMongo::find(false);


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
                ->andFilterWhere(['like', 'real_name', trim(urldecode($this->real_name))])
                ->andFilterWhere(['like', 'user_name', trim(urldecode($this->user_name))])
                ->andFilterWhere(['like', 'action_filter_id', $this->action_filter_id]);

            if ($this->action_start_at) {
                $query->andFilterWhere(['>=', 'created_at', strtotime($this->action_start_at)]);
            }
            if ($this->action_end_at) {
                $query->andFilterWhere(['<=', 'created_at', strtotime($this->action_end_at . ' 23:59:59')]);
            }

            if ($includeSubNode == '1') {
                $tempModel = new FwTreeNode();

                $treeNodeService = new TreeNodeService();

                $treeTypeId = $treeNodeService->getTreeTypeId("orgnization");

                if ($parentNodeId != '') {
                    $treeNodeModel = FwTreeNode::findOne($parentNodeId);
                    $nodeIdPath = $treeNodeModel->node_id_path . $parentNodeId . "/%";

                    $condition = ['or',
                        BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'",
                        ['=', FwOrgnization::realTableName() . '.tree_node_id', $parentNodeId]];

                    $tempResult = $tempModel->find(false)
                        ->andFilterWhere(['=', 'tree_type_id', $treeTypeId])
                        ->andFilterWhere($condition)
                        ->all();


//                $query->andWhere(BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'");
                } else {
                    $condition = BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '/%'";
                    $tempResult = $tempModel->find(false)
                        ->andFilterWhere(['=', 'tree_type_id', $treeTypeId])
                        ->andWhere($condition)
                        ->all();
                }

                $treeNodeIdList = [];
                if (isset($tempResult) && $tempResult != null) {
                    $tempList = ArrayHelper::map($tempResult, 'kid', 'kid');

                    $treeNodeIdList = array_keys($tempList);
                }

                if ($parentNodeId != '') {
                    $query->andFilterWhere(['in', 'orgnization_tree_node_id', $treeNodeIdList]);
                }
                else {
                    $tempCondition = ['or',
                        ['orgnization_id' => null],
                        ['in', 'orgnization_tree_node_id', $treeNodeIdList]];
                    $query->andWhere($tempCondition);
                }

//            $treeNodeService = new TreeNodeService();
//            $treeTypeId = $treeNodeService->getTreeTypeId('orgnization');
//            $query->andFilterWhere(['=','tree_type_id',$treeTypeId]);

            } else {
                if ($parentNodeId == '') {
                    $query->andWhere(['orgnization_id' => null]);
                } else {
                    $query->andFilterWhere(['=', 'orgnization_tree_node_id', $parentNodeId]);
                }
            }

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

                    $query->andFilterWhere(['in','orgnization_id', $orgnizationIdList]);
                }

            }


            $dataProvider->setSort(false);

            $query->addOrderBy(['created_at' => SORT_DESC]);

            return $dataProvider;
        }
    }
}