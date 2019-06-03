<?php

namespace common\base;

use common\models\framework\FwPrimaryKey;
use common\traits\CacheTrait;
use common\helpers\TLoggerHelper;
use common\helpers\TNetworkHelper;
use common\helpers\TStringHelper;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\caching\DbDependency;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use common\eLearningLMS;
use common\helpers\TTimeHelper;
use yii\db\ActiveQuery;
use yii\helpers\Html;

/**
 * @property \MongoId|string $_id
 * @property integer $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $created_ip
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $updated_ip
 * @property string $is_deleted
 * @property string $systemKey
 *
 */
class BaseActiveRecordMongoDB extends \yii\mongodb\ActiveRecord
{
    use CacheTrait;
    
    const NO = "0";
    const YES = "1";

    const DELETE_FLAG_NO = "0";
    const DELETE_FLAG_YES = "1";

    const DISPLAY_FLAG_NO = "0";
    const DISPLAY_FLAG_YES = "1";

    const STATUS_FLAG_TEMP = "0";
    const STATUS_FLAG_NORMAL = "1";
    const STATUS_FLAG_STOP = "2";

    const LIMITATION_NONE = "N";
    const LIMITATION_READONLY = "R";
    const LIMITATION_HIDDEN = "H";
    const LIMITATION_ONLYNAME = "U";

    const SHARE_FLAG_EXCLUSIVE = "0";
    const SHARE_FLAG_SHARE = "1";
    
    const DURATION_HOUR = 3600;
    const DURATION_HALFDAY = 43200;
    const DURATION_DAY = 86400;
    const DURATION_MONTH = 2592000;
    const DURATION_YEAR = 31536000;

//    const DEPEND_MODE_SQL = "sql";
    const DEPEND_MODE_DURATION = "duration";


    public $saveEncode = false;//保存数据前，把所有数据Encode，防止script攻击等
    public $saveUpdateVersion = true;//保存数据前，更新版本号
    public $needReturnKey = true;//保存数据后，是否要返回Key值
    public $checkKeyExist = false;//保存数据前检查Key值是否已经存在


    public $systemKey = "PC";

    public static $defaultKey = "PC";

