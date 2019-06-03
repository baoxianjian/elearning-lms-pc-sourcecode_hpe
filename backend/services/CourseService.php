<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 9/21/2015
 * Time: 3:04 PM
 */

namespace backend\services;


use common\models\learning\LnCourse;
use common\models\learning\LnResourceDomain;
use common\services\framework\UserDomainService;
use common\base\BaseActiveRecord;
use yii\helpers\ArrayHelper;

class CourseService  extends LnCourse
{

    /**
     * 获取相关课程总数
     * @param $userId
     * @param $isSpecial
     * @return int|string
     */
    public function getCourseCount($userId, $isSpecial){
//        $resourceDomainService = new LnResourceDomain();
//        $resourceResultSql = $resourceDomainService->find(false)
//            ->andFilterWhere(['in','domain_id', $domainIdList])
//            ->andFilterWhere(['=','resource_type',LnResourceDomain::RESOURCE_TYPE_COURSE])
//            ->select('resource_id')
//            ->distinct()
//            ->createCommand()->getRawSql();

        $model = new LnCourse();
        $query = $model->find(false);

        if (!$isSpecial) {
            $userDomainService = new UserDomainService();
            $domainList = $userDomainService->getSearchListByUserId($userId, null, false);

            if (isset($domainList) && $domainList != null) {
                $selectedList = ArrayHelper::map($domainList, 'kid', 'kid');

                $domainIdList = array_keys($selectedList);
            }
            else {
                $domainIdList = null;
            }

            $query->innerJoinWith('lnResourceDomains')
                ->andFilterWhere(['in',LnResourceDomain::tableName() . '.' . BaseActiveRecord::getQuoteColumnName("domain_id"), $domainIdList]);
        }


//            ->andFilterWhere(['=','resource_type',LnResourceDomain::RESOURCE_TYPE_COURSE])
//            ->andWhere('kid in (' . $resourceResultSql . ')')

        $result = $query->count(1);
        return $result;
    }

}
