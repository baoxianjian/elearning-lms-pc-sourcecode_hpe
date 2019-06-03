<?php

namespace common\base;

use common\models\framework\FwPrimaryKey;
use common\helpers\TNetworkHelper;
use common\helpers\TStringHelper;
use common\traits\CacheTrait;
use yii;
use yii\base\Exception;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\db\ActiveQuery;
use yii\helpers\Html;

/**
 * @property string $kid
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
class BaseActiveRecord extends ActiveRecord
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

    const DEPEND_MODE_SQL = "sql";
    const DEPEND_MODE_DURATION = "duration";

    const DATABASE_TYPE_ORACLE = "ORACLE";
    const DATABASE_TYPE_MYSQL = "MYSQL";
    const DATABASE_TYPE_MSSQL = "MSSQL";


    public $saveEncode = false;//保存数据前，把所有数据Encode，防止script攻击等
    public $saveUpdateVersion = true;//保存数据前，更新版本号
    public $needReturnKey = true;//保存数据后，是否要返回Key值
    public $checkKeyExist = false;//保存数据前检查Key值是否已经存在

    private $keyConflictCurrentNumber = 1;//主键冲突次数
    private $keyConflictMaxRetryNumber = 3;//主键冲突最大重试次数

    public $systemKey = "PC";

    public static $defaultKey = "PC";

    //需要强制缓存的表名，建议只放数量少或不频繁更新的表
        public static $forceCacheTable = [
        '{{%fw_company}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_MONTH],
        '{{%fw_service}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%fw_action_log_filter}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%fw_dictionary}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%fw_dictionary_category}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%fw_domain}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_MONTH],
        '{{%fw_external_system}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%fw_orgnization}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_MONTH],
        '{{%fw_permission}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%fw_position}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%fw_role}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%fw_system_info}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%ln_tag}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%fw_tag_category}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_MONTH],
        '{{%fw_tree_type}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%ln_certification}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_MONTH],
        '{{%ln_courseware_book}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%fw_user}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_teacher}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_MONTH],
        '{{%ln_task}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_course}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_courseware}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_mod_res}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_courseactivity}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_files}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],//如果存在后台转换格式的文件，可能要特殊处理一下，比如2小时刷新
        '{{%ln_course_mods}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_examination}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_investigation}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_homework}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_homework_file}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_courseware_scorm}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%ln_scorm_scoes}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%ln_scorm_seq_mapinfo}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%ln_scorm_seq_objective}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%ln_scorm_seq_rollru}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%ln_scorm_seq_rollrucond}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%ln_scorm_seq_rulecond}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%ln_scorm_seq_ruleconds}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%ln_certification_template}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%ln_component}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR],
        '{{%ln_courseware_category}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_course_category}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_examination_category}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_exam_paper_category}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_exam_question_category}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],

        '{{%ln_examination_paper}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_examination_question}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_exam_question_user}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],
        '{{%ln_examination_paper_user}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_DAY],

        '{{%ms_subscribe_type}}'=>['depend'=>self::DEPEND_MODE_DURATION,'duration'=>self::DURATION_YEAR]
    ];


    //部分表不存在删除操作,可以强制物理删除
    public static $forcePhysicalDeleteTable = [
        '{{%ln_scorm_scoes_track}}',
        '{{%fw_action_log}}',
        '{{%fw_service_log}}'
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
        $tableName = parent::tableName();
        if (!array_key_exists($tableName, self::$forcePhysicalDeleteTable)) {
            if (!$includeDeleted) {
                return parent::find()->andFilterWhere([parent::tableName() . '.'
                . self::getQuoteColumnName("is_deleted") => self::DELETE_FLAG_NO]);
            }
        }

        return parent::find();
    }

    /**
     * 获取带数据分隔符的列名
     * @return string
     */
    public static function getQuoteColumnName($name) {
        $quote = '`';
        if (self::getDatabaseType() == self::DATABASE_TYPE_ORACLE) {
            $quote = '"';
        }

        return $quote . $name . $quote;
    }

    public static function realTableName()
    {
        return self::calculateTableName(self::tableName());
    }

    /**
     * 获取数据库类型（默认是Mysql）
     * @return string
     */
    public static function getDatabaseType() {
        $databaseType = self::DATABASE_TYPE_MYSQL;
        if (isset(Yii::$app->params['database_type'])) {
            $databaseType = strtoupper(Yii::$app->params['database_type']);
        }

        return $databaseType;
    }

    /**
     * 获取缓存类型
     * @return mixed|string
     */
    public static function getCacheType(){
        return "normal";
    }

    /**
     * 获取名字
     * @return mixed|string
     */
    public static function getName(){
        $tableName = parent::tableName();
        $calculatedTableName = self::calculateTableName($tableName);
        return $calculatedTableName;
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
        $tableName = parent::tableName();
        if (count(self::$forceCacheTable) > 0) {
            if (array_key_exists($tableName, self::$forceCacheTable)) {
                $depandMode = self::$forceCacheTable[$tableName]["depend"];
                $duration = self::$forceCacheTable[$tableName]["duration"];
                $withCache = true;
            }
        }

        $calculatedTableName = self::calculateTableName($tableName);
        if ($withCache) {
            $isAssociative = ArrayHelper::isAssociative($condition);
            $isSingleKey = false;
            if (!$isAssociative) {
                //判断是不是按kid取值
                if (is_array($condition) && count($condition) == 1 && is_string(current(array_keys($condition))) && strtolower(current(array_keys($condition))) == "kid") {
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

                $cacheKey = self::getCacheType() . "-" . $calculatedTableName . "-" . $key;
                $result = Yii::$app->cache->get($cacheKey);
            }
            else {
                $result = null;
                $key = null;
                $cacheKey = null;
            }

            if ($result == false || empty($result)) {
                if ($includeDeleted == false) {
                    if (!array_key_exists($tableName, self::$forcePhysicalDeleteTable)) {
                        if (is_array($condition) && !array_key_exists("is_deleted", $condition)) {
                            $condition = ArrayHelper::merge($condition, ['is_deleted' => self::DELETE_FLAG_NO]);
                        } else if (is_string($condition)) {
                            $tempCondition['kid'] = $condition;
                            $condition = ArrayHelper::merge($tempCondition, ['is_deleted' => self::DELETE_FLAG_NO]);
                        }
                    }
                }

                $result = parent::findOne($condition);

                if ($isSingleKey && !empty($result)) {
                    if ($depandMode == self::DEPEND_MODE_SQL) {
                        $dependencySql = "SELECT " . self::getQuoteColumnName("updated_at") . " FROM " .
                            self::getQuoteColumnName($tableName) . " WHERE ". self::getQuoteColumnName("kid") . " = '" . $key . "'";

                        self::saveToCache($cacheKey, $result, $dependencySql, $duration, $withCache);
                    }
                    else {
                        self::saveToCache($cacheKey, $result, null, $duration, $withCache);
                    }
                }
            }
        }
        else {
            if ($includeDeleted == false) {
                if (!array_key_exists($tableName, self::$forcePhysicalDeleteTable)) {
                    if (is_array($condition) && !array_key_exists("is_deleted", $condition)) {
                        $condition = ArrayHelper::merge($condition, ['is_deleted' => self::DELETE_FLAG_NO]);
                    } else if (is_string($condition)) {
                        $tempCondition['kid'] = $condition;
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
        $tableName = parent::tableName();
        if (!array_key_exists($tableName, self::$forcePhysicalDeleteTable)) {
            if ($includeDeleted == false) {
                $tableName = parent::tableName();
                if (is_array($condition) && !array_key_exists("is_deleted", $condition)) {
                    $condition = ArrayHelper::merge($condition, [parent::tableName() . "." .
                    self::getQuoteColumnName("is_deleted") => self::DELETE_FLAG_NO]);
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
        $tableName = parent::tableName();
        if (!array_key_exists($tableName, self::$forcePhysicalDeleteTable)) {
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
     * 批量插入数据(常规模式），通过循环对象批量插入
     * @param array $models 需要插入的数据数组
     * @param null $errMsg 错误信息
     * @param bool|false $needReturnKey 是否需要返回id
     * @param array $resultId 返回的ID数组
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function batchInsertNormalMode($models, &$errMsg = null, $needReturnKey = false, &$resultId = [])
    {
        if (!empty($models) && count($models) > 0) {

            $transaction = Yii::$app->db->beginTransaction();
            try {

                foreach ($models as $model) {
                    if (!$model->save()) {
                        throw new Exception();
                    }
                    else {
                        if ($needReturnKey) {
                            array_push($resultId, $model->kid);
                        }
                    }
                }
                $transaction->commit();

                return true;
            } catch (Exception $e) {
                $errMsg = $e->getMessage();
                $transaction->rollBack();
                return false;
            }
        }
        else {
            return false;
        }
    }

    /**
     * 批量更新数据(常规模式），通过循环对象批量更新
     * @param array $models 需要更新的数据数组
     * @param null $errMsg 错误信息
     * @param bool|false $needReturnKey 是否需要返回id
     * @param array $resultId 返回的ID数组
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function batchUpdateNormalMode($models, &$errMsg = null, $needReturnKey = false, &$resultId = [])
    {
        if (!empty($models) && count($models) > 0) {

            $transaction = Yii::$app->db->beginTransaction();
            try {

                foreach ($models as $model) {
                    if (!$model->save()) {
                        throw new Exception();
                    }
                    else {
                        if ($needReturnKey) {
                            array_push($resultId, $model->kid);
                        }
                    }
                }
                $transaction->commit();

                return true;
            } catch (Exception $e) {
                $errMsg = $e->getMessage();
                $transaction->rollBack();
                return false;
            }
        }
        else {
            return false;
        }
    }

    /**
     * 批量删除数据(常规模式），通过循环对象批量删除
     * @param array $models 需要删除的数据数组
     * @param null $errMsg 错误信息
     * @param bool|false $needReturnKey 是否需要返回id
     * @param array $resultId 返回的ID数组
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function batchDeleteNormalMode($models, &$errMsg = null, $needReturnKey = false, &$resultId = [])
    {
        if (!empty($models) && count($models) > 0) {

            $transaction = Yii::$app->db->beginTransaction();
            try {

                foreach ($models as $model) {
                    if (!$model->delete()) {
                        throw new Exception();
                    }
                    else {
                        if ($needReturnKey) {
                            array_push($resultId, $model->kid);
                        }
                    }
                }
                $transaction->commit();

                return true;
            } catch (Exception $e) {
                $errMsg = $e->getMessage();
                $transaction->rollBack();
                return false;
            }
        }
        else {
            return false;
        }
    }

    /**
     * 批量插入数据(SQL数组模式），通过拼接SQL数组批量插入
     * @param array $models 需要插入的数据数组
     * @param null $errMsg 错误信息
     * @param array $columns 自定义插入字段名
     * @param null $systemKey 系统标识
     * @param bool|false $needReturnKey 是否需要返回id
     * @param array $resultId 返回的ID数组
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function batchInsertSqlArray($models, &$errMsg = null, $columns = [], $systemKey = null, $needReturnKey = false, &$resultId = [])
    {
        if (!empty($models) && count($models) > 0) {
            if (empty($systemKey)) {
                $systemKey = self::$defaultKey;
            }
//        $errorNumber = 0;
            $tableName = null;

            $transaction = Yii::$app->db->beginTransaction();
            try {
                if (empty($tableName)) {
                    $tableName = $models[0]->tableName();
                }
                if (empty($columns)) {
                    $columns = $models[0]->attributes();
                }

                $err = false;
                $rows = self::batchPrepareData($models,$systemKey,true,true,$needReturnKey,$resultId,$err,$errMsg);

                if (!$err && count($rows) > 0) {
                    Yii::$app->db->createCommand()->batchInsert($tableName, $columns, $rows)->execute();
                    $transaction->commit();
                    return true;
                }
                else {
                    $transaction->rollBack();
                    return false;
                }
            } catch (Exception $e) {
                $errMsg = $e->getMessage();
                $transaction->rollBack();
                return false;
            }
        }
        else {
            return false;
        }
    }

    /**
     * 逻辑删除所有数据
     * @param string $condition
     * @param array $params
     * @param null $systemKey
     * @return int
     * @throws \yii\db\Exception
     */
    public static function deleteAll($condition = '', $params = [], $systemKey = null)
    {
        $tableName = parent::tableName();
        if (!array_key_exists($tableName, self::$forcePhysicalDeleteTable)) {
            $attributes = [
                'is_deleted' => self::DELETE_FLAG_YES,
            ];

//            if (!is_array($condition)) {
//                if ($condition != '')
//                    $condition = $condition . ' AND ' . self::getQuoteColumnName("is_deleted") . " = '" . self::DELETE_FLAG_NO . "'";
//                else
//                    $condition = self::getQuoteColumnName("is_deleted") . "='" . self::DELETE_FLAG_NO . "'";
//            } else {
//                $condition = ArrayHelper::merge($condition, ['is_deleted' => self::DELETE_FLAG_NO]);
//            }

            return self::updateAll($attributes, $condition, $params, false, $systemKey);
        }
        else {
            return parent::deleteAll($condition,$params);
        }
    }

    /**
     * 通过Kid逻辑删除所有数据
     * @param string $condition
     * @param array $params
     * @param null $systemKey
     * @return int
     * @throws \yii\db\Exception
     */
    public static function deleteAllByKid($kids, $systemKey = null)
    {
        if ($kids != '') {
            if (is_array($kids)) {
                $kids = "'" . join("','", $kids) . "'";
            } else {
//                $kids = str_replace("'",'',$kids);
//                $kids = "'{$kids}'";
            }
            $condition = self::getQuoteColumnName("kid") . " in (" . $kids . ") AND " . self::getQuoteColumnName("is_deleted") . " = '" . self::DELETE_FLAG_NO . "'";
        }
        else {
            $condition = null;
        }

        $tableName = parent::tableName();
        if (!array_key_exists($tableName, self::$forcePhysicalDeleteTable)) {
            $attributes = [
                'is_deleted' => self::DELETE_FLAG_YES,
            ];

            if ($kids != '') {
                return self::updateAll($attributes, $condition, null, false, $systemKey);
            } else {
                return false;
            }
        }
        else {
            return parent::deleteAll($condition);
        }
    }

    /**
     * 根据条件更新数据
     * @param array $attributes
     * @param string $condition
     * @param array $params
     * @param bool|false $includeDeleted
     * @param bool|false $saveEncode
     * @param null $systemKey
     * @return int
     * @throws \yii\db\Exception
     */
    public static function updateAll($attributes = [], $condition = '', $params = [], $includeDeleted = false,
                                      $saveEncode = false, $systemKey = null, $withVersion = true)
    {
        if (empty($systemKey)) {
            $systemKey = self::$defaultKey;
        }

        if (Yii::$app->user->isGuest)
            $currentUserId = "00000000-0000-0000-0000-000000000000";
        else
            $currentUserId = strval(Yii::$app->user->getId());

        $currentDate = time();

        if ($withVersion) {
            $newAttributes = [
                'updated_at' => $currentDate,
                'updated_by' => $currentUserId,
                'updated_from' => $systemKey,
                'updated_ip' => TNetworkHelper::getClientRealIP(),
                'version' => new Expression(BaseActiveRecord::getQuoteColumnName("version") . "+1")
            ];
        }
        else {
            $newAttributes = [
                'updated_at' => $currentDate,
                'updated_by' => $currentUserId,
                'updated_from' => $systemKey,
                'updated_ip' => TNetworkHelper::getClientRealIP(),
            ];
        }

        $attributes = ArrayHelper::merge($attributes, $newAttributes);

        if ($saveEncode) {
            foreach ($attributes as $key => $attribute) {
                if (!empty($attribute) && is_string($attribute)) {
                    $isUTF8 = TStringHelper::isUTF8($attribute);
                    if (!$isUTF8) {
                        $attribute = iconv("gb2312", "utf-8//IGNORE", $attribute);
                        $attributes[$key] = $attribute;
                    }

                    $oldValue = $attribute;
                    $decodeValue = Html::decode($attribute);
                    //如果这2个值不一样，说明已经被加密过，要避免重复加密
                    if ($oldValue == $decodeValue) {
                        $attributes[$key] = Html::encode($attribute);
                    }
                }
            }
        }
        else {
            foreach ($attributes as $key => $attribute) {
                if (!empty($attribute) && is_string($attribute)) {
                    $isUTF8 = TStringHelper::isUTF8($attribute);
                    if (!$isUTF8) {
                        $attribute = iconv("gb2312", "utf-8//IGNORE", $attribute);
                        $attributes[$key] = $attribute;
                    }
                }
            }
        }

        if (!$includeDeleted) {
            $tableName = parent::tableName();
            if (!array_key_exists($tableName, self::$forcePhysicalDeleteTable)) {
                if (!is_array($condition)) {
                    if ($condition != '')
                        $condition = $condition . ' AND ' . self::getQuoteColumnName("is_deleted") . " = '" . self::DELETE_FLAG_NO . "'";
                    else
                        $condition = self::getQuoteColumnName("is_deleted") . " = '" . self::DELETE_FLAG_NO . "'";
                }
                else {
                    $condition = ArrayHelper::merge($condition, ['is_deleted' => self::DELETE_FLAG_NO]);
                }
            }
        }

        return parent::updateAll($attributes,$condition,$params);
    }


    /**
     * 增加字段结果
     * @param $id
     * @param $field
     * @param null $systemKey
     * @return int
     */
    public static function addFieldNumber($id, $field, $systemKey = null){

        if (empty($systemKey)) {
            $systemKey = self::$defaultKey;
        }

        $condition = self::getQuoteColumnName("kid") . " = :kid";

        $param = [
            ':kid' => $id,
        ];

        $attributes = [
            $field => new Expression(self::getQuoteColumnName($field) . ' + 1'),
        ];

        return self::updateAll($attributes, $condition, $param, false, false,  $systemKey, false);
    }


    /**
     * 减少字段结果
     * @param $id
     * @param $field
     * @param null $systemKey
     * @return int
     */
    public static function subFieldNumber($id, $field, $systemKey = null){

        if (empty($systemKey)) {
            $systemKey = self::$defaultKey;
        }

        $condition = self::getQuoteColumnName("kid") . " = :kid";

        $param = [
            ':kid' => $id,
        ];

        $attributes = [
            $field => new Expression(self::getQuoteColumnName($field) . ' - 1'),
        ];

        return self::updateAll($attributes, $condition, $param, false, false,  $systemKey, false);
    }




    /**
     * 物理删除数据（当前数据）
     * @return false|int
     * @throws \Exception
     */
    public function physicalDelete()
    {
        if (isset($this->kid)) {
            $withCache = false;
//            $depandMode = self::DEPEND_MODE_SQL;
            $tableName = parent::tableName();
            if (count(self::$forceCacheTable) > 0) {
                if (array_key_exists($tableName, self::$forceCacheTable)) {
//                    $depandMode = self::$forceCacheTable[$tableName]["depend"];
                    $withCache = true;
                }
            }
            if ($withCache) {
                $kid = $this->kid;
                self::removeFromCacheByKid($kid);
            }
        }

        if (empty($this->kid)) {
            return false;
        }
        $condition = self::getQuoteColumnName("kid") . " = '" . $this->kid . "'";

        return parent::deleteAll($condition);
    }

    /**
     * 物理删除数据（根据条件）
     * @param string $condition
     * @param array $params
     * @return int
     */
    public static function physicalDeleteAll($condition = '', $params = [])
    {
        if ($condition != '') {
//            $condition = $condition . ' AND is_deleted="' . self::DELETE_FLAG_NO . '"';
            return parent::deleteAll($condition,$params);//物理删除不考虑任何已被逻辑删除的数据
        }
        else {
            return parent::deleteAll();
        }
    }

    /**
     * 物理删除数据（根据条件）
     * @param string $condition
     * @param array $params
     * @return int
     */
    public static function physicalDeleteAllByKid($kids, $params = [])
    {
        if ($kids != '') {
//            $condition = 'kid in (' . $kids . ') AND is_deleted="' . self::DELETE_FLAG_NO . '"';
            if (is_array($kids)){
                $kids = "'".join("','", $kids)."'";
            }
            $condition = self::getQuoteColumnName("kid") . ' in (' . $kids . ')';//物理删除不考虑任何已被逻辑删除的数据
            return parent::deleteAll($condition,$params);
        }
        else {
            return false;
        }
    }

    /**
     * 包装数据
     * @param $model
     * @param $systemKey
     * @param bool|true $insert
     * @param bool|true $saveUpdateVersion
     */
    private static function batchPrepareData($models, $systemKey = null, $insert = true, $saveUpdateVersion = true,$needReturnKey = false,&$resultId = [],&$err = false,&$errMsg = null) {
        $currentDate = time();
        $rows = [];
        if (empty($systemKey)) {
            $systemKey = self::$defaultKey;
        }

        if (Yii::$app->user->isGuest)
            $currentUserId = "00000000-0000-0000-0000-000000000000";
        else
            $currentUserId = strval(Yii::$app->user->getId());

        $checkKeyExist = false;

        foreach ($models as $model) {
            $result = self::prepareData($model,$currentDate,$currentUserId,$systemKey,$insert,$saveUpdateVersion,$checkKeyExist,$needReturnKey,$errMsg);
            if ($result) {
                $rows[] = $model->attributes;
            }
            else {
                $err = true;
                break;//一旦有错误，就立马中止（批量模式没有必要一直检查到底）
            }
        }

        return $rows;
    }

    /**
     * 包装数据
     * @param $model
     * @param $systemKey
     * @param bool|true $insert
     * @param bool|true $saveUpdateVersion
     */
    private static function prepareData($model,$currentDate,$currentUserId, $systemKey, $insert = true, $saveUpdateVersion = true,$checkKeyExist = false,$needReturnKey = true,&$errMsg,&$resultId = []) {
        if ($model->validate()) {
            if (empty($systemKey)) {
                $systemKey = self::$defaultKey;
            }

            $ip = TNetworkHelper::getClientRealIP();

            if ($insert) {
                if ($model->kid == null) {
//                    $primaryKeys = $this->getPrimaryKey(true);
//                    foreach($primaryKeys as $key => $value)
//                    {
                    if ($needReturnKey) {
                        $genkey = FwPrimaryKey::generateNextPrimaryID($model->tableName(), true, $checkKeyExist);
                        if ($genkey != null) {
                            $model->kid = $genkey;
                        }
                    }
                    else {
                        $model->kid = new Expression('UPPER(UUID())');
                    }
                }

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

            if ($needReturnKey) {
                array_push($resultId, $model->kid);
            }

            return true;
        }
        else {
            $errMsg = $model->getErrors();
            return false;
        }
    }


    public function save($runValidation = true, $attributeNames = null)
    {
        try {
            return parent::save($runValidation, $attributeNames);
        } catch (Exception $ex) {
            $errorCode = $ex->getCode();
            $errorMessage = $ex->getMessage();
            $isKeyConflict = false;
            if ($this->getDatabaseType() == self::DATABASE_TYPE_MYSQL) {
                if ($errorCode == "23000" && strpos($errorMessage,"PRIMARY") != false) {
                    $isKeyConflict = true;
                }
            }
            else if ($this->getDatabaseType() == self::DATABASE_TYPE_ORACLE){
                if (strpos($errorMessage,"ORA-00001") != false) {
                    $isKeyConflict = true;
                }
            }

            if ($isKeyConflict) {
                //因为主键冲突导致数据插入失败，可以自动生成新的key，然后进行重试；
                if ($this->keyConflictCurrentNumber <= $this->keyConflictMaxRetryNumber) {
                    $genkey = FwPrimaryKey::generateNextPrimaryID($this->tableName());
                    if ($genkey != null) {
                        $this->keyConflictCurrentNumber = $this->keyConflictCurrentNumber + 1;
                        $this->kid = $genkey;
                        return $this->save();
                    }
                    else {
                        return false;
                    }
                }
                else {
                    return false;
                }
            }
            else {
                return false;
            }
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
                $this->prepareData($this, $currentDate, $currentUserId, $this->systemKey, true, true, $this->checkKeyExist, $this->needReturnKey, $errMsg);
            } else {
                $this->prepareData($this, $currentDate, $currentUserId, $this->systemKey, false, $this->saveUpdateVersion, $this->checkKeyExist, $this->needReturnKey, $errMsg);
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
        if ($this->validate() && !$insert && isset($this->kid)) {
            $canWriteToCache = true;
        }

//        if (!$insert) {
        if ($canWriteToCache) {
            $withCache = false;
            $depandMode = self::DEPEND_MODE_SQL;
            $tableName = parent::tableName();
            if (count(self::$forceCacheTable) > 0) {
                if (array_key_exists($tableName, self::$forceCacheTable)) {
                    $depandMode = self::$forceCacheTable[$tableName]["depend"];
                    $duration = self::$forceCacheTable[$tableName]["duration"];
                    $withCache = true;
                }
            }
//                if ($withCache && $depandMode == self::DEPEND_MODE_DURATION) {
//                    $kid = $this->kid;
//                    self::removeFromCacheByKid($kid);
//                }

            if ($withCache) {
                $calculatedTableName = self::calculateTableName($tableName);
                $key = $this->kid;
                $cacheKey = self::getCacheType() . "-" . $calculatedTableName . "-" . $key;

                if ($this->is_deleted == self::DELETE_FLAG_YES) {
                    self::removeFromCache($cacheKey);
                }
                else {
                    if ($depandMode == self::DEPEND_MODE_SQL) {
                        $dependencySql = "SELECT " . self::getQuoteColumnName("updated_at") . " FROM " . self::getQuoteColumnName($tableName) . " WHERE "
                            . self::getQuoteColumnName("kid") . " = '" . $key . "'";

                        self::saveToCache($cacheKey, $this, $dependencySql, $duration, $withCache);
                    } else {
                        self::saveToCache($cacheKey, $this, null, $duration, $withCache);
                    }
                }
            }
        }
//        }
    }

    /**
     * 计算表名
     * @param $originTableName
     * @return mixed|string
     */
    public static function calculateTableName($originTableName = null)
    {
        if ($originTableName == null){
            $originTableName = self::tableName();
        }
        $tbPrefix = Yii::$app->db->tablePrefix;
        $tbName = $originTableName;
//        $tbPrefix = $tbPrefix;

        if (strpos($tbName, "{{%") === false)
        {
            return $tbName;
        }
        else
        {
            $tbName = str_replace("{{%","",$tbName);
            $tbName = str_replace("}}","",$tbName);
            return $tbPrefix . $tbName;
        }
    }
    
    public function getOffset($page, $size)
    {
        $_page = (int)$page - 1;

        return $size < 1 ? 0 : $_page * $size;
    }
}
