<?php

namespace common\models\message;

use common\models\learning\LnCourse;
use common\models\learning\LnExamination;
use common\models\learning\LnInvestigation;
use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ms_task_item}}".
 *
 * @property string $kid
 * @property string $task_id
 * @property string $item_id
 * @property string $item_title
 * @property string $item_type
 * @property integer $plan_complete_at
 * @property integer $sequence_number
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class MsTaskItem extends BaseActiveRecord
{
    /**
     * 课程
     */
    const ITEM_TYPE_COURSE = '0';
    /**
     * 考试
     */
    const ITEM_TYPE_EXAM = '1';
    /**
     * 调查问卷
     */
    const ITEM_TYPE_SURVEY = '2';

    public $item_name;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_task_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'item_id', 'item_type', 'sequence_number'], 'required'],
            [['sequence_number', 'created_at', 'updated_at', 'plan_complete_at'], 'integer'],
            [['kid', 'task_id', 'item_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['item_title'], 'string', 'max' => 500],
            [['created_from', 'updated_from'], 'string', 'max' => 50],

            [['item_type'], 'string', 'max' => 1],
            [['item_type'], 'default', 'value' => self::ITEM_TYPE_COURSE],
            [['item_type'], 'in', 'range' => [self::ITEM_TYPE_COURSE, self::ITEM_TYPE_EXAM, self::ITEM_TYPE_SURVEY]],

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
            'kid' => Yii::t('frontend', 'Kid'),
            'task_id' => Yii::t('frontend', 'Task ID'),
            'item_id' => Yii::t('frontend', 'Item ID'),
            'item_title' => Yii::t('frontend', 'item_title'),
            'item_type' => Yii::t('frontend', 'Item Type'),
            'plan_complete_at' => Yii::t('common', 'plan_complete_at'),
            'sequence_number' => Yii::t('frontend', 'Sequence Number'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourse()
    {
        return $this->hasOne(LnCourse::className(), ['kid' => 'item_id'])
            ->onCondition([LnCourse::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnExamination()
    {
        return $this->hasOne(LnExamination::className(), ['kid' => 'item_id'])
            ->onCondition([LnExamination::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnInvestigation()
    {
        return $this->hasOne(LnInvestigation::className(), ['kid' => 'item_id'])
            ->onCondition([LnInvestigation::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
