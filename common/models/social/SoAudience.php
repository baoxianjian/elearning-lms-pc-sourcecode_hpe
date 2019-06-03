<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2016/3/14
 * Time: 11:02
 */
namespace common\models\social;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%so_audience}}".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $owner_id
 * @property string $category_id
 * @property string $audience_code
 * @property string $audience_name
 * @property string $description
 * @property string $source_id
 * @property string $audience_type
 * @property string $status
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
 * @property SoAudienceMember[] $soAudienceMembers
 */
class SoAudience extends BaseActiveRecord
{

    const AUDIEBCE_TYPE_MEMBER = '0'; //列表
    const AUDIEBCE_TYPE_COURSE = '1'; //课程
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%so_audience}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'owner_id', 'category_id', 'audience_code', 'audience_name'], 'required'],
            [['description'], 'string'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['company_id', 'owner_id', 'category_id', 'audience_code', 'source_id', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['audience_name'], 'string', 'max' => 200],
            [['audience_type', 'status', 'is_deleted'], 'string', 'max' => 1]
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
            'owner_id' => Yii::t('common', 'owner_id'),
            'category_id' => Yii::t('common', 'category_id'),
            'audience_code' => Yii::t('common', 'audience_code'),
            'audience_name' => Yii::t('common', 'audience_name'),
            'description' => Yii::t('common', 'description'),
            'source_id' => Yii::t('common', 'source_id'),
            'audience_type' => Yii::t('common', 'audience_type'),
            'status' => Yii::t('common', 'status'),
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
    public function getSoAudienceMembers()
    {
        return $this->hasMany(SoAudienceMember::className(), ['audience_id' => 'kid']);
    }

    /*
     * 设置受众编号
     * 规则：日期+sprintf("%03d", $count);
     * @param string $courseId
     * @return string
     */
    public function setAudienceCode($kid=""){
        if (!empty($kid)){
            $info = SoAudience::findOne($kid);
            return $info->audience_code;
        }
        $start_at = strtotime(date('Y-m-d'));
        $end_at = $start_at+86399;
        $count = $this->find()->where("created_at>".$start_at)->andWhere("created_at<".$end_at)->count();
        $count = $count+1;/*默认成1开始*/
        return date('Ymd').sprintf("%03d", $count);
    }
}
