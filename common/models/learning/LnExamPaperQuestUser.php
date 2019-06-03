<?php

namespace common\models\learning;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ln_exam_paper_quest_user}}".
 *
 * @property string $kid
 * @property string $examination_paper_user_id
 * @property string $examination_question_user_id
 * @property string $default_score
 * @property string $relation_type
 * @property integer $sequence_number
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
 * @property LnExaminationPaperUser $examinationPaperUser
 * @property LnExamQuestionUser $examQuestionUser
 */
class LnExamPaperQuestUser extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_exam_paper_quest_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['examination_paper_user_id', 'sequence_number'], 'required'],
            [['default_score'], 'number'],
            [['sequence_number', 'start_at', 'end_at', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'examination_paper_user_id', 'examination_question_user_id', 'created_by', 'created_from', 'updated_by', 'updated_from'], 'string', 'max' => 50],
            [['relation_type', 'status', 'is_deleted'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => '个人试卷试题关系ID',
            'examination_paper_user_id' => '个人试卷ID',
            'examination_question_user_id' => '个人试题ID',
            'default_score' => '默认分',
            'relation_type' => '关系分类；0：试题，1：分页符号',
            'sequence_number' => '排序号',
            'status' => '状态；0：临时，1：正常，2：停用',
            'start_at' => '关系生效时间',
            'end_at' => '关系失效时间，如果为空，表示无截止时间限制',
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
    public function getExaminationPaperUser()
    {
        return $this->hasOne(LnExaminationPaperUser::className(), ['kid' => 'examination_paper_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExaminationQuestionUser()
    {
        return $this->hasOne(LnExamQuestionUser::className(), ['kid' => 'examination_question_user_id']);
    }
}
