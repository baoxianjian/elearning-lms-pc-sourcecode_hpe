<?php

namespace common\models\message;

use Yii;
use common\base\BaseActiveRecord;
use common\models\framework\FwUser;

/**
 * This is the model class for table "{{%ms_message}}".
 *
 * @property string $kid
 * @property string $object_id
 * @property string $object_type
 * @property string $sender_id
 * @property string $title
 * @property string $content
 * @property integer $end_time
 * @property string $msg_status
 * @property string $message_type
 * @property string $data_from
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class MsMessage extends BaseActiveRecord
{
    // 系统
    const DATA_FROM_SYSTEM = '0';
    // 个人
    const DATA_FROM_PERSON = '1';
    // 组织推送
    const DATA_FROM_ORG_PUSH = '2';
    // 经理推送
    const DATA_FROM_MANAGER_PUSH = '3';
    // 讲师推送
    const DATA_FROM_TEACHER_PUSH = '4';
    // @我的
    const DATA_FROM_AT = '5';
    // 回复我的
    const DATA_FROM_ANSWER = '6';
    // 关注问题
    const DATA_FROM_CARE_QUESTION = '7';
    // 评论回复
    const DATA_FROM_COMMENT_ANSWER = '8';
    // 域推送
    const DATA_FROM_DOMAIN_PUSH = '9';
    // 岗位推送
    const DATA_FROM_POS_PUSH = '10';
    // 个人推送
    const DATA_FROM_PERSON_PUSH = '11';

    const OBJECT_TYPE_COURSE = '0';
    const OBJECT_TYPE_QUESTION = '1';
    const OBJECT_TYPE_RECORD = '2';
    const OBJECT_TYPE_PERSON = '3';
    const OBJECT_TYPE_EXAM = '4';
    const OBJECT_TYPE_SURVEY = '5';


    const MESSAGE_TYPE_TODO = "0";
    const MESSAGE_TYPE_QA = "1";
    const MESSAGE_TYPE_NEWS = "2";
    const MESSAGE_TYPE_SOCIAL = "3";


    const TYPE_COURSE = "0";
    const TYPE_TODO = "0";
    const TYPE_QA = "1";
    const TYPE_NEWS = "2";
    const TYPE_SOCIAL = "3";

    const SUB_COURSE_PUSH = "course_push";
    const SUB_COURSE_REG = "course_reg";
    const SUB_EXAM_PUSH = "exam_push";
    const SUB_SUB_QUESTION = "sub_question";
    const SUB_AT_QUESTION = "at_question";
    const SUB_ATTENTION_QUESTION = "attention_question";
    const SUB_NEW_COURSE = "new_course_push";
    const SUB_TRAIN_PLAN = "train_plan";
    const SUB_SUB_SUBJECT = "sub_subject";
    const SUB_ATTENTION_USER = "attention_user";
    const SUB_AT_SHARE = "at_share";

    const STATUS_DUE = "DueDate";
    const STATUS_NEARLY = "NearlyDate";

    public $receive_status;
    public $sender;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_message}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'object_type', 'sender_id', 'title'], 'required'],
            [['end_time', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'object_id', 'msg_status', 'message_type', 'created_by', 'updated_by', 'data_from'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 500],
            [['content'], 'string'],
            [['object_type'], 'string', 'max' => 1],
            [['object_type'], 'in', 'range' => [self::OBJECT_TYPE_COURSE, self::OBJECT_TYPE_EXAM, self::OBJECT_TYPE_SURVEY
                , self::OBJECT_TYPE_QUESTION, self::OBJECT_TYPE_RECORD, self::OBJECT_TYPE_PERSON]],
            [['created_from', 'updated_from'], 'string', 'max' => 50],

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
            'object_id' => Yii::t('frontend', 'object_id'),
            'sender_id' => Yii::t('frontend', 'sender_id'),
            'title' => Yii::t('frontend', 'title'),
            'content' => Yii::t('frontend', 'content'),
            'end_time' => Yii::t('frontend', 'end_time'),
            'msg_status' => Yii::t('frontend', 'msg_status'),
            'message_type' => Yii::t('frontend', 'message_type'),
            'data_from' => Yii::t('frontend', 'data_from'),
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
        return $this->hasOne(FwUser::className(), ['kid' => 'created_by'])
            ->onCondition([FwUser::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    public function getFromText()
    {
        if ($this->data_from === self::DATA_FROM_ORG_PUSH) {
            return '组织推送';
        } elseif ($this->data_from === self::DATA_FROM_MANAGER_PUSH) {
            return '经理推送';
        } elseif ($this->data_from === self::DATA_FROM_TEACHER_PUSH) {
            return '讲师推送';
        } elseif ($this->data_from === self::DATA_FROM_SYSTEM) {
            return '系统';
        } elseif ($this->data_from === self::DATA_FROM_AT) {
            return '@我的';
        } elseif ($this->data_from === self::DATA_FROM_ANSWER) {
            return '回复我的';
        } elseif ($this->data_from === self::DATA_FROM_COMMENT_ANSWER) {
            return '回复我的';
//            return '评论回复';
        } elseif ($this->data_from === self::DATA_FROM_CARE_QUESTION) {
            return '关注问题';
        } elseif ($this->data_from === self::DATA_FROM_PERSON) {
            return $this->sender;
        } elseif ($this->data_from === self::DATA_FROM_DOMAIN_PUSH) {
            return '域推送';
        } elseif ($this->data_from === self::DATA_FROM_POS_PUSH) {
            return '岗位推送';
        } elseif ($this->data_from === self::DATA_FROM_PERSON_PUSH) {
            return '个人推送';
        }
    }
}
