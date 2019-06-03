<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2016/3/14
 * Time: 11:02
 */

namespace common\services\social;

use common\helpers\TArrayHelper;
use common\models\framework\FwUser;
use common\models\framework\FwUserDisplayInfo;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseEnroll;
use common\models\learning\LnCourseReg;
use common\models\social\SoAudience;
use common\models\social\SoAudienceCategory;
use common\models\social\SoAudienceMember;
use common\models\social\SoAudienceTemp;
use common\models\treemanager\FwTreeNode;
use common\services\framework\OrgnizationService;
use common\services\framework\UserCompanyService;
use common\base\BaseActiveRecord;
use components\widgets\TPagination;
use yii\db\Query;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\User;

class AudienceManageService extends SoAudience
{

    /**
     * 受众列表
     * @param $params
     * @return array
     */
    public function getSoAudienceList($params, $isMemberCount = true){
        $model = SoAudience::find(false);
        if (!empty($params['keyword'])){
            $keyword = htmlspecialchars($params['keyword']);
            $model->andFilterWhere(['or', ['like', 'audience_name', $keyword], ['like', 'audience_code', $keyword]]);
        }
        $model->andFilterWhere(['owner_id'=>$params['ownerId']])
            ->andFilterWhere(['company_id'=>$params['companyId']]);
            //->andFilterWhere(['audience_type' => SoAudience::AUDIEBCE_TYPE_MEMBER]);

        if (!empty($params['TreeNodeKid']) && $params['TreeNodeKid'] != '-1'){
            $categoryId = $this->getTreeNodeIdToCategoryId($params['TreeNodeKid']);
            if (!empty($categoryId)){
                $model->andFilterWhere(['=', 'category_id', $categoryId]);
            }
        }
        if (isset($params['status']) && $params['status'] != ""){
            $model->andFilterWhere(['=', 'status', $params['status']]);
        }


        $count = $model->count('kid');
        if ($count > 0) {
            if ($isMemberCount) {
                $page = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
                $result = $model->offset($page->offset)->limit($page->limit)->orderBy("created_at DESC")->asArray()->all();

                foreach ($result as $key => $item) {
                    $memberCount = SoAudienceMember::find(false)->andFilterWhere(['=', 'audience_id', $item['kid']])->count('kid');
                    $result[$key]['memberCount'] = $memberCount;
                }
            }else{
                $page = null;
                $result = $model->orderBy("created_at DESC")->all();
            }
            return ['data' => $result, 'page' => $page];
        }else{
            return ['data' => null, 'page' => null];
        }
    }

    /**
     * 返回同一企业的受众量
     * @param $companyId
     * @return int|string
     */
    public function getAudienceCount($companyId, $ownerId){
        $count = SoAudience::find(false)
            ->andFilterWhere(["=", "company_id", $companyId])
            ->andFilterWhere(["=", "owner_id", $ownerId])
            /*->andFilterWhere(["<>", "status", SoAudience::STATUS_FLAG_STOP])*/
            ->count('kid');

        return $count;
    }

    /**
     * @param $audienceId
     * @return array|bool
     */
    public function getAudienceMember($audienceId){
        if (empty($audienceId)) return false;
        $memberList = SoAudienceMember::findAll(['audience_id' => $audienceId, 'status' => SoAudienceMember::STATUS_FLAG_NORMAL]);
        if (!empty($memberList)){
            $user = ArrayHelper::map($memberList, 'user_id', 'user_id');
            $user = array_keys($user);
            return $user;
        }else{
            return false;
        }
    }

    /**
     * tree_node_id 转 category_id
     * @param $tree_node_id
     * @return array|null|string
     **/
    public function getTreeNodeIdToCategoryId($tree_node_id){
        if (empty($tree_node_id)) return null;
        $categories = SoAudienceCategory::findAll(['tree_node_id'=>$tree_node_id],false);
        if (is_array($tree_node_id)){
            $result = array();
            foreach ($categories as $value){
                $result[] = $value->kid;
            }
            return $result;
        }else{
            return $categories ? $categories[0]->kid : '';
        }
    }

    /**
     * category_id 转 tree_node_id
     * @param $category_id
     * @return null|string
     **/
    public function getCategoryIdToTreeNodeId($category_id){
        if (empty($category_id)) return null;
        $find = SoAudienceCategory::findOne($category_id);
        return $find ? $find->tree_node_id : '';
    }

