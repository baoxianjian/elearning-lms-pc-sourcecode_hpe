<?php


namespace common\services\framework;

use common\models\framework\FwExternalSystem;
use common\models\framework\FwExternalSystemValue;
use common\models\framework\FwUser;
use common\base\BaseActiveRecord;
use Yii;

class ExternalSystemService extends FwExternalSystem{


    /**
     * 通过系统Key找到Model
     * @param $systemKey
     * @param bool $withCache
     * @return mixed|null|static
     */
    public function findBySystemKey($systemKey, $withCache = true)
    {
        $cacheKey = "FwExternalSystem_SystemKey_" . $systemKey;

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $result = FwExternalSystem::findOne(['system_key' => $systemKey]);
            self::saveToCache($cacheKey, $result);
        }

        return $result;
    }

    /**
     * 根据外部系统代码获取外部系统ID
     * @param $systemCode
     * @return string
     */
    public function getExternalSystemIdByExternalSystemCode($systemCode)
    {
        $externalSystemId = FwExternalSystem::findOne(['system_code'=>$systemCode])->kid;

        return $externalSystemId;
    }


    /**
     * 根据外部系统代码获取外部系统数据
     * @param $systemCode
     * @return FwExternalSystem
     */
    public function getExternalSystemInfoByExternalSystemCode($systemCode, $withCache = true)
    {
        $cacheKey = "ExternalSystem_Code_" . $systemCode;

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);
        if (empty($result) && !$hasCache) {
            $model = new FwExternalSystem();

            $result = $model->findOne(['system_code' => $systemCode]);

            self::saveToCache($cacheKey, $result);
        }

        return $result;
    }


    /**
     * 判断外部系统是否正在运行中
     * @param $externalSystemId
     * @return bool
     */
    public function isExternalSystemRunning($externalSystemId)
    {
        $isRunning = false;

        if (!empty($externalSystemId)) {
            $model = FwExternalSystem::findOne($externalSystemId);
            if (!empty($model)) {

                $isRunning = $model->status == FwExternalSystem::STATUS_FLAG_NORMAL ? true : false;
            }
        }

        return $isRunning;
    }

    /**
     * 获取访问令牌信息
     * @param $systemKey
     * @param $userId
     * @return array
     */
    public function getAccessTokenArrayBySystemKey($systemKey, $userId, $withCache = true)
    {
        $cacheKey = "GetAccessToken_UserId_".$userId."_SystemKey_" . $systemKey;

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);
        $currentTime = time();

        if (empty($result) && !$hasCache) {
            $model = $this->findBySystemKey($systemKey);
            $externalSystemId = $model->kid;

            $model = new FwExternalSystemValue();
            $result = $model->findOne([
                'system_id' => $externalSystemId,
                'object_id' => $userId,
                'object_type' => FwExternalSystemValue::OBJECT_TYPE_USER,
                'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
                'value_type' => FwExternalSystemValue::VALUE_TYPE_ACCESS_TOKEN,
            ]);

            self::saveToCache($cacheKey, $result);
        }

        $accessTokenArray = [];
        if (!empty($result))
        {
            if (!empty($result->end_at) && $result->end_at > $currentTime) {
                $accessTokenArray['access_token'] = $result->value;
                $accessTokenArray['expire'] =  date('Y-m-d H:i:s',$result->end_at);
            }
            else if (empty($result->end_at)) {
                $accessTokenArray['access_token'] = $result->value;
                $accessTokenArray['expire'] = null;
            }
        }

        return $accessTokenArray;
    }

    /**
     * 根据外部用户主键获取用户ID
     * @param $systemKey
     * @param $userKey
     * @return null|string
     */
    public function getUserIdByUserKey($systemKey, $userKey)
    {
        if (empty($userKey))
            return null;

        $model = $this->findBySystemKey($systemKey);
        $externalSystemId = $model->kid;

        $model =  new FwExternalSystemValue();
        $result = $model->findOne([
            'system_id' => $externalSystemId,
            'object_type' => FwExternalSystemValue::OBJECT_TYPE_USER,
            'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
            'value' => $userKey,
            'value_type' => FwExternalSystemValue::VALUE_TYPE_USER_KEY,
        ]);

        if (!empty($result))
        {
            return $result->object_id;
        }

        return null;
    }

    /**
     * 删除用户外键信息
     * @param $userId
     */
    public function deleteUserInfoByUserId($userId)
    {
        $params = [
            'object_id' => $userId,
            'object_type' => FwExternalSystemValue::OBJECT_TYPE_USER,
            'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
        ];
        
        $condition = BaseActiveRecord::getQuoteColumnName("object_id") . ' = :object_id ' . 
            ' and ' . BaseActiveRecord::getQuoteColumnName("object_type") . ' = :object_type ' .
            ' and ' . BaseActiveRecord::getQuoteColumnName("status") . ' = :status ';

        $model = new FwExternalSystemValue();
        $model->deleteAll($condition, $params);
    }

    /**
     * 删除岗位外键信息
     * @param $positionId
     */
    public function deletePositionInfoByPositionId($positionId)
    {
        $params = [
            'object_id' => $positionId,
            'object_type' => FwExternalSystemValue::OBJECT_TYPE_POSITION,
            'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
        ];

        $condition = BaseActiveRecord::getQuoteColumnName("object_id") . ' = :object_id ' .
            ' and ' . BaseActiveRecord::getQuoteColumnName("object_type") . ' = :object_type ' .
            ' and ' . BaseActiveRecord::getQuoteColumnName("status") . ' = :status ';
        
//        $condition = "object_id = :object_id and object_type = :object_type and status = :status";

        $model = new FwExternalSystemValue();
        $model->deleteAll($condition, $params);
    }


    /**
     * 删除用户外键信息
     * @param $userIds
     */
    public function deleteUserInfoByUserIdList($userIds)
    {
        $params = [
            'object_type' => FwExternalSystemValue::OBJECT_TYPE_USER,
            'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
        ];

        $condition = BaseActiveRecord::getQuoteColumnName("object_id") . ' in (' . $userIds . ')'.
            ' and ' . BaseActiveRecord::getQuoteColumnName("object_type") . ' = :object_type ' .
            ' and ' . BaseActiveRecord::getQuoteColumnName("status") . ' = :status ';
        

        $model = new FwExternalSystemValue();
        $model->deleteAll($condition, $params);
    }

    /**
     * 根据组织外键获取组织ID
     * @param $systemKey
     * @param $orgnizationKey
     * @return null|string
     */
    public function getOrgnizationIdByOrgnizationKey($systemKey, $orgnizationKey)
    {
        if (empty($orgnizationKey))
            return null;

        $model = $this->findBySystemKey($systemKey);
        $externalSystemId = $model->kid;

        $model =  new FwExternalSystemValue();
        $result = $model->findOne([
            'system_id' => $externalSystemId,
            'object_type' => FwExternalSystemValue::OBJECT_TYPE_ORGNIZATION,
            'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
            'value' => $orgnizationKey,
            'value_type' => FwExternalSystemValue::VALUE_TYPE_ORGNIZATION_KEY,
        ]);

        if (!empty($result))
        {
            return $result->object_id;
        }

        return null;
    }

    /**
     * 删除组织外键信息
     * @param $orgnizationId
     */
    public function deleteOrgnizationInfoByOrgnizationId($orgnizationId)
    {
        $params = [
            'object_id' => $orgnizationId,
            'object_type' => FwExternalSystemValue::OBJECT_TYPE_ORGNIZATION,
            'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
        ];
        $condition = BaseActiveRecord::getQuoteColumnName("object_id") . ' = :object_id ' .
            ' and ' . BaseActiveRecord::getQuoteColumnName("object_type") . ' = :object_type ' .
            ' and ' . BaseActiveRecord::getQuoteColumnName("status") . ' = :status ';

        $model = new FwExternalSystemValue();
        $model->deleteAll($condition, $params);
    }

    /**
     * 根据域外键获取域ID
     * @param $systemKey
     * @param $domainKey
     * @return null|string
     */
    public function getDomainIdByDomainKey($systemKey, $domainKey)
    {
        if (empty($domainKey))
            return null;

        $model = $this->findBySystemKey($systemKey);
        $externalSystemId = $model->kid;

        $model =  new FwExternalSystemValue();
        $result = $model->findOne([
            'system_id' => $externalSystemId,
            'object_type' => FwExternalSystemValue::OBJECT_TYPE_DOMAIN,
            'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
            'value' => $domainKey,
            'value_type' => FwExternalSystemValue::VALUE_TYPE_DOMAIN_KEY,
        ]);

        if (!empty($result))
        {
            return $result->object_id;
        }
        else {
            return null;
        }
    }

    /**
     * 根据岗位外键获取岗位ID
     * @param $systemKey
     * @param $positionKey
     * @return null|string
     */
    public function getPositionIdByPositionKey($systemKey, $positionKey)
    {
        if (empty($positionKey))
            return null;

        $model = $this->findBySystemKey($systemKey);
        $externalSystemId = $model->kid;

        $model =  new FwExternalSystemValue();
        $result = $model->findOne([
            'system_id' => $externalSystemId,
            'object_type' => FwExternalSystemValue::OBJECT_TYPE_POSITION,
            'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
            'value' => $positionKey,
            'value_type' => FwExternalSystemValue::VALUE_TYPE_POSITION_KEY,
        ]);

        if (!empty($result))
        {
            return $result->object_id;
        }
        else {
            return null;
        }
    }

    /**
     * 删除域外键信息
     * @param $domainId
     */
    public function deleteDomainInfoByDomainId($domainId)
    {
        $params = [
            'object_id' => $domainId,
            'object_type' => FwExternalSystemValue::OBJECT_TYPE_DOMAIN,
            'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
        ];
        $condition = BaseActiveRecord::getQuoteColumnName("object_id") . " = :object_id"  .
            " and ". BaseActiveRecord::getQuoteColumnName("object_type")  . " = :object_type " .
            " and ". BaseActiveRecord::getQuoteColumnName("status")  . " = :status ";

        $model = new FwExternalSystemValue();
        $model->deleteAll($condition, $params);
    }

    /**
     * 获取用户外部主键
     * @param $systemKey
     * @param $userId
     * @return null|string
     */
    public function getUserKeyByUserId($systemKey, $userId)
    {
        if (empty($userId)) {
            return null;
        }

        $model = $this->findBySystemKey($systemKey);
        $externalSystemId = $model->kid;

        $model =  new FwExternalSystemValue();
        $result = $model->findOne([
            'system_id' => $externalSystemId,
            'object_type' => FwExternalSystemValue::OBJECT_TYPE_USER,
            'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
            'object_id' => $userId,
            'value_type' => FwExternalSystemValue::VALUE_TYPE_USER_KEY,
        ]);

        if (!empty($result))
        {
            return $result->value;
        }
        else {
            return null;
        }
    }

    /**
     * 获取组织外部主键
     * @param $systemKey
     * @param $orgnizationId
     * @return null|string
     */
    public function getOrgnizationKeyByOrgnizationId($systemKey, $orgnizationId)
    {
        if (empty($orgnizationId))
            return null;

        $model = $this->findBySystemKey($systemKey);
        $externalSystemId = $model->kid;

        $model =  new FwExternalSystemValue();
        $result = $model->findOne([
            'system_id' => $externalSystemId,
            'object_type' => FwExternalSystemValue::OBJECT_TYPE_ORGNIZATION,
            'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
            'object_id' => $orgnizationId,
            'value_type' => FwExternalSystemValue::VALUE_TYPE_ORGNIZATION_KEY,
        ]);

        if (!empty($result))
        {
            return $result->value;
        }
        else {
            return null;
        }
    }

    /**
     * 获取域外部主键
     * @param $systemKey
     * @param $domainId
     * @return null|string
     */
    public function getDomainKeyByDomainId($systemKey, $domainId)
    {
        if (empty($domainId))
            return null;

        $model = $this->findBySystemKey($systemKey);
        $externalSystemId = $model->kid;

        $model =  new FwExternalSystemValue();
        $result = $model->findOne([
            'system_id' => $externalSystemId,
            'object_type' => FwExternalSystemValue::OBJECT_TYPE_DOMAIN,
            'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
            'object_id' => $domainId,
            'value_type' => FwExternalSystemValue::VALUE_TYPE_DOMAIN_KEY,
        ]);

        if (!empty($result))
        {
            return $result->value;
        }
        else {
            return null;
        }
    }

    /**
     * 获取岗位外部主键
     * @param $systemKey
     * @param $positionId
     * @return null|string
     */
    public function getPositionKeyByPositionId($systemKey, $positionId)
    {
        if (empty($positionId))
            return null;

        $model = $this->findBySystemKey($systemKey);
        $externalSystemId = $model->kid;

        $model =  new FwExternalSystemValue();
        $result = $model->findOne([
            'system_id' => $externalSystemId,
            'object_type' => FwExternalSystemValue::OBJECT_TYPE_POSITION,
            'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
            'object_id' => $positionId,
            'value_type' => FwExternalSystemValue::VALUE_TYPE_POSITION_KEY,
        ]);

        if (!empty($result))
        {
            return $result->value;
        }
        else {
            return null;
        }
    }

    /**
     * 增加外部用户主键
     * @param $systemKey
     * @param $userKey
     * @param $userId
     */
    public function addExternalSystemUserKey($systemKey, $userKey, $userId)
    {
        $model = $this->findBySystemKey($systemKey);
        $externalSystemId = $model->kid;

        $model =  new FwExternalSystemValue();
        $model->system_id = $externalSystemId;
        $model->object_id = $userId;
        $model->object_type = FwExternalSystemValue::OBJECT_TYPE_USER;
        $model->status = FwExternalSystemValue::STATUS_FLAG_NORMAL;
        $model->value = $userKey;
        $model->value_type = FwExternalSystemValue::VALUE_TYPE_USER_KEY;
        $model->start_at =time();

        $model->systemKey = $systemKey;

        $model->save();
    }

    /**
     * 增加外部组织主键
     * @param $systemKey
     * @param $orgnizationKey
     * @param $orgnizationId
     */
    public function addExternalSystemOrgnizationKey($systemKey, $orgnizationKey, $orgnizationId)
    {
        $model = $this->findBySystemKey($systemKey);
        $externalSystemId = $model->kid;

        $model =  new FwExternalSystemValue();
        $model->system_id = $externalSystemId;
        $model->object_id = $orgnizationId;
        $model->object_type = FwExternalSystemValue::OBJECT_TYPE_ORGNIZATION;
        $model->status = FwExternalSystemValue::STATUS_FLAG_NORMAL;
        $model->value = $orgnizationKey;
        $model->value_type = FwExternalSystemValue::VALUE_TYPE_ORGNIZATION_KEY;
        $model->start_at =time();

        $model->systemKey = $systemKey;
        $model->save();

    }

    /**
     * 增加外部域主键
     * @param $systemKey
     * @param $domainKey
     * @param $domainId
     */
    public function addExternalSystemDomainKey($systemKey, $domainKey, $domainId)
    {
        $model = $this->findBySystemKey($systemKey);
        $externalSystemId = $model->kid;

        $model =  new FwExternalSystemValue();
        $model->system_id = $externalSystemId;
        $model->object_id = $domainId;
        $model->object_type = FwExternalSystemValue::OBJECT_TYPE_DOMAIN;
        $model->status = FwExternalSystemValue::STATUS_FLAG_NORMAL;
        $model->value = $domainKey;
        $model->value_type = FwExternalSystemValue::VALUE_TYPE_DOMAIN_KEY;
        $model->start_at =time();

        $model->systemKey = $systemKey;

        $model->save();
    }


    /**
     * 增加外部岗位主键
     * @param $systemKey
     * @param $positionKey
     * @param $positionId
     */
    public function addExternalSystemPositionKey($systemKey, $positionKey, $positionId)
    {
        $model = $this->findBySystemKey($systemKey);
        $externalSystemId = $model->kid;

        $model =  new FwExternalSystemValue();
        $model->system_id = $externalSystemId;
        $model->object_id = $positionId;
        $model->object_type = FwExternalSystemValue::OBJECT_TYPE_POSITION;
        $model->status = FwExternalSystemValue::STATUS_FLAG_NORMAL;
        $model->value = $positionKey;
        $model->value_type = FwExternalSystemValue::VALUE_TYPE_POSITION_KEY;
        $model->start_at =time();

        $model->systemKey = $systemKey;

        $model->save();
    }

    /**
     * 生成授权访问令牌
     * @param $systemKey
     * @param $userId
     * @return array
     */
    public function generateAccessTokenBySystemKey($systemKey, $userId, $withCache = true)
    {
        $cacheKey = "GetAccessToken_UserId_".$userId."_SystemKey_" . $systemKey;

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);
        $currentTime = time();

        $model = $this->findBySystemKey($systemKey);
        $externalSystemId = $model->kid;

        if (empty($result) && !$hasCache) {
            $currentTime = time();
            $model = new FwExternalSystemValue();
            $result = $model->findOne([
                'system_id' => $externalSystemId,
                'object_id' => $userId,
                'object_type' => FwExternalSystemValue::OBJECT_TYPE_USER,
                'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
                'value_type' => FwExternalSystemValue::VALUE_TYPE_ACCESS_TOKEN,
            ]);
        }

        $value = md5($systemKey .  '_' . Yii::$app->security->generateRandomString() . '_' . time());
        $companyId = FwUser::findOne($userId)->company_id;
        $commonCompanyService = new CompanyService();
        $expire = $commonCompanyService->getCompanySettingValueByCode($companyId, $systemKey . ".accessTokenExpire");

        if ($expire === null) {
            //为null表示未设置，所以取接口设定的值
            $expire = $model->token_expire;
        }

        if (!isset($expire) || $expire == null || $expire == "" || $expire == 0)
        {
            $endAt = null;
        }
        else {
            $timestamp = $currentTime;
            $endAt = $timestamp + $expire;
        }

        if (!empty($result))
        {
            $result->value = $value;
            $result->start_at = $currentTime;
            $result->end_at = $endAt;
            $result->systemKey = $systemKey;
            if ($result->save()) {
                self::saveToCache($cacheKey, $result);

                $cacheKey = "GetUserId_AccessToken_".$value."_ExternalSystemId_" . $externalSystemId;
                self::removeFromCache($cacheKey);
            }
        }
        else {
            $result =  new FwExternalSystemValue();
            $result->system_id = $externalSystemId;
            $result->object_id = $userId;
            $result->object_type = FwExternalSystemValue::OBJECT_TYPE_USER;
            $result->status = FwExternalSystemValue::STATUS_FLAG_NORMAL;
            $result->value_type = FwExternalSystemValue::VALUE_TYPE_ACCESS_TOKEN;
            $result->value = $value;
            $result->start_at = $currentTime;
            $result->end_at = $endAt;
            $result->systemKey = $systemKey;
            $result->needReturnKey = true;
            if ($result->save()) {
                self::saveToCache($cacheKey, $result);

                $cacheKey = "GetUserId_AccessToken_".$value."_ExternalSystemId_" . $externalSystemId;
                self::removeFromCache($cacheKey);
            }
        }


        $accessTokenArray = [];
        if (!empty($result))
        {
            if (!empty($result->end_at) && $result->end_at > $currentTime) {
                $accessTokenArray['access_token'] = $result->value;
                $accessTokenArray['expire'] =  date('Y-m-d H:i:s',$result->end_at);
            }
            else if (empty($result->end_at)) {
                $accessTokenArray['access_token'] = $result->value;
                $accessTokenArray['expire'] = null;
            }
        }

        return $accessTokenArray;
    }

    /**
     * 延期访问令牌有效期
     * @param $systemKey
     * @param $userId
     * @return array
     */
    public function delayAccessTokenExpireBySystemKey($systemKey, $userId, $withCache = true)
    {
        $cacheKey = "GetAccessToken_UserId_".$userId."_SystemKey_" . $systemKey;

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);
        $currentTime = time();
        $model = $this->findBySystemKey($systemKey);
        $externalSystemId = $model->kid;

        if (empty($result) && !$hasCache) {
            $model = new FwExternalSystemValue();
            $result = $model->findOne([
                'system_id' => $externalSystemId,
                'object_id' => $userId,
                'object_type' => FwExternalSystemValue::OBJECT_TYPE_USER,
                'status' => FwExternalSystemValue::STATUS_FLAG_NORMAL,
                'value_type' => FwExternalSystemValue::VALUE_TYPE_ACCESS_TOKEN,
            ]);
        }