    //需要强制缓存的表名，建议只放数量少或不频繁更新的表
    public static $forceCacheCollection = [
        'eln_ln_scorm_scoes_track'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_MONTH]
    ];

    //部分表不存在删除操作,可以强制物理删除
    public static $forcePhysicalDeleteCollection = [
        'eln_ln_scorm_scoes_track'
    ];

    /**
     * 获取状态文字
     * @return string
     */
    public function getStatusText()
    {
        if (isset($this->status)) {
            $status = $this->status;
            if ($status == self::STATUS_FLAG_TEMP)
                return Yii::t('common', 'status_temp');
            else if ($status == self::STATUS_FLAG_NORMAL)
                return Yii::t('common', 'status_normal');
            else if ($status == self::STATUS_FLAG_STOP)
                return Yii::t('common', 'status_stop');
            else
                return "";
        }
        else {
            return "";
        }
    }

    /**
     * 获取限制文字
     * @return string
     */
    public function getLimitationText()
    {
        if (isset($this->limitation)) {
            $limitation = $this->limitation;
            if ($limitation == self::LIMITATION_NONE)
                return Yii::t('common', 'limitation_none');
            else if ($limitation == self::LIMITATION_READONLY)
                return Yii::t('common', 'limitation_readonly');
            else if ($limitation == self::LIMITATION_HIDDEN)
                return Yii::t('common', 'limitation_hidden');
            else
                return Yii::t('common', 'limitation_onlyname');
        } else {
            return "";
        }

    }

    /**
     * 是否显示
     * @return string
     */
    public function getIsDisplayText()
    {
        if (isset($this->is_display)) {
            $isDisplay = $this->is_display;
            if ($isDisplay == self::DISPLAY_FLAG_NO)
                return Yii::t('common', 'display_no');
            else
                return Yii::t('common', 'display_yes');
        } else {
            return "";
        }
    }

    /**
     * 共享标志文字
     * @return string
     */
    public function getShareFlagText()
    {
        if (isset($this->share_flag)) {
            $shareFlag = $this->share_flag;
            if ($shareFlag == self::SHARE_FLAG_EXCLUSIVE)
                return Yii::t('common', 'share_flag_exclusive');
            else if ($shareFlag == self::SHARE_FLAG_SHARE)
                return Yii::t('common', 'share_flag_share');
        } else {
            return "";
        }
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * 根据条件查询数据
     * @inheritdoc
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find($includeDeleted = true)
    {
        $collectionName = self::getName();
        if (!array_key_exists($collectionName, self::$forcePhysicalDeleteCollection)) {
            if (!$includeDeleted) {
                return parent::find()->andFilterWhere(["is_deleted" => self::DELETE_FLAG_NO]);
            }
        }
        return parent::find();
    }

    /**
     * 查找单条数据
     * @param mixed $condition
     * @param bool|false $includeDeleted
     * @param bool|false $withCache
     * @param bool|false $loadDeCode
     * @param bool|false $loadEnCode
     * @return mixed|null|static ActiveRecord instance matching the condition, or `null` if nothing matches.
     */
    public static function findOne($condition, $includeDeleted = false, $withCache = false, $loadDeCode = false, $loadEnCode = false)
    {
//        $depandMode = "";
//        $duration = 0;
        $collectionName = self::getName();

        if (count(self::$forceCacheCollection) > 0) {
            if (array_key_exists($collectionName, self::$forceCacheCollection)) {
                $depandMode = self::$forceCacheCollection[$collectionName]["depend"];
                $duration = self::$forceCacheCollection[$collectionName]["duration"];
                $withCache = true;
            }
        }

        if ($withCache) {
            $isAssociative = ArrayHelper::isAssociative($condition);
            $isSingleKey = false;
            if (!$isAssociative) {
                //判断是不是按kid取值
                if (is_array($condition) && count($condition) == 1 
                    && is_string(current(array_keys($condition))) 
                    && strtolower(current(array_keys($condition))) == "_id") {
                    $isSingleKey = true;
                }
                else if (is_string($condition)) {
                    $isSingleKey = true;
                }
            }

            if ($isSingleKey) {
                //如果是根据Kid值进行查询，则从缓存取
                if (is_array($condition))
                    $key = current(array_values($condition));
                else
                    $key = $condition;

                $cacheKey = self::getCacheType() . "-" . $collectionName . "-" . $key;
                $result = self::loadFromCache($cacheKey);
            }
            else {
                $result = null;
                $key = null;
                $cacheKey = null;
            }

            if ($result == false || empty($result)) {
                if (!array_key_exists($collectionName, self::$forcePhysicalDeleteCollection)) {
                    if ($includeDeleted == false) {
                        if (is_array($condition) && !array_key_exists("is_deleted", $condition)) {
                            $condition = ArrayHelper::merge($condition, ['is_deleted' => self::DELETE_FLAG_NO]);
                        } else if (is_string($condition)) {
                            $tempCondition['_id'] = $condition;
                            $condition = ArrayHelper::merge($tempCondition, ['is_deleted' => self::DELETE_FLAG_NO]);
                        }
                    }
                }

                $result = parent::findOne($condition);

                if ($isSingleKey && !empty($result)) {
                    self::saveToCache($cacheKey, $result, null, $duration, $withCache);
                }
            }
        }
        else {
            if (!array_key_exists($collectionName, self::$forcePhysicalDeleteCollection)) {
                if ($includeDeleted == false) {
                    if (is_array($condition) && !array_key_exists("is_deleted", $condition)) {
                        $condition = ArrayHelper::merge($condition, ['is_deleted' => self::DELETE_FLAG_NO]);
                    } else if (is_string($condition)) {
                        $tempCondition['_id'] = $condition;
                        $condition = ArrayHelper::merge($tempCondition, ['is_deleted' => self::DELETE_FLAG_NO]);
                    }
                }
            }

            $result = parent::findOne($condition);
        }


        if ($loadDeCode) {
            foreach ($result as $key => $attribute) {
                if (!empty($attribute) && is_string($attribute)) {
                    $result[$key] = Html::decode($attribute);
                }
            }
        }

        if ($loadEnCode) {
            foreach ($result as $key => $attribute) {
                if (!empty($attribute) && is_string($attribute)) {
                    $result[$key] = Html::encode($attribute);
                }
            }
        }

        return $result;
    }

    /**
     * 根据条件查询数据
     * @inheritdoc
     * @return static[] an array of ActiveRecord instances, or an empty array if nothing matches.
     */
    public static function findAll($condition, $includeDeleted = false)
    {
        $collectionName = self::getName();
        if (!array_key_exists($collectionName, self::$forcePhysicalDeleteCollection)) {
            if ($includeDeleted == false) {
                if (is_array($condition) && !array_key_exists("is_deleted", $condition)) {
                    $condition = ArrayHelper::merge($condition, ["is_deleted" => self::DELETE_FLAG_NO]);
                }
            }
        }
        return parent::findAll($condition);
    }

    /**
     * 逻辑删除数据
     * Deletes the table row corresponding to this active record.
     *
     * @return integer|boolean the number of rows deleted
     */
    public function delete()
    {
        $collectionName = self::getName();
        if (!array_key_exists($collectionName, self::$forcePhysicalDeleteCollection)) {
            if (!$this->isNewRecord) {

                $this->is_deleted = self::DELETE_FLAG_YES;
                return $this->update();
            }
            return false;
        }
        else {
            parent::delete();
        }
    }

    /**
     * 逻辑删除所有数据
     * @param string $condition
     * @param array $options
     * @param null $systemKey
     * @return int
     * @throws \yii\db\Exception
     */
    public static function deleteAll($condition = [], $options = [], $systemKey = null)
    {
        $collectionName = self::getName();
        if (!array_key_exists($collectionName, self::$forcePhysicalDeleteCollection)) {
            $attributes = [
                '$set' => [
                    'is_deleted' => self::DELETE_FLAG_YES
                ]
            ];

            return self::updateAll($attributes, $condition, $options);
        }
        else {
            return parent::deleteAll($condition, $options);
        }
    }

    /**
     * 获取名字
     * @return mixed|string
     */
    public static function getName(){
        $collectionName = self::getCollection()->getName();
        return $collectionName;
    }

    /**
     * 获取缓存类型
     * @return mixed|string
     */
    public static function getCacheType(){
        return "mongo";
    }

    /**
     * 删除键值
     * @param $attributes
     * @param array $condition
     * @param array $options
     * @param null $systemKey
     * @param bool $withVersion
     * @return int
     */
    public static function unsetAll($attributes, $condition = [], $options = [], $systemKey = null, $withVersion = true)
    {
        $tempAttributes = [];
        foreach ($attributes as $attribute){
            $tempAttributes[$attribute] = 1;
        }

        $newAttributes['$unset'] = $tempAttributes;

        return self::updateAll($newAttributes, $condition, $options, false, $systemKey, $withVersion);
    }

    /**
     * 更新数据
     * @param array $attributes
     * @param array $condition
     * @param array $options
     * @param bool $upsert 要是没有找到符合更新条件的文档，就会以这个条件和更新文档为基础创建一个新的文档
     * @param null $systemKey
     * @param bool $withVersion
     * @return int
     */
    public static function updateAll($attributes, $condition = [], $options = [], $upsert = false,
                                     $systemKey = null, $withVersion = true)
    {
        if (empty($systemKey)) {
            $systemKey = self::$defaultKey;
        }

        $currentDate = time();//date("Y-m-d H:i:s");

        if (Yii::$app->user->isGuest)
            $currentUserId = "00000000-0000-0000-0000-000000000000";
        else
            $currentUserId = strval(Yii::$app->user->getId());

        if (!$withVersion) {
            $newAttributes = [
                '$set' => [
                    'updated_at' => $currentDate,
                    'updated_by' => $currentUserId,
                    'updated_from' => $systemKey,
                    'updated_ip' => TNetworkHelper::getClientRealIP()
                ]
            ];
        }
        else {
            $newAttributes = [
                '$set' => [
                    'updated_at' => $currentDate,
                    'updated_by' => $currentUserId,
                    'updated_from' => $systemKey,
                    'updated_ip' => TNetworkHelper::getClientRealIP()
                ],
                '$inc' => ['version' => 1]
            ];
        }

        $tempAttributes = [];
        foreach ($attributes as $key=>$value) {
            if (TStringHelper::startWith($key, "$")) {
                $tempAttributes[$key] = $value;
            }
            else {
                $tempAttributes['$set'] = [$key => $value];
            }
        }

        $attributes = ArrayHelper::merge($tempAttributes, $newAttributes);

        if ($upsert){
            $options = array_merge($options, ['upsert' => true]);
        }

        return parent::updateAll($attributes, $condition, $options);
    }
   


    /**
     * 物理删除数据（当前数据）
     * @return false|int
     * @throws \Exception
     */
    public function physicalDelete()
    {
        if (isset($this->_id)) {
            $withCache = false;
//            $depandMode = self::DEPEND_MODE_SQL;
            $collectionName = self::getName();
            if (count(self::$forceCacheCollection) > 0) {
                if (array_key_exists($collectionName, self::$forceCacheCollection)) {
//                    $depandMode = self::$forceCacheTable[$tableName]["depend"];
                    $withCache = true;
                }
            }
            if ($withCache) {
                $kid = $this->_id;
                self::removeFromCacheByKid($kid);
            }
        }


        return parent::delete();
    }

    /**
     * 物理删除数据（根据条件）
     * @param string $condition
     * @param array $options
     * @return int
     */
    public static function physicalDeleteAll($condition = [], $options = [])
    {
        parent::deleteAll($condition,$options);
    }


    /**
     * 包装数据
     * @param $model
     * @param $currentDate
     * @param $currentUserId
     * @param $systemKey
     * @param bool $insert
     * @param bool $saveUpdateVersion
     * @param $errMsg
     * @return bool
     */
    private static function prepareData($model, $currentDate, $currentUserId, $systemKey, $insert = true,
                                        $saveUpdateVersion = true, &$errMsg) {
        if ($model->validate()) {
            if (empty($systemKey)) {
                $systemKey = self::$defaultKey;
            }

            $ip = TNetworkHelper::getClientRealIP();

            if ($insert) {
                if ($model->created_at == null)
                    $model->created_at = $currentDate;

                if ($model->updated_at == null)
                    $model->updated_at = $currentDate;

                if ($model->created_by == null)
                    $model->created_by = $currentUserId;

                if ($model->updated_by == null)
                    $model->updated_by = $currentUserId;

                if ($model->created_from == null)
                    $model->created_from = $systemKey;

                if ($model->updated_from == null)
                    $model->updated_from = $systemKey;

                if ($model->created_ip == null)
                    $model->created_ip = $ip;

                if ($model->updated_ip == null)
                    $model->updated_ip = $ip;

                $model->version = 1;
                $model->is_deleted = self::DELETE_FLAG_NO;
            } else {

                $model->updated_at = $currentDate;

                $model->updated_by = $currentUserId;

                $model->updated_from = $systemKey;

                $model->updated_ip = $ip;

                if ($saveUpdateVersion)
                    $model->version = $model->version + 1;

                if (empty($model->is_deleted)) {
                    $model->is_deleted = self::DELETE_FLAG_NO;
                }
            }

            return true;
        }
        else {
            $errMsg = $model->getErrors();
            return false;
        }
    }

    /**
     * 增加字段结果
     * @param $id
     * @param $field
     * @param null $systemKey
     * @return int
     */
    public static function addFieldNumber($id, $field, $systemKey = null)
    {

        $condition = [
            '_id' => $id
        ];

        $attributes = [
            '$inc' => [
                $field => 1
            ]
        ];

        return self::updateAll($attributes, $condition, [], $systemKey);
    }


    /**
     * 减少字段结果
     * @param $id
     * @param $field
     * @param null $systemKey
     * @return int
     */
    public static function subFieldNumber($id, $field, $systemKey = null)
    {

        $condition = [
            '_id' => $id
        ];

        $attributes = [
            '$inc' => [
                $field => -1
            ]
        ];

        return self::updateAll($attributes, $condition, [], $systemKey);
    }

    /**
     * 包装数据
     * @param $model
     * @param $systemKey
     * @param bool|true $insert
     * @param bool|true $saveUpdateVersion
     */
    private static function batchPrepareData($models, $systemKey = null, $insert = true, $saveUpdateVersion = true, &$err = false,&$errMsg = null) {
        $currentDate = time();
        $rows = [];
        if (empty($systemKey)) {
            $systemKey = self::$defaultKey;
        }

        if (Yii::$app->user->isGuest)
            $currentUserId = "00000000-0000-0000-0000-000000000000";
        else
            $currentUserId = strval(Yii::$app->user->getId());


        foreach ($models as $model) {
            $result = self::prepareData($model,$currentDate,$currentUserId,$systemKey,$insert,$saveUpdateVersion,$errMsg);
            if ($result) {
               $rows[] = self::unsetNullAttributes($model);
            }
            else {
                $err = true;
                break;//一旦有错误，就立马中止（批量模式没有必要一直检查到底）
            }
        }

        return $rows;
    }

    /**
     * 去除无用属性
     * @param $model
     * @return array
     */
    private static function unsetNullAttributes($model) {
        $result = [];
        $attributes = $model->attributes;
        foreach ($attributes as $key=>$value) {
            if ($value != null){
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * 批量插入数据，通过循环对象批量插入
     * @param array $models 需要插入的数据数组
     * @param array $options
     * @param bool $continueOnError 失败后是否继续
     * @param null $systemKey
     * @return array
     * @throws \yii\mongodb\Exception
     */
    public static function batchInsert($models, $options = [], $continueOnError = false, $systemKey = null)
    {
        $rows = self::batchPrepareData($models,$systemKey,true,true,$err, $errMsg);

        if ($continueOnError){
            $options = array_merge($options, ['continueOnError' => true]);
        }
        
        return self::getCollection()->batchInsert($rows, $options);
    }

    /**
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        try {
            return parent::save($runValidation, $attributeNames);
        } catch (Exception $ex) {
            $errorCode = $ex->getCode();
            $errorMessage = $ex->getMessage();
            return false;
        }
    }

    public function beforeSave($insert)
    {
        if (empty($this->systemKey)) {
            $this->systemKey = self::$defaultKey;
        }

        if (parent::beforeSave($insert)) {

            $currentDate = time();// date("Y-m-d H:i:s");

            if (Yii::$app->user->isGuest)
                $currentUserId = "00000000-0000-0000-0000-000000000000";
            else
                $currentUserId = strval(Yii::$app->user->getId());

            $errMsg = null;
            if ($insert) {
                $this->prepareData($this, $currentDate, $currentUserId, $this->systemKey, true, true, $errMsg);
            } else {
                $this->prepareData($this, $currentDate, $currentUserId, $this->systemKey, false, $this->saveUpdateVersion, $errMsg);
            }


            if ($this->saveEncode) {
                foreach ($this->attributes as $key => $attribute) {
                    if (!empty($attribute) && is_string($attribute)) {
                        $isUTF8 = TStringHelper::isUTF8($attribute);
                        if (!$isUTF8) {
                            $attribute = iconv("gb2312", "utf-8//IGNORE", $attribute);
                            $this->setAttributes([$key => $attribute]);
                        }
                        $oldValue = $attribute;
                        $decodeValue = Html::decode($attribute);
                        //如果这2个值不一样，说明已经被加密过，要避免重复加密
                        if ($oldValue == $decodeValue) {
                            $this->setAttributes([$key => Html::encode($attribute)]);
                        }
                    }
                }
            } else {
                foreach ($this->attributes as $key => $attribute) {
                    if (!empty($attribute) && is_string($attribute)) {
                        $isUTF8 = TStringHelper::isUTF8($attribute);
                        if (!$isUTF8) {
                            $attribute = iconv("gb2312", "utf-8//IGNORE", $attribute);
                            $this->setAttributes([$key => $attribute]);
                        }
                    }
                }
            }
            
            return true;
        } else {
            return false;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        //写数据时就同步写入缓存，以便提高速度
        $canWriteToCache = false;
        //插入数据时不能直接写缓存，因为还有很多字段是数据库在控制默认值，写的时候并不一定知道
        if ($this->validate() && !$insert && isset($this->_id)) {
            $canWriteToCache = true;
        }

//        if (!$insert) {
        if ($canWriteToCache) {
            $withCache = false;
            $collectionName = self::getName();
            if (count(self::$forceCacheCollection) > 0) {
                if (array_key_exists($collectionName, self::$forceCacheCollection)) {
                    $depandMode = self::$forceCacheCollection[$collectionName]["depend"];
                    $duration = self::$forceCacheCollection[$collectionName]["duration"];
                    $withCache = true;
                }
            }
//                if ($withCache && $depandMode == self::DEPEND_MODE_DURATION) {
//                    $kid = $this->kid;
//                    self::removeFromCacheByKid($kid);
//                }

            if ($withCache) {
                $key = $this->_id;
                $cacheKey = self::getCacheType() . "-" . $collectionName . "-" . $key;

                if ($this->is_deleted == self::DELETE_FLAG_YES) {
                    self::removeFromCache($cacheKey);
                }
                else {
                    self::saveToCache($cacheKey, $this, null, $duration, $withCache);
                }
            }
        }
//        }
    }

   
    
    public function getOffset($page, $size)
    {
        $_page = (int)$page - 1;

        return $size < 1 ? 0 : $_page * $size;
    }
}
