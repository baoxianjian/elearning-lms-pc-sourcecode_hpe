<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 3/21/2015
 * Time: 10:57 AM
 */
namespace common\services\learning;

use common\models\learning\LnResourceAudience;
use common\services\social\AudienceManageService;
use Yii;
use yii\helpers\ArrayHelper;

class ResourceAudienceService extends LnResourceAudience
{

    /**
     * 查询受众资源表
     * @param $resourceId
     * @param $companyId
     * @param $resourceType
     * @param string $status
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getResourceAudience($resourceId, $companyId, $resourceType, $status = self::STATUS_FLAG_NORMAL){
        if (empty($resourceId)) return array();
        $model = LnResourceAudience::find(false);
        $result = $model->andFilterWhere(['=', 'resource_id', $resourceId])
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['=', 'resource_type', $resourceType])
            ->andFilterWhere(['=', 'status', $status])
            ->select('audience_id')
            ->all();
        if (!empty($result)) {
            $selectedList = ArrayHelper::map($result, 'audience_id', 'audience_id');
            $selected_keys = array_keys($selectedList);
            return $selected_keys;
        }
        return array();
    }

    /**
     * 更新数据
     * @param $resourceId
     * @param $status
     * @param $resourceType
     */
    public function updateResourceAudience($resourceId, $status, $resourceType){
        LnResourceAudience::updateAll(['status' => $status], 'resource_id=:resource_id and resource_type=:resource_type', [':resource_id' => $resourceId, 'resource_type' => $resourceType]);
    }

    /**
     * 查询受众资源
     * @param $audienceId
     * @param $resourceId
     * @return mixed|null|static
     */
    public function getDataByAudienceId($audienceId, $resourceId){
        $find = LnResourceAudience::findOne(['resource_id' => $resourceId, 'audience_id' => $audienceId]);
        return $find;
    }

    /**
     * 保存受众资源
     * @param LnResourceAudience $saveData
     * @return bool
     */
    public function saveData(LnResourceAudience $saveData){
        if (empty($saveData)){
            return false;
        }
        $saveData->save();
    }

    /**
     * 判断用户是否在此受众范围内
     * @param $userId
     * @param $resourceId
     * @return bool
     */
    public function isResourceAudience($userId, $resourceId){
        $resourceAudience = LnResourceAudience::findOne(['resource_id' => $resourceId, 'status' => LnResourceAudience::STATUS_FLAG_NORMAL]);
        if (empty($resourceAudience)) return true;
        $audienceService = new AudienceManageService();
        $user = $audienceService->getAudienceMember($resourceAudience->audience_id);
        if (empty($user)) return true;
        if (!in_array($userId, $user)){
            return false;
        }
        return true;
    }
}