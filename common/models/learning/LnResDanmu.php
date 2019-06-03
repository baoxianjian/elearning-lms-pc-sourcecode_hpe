<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_res_danmu}}".
 *
 * @property string $kid
 * @property string $course_id
 * @property string $user_id
 * @property string $mod_res_id
 * @property string $courseware_id
 * @property string $courseactivity_id
 * @property string $resource_type
 * @property string $danmu_text
 * @property string $danmu_color
 * @property string $danmu_size
 * @property string $danmu_position
 * @property integer $danmu_time
 * @property string $danmu_string
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
 *
 * @property LnModRes $lnModRes
 */
class LnResDanmu extends BaseActiveRecord
{
    const RESOURCE_TYPE_COURSEWARE = '0';
    const RESOURCE_TYPE_COURSEACTIVITY = '1';

    const DANMU_SIZE_SMALL = '0';
    const DANMU_SIZE_BIG = '1';

    const DANMU_POSITION_ROLL = '0';
    const DANMU_POSITION_TOP = '1';
    const DANMU_POSITION_BOTTOM = '2';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_res_danmu}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['course_id', 'user_id', 'mod_res_id', 'danmu_text'], 'required'],
            [['danmu_time', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'course_id', 'user_id', 'mod_res_id', 'courseware_id', 'courseactivity_id', 'danmu_color',
                'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['danmu_text'], 'string', 'max' => 500],
            [['danmu_string'], 'string', 'max' => 600],

            [['resource_type'], 'string', 'max' => 1],
            [['resource_type'], 'in', 'range' => [self::RESOURCE_TYPE_COURSEWARE, self::RESOURCE_TYPE_COURSEACTIVITY]],
            [['resource_type'], 'default', 'value'=> self::RESOURCE_TYPE_COURSEWARE],


            [['danmu_size'], 'string', 'max' => 1],
            [['danmu_size'], 'in', 'range' => [self::DANMU_SIZE_SMALL, self::DANMU_SIZE_BIG]],
            [['danmu_size'], 'default', 'value'=> self::DANMU_SIZE_SMALL],


            [['danmu_position'], 'string', 'max' => 1],
            [['danmu_position'], 'in', 'range' => [self::DANMU_POSITION_ROLL, self::DANMU_POSITION_TOP, self::DANMU_POSITION_BOTTOM]],
            [['danmu_position'], 'default', 'value'=> self::DANMU_POSITION_ROLL],

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
            'kid' => Yii::t('common', 'kid'),
            'course_id' => Yii::t('common', 'Course ID'),
            'user_id' => Yii::t('common', 'User ID'),
            'mod_res_id' => Yii::t('common', 'Mod Res ID'),
            'courseware_id' => Yii::t('common', 'Courseware ID'),
            'courseactivity_id' => Yii::t('common', 'Courseactivity ID'),
            'resource_type' => Yii::t('common', 'Resource Type'),
            'danmu_text' => Yii::t('common', 'Danmu Text'),
            'danmu_color' => Yii::t('common', 'Danmu Color'),
            'danmu_size' => Yii::t('common', 'Danmu Size'),
            'danmu_position' => Yii::t('common', 'Danmu Position'),
            'danmu_time' => Yii::t('common', 'Danmu Time'),
            'danmu_string' => Yii::t('common', 'Danmu String'),
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
    public function getLnModRes()
    {
        return $this->hasOne(LnModRes::className(), ['kid' => 'mod_res_id'])
            ->onCondition([LnModRes::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
