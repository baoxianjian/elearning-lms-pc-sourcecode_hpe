<?php

namespace common\models\message;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ms_push_msg_object}}".
 *
 * @property string $kid
 * @property string $push_msg_id
 * @property string $push_flag
 * @property string $obj_flag
 * @property string $obj_type
 * @property string $obj_range
 * @property string $obj_id
 * @property string $ext_obj_address
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
 */
class MsPushMsgObject extends BaseActiveRecord
{
    const PUSH_FLAG_SEND = '0';
    const PUSH_FLAG_CC = '1';
    const PUSH_FLAG_BCC = '2';

    const OBJ_FLAG_SYSTEM = '0';
    const OBJ_FLAG_EXTERNAL = '1';

    /**
     * 域
     */
    const OBJ_TYPE_DOM = '0';
    /**
     * 组织
     */
    const OBJ_TYPE_ORG = '1';
    /*
     * 岗位
     */
    const OBJ_TYPE_POS = '2';
    /**
     * 受众
     */
    const OBJ_TYPE_AUD = '3';
    /**
     * 个人
     */
    const OBJ_TYPE_PER = '4';

    const OBJ_RANGE_SUB_YES = '0';
    const OBJ_RANGE_SUB_NO = '1';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_push_msg_object}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['push_msg_id',], 'required'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'push_msg_id', 'obj_id', 'ext_obj_address', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['push_flag', 'obj_flag', 'obj_type', 'obj_range'], 'string', 'max' => 1],

            [['push_flag', 'obj_flag', 'obj_type'], 'default', 'value' => '0'],

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
            'push_msg_id' => Yii::t('common', 'push_msg_id'),
            'push_flag' => Yii::t('common', 'push_flag'),
            'obj_flag' => Yii::t('common', 'obj_flag'),
            'obj_type' => Yii::t('common', 'obj_type'),
            'obj_id' => Yii::t('common', 'obj_id'),
            'ext_obj_address' => Yii::t('common', 'ext_obj_address'),
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'created_ip' => Yii::t('common', 'created_ip'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'updated_ip' => Yii::t('common', 'updated_ip'),
            'is_deleted' => Yii::t('common', 'is_deleted'),
        ];
    }
}
