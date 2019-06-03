<?php

namespace common\models\message;

use common\models\learning\LnCourse;
use common\models\social\SoQuestion;
use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ms_timeline}}".
 *
 * @property string $kid
 * @property string $owner_id
 * @property string $sender_id
 * @property string $object_id
 * @property string $object_type
 * @property string $title
 * @property string $content
 * @property integer $start_at
 * @property integer $end_at
 * @property integer $duration
 * @property string $complete_status
 * @property string $from_type
 * @property string $timeline_type
 * @property string $button_type
 * @property string $type_code
 * @property string $url
 * @property string $attach_original_filename
 * @property string $attach_url
 * @property string $image_url
 * @property integer $is_stick
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class MsTimeline extends BaseActiveRecord
{
    /**
     * 课程
     */
    const OBJECT_TYPE_COURSE = '0';
    /**
     * 考试
     */
    const OBJECT_TYPE_EXAM = '1';
    /**
     * 调查
     */
    const OBJECT_TYPE_SURVEY = '2';
    /**
     * 提问
     */
    const OBJECT_TYPE_QUESTION = '3';
    /**
     * 培训计划
     */
    const OBJECT_TYPE_TRAIN = '4';
    /**
     * 分享
     */
    const OBJECT_TYPE_SHARE = '5';
    /**
     * 记录（网页）
     */
    const OBJECT_TYPE_RECORD_WEB = '6';
    /**
     * 记录（事件）
     */
    const OBJECT_TYPE_RECORD_EVENT = '7';
    /**
     * 记录（书籍）
     */
    const OBJECT_TYPE_RECORD_BOOK = '8';
    /**
     * 记录（经验）
     */
    const OBJECT_TYPE_RECORD_EXP = '9';

    /**
     * 推送
     */
    const FROM_TYPE_PUSH = '0';
    /**
     * 自建
     */
    const FROM_TYPE_SELF = '1';

    /**
     * 需完成的课程
     */
    const TYPE_COURSE = "01";
    /**
     * 需完成的考试
     */
    const TYPE_EXAM = "02";
    /**
     * 需完成的调查
     */
    const TYPE_SURVEY = "03";

    /**
     * @我的问题
     */
    const TYPE_AT_QUESTION = "11";
    /**
     * 我提的问题
     */
    const TYPE_SUB_QUESTION = "12";
    /**
     * 我关注的问题
     */
    const TYPE_ATTENTION_QUESTION = "13";
    /**
     * 新发布的在线课程
     */
    const TYPE_NEW_ONLINE_COURSE = "21";
    /**
     * 新更新的专题内容项
     */
//    const TYPE_NEW_SUBJECT = "22";
    /**
     * 新发布的面授课程
     */
    const TYPE_NEW_FACETOFACE_COURSE = "22";
    /**
     * 新发布的学习项目
     */
//    const TYPE_NEW_STUDY_PROJECT = "24";
    /**
     * 关注对象的分享
     */
    const TYPE_ATTENTION_USER = "31";
    /**
     * @我的分享
     */
    const TYPE_AT_SHARE = "32";

    const TIMELINE_TYPE_TODO = '0';
    const TIMELINE_TYPE_QA = '1';
    const TIMELINE_TYPE_NEWS = '2';
    const TIMELINE_TYPE_SOCIAL = '3';

    const COMPLETE_STATUS_UNDONE = '0';
    const COMPLETE_STATUS_DONE = '1';

    // 查看
    const BUTTON_TYPE_LOOK = '0';
    // 去完成
    const BUTTON_TYPE_GO = '1';
    // 继续完成
    const BUTTON_TYPE_CONTINUE = '2';
    // 待开课
    const BUTTON_TYPE_NO_START = '3';
    // 进行中
    const BUTTON_TYPE_PROCESS = '4';

    public $sender;
    public $qa_sender;
    public $course_type;
    public $course_end_at;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_timeline}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['owner_id', 'sender_id', 'object_id', 'object_type', 'title'], 'required'],
            [['start_at', 'end_at', 'duration', 'created_at', 'updated_at', 'is_stick'], 'integer'],
            [['kid', 'owner_id', 'sender_id', 'object_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['title', 'image_url'], 'string', 'max' => 500],
            [['content'], 'string', 'max' => 5000],
            [['created_from', 'updated_from'], 'string', 'max' => 50],

            [['button_type'], 'string', 'max' => 10],
            [['button_type'], 'default', 'value' => self::BUTTON_TYPE_LOOK],

            [['complete_status'], 'string', 'max' => 1],
            [['complete_status'], 'default', 'value' => self::COMPLETE_STATUS_UNDONE],
            [['complete_status'], 'in', 'range' => [self::COMPLETE_STATUS_UNDONE, self::COMPLETE_STATUS_DONE]],

            [['timeline_type'], 'string', 'max' => 1],
            [['timeline_type'], 'default', 'value' => self::TIMELINE_TYPE_TODO],
            [['timeline_type'], 'in', 'range' => [self::TIMELINE_TYPE_TODO, self::TIMELINE_TYPE_QA, self::TIMELINE_TYPE_NEWS, self::TIMELINE_TYPE_SOCIAL]],

            [['object_type'], 'string', 'max' => 1],
            [['object_type'], 'in', 'range' => [self::OBJECT_TYPE_COURSE, self::OBJECT_TYPE_EXAM, self::OBJECT_TYPE_QUESTION,
                self::OBJECT_TYPE_SHARE, self::OBJECT_TYPE_SURVEY, self::OBJECT_TYPE_TRAIN,
                self::OBJECT_TYPE_RECORD_WEB, self::OBJECT_TYPE_RECORD_EVENT, self::OBJECT_TYPE_RECORD_BOOK,
                self::OBJECT_TYPE_RECORD_EXP]],

            [['from_type'], 'string', 'max' => 1],
            [['from_type'], 'in', 'range' => [self::FROM_TYPE_PUSH, self::FROM_TYPE_SELF]],

            [['type_code'], 'string', 'max' => 50],
            [['type_code'], 'in', 'range' => [
                self::TYPE_COURSE, self::TYPE_EXAM, self::TYPE_SURVEY,
                self::TYPE_AT_QUESTION, self::TYPE_SUB_QUESTION, self::TYPE_ATTENTION_QUESTION,
                self::TYPE_NEW_ONLINE_COURSE, self::TYPE_NEW_FACETOFACE_COURSE, self::TYPE_ATTENTION_USER,
                self::TYPE_AT_SHARE]],

            [['is_stick'], 'default', 'value' => 0],

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
            'owner_id' => Yii::t('frontend', 'owner_id'),
            'sender_id' => Yii::t('frontend', 'sender_id'),
            'object_id' => Yii::t('frontend', 'object_id'),
            'object_type' => Yii::t('frontend', 'object_type'),
            'title' => Yii::t('frontend', 'title'),
            'content' => Yii::t('frontend', 'content'),
            'start_at' => Yii::t('frontend', 'start_at'),
            'end_at' => Yii::t('frontend', 'end_at'),
            'duration' => Yii::t('frontend', 'duration'),
            'complete_status' => Yii::t('frontend', 'complete_status'),
            'from_type' => Yii::t('frontend', 'from_type'),
            'timeline_type' => Yii::t('frontend', 'timeline_type'),
            'button_type' => Yii::t('frontend', 'button_type'),
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

    public function getCourseTheme()
    {
        $course = LnCourse::findOne($this->object_id);
        return $course->theme_url;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSoQuestion()
    {
        return $this->hasOne(SoQuestion::className(), ['kid' => 'object_id'])
            ->onCondition([SoQuestion::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourse()
    {
        return $this->hasOne(LnCourse::className(), ['kid' => 'object_id'])
            ->onCondition([LnCourse::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    public function getButtonText()
    {
        if ($this->button_type === self::BUTTON_TYPE_LOOK) {
            return Yii::t('common','view_button');
        } elseif ($this->button_type === self::BUTTON_TYPE_GO) {
            return Yii::t('frontend','to_complete');
        } elseif ($this->button_type === self::BUTTON_TYPE_CONTINUE) {
            return Yii::t('frontend','continue_complete');
        } elseif ($this->button_type === self::BUTTON_TYPE_NO_START) {
            return Yii::t('frontend','waiting_for_class');
        } elseif ($this->button_type === self::BUTTON_TYPE_PROCESS) {
            return Yii::t('frontend','complete_status_doing');
        }
    }
}
