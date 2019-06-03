<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 3/21/2015
 * Time: 10:57 AM
 */
namespace common\services\framework;

use Yii;
use common\models\treemanager\FwCntManageRef;
use yii\helpers\ArrayHelper;

class CntManageRefService extends FwCntManageRef{


    /**
     * 停用关系
     * @param FwCntManageRef $targetModel
     */
    public function StopRelationship(FwCntManageRef $targetModel)
    {
       if (isset($targetModel) &&  $targetModel != null)
       {
           $sourceMode = new FwCntManageRef();

           $params = [
               ':subject_id'=>$targetModel->subject_id,
               ':subject_type'=>$targetModel->subject_type,
               ':content_id'=>$targetModel->content_id,
               ':content_type'=>$targetModel->content_type,
               ':reference_type'=>$targetModel->reference_type,
           ];

           $condition = 'subject_id = :subject_id and content_id = :content_id '
                    . 'and subject_type = :subject_type and content_type = :content_type and reference_type = :reference_type';

           $attributes = [
               'status' => self::STATUS_FLAG_STOP,
               'end_at' => time(),
           ];

           if ($this->isRelationshipExist($targetModel)) {
               $sourceMode->updateAll($attributes,$condition,$params);
           }
       }
    }


//    /**
//     * 删除关系
//     * @param FwCntManageRef $targetModel
//     */
//    public function RemoveRelationship(FwCntManageRef $targetModel)
//    {
//        if (isset($targetModel) &&  $targetModel != null)
//        {
//            $sourceMode = new FwCntManageRef();
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
     * @param FwCntManageRef $targetModel
     */
    public function startRelationship(FwCntManageRef $targetModel)
    {
        if (isset($targetModel) &&  $targetModel != null)
        {
            $cntManageModel = new FwCntManageRef();
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
     * 判断关系是否存在
     * @param FwCntManageRef $targetModel
     * @return bool
     */
    public function isRelationshipExist(FwCntManageRef $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {
            $condition = [
                'status' => self::STATUS_FLAG_NORMAL,
                'subject_id' => $targetModel->subject_id,
                'subject_type' => $targetModel->subject_type,
                'content_id' => $targetModel->content_id,
                'content_type' => $targetModel->content_type,
                'reference_type' => $targetModel->reference_type,
            ];
            $model = FwCntManageRef::findOne($condition);

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
     * @param FwCntManageRef $targetModel
     * @return array
     */
    public function getContentList(FwCntManageRef $targetModel)
    {
        if (isset($targetModel) && $targetModel != null) {

            $query = FwCntManageRef::find(false);

            $query
                ->andFilterWhere(['=', 'status', self::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['=', 'subject_id', $targetModel->subject_id])
                ->andFilterWhere(['=', 'subject_type', $targetModel->subject_type])
                ->andFilterWhere(['=', 'content_id', $targetModel->content_id])
                ->andFilterWhere(['=', 'content_type', $targetModel->content_type])
                ->andFilterWhere(['=', 'reference_type', $targetModel->reference_type]);

            $selectedResult = $query->all();

            $selectedList = ArrayHelper::map($selectedResult, 'content_id', 'content_id');

            $selected_keys = array_keys($selectedList);

            return $selected_keys;

        } else {
            return [];
        }
    }
}