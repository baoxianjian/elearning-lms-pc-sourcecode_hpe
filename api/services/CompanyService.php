<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 5/24/15
 * Time: 3:06 PM
 */

namespace api\services;

use common\models\framework\FwCompany;
use common\models\framework\FwCompanySystem;
use common\models\framework\FwUser;
use common\models\treemanager\FwTreeNode;
use common\base\BaseActiveRecord;
use Yii;
use yii\helpers\Url;
use yii\web\Link;
use yii\web\Linkable;

class CompanyService extends FwCompany{


    /**
     * 通过客户端令牌获取相关企业基本信息
     * @param $systemId
     * @return array|FwCompany[]
     */
    public function getCompanyListBySystemId($systemId,$limit = 1,$offset = 0) {

        $systemModel = new FwCompanySystem();
        $companyQuery = $systemModel->find(false)
            ->select(BaseActiveRecord::getQuoteColumnName("company_id"))
            ->andFilterWhere(['=','system_id',$systemId])
            ->andFilterWhere(['=','status',FwCompanySystem::STATUS_FLAG_NORMAL])
            ->distinct();

        $companyQuerySql = $companyQuery->createCommand()->rawSql;

        $companyModel = new FwCompany();
        $result = $companyModel->find(false)
            ->andWhere(BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $companyQuerySql . ')')
            ->andFilterWhere(['=','status',FwCompany::STATUS_FLAG_NORMAL])
            ->limit($limit)
            ->offset($offset)
            ->all();

        return $result;
    }


    /**
     * 通过客户端令牌获取相关企业记录数信息
     * @param $systemId
     * @return Integer
     */
    public function getCompanyListCountBySystemId($systemId) {

        $systemModel = new FwCompanySystem();
        $companyQuery = $systemModel->find(false)
            ->select(BaseActiveRecord::getQuoteColumnName("company_id"))
            ->andFilterWhere(['=','system_id',$systemId])
            ->andFilterWhere(['=','status',FwCompanySystem::STATUS_FLAG_NORMAL])
            ->distinct();

        $companyQuerySql = $companyQuery->createCommand()->rawSql;

        $companyModel = new FwCompany();
        $result = $companyModel->find(false)
            ->andWhere(BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $companyQuerySql . ')')
            ->andFilterWhere(['=','status',FwCompany::STATUS_FLAG_NORMAL])
            ->count(1);

        return $result;
    }
}