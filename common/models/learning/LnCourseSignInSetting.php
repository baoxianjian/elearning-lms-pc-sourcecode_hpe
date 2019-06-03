<?php
/**
* @author baoxianjian 
* @date 15:21 2016/4/29
*/

namespace common\models\learning;

use Yii;
use \common\base\BaseActiveRecord;

/**
 * This is the model class for table "eln_ln_course_sign_in_setting".
 *
 * @property string $kid
 * @property string $course_id
 * @property string $sign_date
 * @property string $title
 * @property string $start_at_str
 * @property integer $start_at
 * @property string $end_at_str
 * @property integer $end_at
 * @property string $is_all_day
 * @property string $qr_code
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
 * @property LnCourseSignIn[] $lnCourseSignIns
 */
class LnCourseSignInSetting extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_course_sign_in_setting}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'course_id', 'sign_date', 'title', 'qr_code'], 'required'],
            [['sign_date'], 'safe'],
            [['start_at', 'end_at', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'course_id', 'qr_code', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['title', 'start_at_str', 'end_at_str'], 'string', 'max' => 20],
            [['is_all_day', 'is_deleted'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => 'Kid',
            'course_id' => 'Course ID',
            'sign_date' => 'Sign Date',
            'title' => 'Title',
            'start_at_str' => 'Start At Str',
            'start_at' => 'Start At',
            'end_at_str' => 'End At Str',
            'end_at' => 'End At',
            'is_all_day' => 'Is All Day',
            'qr_code' => 'Qr Code',
            'version' => 'Version',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'created_from' => 'Created From',
            'created_ip' => 'Created Ip',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
            'updated_from' => 'Updated From',
            'updated_ip' => 'Updated Ip',
            'is_deleted' => 'Is Deleted',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourseSignIns()
    {
        return $this->hasMany(LnCourseSignIn::className(), ['sign_in_setting_id' => 'kid']);
    }
}
