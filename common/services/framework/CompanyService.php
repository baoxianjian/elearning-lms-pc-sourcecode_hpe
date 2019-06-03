<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 5/15/15
 * Time: 10:54 AM
 */

namespace common\services\framework;

use common\models\framework\FwCompany;
use common\models\framework\FwCompanySetting;
use common\models\framework\FwCompanyWechat;
use common\models\framework\FwOrgnization;
use common\base\BaseActiveRecord;
use Yii;

class CompanyService extends FwCompany{

    /**
     * 获取企业数
     * @return int|string
     */
    public function getCompanyCount($withCache = false)
    {
        $cacheKey = "TotalCompanyCount";

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $model = new FwCompany();
            $query = $model->find(false);

            $result = $query->count('kid');

            if ($withCache) {
//                $dependencySql = "SELECT max(updated_at) FROM " . self::calculateTableName(FwCompany::tableName());
                self::saveToCache($cacheKey, $result);
            }
        }

        return $result;
    }

    /**
     * 获取默认注册企业
     * @return mixed|string
     */
    public function getDefaultRegisterCompanyList()
    {
        $companyModel = new FwCompany();

        $companyResult = $companyModel->find(false)
            ->andFilterWhere(['is_default_company' => FwCompany::YES])
            ->one();

        if ($companyResult == null) {
            $companyResult = $companyModel->find(false)->one();
        }

        return $companyResult;
    }

    /**
     * 根据树节点ID获取企业ID
     * @param $id
     * @return null|string
     */
    public function getCompanyIdByTreeNodeId($id)
    {
        if ($id != null && $id != "") {
            $companyModel = new FwCompany();

            $companyResult = $companyModel->findOne(['tree_node_id' => $id]);

            if ($companyResult != null)
            {
                $companyId = $companyResult->kid;
            }
            else
            {
                $companyId = null;
            }
        }
        else
        {
            $companyId = null;
        }

        return $companyId;
    }

    /**
     * 获取企业设置信息
     * @param $companyId
     * @param $code
     * @return mixed
     */
    public function getCompanySettingValueByCode($companyId,$code,$withCache = true)
    {
        $cacheKey = "CompanySetting_CompanyId_" . $companyId . "_Code_" . $code;

        $result = BaseActiveRecord::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $model = new FwCompanySetting();
            $result = $model->find(false)
                ->andFilterWhere(['=','company_id',$companyId])
                ->andFilterWhere(['=','code',$code])
                ->one();
            BaseActiveRecord::saveToCache($cacheKey, $result);
        }

        if (!empty($result))
            return $result->value;
        else
            return null;
    }


    /**
     * 获取企业微信公众号信息
     * @param $companyId
     * @return mixed
     */
    public function getCompanyWechat($companyId)
    {
        $model = new FwCompanyWechat();
        $result = $model->find(false)
            ->andFilterWhere(['=','company_id',$companyId])
            ->one();

        if (!empty($result))
            return $result;
        return null;
    }


    /**
     * 判断是否存在相同的二级域名
     * @param string $kid 公司id
     * @param string $secondLevelDomain 二级域名
     * @return bool
     */
    public function isExistSameSecondLevelDomain($kid, $secondLevelDomain)
    {
        $model = new FwCompany();
        $query = $model->find(false);

        $count = $query->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'second_level_domain', $secondLevelDomain])
            ->count();

        if ($count > 0) {
            return true;
        }
        else {
            return false;
        }
    }


    /**
     * 当前节点是否包含子企业
     * @param $kid
     * @return bool
     */
    public function isExistSubCompany($kid)
    {
        $model = new FwCompany();
        $query = $model->find(false);

        $query->andFilterWhere(['=', 'parent_company_id', $kid]);

        $count = $query->count(1);

        if ($count > 0) {
            return true;
        }
        else {
            return false;
        }
    }


    /**
     * 当前节点是否包含组织
     * @param $kid
     * @return bool
     */
    public function isExistOrgnization($kid)
    {
        $model = new FwOrgnization();
        $query = $model->find(false);

        $query->andFilterWhere(['=', 'company_id', $kid]);

        $count = $query->count(1);

        if ($count > 0) {
            return true;
        }
        else {
            return false;
        }
    }
}