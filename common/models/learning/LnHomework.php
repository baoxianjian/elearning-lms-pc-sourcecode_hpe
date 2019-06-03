<?php

namespace common\models\learning;

use Yii;
use \common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ln_homework}}".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $title
 * @property string $requirement
 * @property integer $finish_before_at
 * @property string $homework_mode
 * @property string $description
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnHomeworkFile[] $lnHomeworkFiles
 * @property LnHomeworkResult[] $lnHomeworkResults
 */
class LnHomework extends BaseActiveRecord
{
    const HOMEWORK_MODE_FILE = '0';
    const HOMEWORK_MODE_TEXT = '1';
    const HOMEWORK_MODE_ALL = '2';

    public $modResId = null;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_homework}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['requirement', 'description'], 'string'],
            [['finish_before_at', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'company_id', 'created_by', 'created_from', 'updated_by', 'updated_from'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 500],
            [['homework_mode', 'is_deleted'], 'string', 'max' => 1],

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
            'kid' => '作业ID',
            'company_id' => '企业ID',
            'title' => '作业名称',
            'requirement' => '作业要求',
            'finish_before_at' => '截至日期',
            'homework_mode' => '作业模式；0：附件文档，1：在线填写，2：全部',
            'description' => '描述',
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
    public function getLnHomeworkFiles()
    {
        return $this->hasMany(LnHomeworkFile::className(), ['homework_id' => 'kid'])
            ->onCondition([LnHomeworkFile::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnHomeworkResults()
    {
        return $this->hasMany(LnHomeworkResult::className(), ['homework_id' => 'kid'])
            ->onCondition([LnHomeworkFile::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
