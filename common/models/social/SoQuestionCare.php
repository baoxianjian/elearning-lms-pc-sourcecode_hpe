<?php

namespace common\models\social;

use common\models\message\MsTimeline;
use common\services\message\TimelineService;
use Yii;
use common\base\BaseActiveRecord;
use common\models\framework\FwUser;
use common\models\social\SoQuestion;
use yii\db\Expression;

/**
 * This is the model class for table "{{%so_question_care}}".
 *
 * @property string $kid
 * @property string $question_id
 * @property string $user_id
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
 */
class SoQuestionCare extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%so_question_care}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['question_id', 'user_id'], 'required'],
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'question_id', 'user_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

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
            'kid' => Yii::t('frontend', 'kid'),
            'question_id' => Yii::t('frontend', 'question_id'),
            'user_id' => Yii::t('frontend', 'user_id'),
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwUser()
    {
        return $this->hasOne(FwUser::className(), ['kid' => 'user_id'])
            ->onCondition([FwUser::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    public function saveAndUpdateQuestion()
    {
        if ($this->validate()) {
            $condition = [
                'user_id' => $this->user_id,
                'question_id' => $this->question_id,
            ];
            $model = $this->findOne($condition);

            if ($model != null) {
                return true;
            } else if ($this->save()) {
                SoQuestion::addFieldNumber($this->question_id, 'attention_num');

                $timelineService = new TimelineService();
                $timelineService->pushByCareQuestion($this->user_id, $this->question_id);
                return true;
            }
        }
        return false;
    }
}
