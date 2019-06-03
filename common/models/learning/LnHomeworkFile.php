<?php

namespace common\models\learning;

use Yii;
use \common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ln_homework_file}}".
 *
 * @property string $kid
 * @property string $homework_id
 * @property string $user_id
 * @property string $company_id
 * @property string $homework_file_type
 * @property string $file_url
 * @property string $file_name
 * @property string $file_md5
 * @property integer $file_size
 * @property string $mime_type
 * @property string $file_extension
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnHomework $lnHomework
 */
class LnHomeworkFile extends BaseActiveRecord
{
    const FILE_TYPE_TEACHER = '0';

    const FILE_TYPE_STUDENT = '1';


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_homework_file}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['homework_file_type', 'file_name',], 'required'],
            [['file_size', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'homework_id', 'user_id', 'company_id', 'file_md5', 'created_by', 'created_from', 'updated_by', 'updated_from', 'course_id', 'course_reg_id', 'mod_id', 'mod_res_id', 'courseactivity_id', 'component_id', 'course_complete_id', 'res_complete_id'], 'string', 'max' => 50],
            [['homework_file_type', 'is_deleted'], 'string', 'max' => 1],
            [['file_url'], 'string', 'max' => 500],
            [['file_name', 'mime_type'], 'string', 'max' => 100],
            [['file_extension'], 'string', 'max' => 10],
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
            'kid' => '作业相关文件ID',
            'homework_id' => '作业ID',
            'user_id' => '用户ID',
            'company_id' => '企业ID',
            'homework_file_type' => '文件类型；0：老师要求文件；1：学员作答文件；',
            'file_url' => '文件url',
            'file_name' => '文件名',
            'file_md5' => '文件md5码',
            'file_size' => '文件大小：bytes',
            'mime_type' => '文件MIME类型',
            'file_extension' => '文件后缀名',
            'version' => '版本号',
            'created_by' => '创建人ID',
            'created_at' => '创建时间',
            'created_from' => '创建来源',
            'updated_by' => '更新人ID',
            'updated_at' => '更新时间',
            'updated_from' => '更新来源',
            'is_deleted' => '删除标记；0：正常，1：已删除',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnHomework()
    {
        return $this->hasOne(LnHomework::className(), ['kid' => 'homework_id'])
            ->onCondition([LnHomework::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
