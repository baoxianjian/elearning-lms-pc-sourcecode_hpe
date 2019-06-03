<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_course_recommend}}".
 *
 * @property string $kid
 * @property string $course_id
 * @property string $user_id
 * @property string $recommend_ratio
 * @property integer $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $created_ip
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $updated_ip
 * @property string $is_deleted
 */
class LnCourseRecommend extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_course_recommend}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['course_id', 'user_id'], 'required'],
            [['recommend_ratio'], 'number'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'course_id', 'user_id', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['is_deleted'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => 'kid',
            'course_id' => 'course_id',
            'user_id' => 'user_id',
            'recommend_ratio' => 'recommend_ratio',
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'created_ip' => 'created_ip',
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'updated_ip' => 'updated_ip',
            'is_deleted' => Yii::t('common', 'is_deleted'),
        ];
    }
}
