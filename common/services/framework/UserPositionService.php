<?php
/**
 * Created by PhpStorm.
 * User: t62539
 * Date: 2/20/2016
 * Time: 11:19 PM
 */

namespace common\services\framework;


use common\models\framework\FwPosition;
use common\models\framework\FwUserPosition;
use common\base\BaseActiveRecord;

class UserPositionService  extends FwUserPosition
{
    /**
     * 停用岗位相关所有权限
     * @param $roleIds
     */
    public function stopRelationshipByUserId($userId)
    {
        $sourceMode = new FwUserPosition();

        $params = [
            ':user_id'=>$userId,
            ':status'=>self::STATUS_FLAG_NORMAL,
        ];

        $condition = BaseActiveRecord::getQuoteColumnName("user_id") . ' = :user_id'
            . ' and ' . BaseActiveRecord::getQuoteColumnName("status") . ' = :status';

        $attributes = [
            'status' => self::STATUS_FLAG_STOP,
            'end_at' => time(),
        ];

        $sourceMode->updateAll($attributes,$condition,$params);
    }

    /**
     * 批量启用关系
     * @param FwUserPosition $targetModel
     */
    public function batchStartRelationship($targetModels)
    {
        if (isset($targetModels) &&  $targetModels != null && count($targetModels) > 0)
        {
            BaseActiveRecord::batchInsertSqlArray($targetModels);
        }
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
        $model = new FwPosition();
        $query = $model->find(false);

        $query->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'position_code', $positionCode]);

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
}