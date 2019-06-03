<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 5/24/15
 * Time: 3:06 PM
 */

namespace api\services;

use common\models\framework\FwPosition;
use common\models\framework\FwUserPosition;
use common\services\framework\ExternalSystemService;
use common\base\BaseActiveRecord;
use Yii;

class PositionService extends FwPosition{

    /**
     * 通过企业ID获取相关岗位列表信息
     * @param $companyId
     * @return array|FwPosition[]
     */
    public function getPositionListByCompanyId($companyId,$limit = 1,$offset = 0) {
        $condition = ['or',
            [ 'in', 'company_id', $companyId],
            BaseActiveRecord::getQuoteColumnName("company_id") . ' is null'];

        $model = new FwPosition();
        $result = $model->find(false)
            ->andFilterWhere($condition)
            ->andFilterWhere(['=','status', FwPosition::STATUS_FLAG_NORMAL])
            ->limit($limit)
            ->offset($offset)
            ->all();

        return $result;
    }

    /**
     * 通过企业ID获取相关岗位列表记录数信息
     * @param $companyId
     * @return Integer
     */
    public function getPositionListCountByCompanyId($companyId) {
        $condition = ['or',
            [ 'in', 'company_id', $companyId],
            BaseActiveRecord::getQuoteColumnName("company_id") . ' is null'];

        $model = new FwPosition();
        $result = $model->find(false)
            ->andFilterWhere($condition)
            ->andFilterWhere(['=','status', FwPosition::STATUS_FLAG_NORMAL])
            ->count(1);

        return $result;
    }

    /**
     * 增加岗位信息
     * @param $companyId
     * @param $systemKey
     * @param $positionList
     * @return null
     */
    public function addPosition($companyId, $systemKey, $positionList, &$checkType, &$errorMessage){
        $userPosition = null;
        $externalSystemService = new ExternalSystemService();
        foreach ($positionList as $position) {
            if ($checkType) {
                $positionAdd = true;
                $needUpdatePosition = false;
                $positionKeyType = null;
                $positionKey = null;
                $positionId = null;
                $positionCode = null;
                $positionName = null;
                $isMaster = null;

                if (isset($position['position_key_type']) && !empty($position['position_key_type'])) {
                    $positionKeyType = $position['position_key_type'];
                }
                if (isset($position['position_key']) && !empty($position['position_key'])) {
                    $positionKey = $position['position_key'];
                }

                if (isset($position['position_code']) && !empty($position['position_code'])) {
                    $positionCode = $position['position_code'];
                }

                if (isset($position['position_name']) && !empty($position['position_name'])) {
                    $positionName = $position['position_name'];
                }

                if (isset($position['is_master'])) {
                    $isMaster = $position['is_master'];
                }

                if (!empty($positionKeyType)) {
                    if ($positionKeyType == "1") {
                        $positionId = $externalSystemService->getPositionIdByPositionKey($systemKey, $positionKey);
                    } else if ($positionKeyType == "2") {
                        $positionId = $positionKey;
                    } else {
                        return null;
                    }

                    $positionModel = null;
                    if (!empty($positionId)) {
                        $positionModel = FwPosition::findOne($positionId);
                    }

                    if (isset($positionModel) && !empty($positionModel)) {
                        $positionAdd = false;
                    } else {
                        $positionModel = new FwPosition();
                        $needUpdatePosition = true;
                        $positionModel->company_id = $companyId;
                        $positionModel->position_name = $positionName;
                        $positionModel->position_code = $positionCode;
                        $positionModel->limitation = FwPosition::LIMITATION_NONE;
                    }

                    if (!$positionAdd) {
                        if ($positionCode != $positionModel->position_code) {
                            $positionModel->position_code = $positionCode;
                            $needUpdatePosition = true;
                        }

                        if ($positionName != $positionModel->position_name) {
                            $positionModel->position_name = $positionName;
                            $needUpdatePosition = true;
                        }
                    }

                    if ($needUpdatePosition) {
                        $positionModel->setScenario("api-manage");
                        if ($positionModel->validate()) {
                            $positionModel->data_from = $systemKey;
                            $positionModel->systemKey = $systemKey;

                            if ($this->isExistSamePositionCode($positionModel->kid, $positionModel->company_id, $positionModel->position_code)) {
                                $errorMessage = ["position_code" => Yii::t('common', 'exist_same_code_{value}',
                                    ['value' => Yii::t('common', 'position_code')]) . $positionModel->position_code];
                                $checkType = false;
                            } else {
                                $checkType = true;
                            }

                            if ($checkType) {
                                if ($positionAdd) {
                                    $positionModel->needReturnKey = true;
                                    if ($positionModel->save()) {
                                        $positionId = $positionModel->kid;

                                        if ($positionKeyType == "1")
                                            $externalSystemService->addExternalSystemPositionKey($systemKey, $positionKey, $positionId);
                                    }
                                } else {
                                    $positionModel->save();
                                }
                            }
                        }
                    }

                    if (!empty($positionId)) {

                        if ($isMaster === null) {
                            $isMaster = FwUserPosition::YES;
                        }

                        if ($isMaster !== FwUserPosition::YES && $isMaster !== FwUserPosition::NO) {
                            $isMaster = FwUserPosition::YES;
                        }

                        $userPosition[$positionId]["is_master"] = $isMaster;
                    }
                }
            }
        }

        return $userPosition;
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
        $userPositionService = new \common\services\framework\UserPositionService();
        return $userPositionService->isExistSamePositionCode($kid, $companyId, $positionCode);
    }
}