    /**
     * 根据树节点ID获取受众目录ID
     * @param $id
     * @return null|string
     */
    public function getSoAudienceCategoryIdByTreeNodeId($id)
    {
        if ($id != null && $id != "") {
            $soAudienceCategoryModel = new SoAudienceCategory();
            $soAudienceCategoryResult = $soAudienceCategoryModel->findOne(['tree_node_id' => $id]);
            if ($soAudienceCategoryResult != null)
            {
                $soAudienceCategoryId = $soAudienceCategoryResult->kid;
            }
            else
            {
                $soAudienceCategoryId = null;
            }
        }
        else
        {
            $soAudienceCategoryId = null;
        }
        return $soAudienceCategoryId;
    }

    /**
     * 根据树节点ID，删除相关受众目录ID
     * @param $treeNodeId
     */
    public function deleteRelateData($treeNodeId){
        $model = new SoAudienceCategory();

        $kids = "";
        if (is_array($treeNodeId)) {
            foreach ($treeNodeId as $key) {
                $kids = $kids . "'" . $key . "',";
                $categoryKey = $this->getSoAudienceCategoryIdByTreeNodeId($key);
                SoAudienceCategory::removeFromCacheByKid($categoryKey);
            }
            $kids = rtrim($kids, ",");
        }else{
            $kids = "'".$treeNodeId."'";
            $examQuestionCategoryKey = $this->getSoAudienceCategoryIdByTreeNodeId($treeNodeId);
            SoAudienceCategory::removeFromCacheByKid($examQuestionCategoryKey);
        }
        $model->deleteAll(BaseActiveRecord::getQuoteColumnName("tree_node_id") . " in (".$kids.")");
        FwTreeNode::deleteAll(BaseActiveRecord::getQuoteColumnName("kid") . " in (".$kids.")");
    }

    /**
     * @param $params
     * @return array
     */
    public function getOrgnizationUser($params){
        $companyId = Yii::$app->user->identity->company_id;
        $userId = Yii::$app->user->getId();
        $companyService = new UserCompanyService();
        $manageCompanyList = $companyService->getUserManagedCompanyList($userId);
        $model = FwUserDisplayInfo::find(false)
            ->andFilterWhere(['in', 'company_id', $manageCompanyList])
            ->andFilterWhere(['=', 'status', FwUserDisplayInfo::STATUS_FLAG_NORMAL]);

        if (!empty($params['TreeNodeKid']) && $params['TreeNodeKid'] != '-1') {
            $treeNodeId = explode(',', $params['TreeNodeKid']);
            $orginizationService = new OrgnizationService();
            $allOrginization = $orginizationService->getAllOrgnizationIdByTreeNodeId($treeNodeId);
            $allOrginizationKids = $orginizationService->getSubOrgnization($allOrginization, $manageCompanyList);
            if (!empty($allOrginizationKids)){
                $allOrginization = array_merge($allOrginization, $allOrginizationKids);
            }
            $model->andFilterWhere(['in', 'orgnization_id', $allOrginization]);
        }

        if (!empty($params['audience_batch'])){
            $audience_batch = $params['audience_batch'];
            $userId = Yii::$app->user->getId();
            //$model->andWhere(" not exists (select * from {{%so_audience_temp}} t where ".FwUserDisplayInfo::tableName().".user_id=t.user_id and t.status='".SoAudienceTemp::STATUS_FLAG_NORMAL."' and t.audience_batch='".$audience_batch."' and t.owner_id='".$userId."' and t.company_id='".$companyId."')");
        }

        if (!empty($params['keyword'])) {
            $keyword = htmlspecialchars($params['keyword']);
            $model->andFilterWhere(['or', ['like', 'real_name', $keyword], ['like', 'email', $keyword]]);
        }

        $count = $model->count('kid');
        if ($count) {
            if (!empty($params['format'])){
                $result = $model->all();
                return ['data' => $result];
            }else {
                $page = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
                $result = $model->offset($page->offset)->limit($page->limit)->orderBy('created_at DESC')->all();
                return ['data' => $result, 'page' => $page];
            }
        }else{
            return ['data' => null, 'page' => null];
        }

    }

