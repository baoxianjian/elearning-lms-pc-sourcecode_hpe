<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/4/26
 * Time: 10:41
 */
namespace common\services\api;

use common\models\framework\FwCompany;
use common\models\framework\FwCompanySystem;
use common\base\BaseActiveRecord;
use Yii;
use common\helpers\TMessageHelper;
use common\services\framework\ExternalSystemService;
use common\traits\ResponseTrait;

class CompanyService extends FwCompany{

    public $systemKey;
    public function __construct($system_key,array $config = [])
    {
        $this->systemKey = $system_key;
        parent::__construct($config);
    }
    use ResponseTrait;
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


    public function setSystemKey($key) {
        $this->systemKey = $key;
    }

    /**
     * 获取企业记录数
     * @param $is_running
     * @return array
     */
    public function getCompanyCount() {
        $externalSystemService = new ExternalSystemService();
        $model = $externalSystemService->findBySystemKey($this->systemKey);
        $externalSystemId = $model->kid;
        $count = $this->getCompanyListCountBySystemId($externalSystemId);

        $jsonResult["count"] = $count;
        $code = "OK";
        $result = TMessageHelper::resultBuild($this->systemKey, $code, null, null, $jsonResult);
        return $result;
    }

    /**
     * 
     * @return array
     */
    public function decryptPaginate() {
        $errorCode = null;
        $errorMessage = null;
        $default = ['limit' => 1,'offset' => 0];
        $isGet = Yii::$app->request->isGet;
        $queryParams = Yii::$app->request->getQueryParams();
        $rawBody = Yii::$app->request->getRawBody();

        if(!$isGet) {
            $rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);
            if(!empty($errorCode)) {
                return $default;
            }
            $bodyParams = json_decode($rawDecryptBody, true);
            $default['limit'] = isset($bodyParams['limit']) ? intval($bodyParams['limit']) : 1;
            $default['offset'] = isset($bodyParams['offset']) ? intval($bodyParams['offset']) : 0;
        } else {
            $offset = (int) TMessageHelper::decryptMsg($this->systemKey, $queryParams['offset'], $errorCode, $errorMessage);
            if(!empty($errorCode)) {
                return $default;
            }

            $default['offset'] = $offset;
            $limit = TMessageHelper::decryptMsg($this->systemKey, $queryParams['limit'], $errorCode, $errorMessage);
            $limit = empty($limit) ? 1 : $limit;
            if(!empty($errorCode)) {
                return $default;
            }
            $default['limit'] = $limit;
        }

        return $default;
    }

    /**
     * 获取企业信息
     * @param $actionId
     * @return array
     */
    public function getCompanyInfo($actionId) {
        $externalSystemService = new ExternalSystemService();
        $key = $externalSystemService->findBySystemKey($this->systemKey);
        $externalSystemId = $key->kid;
        $paginate = $this->decryptPaginate();
        $models = $this->getCompanyListBySystemId($externalSystemId,$paginate['limit'],$paginate['offset']);

        if(empty($models)) {
            return $this->exception(['code' => $actionId,'number' => '006','name' => Yii::t('common', 'data_not_exist')]);
        }
        
        $filter = function($val) {
            return empty($val) ? null : $val;
        };
        $fields = [
            'kid' => ['as' => 'company_id'],
            'company_code' => ['as' => 'company_code'],
            'company_name' => ['as' => 'company_name'],
            'org_certificate_code' => ['as' => 'org_certificate_code','filter' => $filter],
            'representative' => ['as' => 'representative','filter' => $filter],
            'status' => ['as' => 'status'],
            'description' => ['as' => 'description','filter' => $filter]
        ];
        
        $tmp = [];
        $ret = ['company' => []];

        foreach($models as $model) {
            foreach($fields as $field => $item) {
                $tmp[$item['as']] = is_callable($item['filter']) ? $item['filter']($model->{$field}) : $model->{$field};
            }
            if($paginate['limit'] == 1) {
                $ret['company'] = $tmp;
            } else {
                $ret['company'][] = $tmp;
            }
            $tmp = [];
        }
        $ret = TMessageHelper::resultBuild($this->systemKey, 'OK', null, null, $ret);
        return $ret;
    }
}