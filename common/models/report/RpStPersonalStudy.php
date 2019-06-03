<?php

namespace common\models\report;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%rp_st_personal_study}}".
 *
 * @property string $kid
 * @property string $op_time
 * @property string $domain_id
 * @property string $user_id
 * @property string $duration
 * @property integer $reg_num
 * @property integer $com_num
 * @property integer $certification
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class RpStPersonalStudy extends BaseActiveRecord
{
	
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rp_st_personal_study}}';
    }

    
}
