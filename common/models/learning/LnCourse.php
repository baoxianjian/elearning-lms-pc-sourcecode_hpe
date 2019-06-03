<?php

namespace common\models\learning;

use common\helpers\TFileModelHelper;
use common\models\framework\FwDictionaryCategory;
use common\models\framework\FwDictionary;
use common\models\framework\FwDomain;
use common\models\framework\FwTagReference;
use common\models\social\SoCollect;
use common\services\framework\DictionaryService;
use common\base\BaseActiveRecord;
use common\helpers\TStringHelper;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%ln_course}}".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $short_code
 * @property string $course_code
 * @property string $course_name
 * @property string $course_desc
 * @property string $course_desc_nohtml
 * @property string $category_id
 * @property string $course_level
 * @property string $course_type
 * @property string $reg_type
 * @property string $course_period
 * @property string $course_period_unit
 * @property string $course_language
 * @property string $currency
 * @property string $course_price
 * @property string $theme_url
 * @property string $is_record_score
 * @property string $is_display_pc
 * @property string $is_display_mobile
 * @property string $pass_grade
 * @property string $course_version
 * @property integer $default_credit
 * @property integer $start_time
 * @property integer $end_time
 * @property string $status
 * @property string $learned_number
 * @property string $register_number
 * @property string $rated_number
 * @property string $average_rating
 * @property string $visit_number
 * @property string $mod_type
 * @property integer $enroll_start_time
 * @property integer $enroll_end_time
 * @property integer $open_start_time
 * @property integer $open_end_time
 * @property string $open_status
 * @property string $training_address
 * @property string $training_address_id
 * @property string $vendor
 * @property string $vendor_id
 * @property integer $max_attempt
 * @property string $is_recalculate_score
 * @property integer $release_at
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnCourseComplete[] $lnCourseCompletes
 * @property LnResourceDomain[] $lnResourceDomains
 * @property LnCourseMark[] $lnCourseMarks
 * @property LnCourseMods[] $lnCourseMods
 * @property LnCourseQuestion[] $lnCourseQuestions
 * @property LnCourseReg[] $lnCourseRegs
 */
class LnCourse extends BaseActiveRecord
{

    //级别
    const PRIMARY_LEVEL = "1";
    const INTERMEDIATE_LEVEL = "2";
    const HIGHER_LEVEL = '3';

    const DISPLAY_PC_NO = "0";
    const DISPLAY_PC_YES = "1";

    const DISPLAY_MOBILE_NO = "0";
    const DISPLAY_MOBILE_YES = "1";
    
    const COURSE_NOT_START = "0"; //课程未开始
    const COURSE_START = "1"; // 课程开始
    const COURSE_END = "2";  //课程完成

    const COURSE_TYPE_ONLINE = "0";//在线
    const COURSE_TYPE_FACETOFACE = "1";//面授

    const IS_ALLOW_OVER_NO = "0";
    const IS_ALLOW_OVER_YES = "1";

    const MOD_TYPE_RANDOM = "0";//自由学习
    const MOD_TYPE_ORDER = "1";//顺序学习

    const IS_SURVEY_ONLY_NO = "0";//是否纯调查：否
    const IS_SURVEY_ONLY_YES = "1";//是否纯调查：是

    const IS_EXAM_ONLY_NO = "0";//是否纯考试：否
    const IS_EXAM_ONLY_YES = "1";//是否纯考试：是

    const REG_TYPE_SELF = "1";//1：自助注册，学员自选课程注册学习（选修课）
    const REG_TYPE_TUISONG = "2";//2：指派推送，必须由管理员、经理推送报名的课程。（必修课）

    const COURSE_PERIOD_UNIT_MINUTE = '1'; //分钟
    const COURSE_PERIOD_UNIT_HOUR = '2'; //小时
    const COURSE_PERIOD_UNIT_DAY = '3'; //天
    
    const COURSE_APPROVAL_DEFAULT = 'NONE'; //无需审批
    const COURSE_APPROVAL_N1 = 'N1'; //一级审批
    const COURSE_APPROVAL_N2 = 'N2'; //二级审批

