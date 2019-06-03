<?php

namespace common\models\framework;

use common\base\BaseActiveRecord;
use common\base\BaseActiveRecordMongoDB;
use Yii;

/**
 * This is the model class for table "{{%fw_action_log}}".
 *
 * @property \MongoId|string $_id
 * @property string $user_id
 * @property string $system_id
 * @property string $action_filter_id
 * @property string $controller_id
 * @property string $action_id
 * @property string $action_parameter_query
 * @property string $action_parameter_body
 * @property string $orgnization_name
 * @property string $filter_name
 * @property string $orgnization_id
 * @property string $orgnization_tree_node_id
 * @property string $real_name
 * @property string $user_name
 * @property string $encrypt_mode
 * @property string $http_mode
 * @property string $system_flag
 * @property string $action_url
 * @property string $action_ip
 * @property string $start_at
 * @property string $end_at
 * @property string $duration_time
 * @property string $machine_label
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property FwUser $fwUser
 * @property FwActionLogFilter $fwActionFilter
 */
class FwActionLogMongo extends BaseActiveRecordMongoDB
{
    const ENCRYPT_MODE_NONE = "0";
    const ENCRYPT_MODE_AES = "1";
    const ENCRYPT_MODE_DES = "2";

    const HTTP_MODE_GET = "0";
    const HTTP_MODE_POST = "1";

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'eln_fw_action_log';
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'user_id',
            'system_id',
            'orgnization_id',
            'orgnization_tree_node_id',
            'real_name',
            'user_name',
            'action_filter_id',
            'controller_id',
            'action_id',
            'orgnization_name',
            'filter_name',
            'action_parameter_query',
            'action_parameter_body',
            'encrypt_mode',
            'http_mode',
            'system_flag',
            'action_url',
            'action_ip',
            'start_at',
            'end_at',
            'duration_time',
            'machine_label',
            'version',
            'created_by',
            'created_at',
            'created_from',
            'created_ip',
            'updated_by',
            'updated_at',
            'updated_from',
            'updated_ip',
            'is_deleted',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'controller_id', 'action_id', 'action_url', 'system_flag', 'action_ip'], 'required', 'on' => 'manage'],
            [['created_at', 'updated_at'], 'integer'],
            [['start_at', 'end_at', 'duration_time'], 'number'],
            [['user_id', 'system_id', 'action_filter_id', 'controller_id', 'action_id', 'created_by', 'updated_by','machine_label'], 'string', 'max' => 50],
            [['system_flag', 'action_ip'], 'string', 'max' => 30],
            [['action_parameter_query','action_parameter_body','action_url'], 'string'],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['encrypt_mode'], 'string', 'max' => 1],
            [['encrypt_mode'], 'in', 'range' => [self::ENCRYPT_MODE_NONE, self::ENCRYPT_MODE_AES, self::ENCRYPT_MODE_DES]],
            [['encrypt_mode'], 'default', 'value'=> self::ENCRYPT_MODE_NONE],

            [['http_mode'], 'string', 'max' => 1],
            [['http_mode'], 'in', 'range' => [self::HTTP_MODE_GET, self::HTTP_MODE_POST]],
            [['http_mode'], 'default', 'value'=> self::HTTP_MODE_GET],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

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
            '_id' => Yii::t('common', 'kid'),
            'user_id' => Yii::t('common', 'user_id'),
            'system_id' => Yii::t('common', 'system_id'),
            'action_filter_id' => Yii::t('common', 'action_filter_id'),
            'controller_id' => Yii::t('common', 'controller_id'),
            'orgnization_id' => Yii::t('common', 'orgnization_id'),
            'orgnization_tree_node_id' => Yii::t('common', 'tree_node_id'),
            'real_name' => Yii::t('common', 'real_name'),
            'user_name' => Yii::t('common', 'user_name'),
            'action_id' => Yii::t('common', 'action_id'),
            'action_parameter_query' => Yii::t('common', 'action_parameter_query'),
            'action_parameter_body' => Yii::t('common', 'action_parameter_body'),
            'orgnization_name' => Yii::t('common', 'orgnization_name'),
            'filter_name' => Yii::t('common', 'filter_name'),
            'encrypt_mode' => Yii::t('common', 'encrypt_mode'),
            'http_mode' => Yii::t('common', 'http_mode'),
            'system_flag' => Yii::t('common', 'system_flag'),
            'action_url' => Yii::t('common', 'action_url'),
            'action_ip' => Yii::t('common', 'action_ip'),
            'start_at' => Yii::t('common', 'start_at'),
            'end_at' => Yii::t('common', 'end_at'),
            'duration_time' => Yii::t('common', 'duration_time'),
            'machine_label' => Yii::t('common', 'machine_label'),
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'created_ip' => Yii::t('common', 'created_ip'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'updated_ip' => Yii::t('common', 'updated_ip'),
            'is_deleted' => Yii::t('common', 'is_deleted')
        ];
    }


    public function getUserName()
    {
        return $this->user_name;
    }


    public function getRealName()
    {
        return $this->real_name;
    }

    public function getOrgnizationName()
    {
        return $this->orgnization_name;
    }

    public function getFilterName()
    {
        return $this->filter_name;
    }

}
