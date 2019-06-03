<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use common\eLearningLMS;
use Yii;
use yii\caching\DbDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "eln_primary_key".
 *
 * @property string $kid
 * @property string $table_name
 * @property string $next_primary_id
 * @property string $key_type
 * @property string $key_prefix
 * @property integer $padding_count
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 */
class FwPrimaryKey extends BaseActiveRecord
{
    const KEY_TYPE_SYSGEN = "0";
    const KEY_TYPE_INCREASE = "1";
    const KEY_TYPE_GUID = "2";

    const KEY_INCREASE_STEP = 20;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_primary_key}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['table_name', 'created_by', 'created_at'], 'required'],
            [['padding_count'], 'integer'],
            [['padding_count'], 'default', 'value' => 0],
            [['created_at', 'updated_at'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['table_name'], 'string', 'max' => 100],
            [['next_primary_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['key_type'], 'string', 'max' => 1],
            [['key_prefix'], 'string', 'max' => 30],

            [['key_type'], 'default', 'value' => self::KEY_TYPE_INCREASE],
            [['key_type'], 'in', 'range' => [self::KEY_TYPE_SYSGEN, self::KEY_TYPE_INCREASE, self::KEY_TYPE_GUID]],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'default', 'value' => self::DELETE_FLAG_NO],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'kid'),
            'table_name' => Yii::t('common', 'table_name'),
            'next_primary_id' => Yii::t('common', 'next_primary_id'),
            'key_type' => Yii::t('common', 'key_type'),
            'key_prefix' => Yii::t('common', 'key_prefix'),
            'padding_count' => Yii::t('common', 'padding_count'),
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'is_deleted' => Yii::t('common', 'is_deleted'),
        ];
    }

    /**
     * 生成主键（如果检查到存在重复值，则重新计算一个）
     * @param $originTableName
     * @return null|string
     */
    public static function generateNextPrimaryID($originTableName,$withCache=true,$checkKeyExist = false)
    {
        $key = FwPrimaryKey::generateNextPrimaryIDInternal($originTableName,$withCache);

        if ($checkKeyExist) {
            $result = FwPrimaryKey::isKeyExist($originTableName, $key);
        }
        else {
            $result = false;
        }

        if ($result == true) {
            return FwPrimaryKey::generateNextPrimaryID($originTableName,$withCache);
        } else {
            return $key;
        }
    }

    /**
     * 生成主键（内部方法）
     * @inheritdoc
     */
    private static function generateNextPrimaryIDInternal($originTableName,$withCache)
    {
        if (FwPrimaryKey::tableName() == $originTableName) {
            return null;
        }
        else {
            $currentPrimaryID = 1;
            $tbName = self::calculateTableName($originTableName);

            $cacheKey = "PK_" . $tbName;
            $cacheKeyNextPrimaryId = "NextPrimaryId";
            $cacheKeyCurrentPrimaryId = "CurrentPrimaryId";
            $cacheKeyType = "KeyType";
            $cachePaddingCount = "PaddingCount";
            $cacheKeyPrefix = "KeyPrefix";

            if ($withCache)
                $primaryKeyData = Yii::$app->cache->get($cacheKey);
            else
                $primaryKeyData = false;

            //load cache info
            if ($primaryKeyData != false)
            {
                $primaryKeyNextPrimaryId = ArrayHelper::getValue($primaryKeyData,$cacheKeyNextPrimaryId);
                $primaryKeyCurrentPrimaryId = ArrayHelper::getValue($primaryKeyData,$cacheKeyCurrentPrimaryId);
                $primaryKeyType = ArrayHelper::getValue($primaryKeyData,$cacheKeyType);
                $primaryKeyPrefix = ArrayHelper::getValue($primaryKeyData,$cacheKeyPrefix);
                $primaryPaddingCount = ArrayHelper::getValue($primaryKeyData,$cachePaddingCount);

                if ($primaryKeyType == self::KEY_TYPE_SYSGEN)
                {
                    return null;
                }
                else if ($primaryKeyType == self::KEY_TYPE_GUID)
                {
                    return self::guid();
                }
                else if ($primaryKeyCurrentPrimaryId < $primaryKeyNextPrimaryId)
                {
                    $currentPrimaryID = $primaryKeyCurrentPrimaryId;
                    $primaryKeyCurrentPrimaryId = $primaryKeyCurrentPrimaryId + 1;

                    $primaryKeyData[$cacheKeyCurrentPrimaryId] = $primaryKeyCurrentPrimaryId;

                    $primaryKeyModel = new FwPrimaryKey();
                    $dependencySql = $primaryKeyModel->find(false)
                        ->andFilterWhere(['=','table_name',$tbName])
                        ->select("updated_at")
                        ->createCommand()
                        ->getRawSql();

                    self::saveToCache($cacheKey, $primaryKeyData,$dependencySql, $withCache);

                    $currentKey = self::CalculateCurrentKey($primaryKeyPrefix,$currentPrimaryID, $primaryPaddingCount);
                    return $currentKey;
                }

            }


            $currentDate = time();// date("Y-m-d H:i:s");

            if (empty(Yii::$app->user) || Yii::$app->user->getId() == null || Yii::$app->user->isGuest)
                $currentUserId = "00000000-0000-0000-0000-000000000000";
            else
                $currentUserId = strval(Yii::$app->user->getId());

            $systemType = "PC";

            //load primary key table setting info
            $result = static::findOne(['table_name' => $tbName]);
            if ($result != null) {
                $currentPrimaryID = $result->next_primary_id;
                $result->updated_at = $currentDate;
                $result->updated_by = $currentUserId;
                $result->updated_from = $systemType;
            } else {
                $result = new FwPrimaryKey();
                $result->table_name = $tbName;
                $result->key_prefix = '';
                $result->created_at = $currentDate;
                $result->created_by = $currentUserId;
                $result->created_from = $systemType;
                $result->padding_count = 0;
                $result->key_type = self::KEY_TYPE_GUID; //设置默认值为取GUID

                //FwPrimaryKey表KID，强制为GUID
                $result->kid = self::guid();
            }

//            $currentKey = "0";
            $keyPrefix = "";
            if ($result->key_type == self::KEY_TYPE_SYSGEN)
            {
                $nextOnePrimaryID = null;
                $result->next_primary_id = null;
                $currentKey = null;
            }
            else if ($result->key_type == self::KEY_TYPE_GUID)
            {
                $nextOnePrimaryID = null;
                $result->next_primary_id = null;
                $currentKey = self::guid();
            }
            else {
                $nextOnePrimaryID = $currentPrimaryID + 1;
                $nextPrimaryID = $currentPrimaryID + self::KEY_INCREASE_STEP;
                $result->next_primary_id = strval($nextPrimaryID);

                $keyPrefix = $result->key_prefix;

                if ($keyPrefix == null)
                    $keyPrefix = "";

                $currentKey = self::CalculateCurrentKey($keyPrefix,$currentPrimaryID, $result->padding_count);

            }

            if ($result->save()) {

                if ($withCache) {
                    //initiate cache value
                    $primaryKeyData = [
                        $cacheKeyNextPrimaryId => $result->next_primary_id,
                        $cacheKeyCurrentPrimaryId => $nextOnePrimaryID,
                        $cacheKeyType => $result->key_type,
                        $cachePaddingCount => $result->padding_count,
                        $cacheKeyPrefix => $keyPrefix];

                    if ($result->key_type == self::KEY_TYPE_GUID || $result->key_type == self::KEY_TYPE_SYSGEN) {
                        self::saveToCache($cacheKey, $primaryKeyData, null, self::DURATION_YEAR);
                    }
                    else {
                        $primaryKeyModel = new FwPrimaryKey();
                        $dependencySql = $primaryKeyModel->find(false)
                            ->andFilterWhere(['=','table_name',$tbName])
                            ->select("updated_at")
                            ->createCommand()
                            ->getRawSql();
                        self::saveToCache($cacheKey, $primaryKeyData, $dependencySql, $withCache);
                    }
                }
            }


            return $currentKey;
        }
    }

    /**
     * 判断Key值是否存在相同的（高并发或使用GUID生成主键时，有一定概率会存在相同值）
     * @param $tbName
     * @param $kid
     * @return bool
     */
    private static function isKeyExist($tableName,$kid)
    {

        if ($kid == null)
            return false;

        $primaryKeyTableName = self::calculateTableName($tableName);

        $sql = "SELECT count(1) FROM ". self::getQuoteColumnName($primaryKeyTableName)  ." WHERE "
            . self::getQuoteColumnName("kid")  . " = '" . $kid . "'";

        $results = eLearningLMS::queryScalar($sql);


        if (!empty($results) && intval($results) >= 1)
            return true;

        return false;
    }

    /**
     * 计算拼接后的key值
     * @param $keyPrefix
     * @param $currentPrimaryID
     * @param $paddingCount
     * @return string
     */
    private static function CalculateCurrentKey($keyPrefix,$currentPrimaryID,$paddingCount)
    {
        return $keyPrefix . str_pad(strval($currentPrimaryID), $paddingCount, "0", STR_PAD_LEFT);
    }

    /**
     * 生成GUID
     * @param bool $opt
     * @return string
     */
    public static function guid($opt = true ){       //  Set to true/false as your default way to do this.

        if( function_exists('com_create_guid') ){
            if($opt){
                return com_create_guid();
            }
            else {
                return trim(com_create_guid(), '{}');
            }
        }
        else {
            mt_srand((double)microtime() * 10000 );    // optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr( 45 );    // "-"
            $left_curly = $opt ? chr(123) : "";     //  "{"
            $right_curly = $opt ? chr(125) : "";    //  "}"

            $uuid = substr( $charid, 0, 8 ) . $hyphen
                . substr( $charid, 8, 4 ) . $hyphen
                . substr( $charid, 12, 4 ) . $hyphen
                . substr( $charid, 16, 4 ) . $hyphen
                . substr( $charid, 20, 12 );
//            $uuid = $left_curly
//                . substr( $charid, 0, 8 ) . $hyphen
//                . substr( $charid, 8, 4 ) . $hyphen
//                . substr( $charid, 12, 4 ) . $hyphen
//                . substr( $charid, 16, 4 ) . $hyphen
//                . substr( $charid, 20, 12 )
//                . $right_curly;
            return $uuid;// 00000000-0000-0000-0000-000000000001
        }
    }


}
