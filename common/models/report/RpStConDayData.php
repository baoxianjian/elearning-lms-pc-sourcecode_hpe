<?php

namespace common\models\report;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%rp_st_con_day_data}}".
 *
 * @property string $kid
 * @property integer $YEAR
 * @property integer $MONTH
 * @property string $TIME
 * @property string $domain_id
 * @property integer $log_user_num
 * @property double $log_user_rate
 * @property double $acc_study_time
 * @property string $max_acc_comment_course
 * @property string $max_acc_study_course
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class RpStConDayData extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rp_st_con_day_data}}';
    }

   
}