    /**
     * 批量插入受众临时数据
     * @param $params
     * @return bool|string
     */
    public function betchInsertAudeniceTemp($params){
        //$result = $this->getOrgnizationUser($params);
        $user = $params['user'];
        SoAudienceTemp::updateAll(['status' => SoAudienceTemp::STATUS_FLAG_STOP], 'audience_batch=:audience_batch', [':audience_batch' =>$params['audience_batch']]);
        if (empty($user)){
            return false;
        }
        if (!empty($user)){
            $ownerId = Yii::$app->user->getId();
            $companyId = Yii::$app->user->identity->company_id;
            $batchModel = array();
            $errMsg = "";
            foreach ($user as $v){
                $item = FwUserDisplayInfo::findOne(['user_id' => $v]);
                $hasData = SoAudienceTemp::findOne(['audience_batch'=>$params['audience_batch'],'owner_id' => $ownerId,'user_id'=>$item->user_id]);
                if (empty($hasData)) {
                    $model = new SoAudienceTemp();
                    $model->audience_batch = $params['audience_batch'];
                    $model->company_id = $companyId;
                    $model->owner_id = $ownerId;
                    $model->user_id = $item->user_id;
                    $model->user_name = $item->user_name;
                    $model->real_name = $item->real_name;
                    $model->orgnization = $item->orgnization_name;
                    $model->position = $item->position_name;
                    $model->email = $item->email;
                    $model->mobile_no = $item->mobile_no;
                    array_push($batchModel, $model);
                }else{
                    //$model = SoAudienceTemp::findOne($hasData->kid);
                    $hasData->status = SoAudienceTemp::STATUS_FLAG_NORMAL;
                    if ($hasData->update() !== false){

                    }else{
                        $errMsg = $hasData->getErrors();
                    }
                }
            }
            if (!empty($batchModel)) {
                BaseActiveRecord::batchInsertNormalMode($batchModel, $errmsg);
            }
            return $errMsg;
        }else{
            return false;
        }
    }

    /**
     * 批量导入受众临时数据
     * @param $params
     * @return bool|string
     */
    public function betchImportAudeniceTemp($params){
        set_time_limit(0);
        $sessionKey = "Audience_".$params['fileMd5'];
        $data = Yii::$app->session->get($sessionKey);
        if (empty($data)){
            return ['result' => 'fail', 'errmsg' => Yii::t('common', 'import_empty_person')];
        }
        $ownerId = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;
        $batchModel = array();
        $right = 0;
        $user = array();
        if (!empty($data['success'] )) {
            foreach ($data['success'] as $item) {
                if ($item['msg'] == 'success') {
                    $fwUser = $item['fwUser'];
                    $count = SoAudienceTemp::find(false)->andFilterWhere(['audience_batch' => $params['audience_batch'], 'owner_id' => $ownerId, 'user_id' => $fwUser->user_id])->count('kid');
                    if (empty($count)) {
                        $model = new SoAudienceTemp();
                        $model->audience_batch = $params['audience_batch'];
                        $model->company_id = $companyId;
                        $model->owner_id = $ownerId;
                        $model->user_id = $fwUser->user_id;
                        $model->user_name = $fwUser->user_name;
                        $model->real_name = $fwUser->real_name;
                        $model->orgnization = $fwUser->orgnization_name;
                        $model->position = $fwUser->position_name;
                        $model->email = $fwUser->email;
                        $model->mobile_no = $fwUser->mobile_no;
                        array_push($batchModel, $model);
                        $right++;
                        $user[] = $fwUser->user_id;
                    }
                } else {
                    continue;
                }
            }
            $errMsg = "";
            if (!empty($batchModel)) {
                BaseActiveRecord::batchInsertNormalMode($batchModel, $errmsg);
            }
            return ['result' => 'success', 'errmsg' => $errMsg, 'right' => $right, 'user' => $user];
        }else{
            return ['result' => 'fail', 'errmsg' => Yii::t('common', 'import_empty_person')];
        }
    }

    /**
     * 刷新页面清空数据
     * @param $userId
     * @param $companyId
     */
    public function deleteAudienceTempAll($userId, $companyId){
        $condition = [
            ':owner_id' => $userId,
            ':company_id' => $companyId,
        ];
        SoAudienceTemp::physicalDeleteAll("owner_id=:owner_id and company_id=:company_id", $condition);
    }

    /**
     * @param $kid
     */
    public function deleteAudienceTemp($kid){
        $kid = explode(',', $kid);
        SoAudienceTemp::physicalDeleteAllByKid($kid);
    }

    /**
     * 查询临时列表
     * @param $params
     * @return array
     */
    public function getAudienceTemp($params){
        $model = SoAudienceTemp::find(false)
            ->andFilterWhere(['=', 'status', SoAudienceTemp::STATUS_FLAG_NORMAL]);

        if ($params['ownerId']) {
            $model->andFilterWhere(['=', 'owner_id', $params['ownerId']]);
        }
        if ($params['companyId']) {
            $model->andFilterWhere(['=', 'company_id', $params['companyId']]);
        }

        if (!empty($params['audience_batch'])){
            $model->andFilterWhere(['=', 'audience_batch', $params['audience_batch']]);
        }

        if (!empty($params['keyword'])) {
            $keyword = htmlspecialchars($params['keyword']);
            $model->andFilterWhere(['or', ['like', 'real_name', $keyword], ['like', 'email', $keyword]]);
        }

        $count = $model->count('kid');
        if ($count) {
            if (!empty($params['format'])){
                $result = $model->orderBy('created_at desc')->all();
                return ['data' => $result];
            }else {
                $page = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
                $result = $model->offset($page->offset)->limit($page->limit)->orderBy('created_at desc')->all();
                return ['data' => $result, 'page' => $page];
            }
        }else{
            return ['data' => null, 'page' => null];
        }
    }

