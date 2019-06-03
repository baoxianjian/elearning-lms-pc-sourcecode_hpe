<?php

namespace common\models\report;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%rp_st_analysis_data}}".
 *
 * @property string $kid
 * @property integer $YEAR
 * @property integer $MONTH
 * @property string $domain_id
 * @property string $reg_course
 * @property string $comp_course
 * @property string $learning_time
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class RpStAnalysisData extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rp_st_analysis_data}}';
    }

    
}