    //是否允许匿名查看
    const IS_ANNONY_VIEW_NO = '0'; //否
    const IS_ANNONY_VIEW_YES = '1'; //是

    //是否课程项目
    const IS_COURSE_PROJECT_NO = '0'; //否
    const IS_COURSE_PROJECT_YES = '1'; //是

    //默认播放高度
    const PLAY_NORMAL_HEIGHT = '600';

    /*是否重新计分*/
    const IS_RECALCULATE_SCORE_NO = '0'; //否
    const IS_RECALCULATE_SCORE_YES = '1'; //是

    /*是否复制*/
    const IS_COPY_YES = '1';
    const IS_COPY_NO = '0';

    /*是否免注册*/
    const IS_ALLOW_NO_REGISTER_NO = 0; //否
    const IS_ALLOW_NO_REGISTER_YES = 1; //是

    /*mode*/
    const PLAY_MOD_PREVIEW = 'preview';
    const PLAY_MOD_NORMAL = 'normal';

    public $editor;
    public $audienceName;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_course}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'course_name', 'course_level','short_code'], 'required', 'on' => 'manage'],
            [['course_desc','course_desc_nohtml'], 'string'],
            [['course_price','pass_grade'], 'number'],
            [['default_credit', 'created_at', 'updated_at', 'max_attempt'], 'integer'],
            [['kid', 'company_id', 'category_id', 'course_level', 'course_period', 'course_code',
                'short_code', 'course_language', 'training_address_id', 'vendor_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['theme_url'], 'string', 'max' => 500],
            [['course_name'], 'string', 'max' => 100],
            [['training_address', 'vendor', 'approval_rule'],'string'],
            [['start_time', 'end_time', 'release_at'],'safe'],
            [['start_time'], 'default', 'value'=> time()],
            [['currency', 'course_version'], 'string', 'max' => 20],
            [['learned_number','register_number','rated_number','average_rating','visit_number'], 'number'],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['reg_type'], 'in', 'range' => [self::REG_TYPE_SELF, self::REG_TYPE_TUISONG]],
            [['reg_type'], 'default', 'value'=> self::REG_TYPE_SELF],

            [['course_type'], 'in', 'range' => [self::COURSE_TYPE_ONLINE, self::COURSE_TYPE_FACETOFACE]],
            [['course_type'], 'default', 'value'=> self::COURSE_TYPE_ONLINE],

            [['mod_type'], 'in', 'range' => [self::MOD_TYPE_RANDOM, self::MOD_TYPE_ORDER]],
            [['mod_type'], 'default', 'value'=> self::MOD_TYPE_RANDOM],

            [['max_attempt'],'default', 'value'=> 0],

            [['is_record_score'], 'in', 'range' => [self::NO, self::YES]],
            [['is_record_score'], 'default', 'value'=> self::YES],

            [['is_recalculate_score'], 'in', 'range' => [self::NO, self::YES]],
            [['is_recalculate_score'], 'default', 'value'=> self::NO],

            [['is_annony_view'], 'in', 'range' => [self::IS_ANNONY_VIEW_NO, self::IS_ANNONY_VIEW_YES]],
            [['is_annony_view'], 'default', 'value'=> self::IS_ANNONY_VIEW_NO],

            [['is_course_project'], 'in', 'range' => [self::IS_COURSE_PROJECT_NO, self::IS_COURSE_PROJECT_YES]],
            [['is_course_project'], 'default', 'value'=> self::IS_COURSE_PROJECT_NO],

            [['is_display_pc'], 'in', 'range' => [self::DISPLAY_PC_NO, self::DISPLAY_PC_YES]],
            [['is_display_pc'], 'default', 'value'=> self::DISPLAY_PC_YES],

            [['open_status'], 'in', 'range' => [self::COURSE_NOT_START, self::COURSE_START,self::COURSE_END]],
            [['open_status'], 'default', 'value'=> self::COURSE_NOT_START],

            [['is_display_mobile'], 'in', 'range' => [self::DISPLAY_MOBILE_NO, self::DISPLAY_MOBILE_YES]],
            [['is_display_mobile'], 'default', 'value'=> self::DISPLAY_MOBILE_YES],

            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],
            [['status'], 'default', 'value' => 0],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]],
            
            [['course_period_unit'], 'in', 'range' => [self::COURSE_PERIOD_UNIT_MINUTE, self::COURSE_PERIOD_UNIT_HOUR, self::COURSE_PERIOD_UNIT_DAY]],
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
            'short_code' => Yii::t('common', 'short_code'),
            'course_code' => Yii::t('common', 'course_code'),
            'course_name' => Yii::t('common', 'course_name'),
            'course_desc' => Yii::t('common', 'course_desc'),
            'course_desc_nohtml' => Yii::t('common', 'course_desc_nohtml'),
            'category_id' => Yii::t('common', 'category_id'),
            'course_level' => Yii::t('common', 'course_level'),
            'course_type' => Yii::t('common', 'course_type'),
            'reg_type' => Yii::t('common', 'reg_type'),
            'course_period' => Yii::t('common', 'course_period'),
            'course_period_unit' => Yii::t('common', 'course_period_unit'),
            'course_language' => Yii::t('common', 'language'),
            'currency' => Yii::t('common', 'currency'),
            'course_price' => Yii::t('common', 'price'),
            'is_record_score' => Yii::t('common', 'is_record_score'),
            'is_display_pc' => Yii::t('common', 'is_display_pc'),
            'is_display_mobile' => Yii::t('common', 'is_display_mobile'),
            'is_recalculate_score' => Yii::t('common', 'is_recalculate_score'),
            'training_address' => Yii::t('common', 'training_address'),
            'training_address_id' => Yii::t('common', 'training_address_id'),
            'vendor' => Yii::t('common', 'vendor'),
            'vendor_id' => Yii::t('common', 'vendor_id'),
            'theme_url'=> Yii::t('common', 'theme_url'),
            'start_time' => Yii::t('common', 'start_time'),
            'end_time' => Yii::t('common', 'end_time'),
            'status' => Yii::t('common', 'status'),
            'course_version' => Yii::t('common', 'course_version'),
            'max_attempt' => Yii::t('common', 'max_attempt'),
            'default_credit' => Yii::t('common', 'course_default_credit'),
            'open_status' => Yii::t('common', 'open_status'),
            'is_annony_view' => Yii::t('common', 'is_annony_view'),
            'is_course_project' => Yii::t('common', 'is_course_project'),
            'approval_rule' => Yii::t('common', 'approval_rule'),
            'release_at' => Yii::t('common', 'release_at'),
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
    public function getLnCourseCompletes()
    {
        return $this->hasMany(LnCourseComplete::className(), ['course_id' => 'kid'])
            ->onCondition([LnCourseComplete::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnResourceDomains()
    {
        return $this->hasMany(LnResourceDomain::className(), ['resource_id' => 'kid'])
            ->onCondition([LnResourceDomain::realTableName() . '.resource_type' => LnResourceDomain::RESOURCE_TYPE_COURSE])
            ->onCondition([LnResourceDomain::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourseMarks()
    {
        return $this->hasMany(LnCourseMark::className(), ['course_id' => 'kid'])
            ->onCondition([LnCourseMark::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourseMods()
    {
        return $this->hasMany(LnCourseMods::className(), ['course_id' => 'kid'])
            ->onCondition([LnCourseMods::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourseQuestions()
    {
        return $this->hasMany(LnCourseQuestion::className(), ['course_id' => 'kid'])
            ->onCondition([LnCourseQuestion::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourseRegs()
    {
        return $this->hasMany(LnCourseReg::className(), ['course_id' => 'kid'])
            ->onCondition([LnCourseReg::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    public function getLnCourseCategory(){
        return $this->hasMany(LnCourseCategory::className(), ['course_id' => 'kid'])
            ->onCondition([LnCourseCategory::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }


    public function getCourseCategoryText(){
        $category = LnCourseCategory::findOne($this->category_id);
        if($category){
            return $category->category_name;
        }
        else
        {
            return "";
        }
    }

    public function getCourseCover(){
//        $file = LnFiles::findOne(['kid',$this->theme_url],false);
        $tFileModel = new TFileModelHelper();
        return $tFileModel->secureLink($this->theme_url);
    }

    /*
     * 设置课程编号
     * 规则：日期+sprintf("%03d", $count);
     * @param string $courseId
     * @return string
     */
    public function setCourseCode($kid=""){
        if (!empty($kid)){
            $info = LnCourse::findOne($kid);
            return $info->course_code;
        }
        $start_at = strtotime(date('Y-m-d'));
        $end_at = $start_at+86399;
        $count = $this->find()->where("created_at>".$start_at)->andWhere("created_at<".$end_at)->count();
        $count = $count+1;/*默认成1开始*/
        return date('Ymd').sprintf("%03d", $count);
    }

    /**
     * @param $domainList
     * @return string
     */
    public function getDomainNameByText($domainList="",$length = ""){
        $str = array();
        if (empty($domainList)){
            $resourceDomain = LnResourceDomain::find(false)->andFilterWhere(['resource_id'=>$this->kid])->andFilterWhere(['status'=>LnResourceDomain::STATUS_FLAG_NORMAL])->distinct()->select('domain_id')->asArray()->all();
            if (!empty($resourceDomain)){
                $resourceDomain = ArrayHelper::map($resourceDomain, 'domain_id', 'domain_id');
                $domainList = array_keys($resourceDomain);
            }
        }
        if (!is_array($domainList)) $domainList = explode(',', $domainList);
        foreach($domainList as $val){
            $domain = FwDomain::findOne($val);
            if (!empty($domain) && $domain->status == FwDomain::STATUS_FLAG_NORMAL) {
                $str[] = $domain->domain_name;
            }
        }
        $str = join('、',$str);
        if (!empty($length)){
            $str = TStringHelper::subStr($str, $length, 'utf-8', 0, '...');
        }
        return $str;
    }

    /**
     * 字典名称
     * 课程表字段别名
     */
    public function getDictionaryName($categoryCode, $field = "", $id = ""){
        $service = new DictionaryService();
        $list = $service->getDictionariesByCategory($categoryCode);
        $array = array();
        if (!empty($list)){
            foreach ($list as $val){
                $array[$val->dictionary_value] = $val->dictionary_name;
            }
        }
        return $array[$this->$field];
    }

    /**
    * 根据字典分类与值获取字典详细信息
    * @return string
    */
    public function getDictionaryText($cate_code, $val)
    {
        if (empty($cate_code)) {
            return "";
        } else {
            $dictionaryService = new DictionaryService();
            $name = $dictionaryService->getDictionaryNameByValue($cate_code, $val);

            return $name;
        }
    }
    
    /**
     * 课程标签
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourseTag($course_id)
    {
        $tagModel = FwTagReference::find(false);
        $tags = $tagModel
            ->leftjoin('{{%fw_tag}} as t1', 't1.kid = ' . FwTagReference::tableName() . ".tag_id")
            ->andWhere([FwTagReference::realTableName() . ".status" => FwTagReference::STATUS_FLAG_NORMAL])
            ->andWhere([FwTagReference::realTableName() . ".subject_id" => $course_id])
            ->andWhere(['=','t1.is_deleted',"0"])
            ->select("t1.tag_value")
            ->asArray()
            ->all();
        return $tags;
    }
    
    /**
     * 课程讲师
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourseTeacher($course_id)
    {
        $teacherModel = LnCourseTeacher::find(false);
        $teacher = $teacherModel
            ->leftjoin('{{%ln_teacher}} as t1', 't1.is_deleted = 0 and t1.kid = ' . LnCourseTeacher::tableName() . ".teacher_id")
            ->andWhere([LnCourseTeacher::realTableName() . ".course_id" => $course_id])
            ->andWhere([LnCourseTeacher::realTableName() . ".status" => LnCourseTeacher::STATUS_FLAG_NORMAL])
            ->select(["t1.teacher_name", "t1.kid"])
            ->asArray()
            ->all();

        return $teacher;
    }

    /**
     * 判断课程是否有人注册
     * @return bool
     */
    public function IsCourseReg(){
        $count = LnCourseReg::find(false)->andFilterWhere(['course_id' => $this->kid, 'reg_state' => LnCourseReg::STATUS_FLAG_NORMAL])->limit(1)->all();
        if ($count && count($count) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取课程类型
     * @return string
     */
    public function getCourseTypeText()
    {
        $text = '';
        if ($this->course_type === LnCourse::COURSE_TYPE_ONLINE) {
            $text = '在线';
        } elseif ($this->course_type === LnCourse::COURSE_TYPE_FACETOFACE) {
            $text = '面授';
        }
        return $text;
    }
    
    /**
     * 判断该课程下是否有调查
     * @return string
     */
    public function isHaveInvestigation()
    {
    
    	$arr=[];
    	$arr=LnCourseactivity::find(false)
    	   ->andFilterWhere(["=","object_type","investigation"])		
    	   ->andFilterWhere(["=","course_id",$this->kid])
    	   ->asArray()
    	   ->all();
    	if(sizeof($arr)>0){
    		return "yes";
    	}else{
    		return "no";
    	}
    	
    }
    
    /**
    * 根据课程类型得到课时单位
    * add by baoxianjian 15:12 2016/1/14
    * @param int $courseType
    */
    public function getCoursePeriodUnitByCourseType($courseType)
    {
        //$unit=self::COURSE_PERIOD_UNIT_MINUTE;
        $temp[self::COURSE_TYPE_ONLINE]=self::COURSE_PERIOD_UNIT_HOUR;
        $temp[self::COURSE_TYPE_FACETOFACE]=self::COURSE_PERIOD_UNIT_DAY;
        
        $unit= $temp[$courseType];
        if(!$unit)
        {
            //default is hour
            $unit=$temp[self::COURSE_TYPE_ONLINE];
        }
        
        return $unit;
        
    }
    
    /**
    * 得到课时单位
    * @author baoxianjian 15:12 2016/1/14
    * @param int $type
    */
    public function getCoursePeriodUnits($unitVal=0)
    {
        $units=array('1'=>Yii::t('common','time_minute'),'2'=>Yii::t('common','time_hour'),'3'=>Yii::t('common','time_day'));
        if($unitVal)
        {
            return $units[$unitVal];
        }
        return $units; 
    }

    /**
     * 收藏
     * @return string
     */
    public function getCourseCollectionText()
    {
        $object_id = $this->kid;
        $user_id = Yii::$app->user->identity->kid;

        $model = new SoCollect();
        $data = $model->find(false)
            ->andFilterWhere(['=', 'object_id', $object_id])
            ->andFilterWhere(['=', 'user_id', $user_id])
            ->andFilterWhere(['=', 'type', SoCollect::TYPE_COURSE])
            ->andFilterWhere(['=', 'status', SoCollect::STATUS_FLAG_NORMAL])
            ->count('kid');

        if (!empty($data) && $data > 0) {
            return Yii::t('common', 'canel_collection');
        } else {
            return Yii::t('common', 'collection');
        }
    }

    /**
     * 是否计分
     * @param null $courseId
     * @return string
     */
    public function isRecordScore($courseId = null){
        if (is_null($courseId)){
            $courseId = $this->kid;
        }
        $count = LnModRes::find(false)->andFilterWhere(['course_id' => $courseId, 'is_record_score' => LnModRes::IS_RECORD_SCORE_YES])->count();
        if ($count){
            return Yii::t('common', 'yes');
        }else{
            return Yii::t('common', 'no');
        }

    }

    /**
     * 货币单位
     * @param $code
     */
    public function getPriceUnit($dictionaryCode = null){
        if (empty($dictionaryCode)) $dictionaryCode = $this->currency;
        $dictionaryService = new DictionaryService();
        $dictionaryValue = $dictionaryService->getDictionaryValueByCode('currency_symbol', $dictionaryCode);
        return $dictionaryValue;
    }
}
