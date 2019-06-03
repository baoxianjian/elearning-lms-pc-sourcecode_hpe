<?php
/**
 * 积分规则权重模型
 * author: 包显建
 * date: 2016/3/10
 * time: 18:20
 */


namespace common\models\framework;

use Yii;
use \common\base\BaseActiveRecord;



/**
 * This is the model class for table "eln_fw_point_rule_scale".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $is_template
 * @property string $scene_code
 * @property string $scene_name
 * @property string $scale_value
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $created_ip
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $updated_ip
 * @property string $is_deleted
 */
class FwPointRuleScale extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fw_point_rule_scale}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id'], 'required'],
            [['scale_value'], 'number'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'company_id', 'scene_code', 'scene_name', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['is_template', 'is_deleted'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => 'Kid',
            'company_id' => 'Company ID',
            'is_template' => 'Is Template',
            'scene_code' => 'Scene Code',
            'scene_name' => 'Scene Name',
            'scale_value' => 'Scale Value',
            'version' => 'Version',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'created_from' => 'Created From',
            'created_ip' => 'Created Ip',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
            'updated_from' => 'Updated From',
            'updated_ip' => 'Updated Ip',
            'is_deleted' => 'Is Deleted',
        ];
    }




}
