<?php

namespace common\models\report;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%rp_st_user_con_data}}".
 *
 * @property string $kid
 * @property string $domain_id
 * @property string $user_id
 * @property double $acc_study_time
 * @property integer $reg_course_num
 * @property integer $comp_course_num
 * @property double $obliga_course_comp_rate
 * @property double $obliga_course_score
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class RpStUserConData extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rp_st_user_con_data}}';
    }

    
}
