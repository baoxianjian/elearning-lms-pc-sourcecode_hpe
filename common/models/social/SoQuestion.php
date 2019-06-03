<?php

namespace common\models\social;

use common\models\framework\FwTag;
use common\models\framework\FwTagCategory;
use common\models\framework\FwTagReference;
use common\models\learning\LnCourse;
use Yii;
use common\base\BaseActiveRecord;
use common\models\framework\FwUser;

/**
 * This is the model class for table "{{%so_question}}".
 *
 * @property string $kid
 * @property string $user_id
 * @property string $company_id
 * @property string $obj_id
 * @property string $title
 * @property string $question_content
 * @property string $browse_num
 * @property string $attention_num
 * @property string $collect_num
 * @property string $answer_num
 * @property string $praise_num
 * @property string $share_num
 * @property string $question_type
 * @property string $is_resolved
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class SoQuestion extends BaseActiveRecord
{
    public $tags;

    const QUESTION_TYPE_NORMAL = "0";
    const QUESTION_TYPE_COURSE = "1";

    const RESOLVED_YES = '1';
    const RESOLVED_NO = '0';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%so_question}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'title', 'question_content'], 'required'],
            [['question_content'], 'string'],
            [['browse_num', 'attention_num', 'collect_num', 'answer_num', 'praise_num', 'share_num'], 'number'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'user_id', 'obj_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 200],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['question_type'], 'in', 'range' => [self::QUESTION_TYPE_NORMAL, self::QUESTION_TYPE_COURSE]],
            [['question_type'], 'default', 'value' => self::QUESTION_TYPE_NORMAL],

            [['is_resolved'], 'string'],
            [['is_resolved'], 'default', 'value' => '0'],
            [['is_resolved'], 'string', 'max' => 1],

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
            'user_id' => Yii::t('frontend', 'user_id'),
            'title' => Yii::t('frontend', 'question_title'),
            'question_content' => Yii::t('frontend', 'question_content'),
            'browse_num' => Yii::t('frontend', 'browse_num'),
            'attention_num' => Yii::t('frontend', 'attention_num'),
            'collect_num' => Yii::t('frontend', 'collect_num'),
            'obj_id' => Yii::t('frontend', 'obj_id'),
            'question_type' => Yii::t('frontend', 'question_type'),
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourse()
    {
        return $this->hasOne(LnCourse::className(), ['kid' => 'obj_id'])
            ->onCondition([LnCourse::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

}
