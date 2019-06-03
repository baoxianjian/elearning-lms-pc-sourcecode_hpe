<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 5/24/15
 * Time: 3:06 PM
 */

namespace api\services;

use common\models\framework\FwOrgnization;
use common\models\framework\FwUser;
use Yii;

class OrgnizationService extends FwOrgnization{


    /**
     * 通过企业ID获取相关组织列表信息
     * @param $companyId
     * @param int $limit
     * @param int $offset
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getOrgnizationListByCompanyId($companyId, $limit = 1, $offset = 0) {
        $orgnizationModel = new FwOrgnization();
        $result = $orgnizationModel->find(false)
            ->andFilterWhere(['=','company_id', $companyId])
            ->andFilterWhere(['=','status', FwOrgnization::STATUS_FLAG_NORMAL])
            ->limit($limit)
            ->offset($offset)
            ->all();

        return $result;
    }

    /**
     * 通过企业ID获取相关组织列表记录数信息
     * @param $companyId
     * @return Integer
     */
    public function getOrgnizationListCountByCompanyId($companyId) {
        $orgnizationModel = new FwOrgnization();
        $result = $orgnizationModel->find(false)
            ->andFilterWhere(['=','company_id', $companyId])
            ->andFilterWhere(['=','status', FwOrgnization::STATUS_FLAG_NORMAL])
            ->count(1);

        return $result;
    }

    /**
     * 判断组织代码是否重复
     * @param $kid
     * @param $companyId
     * @param $orgnizationCode
     * @return bool
     */
    public function isExistSameOrgnizationCode($kid, $companyId, $orgnizationCode)
    {
        $orgService = new \common\services\framework\OrgnizationService();
        return $orgService->isExistSameOrgnizationCode($kid, $companyId, $orgnizationCode);
    }

    /**
     * 更新组织新老板
     * @param string $orgnizationId 组织ID
     * @param string $managerId 老板ID
     * @param integer|null $sequenceNumber 部门排序
     */
    public function updateOrgnizationNewManager($systemKey,$orgnizationId, $managerId, $sequenceNumber,$forceUpdate = false) {
        $orgnizationModel = FwOrgnization::findOne($orgnizationId);
        $currentManagerId = null;
        $newManagerId = null;
        if (!empty($orgnizationModel)) {
            $currentManagerId = $orgnizationModel->orgnization_manager_id;

            if (!empty($currentManagerId)) {
                if ($currentManagerId != $managerId) {
                    $currentManagerModel = FwUser::findOne($currentManagerId);
                    if (!empty($currentManagerModel)) {
                        //要确保当前用户目前仍然还是老板
                        if ($currentManagerModel->manager_flag == FwUser::MANAGER_FLAG_YES && $currentManagerModel->status == FwUser::STATUS_FLAG_NORMAL) {
                            $currentManagerSequenceNumber = $currentManagerModel->sequence_number;
                            //为null的顺序，始终优先
                            if ($currentManagerSequenceNumber == null && $sequenceNumber == null) {
                                $newManagerId = $managerId;
                            } else if ($currentManagerSequenceNumber != null && $sequenceNumber != null && $sequenceNumber <= $currentManagerSequenceNumber) {
                                //当新导入经理的排序号，小于等于之前经理，则会替换，否则保持不变
                                $newManagerId = $managerId;
                            } else if ($currentManagerSequenceNumber != null && $sequenceNumber == null) {
                                $newManagerId = $managerId;
                            }
                        } else {
                            $newManagerId = $managerId;
                        }
                    }
                }
            } else {
                $newManagerId = $managerId;
            }

            if ($forceUpdate || (!empty($newManagerId) && $currentManagerId != $newManagerId)) {
                if ($forceUpdate) {
                    $newManagerId = $managerId;
                }
                $orgnizationModel->orgnization_manager_id = $newManagerId;
                $orgnizationModel->systemKey = $systemKey;
                $orgnizationModel->save();
            }
        }
    }
}