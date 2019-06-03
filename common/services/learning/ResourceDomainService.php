<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 3/21/2015
 * Time: 10:57 AM
 */
namespace common\services\learning;

use common\models\learning\LnResourceDomain;
use common\models\learning\LnCourseware;
use Yii;
use yii\helpers\ArrayHelper;

class ResourceDomainService extends LnResourceDomain
{


    /**
     * 停用关系
     * @param LnResourceDomain $targetModel
     */
    public function StopRelationship(LnResourceDomain $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $sourceMode = new LnResourceDomain();

            $params = [
                ':resource_id' => $targetModel->resource_id,
                ':resource_type' => $targetModel->resource_type,
                ':domain_id' => $targetModel->domain_id,
            ];

            $condition = 'resource_id = :resource_id and resource_type = :resource_type '
                . 'and domain_id = :domain_id';

            $attributes = [
                'status' => self::STATUS_FLAG_STOP,
                'end_at' => time(),
            ];

            if ($this->IsRelationshipExist($targetModel)) {
                $sourceMode->updateAll($attributes, $condition, $params);
            }
        }
    }

    /**
     * 启用关系
     * @param LnResourceDomain $targetModel
     */
    public function startRelationship(LnResourceDomain $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $cntManageModel = new LnResourceDomain();
            $cntManageModel->resource_id = $targetModel->resource_id;
            $cntManageModel->domain_id = $targetModel->domain_id;
            $cntManageModel->company_id = $targetModel->company_id;
            $cntManageModel->resource_type = $targetModel->resource_type;
            $cntManageModel->status = self::STATUS_FLAG_NORMAL;
            $cntManageModel->start_at = time();

            if (!$this->IsRelationshipExist($targetModel)) {
                $cntManageModel->save();
            }

        }
    }

    /**
     * 检查资源是否在域中有效
     * @param $resourceId
     * @param $resourceType
     * @param $domainIdList
     * @return bool
     */
    public function IsRelationshipDomainValidated($resourceId, $resourceType, $domainIdList)
    {
        $resourceModel = new LnResourceDomain();
        $resourceCount = $resourceModel->find(false)
            ->andFilterWhere(['in', 'domain_id', $domainIdList])
            ->andFilterWhere(['=', 'status', LnResourceDomain::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'resource_type', $resourceType])
            ->andFilterWhere(['=', 'resource_id', $resourceId])
            ->count('kid');

        if ($resourceCount == 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 判断关系是否存在
     * @param LnResourceDomain $targetModel
     * @return bool
     */
    public function IsRelationshipExist(LnResourceDomain $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $condition = [
                'status' => self::STATUS_FLAG_NORMAL,
                'resource_id' => $targetModel->resource_id,
                'resource_type' => $targetModel->resource_type,
                'domain_id' => $targetModel->domain_id,
            ];
            $model = LnResourceDomain::findOne($condition);

            if ($model != null)
                return true;
            else
                return false;
        } else {
            return true;
        }
    }

    /**
     * 获取相关内容列表
     * @param LnResourceDomain $targetModel
     * @return array
     */
    public function getContentList(LnResourceDomain $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {

            $query = LnResourceDomain::find(false);

            $query
                ->andFilterWhere(['=', 'status', self::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['=', 'resource_id', $targetModel->resource_id])
                ->andFilterWhere(['=', 'resource_type', $targetModel->resource_type]);

            $selectedResult = $query->all();

            $selectedList = ArrayHelper::map($selectedResult, 'domain_id', 'domain_id');

            $selected_keys = array_keys($selectedList);

            return $selected_keys;

        } else {
            return [];
        }
    }

    public function GetHtmlfileList($page)
    {
        $LnCoursewareModel = LnCourseware::find(false)
            ->limit(10)
            ->andFilterWhere(['courseware_type' => 0])
            ->andFilterWhere(['entry_mode' => 0])
            ->offset($this->getOffset($page, 10))
            ->all();
        return $LnCoursewareModel;
    }

    public function updateStatus($resourceId,$resourceType,$status) {
        $attributes = ['status'=>$status];
        $condition = "resource_id=:resource_id and resource_type =:resource_type";
        $param =  [
            ':resource_id' => $resourceId,
            ':resource_type' => $resourceType
        ];
        return LnResourceDomain::updateAll($attributes,$condition,$param);
    }
//    public function getOffset($page, $size)
//    {
//        $_page = (int)$page - 1;
//
//        return $size < 1 ? 0 : $_page * $size;
//    }
}