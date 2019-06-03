<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 9/21/2015
 * Time: 3:04 PM
 */

namespace backend\services;


use common\models\social\SoQuestion;
use common\services\framework\UserCompanyService;
use yii\helpers\ArrayHelper;

class QuestionService extends SoQuestion
{

    /**
     * 获取问答个数 
     * @param $userId
     * @param $isSpecial
     * @return int|string
     */
    public function getQuestionCount($userId, $isSpecial)
    {
        $model = new SoQuestion();
        $query = $model->find(false);

        if (!$isSpecial) {
            $userCompanyService = new UserCompanyService();
            $companyList = $userCompanyService->getManagedListByUserId($userId, null, false);

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
