<?php

namespace common\models\report;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%rp_st_online_course_comp}}".
 *
 * @property string $kid
 * @property string $op_time
 * @property string $display_id
 * @property string $display_val
 * @property string $type
 * @property string $course_id
 * @property integer $total_user_num
 * @property integer $reg_num
 * @property integer $com_num
 * @property string $com_num_rate
 * @property string $score
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class RpStOnlineCourseComp extends BaseActiveRecord
{
	/**1.汇报经理,2.域,3.企业*/
	const TYPE_REPORTING_MANAGER = "1";
	const TYPE_DOMAIN = "2";
	const TYPE_COMPANY = "3";
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rp_st_online_course_comp}}';
    }

    
}
