<?php

namespace common\models\treemanager;

use Yii;
use common\base\BaseActiveRecord;
/**
 * This is the model class for table "{{%fw_cnt_manage_ref}}".
 *
 * @property string $kid
 * @property string $subject_id
 * @property string $subject_type
 * @property string $content_id
 * @property string $content_type
 * @property string $reference_type
 * @property string $status
 * @property integer $start_at
 * @property integer $end_at
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
class FwCntManageRef extends BaseActiveRecord
{
    const REFERENCE_TYPE_BELONG = "0";
    const REFERENCE_TYPE_MANGER = "1";

//    const CONTENT_TYPE_USER = "user";
//    const CONTENT_TYPE_POSITION = "position";
//    const CONTENT_TYPE_ROLE = "role";
    const CONTENT_TYPE_DOMAIN = "domain";
    const CONTENT_TYPE_ORGNIZATION = "orgnization";
    const CONTENT_TYPE_COMPANY = "company";
//    const CONTENT_TYPE_CERTIFICATION_TEMPLATE = "certification_template";
//    const CONTENT_TYPE_WECHAT_TEMPLATE = "wechat_template";
//    const CONTENT_TYPE_COMPANY_MENU = "company_menu";

//    const SUBJECT_TYPE_TREE = "tree";
    const SUBJECT_TYPE_USER = "user";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_cnt_manage_ref}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subject_id', 'subject_type', 'content_id', 'content_type', 'reference_type'], 'required'],
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'subject_id', 'content_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['content_type', 'subject_type'], 'string', 'max' => 30],
            [['reference_type', 'status', 'is_deleted'], 'string', 'max' => 1],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['status'], 'string', 'max' => 1],
            [['status'], 'default', 'value' => self::STATUS_FLAG_TEMP],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'default', 'value' => self::DELETE_FLAG_NO],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]],

            [['reference_type'], 'string', 'max' => 1],
            [['reference_type'], 'default', 'value' => self::REFERENCE_TYPE_BELONG],
            [['reference_type'], 'in', 'range' => [self::REFERENCE_TYPE_BELONG, self::REFERENCE_TYPE_MANGER]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'cnt_manage_ref_id'),
            'subject_id' => Yii::t('common', 'subject_id'),
            'subject_type' => Yii::t('common', 'subject_type'),
            'content_id' => Yii::t('common', 'content_id'),
            'content_type' => Yii::t('common', 'reference_type'),
            'reference_type' => Yii::t('common', 'reference_type'),
            'status' => Yii::t('common', 'status'),
            'start_at' => Yii::t('common', 'start_at'),
            'end_at' => Yii::t('common', 'end_at'),
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


}