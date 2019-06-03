<?php

namespace common\models\message;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%ms_select_temp}}".
 *
 * @property string $kid
 * @property string $mission_id
 * @property string $select_id
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class MsSelectTemp extends  BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_select_temp}}';
    }

   
}
