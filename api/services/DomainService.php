<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 5/24/15
 * Time: 3:06 PM
 */
// deprecated by GROOT at 2016.04.28
namespace api\services;

use common\models\framework\FwCompany;
use common\models\framework\FwCompanySystem;
use common\models\framework\FwDomain;
use common\models\framework\FwUser;
use Yii;
use yii\helpers\Url;
use yii\web\Link;
use yii\web\Linkable;

class DomainService extends FwDomain{


    /**
     * 通过企业ID获取相关域列表信息
     * @param $companyId
     * @return array|FwDomain[]
     */
    public function getDomainListByCompanyId($companyId,$limit = 1,$offset = 0) {
        $domainModel = new FwDomain();
        $result = $domainModel->find(false)
            ->andFilterWhere(['=','company_id', $companyId])
            ->andFilterWhere(['=','status', FwDomain::STATUS_FLAG_NORMAL])
            ->limit($limit)
            ->offset($offset)
            ->all();

        return $result;
    }

    /**
     * 通过企业ID获取相关域列表记录数信息
     * @param $companyId
     * @return Integer
     */
    public function getDomainListCountByCompanyId($companyId) {
        $domainModel = new FwDomain();
        $result = $domainModel->find(false)
            ->andFilterWhere(['=','company_id', $companyId])
            ->andFilterWhere(['=','status', FwDomain::STATUS_FLAG_NORMAL])
            ->count(1);

        return $result;
    }

    /**
     * 判断域代码是否重复
     * @param $kid
     * @param $companyId
     * @param $domainCode
     * @return bool
     */
    public function isExistSameDomainCode($kid, $companyId, $domainCode)
    {
        $domainService = new \common\services\framework\DomainService();
        return $domainService->isExistSameDomainCode($kid, $companyId, $domainCode);
    }
}