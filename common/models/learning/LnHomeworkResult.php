<?php

namespace common\models\learning;

use Yii;
use \common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ln_homework_result}}".
 *
 * @property string $kid
 * @property string $homework_id
 * @property string $user_id
 * @property string $company_id
 * @property string $homework_result
 * @property string $description
 * @property string $course_id
 * @property string $course_reg_id
 * @property string $mod_id
 * @property string $mod_res_id
 * @property string $courseactivity_id
 * @property string $component_id
 * @property string $course_complete_id
 * @property string $res_complete_id
 * @property string course_attempt_number
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
class LnHomeworkResult extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_homework_result}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['homework_result', 'description'], 'string'],
            [['version','course_attempt_number', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'homework_id', 'user_id', 'company_id', 'created_by', 'created_from', 'updated_by', 'updated_from','course_id', 'course_reg_id', 'mod_id', 'mod_res_id', 'courseactivity_id', 'component_id', 'course_complete_id', 'res_complete_id'], 'string', 'max' => 50],
            [['is_deleted'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => '作业结果ID',
            'homework_id' => '作业ID',
            'user_id' => '用户ID',
            'company_id' => '企业ID',
            'homework_result' => '作业答案',
            'description' => '描述',
            'course_id' => '课程ID',
            'course_reg_id' => '课程注册ID',
            'mod_id' => '模块ID',
            'mod_res_id' => '模块资源ID',
            'courseactivity_id' => '活动ID',
            'component_id' => '组件ID',
            'course_complete_id' => '课程完成ID',
            'res_complete_id' => '资源完成ID',
            'course_attempt_number'=>'课程尝试次数',
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