//        $value = Md5($systemKey .  '_' . Yii::$app->security->generateRandomString() . '_' . time());
        $companyId = FwUser::findOne($userId)->company_id;
        $commonCompanyService = new CompanyService();
        $expire = $commonCompanyService->getCompanySettingValueByCode($companyId, $systemKey . ".accessTokenExpire");

        if ($expire === null) {
            //为null表示未设置，所以取接口设定的值
            $expire = $model->token_expire;
        }

        if (!isset($expire) || $expire == null || $expire == "" || $expire == 0)
        {
            $endAt = null;
        }
        else {
            $timestamp = $currentTime;
            $endAt = $timestamp + $expire;
        }

        $accessTokenArray = [];
        if (!empty($result))
        {
            $result->end_at = $endAt;
            $result->systemKey = $systemKey;

            if ($result->save()) {

                $accessToken = $result->value;
                if (!empty($result->end_at) && $result->end_at > $currentTime) {
                    $accessTokenArray['access_token'] = $accessToken;
                    $accessTokenArray['expire'] = date('Y-m-d H:i:s', $result->end_at);
                } else if (empty($result->end_at)) {
                    $accessTokenArray['access_token'] = $accessToken;
                    $accessTokenArray['expire'] = null;
                }

                if ($result->save()) {
                    self::saveToCache($cacheKey, $result);

                    $cacheKey = "GetUserId_AccessToken_".$accessToken."_ExternalSystemId_" . $externalSystemId;
                    self::removeFromCache($cacheKey);
                }
            }
        }

        return $accessTokenArray;
    }
}