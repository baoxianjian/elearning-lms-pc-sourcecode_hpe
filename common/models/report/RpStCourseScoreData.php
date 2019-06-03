<?php

namespace common\models\report;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%rp_st_course_score_data}}".
 *
 * @property string $kid
 * @property integer $YEAR
 * @property integer $MONTH
 * @property string $TIME
 * @property string $domain_id
 * @property string $course_id
 * @property string $user_id
 * @property integer $reg_time
 * @property integer $comp_time
 * @property double $score
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class RpStCourseScoreData extends  BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rp_st_course_score_data}}';
    }

    
}
