<?php
/**
 * Created by PhpStorm.
 * User: Alex Liu
 * Date: 2016/5/6
 * Time: 18:04
 */
namespace backend\services;

use common\models\framework\FwDomainWorkplace;
use common\base\BaseActiveRecord;
use Yii;

class DomainWorkplaceService extends FwDomainWorkplace
{


    /**
     * 根据ContentId列表批量停用关系
     * @param FwDomainWorkplace $targetModel
     */
    public function stopRelationshipByContentIdList($contentIds, $contentType, $referenceType = null, $subjectType = null)
    {
        if (!empty($contentIds) && !empty($contentType)) {
            $sourceMode = new FwDomainWorkplace();

            if (!empty($referenceType)) {
                $params = [
//                ':subject_id'=>$targetModel->subject_id,
//                ':subject_type'=>$targetModel->subject_type,
//                ':content_id'=>$targetModel->content_id,
                    ':content_type' => $contentType,
//                ':reference_type'=>$targetModel->reference_type,
                ];

                $condition = BaseActiveRecord::getQuoteColumnName("content_id") . ' in (' . $contentIds . ')' .
                    ' and ' . BaseActiveRecord::getQuoteColumnName("content_type") . ' = :content_type';
            } else {
                $params = [
//                ':subject_id'=>$targetModel->subject_id,
//                    ':subject_type' => $subjectType,
//                ':content_id'=>$targetModel->content_id,
                    ':content_type' => $contentType,
                    ':reference_type' => $referenceType,
                ];

                $condition = BaseActiveRecord::getQuoteColumnName("content_id") . ' in (' . $contentIds . ')' .
                    ' and ' . BaseActiveRecord::getQuoteColumnName("content_type") . ' = :content_type' .
                    ' and ' . BaseActiveRecord::getQuoteColumnName("reference_type") . ' = :reference_type';
            }

            $attributes = [
                'status' => self::STATUS_FLAG_STOP,
                'end_at' => time(),
            ];

//            if ($this->IsRelationshipExist($targetModel)) {
            $sourceMode->updateAll($attributes, $condition, $params);
//            }
        }
    }


    /**
     * 根据SubjectId列表批量停用关系
     * @param FwDomainWorkplace $targetModel
     */
    public function stopRelationshipByWorkplaceIdList($workplaceId)
    {
        if (!empty($workplaceId)) {
            $sourceMode = new FwDomainWorkplace();

            $params = [
                ':workplace_id' => $workplaceId,
                ':status' => self::STATUS_FLAG_NORMAL,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("workplace_id") . ' = :workplace_id' .
                ' and ' . BaseActiveRecord::getQuoteColumnName("status") . ' = :status';

            $attributes = [
                'status' => self::STATUS_FLAG_STOP,
                'end_at' => time(),
            ];

//            if ($this->IsRelationshipExist($targetModel)) {
            $sourceMode->updateAll($attributes, $condition, $params);
//            }
        }
    }

//    /**
//     * 删除关系
//     * @param FwDomainWorkplace $targetModel
//     */
//    public function RemoveRelationship(FwDomainWorkplace $targetModel)
//    {
//        if (isset($targetModel) &&  $targetModel != null)
//        {
//            $sourceMode = new FwDomainWorkplace();
//
//            $params = [
//                ':subject_id'=>$targetModel->subject_id,
//                ':subject_type'=>$targetModel->subject_type,
//                ':content_id'=>$targetModel->content_id,
//                ':content_type'=>$targetModel->content_type,
//                ':reference_type'=>$targetModel->reference_type,
//            ];
//
//            $condition = 'tree_node_id = :tree_node_id and content_id = :content_id '
//                . 'and subject_type = :subject_type and content_type = :content_type and reference_type = :reference_type';
//
//
//
//            if ($this->IsRelationshipExist($targetModel)) {
//                $sourceMode->deleteAll($condition, $params);
//            }
//        }
//    }


    /**
     * 启用关系
     * @param FwDomainWorkplace $targetModel
     */
    public function startRelationship(FwDomainWorkplace $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $cntManageModel = new FwDomainWorkplace();
            $cntManageModel->subject_id = $targetModel->subject_id;
            $cntManageModel->subject_type = $targetModel->subject_type;
            $cntManageModel->content_id = $targetModel->content_id;
            $cntManageModel->content_type = $targetModel->content_type;
            $cntManageModel->reference_type = $targetModel->reference_type;
            $cntManageModel->status = self::STATUS_FLAG_NORMAL;
            $cntManageModel->start_at = time();

            if (!$this->isRelationshipExist($targetModel)) {
                $cntManageModel->save();
            }

        }
    }

    /**
     * 批量启用关系
     * @param FwDomainWorkplace $targetModel
     */
    public function batchStartRelationship($targetModels)
    {
        if (isset($targetModels) && $targetModels != null && count($targetModels) > 0) {
            BaseActiveRecord::batchInsertSqlArray($targetModels);
        }
    }

    /**
     * 判断关系是否存在
     * @param FwDomainWorkplace $targetModel
     * @return bool
     */
    public function isRelationshipExist(FwDomainWorkplace $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $condition = [
                'status' => self::STATUS_FLAG_NORMAL,
                'domain_id' => $targetModel->domain_id,
                'workplace_id' => $targetModel->workplace_id,
            ];
            $model = FwDomainWorkplace::findOne($condition);

            if ($model != null)
                return true;
            else
                return false;
        } else {
            return true;
        }
    }
}