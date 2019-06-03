<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 9/21/2015
 * Time: 3:04 PM
 */

namespace backend\services;


use common\models\learning\LnInvestigation;
use common\services\framework\UserCompanyService;
use yii\helpers\ArrayHelper;

class InvestigationService extends LnInvestigation
{

    /**
     * 获取调查个数
     * @param $userId
     * @param $isSpecial
     * @return int|string
     */
    public function getInvestigationCount($userId, $isSpecial){
        $model = new LnInvestigation();
        $query = $model->find(false);

        if (!$isSpecial) {
            $userCompanyService = new UserCompanyService();
            $companyList = $userCompanyService->getManagedListByUserId($userId,null,false);

            if (isset($companyList) && $companyList != null) {
                $selectedList = ArrayHelper::map($companyList, 'kid', 'kid');

                $companyIdList = array_keys($selectedList);
            }
            else {
                $companyIdList = null;
            }

            $query->andFilterWhere(['in', 'company_id', $companyIdList]);
        }

        $result = $query->count(1);

        return $result;
    }

}
