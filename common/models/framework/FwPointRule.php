<?php
/**
 * 积分规则模型
 * author: 包显建
 * date: 2016/2/26
 * time: 10:20
 */
namespace common\models\framework;


use Yii;
use \common\base\BaseActiveRecord;

/**
 * This is the model class for table "eln_fw_point_rule".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $point_type
 * @property string $point_code
 * @property string $point_name
 * @property string $point_op
 * @property string $cycle_range
 * @property string $standard_value
 * @property string $accumulate_increment
 * @property integer $max_increment
 * @property string $is_template
 * @property integer $status
 * @property string $description
 * @property string $i18n_flag
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
 *
 * @property FwUserPointDetail[] $fwUserPointDetails
 */
class FwPointRule extends BaseActiveRecord
{
    //$a=array(0=>'不限制',1=>'一次性',2=>'每天',3=>'每周',4=>'每月',5=>'每年');
    const CYCLE_RANGE_NOT_LIMIT = 0;
    const CYCLE_RANGE_ONE_TIME = 1;
    const CYCLE_RANGE_EVERY_DAY = 2; 
    const CYCLE_RANGE_EVERY_WEEK = 3;
    const CYCLE_RANGE_EVERY_MONTH = 4;
    const CYCLE_RANGE_EVERY_YEAR = 5;

    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'eln_fw_point_rule';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'point_type', 'point_code', 'point_name', 'standard_value'], 'required', 'on' => 'manage'],
            [['standard_value', 'accumulate_increment'], 'number'],
            [['max_increment', 'version', 'created_at', 'updated_at'], 'integer'],
            [['description'], 'string'],
            [['kid', 'company_id', 'point_type', 'point_code', 'point_name', 'created_by', 
                'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip','i18n_flag'], 'string', 'max' => 50],
            [['cycle_range', 'is_template','status', 'is_deleted'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' => Yii::t('common', 'kid'),
            'company_id' => Yii::t('common', 'company_id'),
            'point_type' => Yii::t('common', 'point_type'),
            'point_code' => Yii::t('common', 'point_code'),
            'point_name' => Yii::t('common', 'point_name'),
            'cycle_range' => Yii::t('common', 'cycle_range'),
            'standard_value' => Yii::t('common', 'standard_value'),
            'accumulate_increment' => Yii::t('common', 'accumulate_increment'),
            'max_increment' => Yii::t('common', 'max_increment'),
            'is_template' => Yii::t('common', 'is_template'),
            'status' => Yii::t('common', 'status'),
            'description' => Yii::t('common', 'description'),
            'i18n_flag' => Yii::t('common', 'i18n_flag'),
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'created_ip' => Yii::t('common', 'created_ip'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'updated_ip' => Yii::t('common', 'updated_ip'),
            'is_deleted' => Yii::t('common', 'is_deleted'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwUserPointDetails()
    {
        return $this->hasMany(FwUserPointDetail::className(), ['point_rule_id' => 'kid'])
            ->onCondition([FwUserPointDetail::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * 得到循环周期列表或显示值
     * @param null $id CycleRange值
     */
    public function getCycleRanges($id=null)
    {
        $a=array(
            0=>Yii::t('common','not_limit'),
            1=>Yii::t('common','one_time'),
            2=>Yii::t('common','every_day'),
            3=>Yii::t('common','every_week'),
            4=>Yii::t('common','every_month'),
            5=>Yii::t('common','every_year')
        );
        if($id===null)
        {
            return $a;
        }
        return $a[$id]; 
    }

    /**
     * 得到状态列表或显示值
     * @param null $id status值
     * @return array
     */
    public function getStatuses($id=null)
    {
       $a=array(
           0=>Yii::t('common','status_temp'),
           1=>Yii::t('common','status_normal'),
           2=>Yii::t('common','status_stop')
       );
       if($id===null)
       {
            return $a;
       }
        return $a[$id]; 
    }

    /**
     * 根据多国语言对应的名字
     * @return string
     */
    public function getI18nName()
    {
        if (!empty($this->i18n_flag)) {
            return Yii::t('data',$this->i18n_flag);
        }
        else {
            $this->point_name;
        }
    }
}
