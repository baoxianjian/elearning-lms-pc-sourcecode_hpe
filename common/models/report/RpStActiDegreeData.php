<?php

namespace common\models\report;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%rp_st_acti_degree_data}}".
 *
 * @property string $kid
 * @property integer $YEAR
 * @property integer $MONTH
 * @property string $domain_id
 * @property string $login_user_num
 * @property string $active_user
 * @property string $login_number
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class RpStActiDegreeData extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rp_st_acti_degree_data}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['kid', 'YEAR', 'MONTH', 'created_by', 'created_at'], 'required'],
            [['YEAR', 'MONTH', 'login_user_num', 'active_user', 'login_number', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'domain_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['is_deleted'], 'string', 'max' => 1],
            [['created_from','updated_from'], 'string', 'max' => 50],
        ];
    }

   
}