    /**
     * 保存临时数据表
     * @param $params
     * @return array
     */
    public function saveAudienceTemp($params){
        if (empty($params['audience_title'])){
            return ['result' => 'fail', 'errmsg' => Yii::t('common', 'audience_title_not_empty')];
        }
        $audience_batch = $params['audience_batch'];
        if (empty($audience_batch)) {
            return ['result' => 'fail', 'errmsg' => Yii::t('common', 'loading_fail')];
        }
        $ownerId = $params['owner_id'];
        $companyId = $params['company_id'];

        $res = $this->isExistsAudienceName($params['audienceId'], $ownerId, $companyId, $params['audience_title']);
        if ($res){
            return ['result' => 'fail', 'errmsg' => Yii::t('common', 'having_title')];
        }

        $soAudienceTempModel = SoAudienceTemp::find(false);
        $soAudienceTempModel->andFilterWhere(['=', 'audience_batch', $audience_batch])
            ->andFilterWhere(['=', 'owner_id', $ownerId])
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['=', 'status', SoAudienceTemp::STATUS_FLAG_NORMAL]);
        $count = $soAudienceTempModel->count('kid');
        if (intval($count) > 1000){
            return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'audience_best_count')];
        }
        $user_id = $soAudienceTempModel->select('user_id')->orderBy('created_at desc')->all();

        if (empty($user_id)){
            return ['result' => 'fail', 'errmsg' => Yii::t('common', 'audience_member_not_empty')];
        }

        $treeNodeId = $params['TreeNodeId'];
        $categoryId = $this->getTreeNodeIdToCategoryId($treeNodeId);

        $audienceId = !empty($params['audienceId']) ? $params['audienceId'] : "";

        $model = empty($audienceId) ? new SoAudience() : SoAudience::findOne($audienceId);
        $model->company_id = $companyId;
        $model->owner_id = $ownerId;
        $model->category_id = $categoryId;
        $audience_code = SoAudience::setAudienceCode($audienceId);
        $model->audience_code = $audience_code;
        $model->audience_name = $params['audience_title'];
        $model->description = $params['description'];
        $model->audience_type = SoAudience::AUDIEBCE_TYPE_MEMBER;
        $model->status = isset($params['status']) ? $params['status'] : SoAudience::STATUS_FLAG_NORMAL;
        $model->saveEncode = true;
        if (empty($audienceId)) {
            $model->needReturnKey = true;
            $result = $model->save();
        }else{
            $result = $model->update();
        }
        if ($result !== false){
            $audience_id = $model->kid;
            $modelArray = array();
            if (!empty($audienceId)) {
                $updateArray = array();
                foreach ($user_id as $item) {
                    $modelTemp = SoAudienceMember::findOne(['audience_id' => $audience_id, 'user_id' => $item->user_id]);
                    if (empty($modelTemp)){
                        $modelTemp = new SoAudienceMember();
                        $modelTemp->start_at = time();
                        $modelTemp->audience_id = $audience_id;
                        $modelTemp->user_id = $item->user_id;
                        array_push($modelArray, $modelTemp);
                    }else{
                        array_push($updateArray, $modelTemp);
                    }
                }
                $insertArray = array();
                $errMsg = "";
                $resultId = array();
                if (!empty($modelArray)) {
                    BaseActiveRecord::batchInsertNormalMode($modelArray, $errmsg, true, $resultId);
                    $insertArray = $resultId;
                }
                $errMsg = "";
                $updateResult = array();
                if (!empty($updateArray)) {
                    BaseActiveRecord::batchUpdateNormalMode($updateArray, $errmsg, true, $updateResult);
                    if (!empty($insertArray)) {
                        $insertArray = array_merge($insertArray, $updateResult);
                    }else{
                        $insertArray = $updateResult;
                    }
                }
                $resultIdArray = array_filter($insertArray);
                $resultIdArray = array_unique($resultIdArray);
                if (!empty($resultIdArray)) {
                    $resultIdArraySql = "'".join("','", $resultIdArray)."'";
                    SoAudienceMember::updateAll(
                        [
                            'status' => SoAudienceMember::STATUS_FLAG_STOP,
                            'is_deleted' => SoAudienceMember::DELETE_FLAG_YES
                        ],
                        'kid not in ('.$resultIdArraySql.') and audience_id=:audienceId',
                        [':audienceId' => $audience_id]
                    );
                }
                //return ['result' => 'success'];
            }else{
                foreach ($user_id as $item) {
                    $modelTemp = new SoAudienceMember();
                    $modelTemp->audience_id = $audience_id;
                    $modelTemp->user_id = $item->user_id;
                    $modelTemp->start_at = time();
                    array_push($modelArray, $modelTemp);
                }
                $errMsg = "";
                if (!empty($modelArray)) {
                    BaseActiveRecord::batchInsertNormalMode($modelArray, $errmsg);
                }
            }
            $this->deleteAudienceTempAll($ownerId, $companyId);
            return ['result' => 'success'];
        }else{
            return ['result' => 'fail', 'errmsg' => Yii::t('common', 'SaveFail')];
        }
    }

    /**
     * @param $params
     */
    public function setAudienceBatchUserName($params){
        $sessionAudienceBatchUserName = "Audience_batch_user_name_".$params['audience_batch'];
        $owner_id = Yii::$app->user->getId();
        $model = SoAudienceTemp::find(false);
        $result = $model->andFilterWhere(['=', 'audience_batch', $params['audience_batch']])
            ->andFilterWhere(['=', 'owner_id', $owner_id])
            ->select('user_name')
            ->asArray()
            ->all();
        if (!empty($result)){
            $result = ArrayHelper::map($result, 'user_name', 'user_name');
            $result = array_keys($result);
        }
        Yii::$app->session->set($sessionAudienceBatchUserName, $result);
    }

    /**
     * 复制受众
     * @param $audienceId
     * @param $ownerId
     * @param $companyId
     * @return array
     */
    public function copyAudience($audienceId, $ownerId, $companyId){
        if (empty($audienceId)) {
            return ['result' => 'fail'];
        }
        $find = SoAudience::findOne($audienceId);
        if (empty($find)) {
            return ['result' => 'fail'];
        }
        $model = new SoAudience();
        $model->company_id = $companyId;
        $model->owner_id = $ownerId;
        $model->category_id = $find->category_id;
        $audience_code = SoAudience::setAudienceCode();
        $model->audience_code = $audience_code;
        $model->audience_name = $find->audience_name.Yii::t('common','copies');
        $model->description = $find->description;
        $model->audience_type = $find->audience_type;
        $model->status = SoAudience::STATUS_FLAG_TEMP;
        $model->needReturnKey = true;
        $result = $model->save();
        if ($result !== false) {
            $audience_id = $model->kid;
            $audienceMemberAll = SoAudienceMember::findAll(['audience_id' => $find->kid, 'status' => SoAudienceMember::STATUS_FLAG_NORMAL]);
            if (!empty($audienceMemberAll)){
                $modelArray = array();
                foreach ($audienceMemberAll as $v){
                    $modelTemp = new SoAudienceMember();
                    $modelTemp->audience_id = $audience_id;
                    $modelTemp->user_id = $v->user_id;
                    $modelTemp->start_at = $v->start_at;
                    $modelTemp->end_at = $v->end_at;
                    array_push($modelArray, $modelTemp);
                }
                $errMsg = "";
                if (!empty($modelArray)) {
                    BaseActiveRecord::batchInsertNormalMode($modelArray, $errmsg);
                }
            }
            return ['result' => 'success'];
        }else{
            return ['result' => 'fail', 'errmsg' => Yii::t('common', 'copy_fail')];
        }
    }

    /**
     * 发布
     * @param $audienceId
     * @return array
     * @throws \Exception]
     */
    public function publishAudience($audienceId){
        if (empty($audienceId)){
            return  ['result' => 'fail', 'errmsg' => ''];
        }
        $data = SoAudience::findOne($audienceId);
        if (empty($data)){
            return  ['result' => 'fail', 'errmsg' => ''];
        }
        if ($data['status'] == SoAudience::STATUS_FLAG_NORMAL){
            return  ['result' => 'fail', 'errmsg' => ''];
        }
        $data->status = SoAudience::STATUS_FLAG_NORMAL;
        if ($data->update() !== false){
            return  ['result' => 'success', 'errmsg' => ''];
        }else{
            return  ['result' => 'fail', 'errmsg' => ''];
        }
    }

    /**
     * 启用
     * @param $audienceId
     * @return array
     * @throws \Exception]
     */
    public function startAudience($audienceId){
        if (empty($audienceId)){
            return  ['result' => 'fail', 'errmsg' => ''];
        }
        $data = SoAudience::findOne($audienceId);
        if (empty($data)){
            return  ['result' => 'fail', 'errmsg' => ''];
        }
        if ($data['status'] != SoAudience::STATUS_FLAG_STOP){
            return  ['result' => 'fail', 'errmsg' => ''];
        }
        $data->status = SoAudience::STATUS_FLAG_NORMAL;
        if ($data->update() !== false){
            return  ['result' => 'success', 'errmsg' => ''];
        }else{
            return  ['result' => 'fail', 'errmsg' => ''];
        }
    }

    /**
     * 停用
     * @param $audienceId
     * @return array
     * @throws \Exception]
     */
    public function stopAudience($audienceId){
        if (empty($audienceId)){
            return  ['result' => 'fail', 'errmsg' => ''];
        }
        $data = SoAudience::findOne($audienceId);
        if (empty($data)){
            return  ['result' => 'fail', 'errmsg' => ''];
        }
        if ($data['status'] != SoAudience::STATUS_FLAG_NORMAL){
            return  ['result' => 'fail', 'errmsg' => ''];
        }
        $data->status = SoAudience::STATUS_FLAG_STOP;
        if ($data->update() !== false){
            return  ['result' => 'success', 'errmsg' => ''];
        }else{
            return  ['result' => 'fail', 'errmsg' => ''];
        }
    }

    /**
     * 删除受众
     * @param $audienceId
     * @param $companyId
     * @return array
     */
    public function deletedAudience($audienceId, $companyId) {
        if (empty($audienceId)){
            return  ['result' => 'fail'];
        }
        $find = SoAudience::findOne(['kid' => $audienceId, 'company_id' => $companyId]);
        if (empty($find)){
            return  ['result' => 'fail'];
        }
        $find->delete();
        SoAudienceMember::deleteAll("audience_id=:audience_id", [':audience_id'=>$audienceId]);
        return ['result' => 'success'];
    }

    /**
     * @param $audienceId
     * @param $audience_batch
     * @return array|string
     */
    public function createAudienceTemp($audienceId, $audience_batch){
        if (empty($audienceId)){
            return  ['result' => 'fail'];
        }
        $data = SoAudience::findOne($audienceId);
        if (empty($data)){
            return  ['result' => 'fail'];
        }
        $dataMemberAll = SoAudienceMember::findAll(['audience_id' => $data->kid, 'status' => SoAudienceMember::STATUS_FLAG_NORMAL]);
        if (empty($dataMemberAll)){
            return  ['result' => 'fail'];
        }
        $batchModel = array();
        foreach ($dataMemberAll as $item){
            $user = FwUserDisplayInfo::findOne(['user_id' => $item->user_id]);
            $model = new SoAudienceTemp();
            $model->audience_batch = $audience_batch;
            $model->company_id = $data->company_id;
            $model->owner_id = $data->owner_id;
            $model->user_id = $item->user_id;
            $model->user_name = $user->user_name;
            $model->real_name = $user->real_name;
            $model->orgnization = $user->orgnization_name;
            $model->position = $user->position_name;
            $model->email = $user->email;
            $model->mobile_no = $user->mobile_no;
            array_push($batchModel, $model);
        }
        $errMsg = "";
        if (!empty($batchModel)) {
            BaseActiveRecord::batchInsertNormalMode($batchModel, $errmsg);
        }
        return $errMsg;
    }

    /**
     * 检查是否存在相同的受众名称
     * @param $ownerId
     * @param $companyId
     * @param $audienceName
     * @return bool
     */
    public function isExistsAudienceName($audienceId = null, $ownerId, $companyId, $audienceName){
        $model = SoAudience::find(false)
            ->andFilterWhere(['owner_id' => $ownerId])
            ->andFilterWhere(['company_id' => $companyId])
            ->andFilterWhere(['=', 'audience_name', $audienceName]);
        if (!empty($audienceId)){
            $model->andFilterWhere(['<>', 'kid', $audienceId]);
        }
            $result = $model->one();

        if (!empty($result)){
            return true;
        }else{
            return false;
        }

    }

    /**
     * 读取受众csv数据
     * @param $file
     * @param null $fileName
     * @return array
     * @throws \PHPExcel_Exception
     */
    public function readAudienceFile($audience_batch, $file, $fileName = null, $fileMd5 = null, $withSession = true){
        set_time_limit(0);
        if (empty($file)){
            return ['result' => 'fail', 'errmsg' => ''];
        }
        $sessionKey = "Audience_".$fileMd5;
        $sessionAudienceBatchUserName = "Audience_batch_user_name_".$audience_batch;
        $sessionAudienceBatchResultKey = "Audience_batch_result_".$fileMd5.'_'.$audience_batch;
        $err = "";
        //Yii::$app->session->set($sessionKey, "");
        if ($withSession && !empty($fileMd5)){
            $err = "ok";
            $result = null;
            //$result = Yii::$app->session->get($sessionKey);
        }
        $sessionAudienceBatchUserNameResult = Yii::$app->session->get($sessionAudienceBatchUserName);
        if (empty($result)) {
            $companyId = Yii::$app->user->identity->company_id;
            $file = Yii::$app->basePath . '/../' . $file;
            try {
                //读入上传文件
                $objPHPExcel = \PHPExcel_IOFactory::load($file);
                //内容转换为数组
                $sheet_0 = $objPHPExcel->getSheet(0)->toArray();
                $lastData = str_replace(array(' ', "\r\n", "\r", "\n"), '', $sheet_0[1001][0]);/*判断1002行是否为空，判断不准确*/
                if (!empty($lastData)){
                    return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'audience_best_count')];
                }

                if (!empty($sheet_0)) {
                    $data = array();
                    $row = array();
                    foreach ($sheet_0 as $i => $item) {
                        if ($i > 0) {
                            $userName = $item[0];
                            $realName = $item[1];
                            $userEmail = $item[2];
                            if (empty($userName) && empty($realName) && empty($userEmail)){
                                break;
                            }
                            $data[$i]['row'] = $i + 1;
                            $data[$i]['user_name'] = $userName;
                            $data[$i]['real_name'] = urlencode($realName);
                            $data[$i]['email'] = $userEmail;
                            if (empty($userName)) {
                                $data[$i]['res'] = Yii::t('common', 'user_account_empty');
                                $data[$i]['msg'] = 'fail';
                                continue;
                            }
                            if (in_array($userName, $row)){
                                $data[$i]['res'] = Yii::t('frontend', 'xls_have_data');
                                $data[$i]['msg'] = 'fail';
                                continue;
                            }
                            $row[] = $userName;
                            if (!empty($sessionAudienceBatchUserNameResult) && in_array($userName, $sessionAudienceBatchUserNameResult)) {
                                $data[$i]['res'] = Yii::t('frontend', 're_import_data');
                                $data[$i]['msg'] = 'fail';
                                continue; /*重复导入*/
                            }
                            $findUser = FwUserDisplayInfo::findOne(['user_name' => $userName,'company_id' => $companyId]);
                            if (empty($findUser)) {
                                $data[$i]['res'] = Yii::t('common', 'user_account_not');
                                $data[$i]['msg'] = 'fail';
                                continue;
                            }
                            if (!empty($realName) && $findUser['real_name'] != $realName) {
                                $data[$i]['res'] = Yii::t('common', 'user_real_name_error');
                                $data[$i]['msg'] = 'fail';
                                continue;
                            }
                            /*if (!empty($userEmail) && $findUser['email'] != $userEmail) {
                                $data[$i]['res'] = Yii::t('common', 'user_email_error');
                                $data[$i]['msg'] = 'fail';
                                continue;
                            }*/
                            $data[$i]['fwUser'] = $findUser;
                            /*$data[$i]['res'] = Yii::t('common', 'right');*/
                            $data[$i]['res'] = '';
                            $data[$i]['msg'] = 'success';
                        } else {
                            continue;
                        }
                    }
                    $err = 'ok';
                } else {
                    $err = Yii::t('common', 'xls_error');
                }
                //$result = $data;
                $result = TArrayHelper::index($data, null, 'msg');
            } catch (\Exception $e) {
                return ['result' => 'fail', 'errmsg' => Yii::t('common', 'file_not_read')];
            }
        }
        if ($withSession && $sessionKey){
            Yii::$app->session->set($sessionKey, $result);
        }
        if ((count($result['fail']) + count($result['success'])) > 1000){
            return ['result' => 'fail', 'errmsg' => Yii::t('frontend', 'audience_best_count')];
        }
        $sessionAudienceBatchResult = array();
        if (!empty($result['fail'])) {
            foreach ($result['fail'] as $val) {
                if (!empty($val['row']) && !empty($sessionAudienceBatchUserNameResult) && in_array($val['user_name'], $sessionAudienceBatchUserNameResult)) {
                    $val['res'] = Yii::t('frontend', 're_import_data');
                    $val['msg'] = 'fail';
                }
                $sessionAudienceBatchResult['fail'][] = $val;
            }
        }
        if (!empty($result['success'])) {
            foreach ($result['success'] as $val) {
                if (!empty($val['row']) && !empty($sessionAudienceBatchUserNameResult) && in_array($val['user_name'], $sessionAudienceBatchUserNameResult)) {
                    $val['res'] = Yii::t('frontend', 're_import_data');
                    $val['msg'] = 'fail';
                }
                $sessionAudienceBatchResult['success'][] = $val;
            }
        }
        Yii::$app->session->set($sessionAudienceBatchResultKey, $sessionAudienceBatchResult);
        return ['result' => 'success', 'errmsg' => $err];
    }

    /**
     * 取去临时导入的数据
     * @param $params
     * @return array
     */
    public function getSessionImportData($params){
        //$sessionKey = "Audience_".$params['fileMd5'];
        $sessionKey = "Audience_batch_result_".$params['fileMd5'].'_'.$params['audience_batch'];
        $data = Yii::$app->session->get($sessionKey);
        $status = $params['status'];
        $count = count($data["$status"]);
        if ($count > 0){
            $page = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
            $result = array_slice($data["$status"], $page->offset, $page->limit);
            $res = ['data' => $result, 'page' => $page,];
        }else{
            $res = [
                'data' => null,
                'page' => null,
            ];
        }
        $res['successNumbers'] = count($data['success']);
        $res['failNumbers'] = count($data['fail']);
        return $res;
    }

    /**
     * 讲师添加受众
     * @param $courseId
     * @param $categoryId
     * @param $title
     * @param null $desc
     * @return array
     * @throws \Exception
     */
    public function addAudienceFromTeacher($courseId, $categoryId, $title, $desc = null){
        if (empty($courseId)) return ['result' => 'fail', 'errmsg' => ''];
        if (empty($title)) return ['result' => 'fail', 'errmsg' => ''];
        $userId = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;
        $audience = SoAudience::findOne(['source_id' => $courseId, 'audience_type' => SoAudience::AUDIEBCE_TYPE_COURSE]);
        if (!empty($audience)){
            $model = SoAudience::findOne($audience->kid);
            $model->category_id = $categoryId;
            $model->audience_name = $title;
            $model->description = $desc;
            $result = $model->update();
        }else{
            $model = new SoAudience();
            $model->company_id = $companyId;
            $model->owner_id = $userId;
            $model->category_id = $categoryId;
            $audience_code = SoAudience::setAudienceCode();
            $model->audience_code = $audience_code;
            $model->audience_name = $title;
            $model->description = $desc;
            $model->source_id = $courseId;
            $model->audience_type = SoAudience::AUDIEBCE_TYPE_COURSE;
            $model->needReturnKey = true;
            $result = $model->save();
        }
        if ($result !== false){
            $course = LnCourse::findOne($courseId);
            if ($course->course_type == LnCourse::COURSE_TYPE_FACETOFACE){
                $userList = LnCourseEnroll::findAll(['course_id' => $courseId, 'enroll_type' => LnCourseEnroll::ENROLL_TYPE_ALLOW]);
            }else{
                $userList = LnCourseReg::findAll(['course_id' => $courseId, 'reg_state' => LnCourseReg::REG_STATE_APPROVED]);
            }
            $audience_id = $model->kid;
            $modelArray = array();
            if (!empty($userList)) {
                $updateArray = array();
                foreach ($userList as $item) {
                    $modelTemp = SoAudienceMember::findOne(['audience_id' => $audience_id, 'user_id' => $item->user_id]);
                    if (empty($modelTemp)){
                        $modelTemp = new SoAudienceMember();
                        $modelTemp->start_at = time();
                        $modelTemp->audience_id = $audience_id;
                        $modelTemp->user_id = $item->user_id;
                        array_push($modelArray, $modelTemp);
                    }else{
                        array_push($updateArray, $modelTemp);
                    }
                }
                $insertArray = array();
                $errMsg = "";
                $resultId = array();
                if (!empty($modelArray)) {
                    BaseActiveRecord::batchInsertNormalMode($modelArray, $errmsg, true, $resultId);
                    $insertArray = $resultId;
                }
                $errMsg = "";
                $updateResult = array();
                if (!empty($updateArray)) {
                    BaseActiveRecord::batchUpdateNormalMode($updateArray, $errmsg, true, $updateResult);
                    if (!empty($insertArray)) {
                        $insertArray = array_merge($insertArray, $updateResult);
                    }else{
                        $insertArray = $updateResult;
                    }
                }
                $resultIdArray = array_filter($insertArray);
                $resultIdArray = array_unique($resultIdArray);
                if (!empty($resultIdArray)) {
                    $resultIdArraySql = "'".join("','", $resultIdArray)."'";
                    SoAudienceMember::updateAll(
                        [
                            'status' => SoAudienceMember::STATUS_FLAG_STOP,
                            'is_deleted' => SoAudienceMember::DELETE_FLAG_YES
                        ],
                        'kid not in ('.$resultIdArraySql.') and audience_id=:audienceId',
                        [':audienceId' => $audience_id]
                    );
                }
                return ['result' => 'success', 'errmsg' => ''];
            }else{
                return ['result' => 'success', 'errmsg' => ''];
            }
        }else{
            return ['result' => 'fail', 'errmsg' => ''];
        }
    }

    /**
     * 根据KID查询受众基础数据
     * @param $kids
     * @return bool|static[]
     */
    public function getAudienceByKid($kids){
        if (empty($kids)){
            return false;
        }
        $result = SoAudience::findAll(['kid' => $kids]);
        return $result;
    }
}