<?php

namespace common\models\learning;

use Yii;
use \common\base\BaseActiveRecord;

/**
 * This is the model class for table "eln_ln_files".
 *
 * @property string $kid
 * @property string $component_id
 * @property string $file_name
 * @property string $file_title
 * @property string $file_md5
 * @property string $file_size
 * @property string $file_path
 * @property string $file_dir
 * @property string $backup_file_path
 * @property string $file_extension
 * @property string $mime_type
 * @property string $status
 * @property string $upload_batch
 * @property string $manifest_info
 * @property string $entrance_address
 * @property string $format_transfer_status
 * @property string $thumbnail_url
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
class LnFiles extends BaseActiveRecord
{
    const FORMAT_TRANSFER_STATUS_WAITING = '0';
    const FORMAT_TRANSFER_STATUS_COMPLETED = '1';
    const FORMAT_TRANSFER_STATUS_FAILED = '2';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_files}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file_name', 'file_md5', 'file_size', 'file_path', 'file_dir', 'status'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'file_md5', 'file_size', 'upload_batch', 'file_extension', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['file_name','mime_type'], 'string', 'max' => 100],
            [['file_path', 'file_dir', 'backup_file_path','thumbnail_url'], 'string', 'max' => 1000],
            ['manifest_info','string'],
            ['file_title', 'string', 'max' => 200],
            ['entrance_address', 'string', 'max' => 500],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

            [['format_transfer_status'], 'string', 'max' => 1],
            [['format_transfer_status'], 'in', 'range' => [self::FORMAT_TRANSFER_STATUS_WAITING, self::FORMAT_TRANSFER_STATUS_COMPLETED, self::FORMAT_TRANSFER_STATUS_FAILED]],

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
            'file_name' => Yii::t('common', 'file_name'),
            'file_title' => Yii::t('common', 'file_title'),
            'file_md5' => Yii::t('common', 'file_md5'),
            'file_size' => Yii::t('common', 'file_size'),
            'file_path' => Yii::t('common', 'file_path'),
            'file_dir' => Yii::t('common', 'file_dir'),
            'backup_file_path' => Yii::t('common', 'backup_file_path'),
            'manifest_info' => Yii::t('common', 'manifest_info'),
            'mime_type' => Yii::t('common', 'mime_type'),
            'file_extension' => Yii::t('common', 'mime_type'),
            'status' => Yii::t('common', 'status'),
            'upload_batch' => Yii::t('common', 'upload_batch'),
            'format_transfer_status' => Yii::t('common', 'format_transfer_status'),
            'thumbnail_url' => Yii::t('common', 'thumbnail_url'),
            'entrance_address' => Yii::t('common', 'entrance_address'),
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