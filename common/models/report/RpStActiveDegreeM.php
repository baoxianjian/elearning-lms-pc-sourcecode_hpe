<?php

namespace common\models\report;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%rp_st_active_degree_m}}".
 *
 * @property string $kid
 * @property string $op_time
 * @property string $domain_id
 * @property string $type
 * @property integer $login_user_num
 * @property string $login_user_num_rate
 * @property integer $login_num
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
class RpStActiveDegreeM extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rp_st_active_degree_m}}';
    }

    
}
