<?php

namespace common\models\message;

use common\base\BaseActiveRecord;
use Yii;
use yii\caching\DbDependency;

/**
 * This is the model class for table "{{%ms_subscribe_type}}".
 *
 * @property string $kid
 * @property string $type
 * @property string $type_name
 * @property string $type_code
 * @property string $is_turnoff
 * @property string $i18n_flag
 * @property string $default_status
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class MsSubscribeType extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_subscribe_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'type_name', 'type_code'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'type_name', 'type_code', 'i18n_flag', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['type', 'is_turnoff', 'default_status'], 'string', 'max' => 1],
            [['created_from', 'updated_from'], 'string', 'max' => 50],

            [['version'], 'number'],
            [['version'], 'default', 'value' => 1],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'kid'),
            'type' => Yii::t('common', 'type'),
            'type_name' => Yii::t('common', 'type_name'),
            'type_code' => Yii::t('common', 'type_code'),
            'is_turnoff' => Yii::t('common', 'Is Turnoff'),
            'i18n_flag' => Yii::t('common', 'i18n_flag'),
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
     * 使用缓存技术获取MsSubscribeType
     * @param $type 分类
     * @param bool|true $withCache
     * @return mixed|null|MsSubscribeType
     */
    public static function GetSubscribeType($type, $withCache = true)
    {
        $key = $type;
        if ($type === null) {
            $key = 'all';
        }

        $cacheKey = MsSubscribeType::realTableName() . "-" . $key;

        $cacheSubscribeType = self::loadFromCache($cacheKey, $withCache, $hasCache);

        if (($cacheSubscribeType == false || empty($cacheSubscribeType)) && !$hasCache) {
            $query = MsSubscribeType::find(false);
//            if ($type != null) {
//                $query->andFilterWhere(['=', 'type', $type]);
//                $dependencySql = "SELECT * FROM " . MsSubscribeType::realTableName() . " WHERE type = '" . $type . "' AND is_deleted ='0'";
//            } else {
//                $dependencySql = "SELECT * FROM " . MsSubscribeType::realTableName() ." WHERE is_deleted ='0'";
//            }

            $cacheSubscribeType = $query->all();

            self::saveToCache($cacheKey, $cacheSubscribeType);
        }

        if (!empty($cacheSubscribeType)) {
            return $cacheSubscribeType;
        } else {
            return null;
        }
    }
}
