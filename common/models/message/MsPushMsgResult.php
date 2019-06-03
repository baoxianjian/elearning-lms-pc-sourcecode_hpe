<?php

namespace common\models\message;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ms_push_msg_result}}".
 *
 * @property string $kid
 * @property string $push_msg_id
 * @property string $obj_flag
 * @property string $user_id
 * @property string $ext_obj_address
 * @property string $push_status
 * @property string $error_msg_code
 * @property string $error_msg_title
 * @property string $error_msg_content
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
class MsPushMsgResult extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_push_msg_result}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['push_msg_id'], 'required'],
            [['error_msg_content'], 'string'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'push_msg_id', 'user_id', 'ext_obj_address', 'error_msg_code', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['obj_flag', 'push_status', 'is_deleted'], 'string', 'max' => 1],
            [['error_msg_title'], 'string', 'max' => 500],

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
            'obj_flag' => Yii::t('common', 'obj_flag'),
            'user_id' => Yii::t('common', 'user_id'),
            'ext_obj_address' => Yii::t('common', 'ext_obj_address'),
            'push_status' => Yii::t('common', 'push_status'),
            'error_msg_code' => Yii::t('common', 'error_msg_code'),
            'error_msg_title' => Yii::t('common', 'error_msg_title'),
            'error_msg_content' => Yii::t('common', 'error_msg_content'),
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
