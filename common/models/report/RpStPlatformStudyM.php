<?php

namespace common\models\report;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%rp_st_platform_study_m}}".
 *
 * @property string $kid
 * @property string $op_time
 * @property integer $year
 * @property integer $month
 * @property string $domain_id
 * @property string $login_num
 * @property string $login_num_rate
 * @property string $reg_num
 * @property string $reg_num_rate
 * @property string $com_num
 * @property string $com_num_rate
 * @property string $duration
 * @property string $duration_rate
 * @property string $certif_num
 * @property string $certif_num_rate
 * @property string $total_user_num
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class RpStPlatformStudyM extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rp_st_platform_study_m}}';
    }

    
}
