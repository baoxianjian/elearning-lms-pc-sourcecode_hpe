<?php

namespace  common\models\learning;

use Yii;
use common\base\BaseActiveRecord;
use common\models\framework\FwUser;


/**
 * This is the model class for table "{{%ln_investigation}}".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $courseactivity_id
 * @property string $title
 * @property string $investigation_type
 * @property string $description
 * @property integer $start_at
 * @property integer $end_at
 * @property string $status
 * @property string $investigation_range
 * @property string $answer_type
 * @property string $investigation_version
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 * @property string $modResId
 *
 * @property LnCourseactivity $courseactivity
 * @property LnInvestigationQuestion[] $lnInvestigationQuestions
 * @property LnInvestigationResult[] $lnInvestigationResults
 */
class LnInvestigation extends BaseActiveRecord
{
    public $modResId = null;

    const INVESTIGATION_TYPE_SURVEY = '0';
    const INVESTIGATION_TYPE_VOTE = '1';

    const INVESTIGATION_RANGE_NORMAL = '0';
    const INVESTIGATION_RANGE_COURSE = '1';

    const ANSWER_TYPE_REALNAME = '0';
    const ANSWER_TYPE_ANONYMOUS = '1';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_investigation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'title'], 'required'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid',  'investigation_version', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 500],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

            [['investigation_type'], 'string', 'max' => 1],
            [['investigation_type'], 'in', 'range' => [self::INVESTIGATION_TYPE_SURVEY, self::INVESTIGATION_TYPE_VOTE]],

            [['investigation_range'], 'string', 'max' => 1],
            [['investigation_range'], 'in', 'range' => [self::INVESTIGATION_RANGE_NORMAL, self::INVESTIGATION_RANGE_COURSE]],

            [['answer_type'], 'string', 'max' => 1],
            [['answer_type'], 'in', 'range' => [self::ANSWER_TYPE_REALNAME, self::ANSWER_TYPE_ANONYMOUS]],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'kid' =>Yii::t('common', 'kid'),
            'company_id' =>Yii::t('common', 'company_id'),
            'title' => Yii::t('common', 'investigation_title'),
            'investigation_type' => Yii::t('common', 'investigation_type'),
            'description' => Yii::t('common', 'description'),
            'start_at' => Yii::t('common', 'investigation_start_at'),
            'end_at' =>  Yii::t('common', 'investigation_end_at'),
            'status' => Yii::t('common', 'status'),
            'investigation_range' => Yii::t('common', 'investigation_range'),
            'answer_type' =>  Yii::t('common', 'answer_type'),
            'investigation_version' =>  Yii::t('common', 'investigation_version'),
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'is_deleted' => Yii::t('common', 'is_deleted'),
        ];
    }

   
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnInvestigationQuestions()
    {
        return $this->hasMany(LnInvestigationQuestion::className(), ['investigation_id' => 'kid'])
           ->onCondition([LnInvestigationQuestion::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnInvestigationResults()
    {
        return $this->hasMany(LnInvestigationResult::className(), ['investigation_id' => 'kid'])
          ->onCondition([LnInvestigationResult::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
    
    public function getCreatedBy($created_by = null){
        if (empty($created_by)){
            $created_by = $this->created_by;
        }
    	$user = FwUser::findOne($created_by);
    	if($user){
            $name = $user->getDisplayName();
            return $name;
    	}
    	else
    	{
    		return "";
    	}
    }
    
    
     public function getQuestionNum(){
     	
     	$i_questions=LnInvestigationQuestion::find(false)
     	         ->andFilterWhere(["=","investigation_id",$this->kid])
     	         ->andFilterWhere(["!=","question_type",'3'])
    	         ->all();
     	
     	
     	if($i_questions){
     		return count($i_questions);
     	}else{
     		return 0;
     	}
     }

    /*
     * 版本号
     * 规则：日期+sprintf("%03d", course_version);
     * @param string $courseId
     * @return string
     */
    public static function getResourceVersion($id="")
    {
        if (empty($id)) return date('Ymd') . '001';
        $lninvestigation = new LnInvestigation();
        $condition = ['kid' => $id];
        $result = $lninvestigation->findOne($condition,false);
        $course_version = $result->investigation_version;
        if (substr($course_version, 0, 8) == date('Ymd')) {
            $last_version = substr($course_version, -3);
            return date('Ymd') . sprintf("%03d", intval($last_version) + 1);
        } else {
            return date('Ymd') . '001';
        }
    }
}
