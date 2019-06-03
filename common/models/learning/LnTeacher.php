<?php

namespace common\models\learning;


use common\base\BaseActiveRecord;
use Yii;
use common\services\framework\DictionaryService;


/**
 * This is the model class for table "{{%ln_teacher}}".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $teacher_name
 * @property string $teacher_type
 * @property string $teacher_title
 * @property string $teacher_level
 * @property string $teacher_level_id
 * @property string $company_name
 * @property string $description
 * @property string $user_id
 * @property string $gender
 * @property string $birthday
 * @property string $degree
 * @property string $graduate_school
 * @property double $teach_year
 * @property string $teach_domain
 * @property string $mobile_no
 * @property string $home_phone_no
 * @property string $language
 * @property string $timezone
 * @property string $data_from
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnCourseTeacher[] $lnCourseTeachers
 */
class LnTeacher extends BaseActiveRecord
{
    const TEACHER_TYPE_INTERNAL = "0";
    const TEACHER_TYPE_EXTERNAL = "1";
    const TEACHER_TYPE_ASSISTANT = "2";

    const TEACHER_LEVEL_NO_SETTING=0;
    const TEACHER_LEVEL_PRIMARY=1;
    const TEACHER_LEVEL_INTERMEDIATE=2;
    const TEACHER_LEVEL_HIGHER=3;

    const DATA_FROM_TEACHER_MANAGEMENT = "0";
    const DATA_FROM_USER_MANAGEMENT = "1";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_teacher}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'teacher_name', 'teacher_type'], 'required'],
            [['description'], 'string'],
            [['birthday'], 'safe'],
            [['teach_year'], 'number'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'company_id', 'teacher_name', 'user_id', 'language', 'timezone', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['teacher_type'], 'string', 'max' => 1],
            [['teacher_title','teacher_level', 'company_name', 'graduate_school', 'teach_domain'], 'string', 'max' => 250],
            [['gender', 'degree', 'mobile_no', 'home_phone_no'], 'string', 'max' => 30],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['teacher_type'], 'string', 'max' => 1],
            [['teacher_type'], 'in', 'range' => [self::TEACHER_TYPE_INTERNAL,self::TEACHER_TYPE_EXTERNAL, self::TEACHER_TYPE_ASSISTANT]],
            [['teacher_type'], 'default', 'value'=> self::TEACHER_TYPE_INTERNAL],

            [['data_from'], 'string', 'max' => 1],
            [['data_from'], 'in', 'range' => [self::DATA_FROM_TEACHER_MANAGEMENT,self::DATA_FROM_USER_MANAGEMENT]],
            [['data_from'], 'default', 'value'=> self::DATA_FROM_TEACHER_MANAGEMENT],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

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
            'kid' => Yii::t('common', 'kid'),
            'company_id' => Yii::t('common', 'company_id'),
            'teacher_name' => Yii::t('common', 'teacher_name'),
            'teacher_type' => Yii::t('common', 'teacher_type'),
            'teacher_title' => Yii::t('common', 'teacher_title'),
            'teacher_level' => Yii::t('common', 'teacher_level'),
            'company_name' => Yii::t('common', 'company_name'),
            'description' => Yii::t('common', 'description'),
            'user_id' => Yii::t('common', 'user_id'),
            'gender' => Yii::t('common', 'gender'),
            'birthday' => Yii::t('common', 'birthday'),
            'degree' => Yii::t('common', 'degree'),
            'graduate_school' => Yii::t('common', 'graduate_school'),
            'teach_year' => Yii::t('common', 'teach_year'),
            'teach_domain' => Yii::t('common', 'teach_domain'),
            'mobile_no' => Yii::t('common', 'mobile_no'),
            'home_phone_no' => Yii::t('common', 'mobile_no'),
            'language' => Yii::t('common', 'language'),
            'timezone' => Yii::t('common', 'timezone'),
            'data_from' => Yii::t('common', 'data_from'),
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
    public function getLnCourseTeachers()
    {
        return $this->hasMany(LnCourseTeacher::className(), ['teacher_id' => 'kid'])
            ->onCondition([LnCourseTeacher::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /*获取讲师信息*/
    public function getTeacherInfo($teacher_id){
        if (empty($teacher_id)) return ;
        $result = LnTeacher::findAll(['kid'=>$teacher_id],false);
        return $result;
    }
    
    /*讲师有正在进行中和待开课的课程时*/
    public function getCoursingToTeacher($teacher_id){
    	
    	$result = LnCourse::find(false)
    	           	->leftjoin('{{%ln_course_teacher}} as t1',LnCourse::tableName().".kid= t1.course_id")
    	            ->andWhere(LnCourse::realTableName().".open_status in ('".LnCourse::COURSE_START."','".LnCourse::COURSE_NOT_START."')")
    				->andWhere(LnCourse::realTableName().".course_type ='".LnCourse::COURSE_TYPE_FACETOFACE."'")
    				->andWhere("t1.status ='".LnCourse::STATUS_FLAG_NORMAL."'")
    				->andWhere("t1.teacher_id ='".$teacher_id."'")
    				->andWhere("t1.is_deleted ='".LnCourseTeacher::DELETE_FLAG_NO."'")
    				->asArray()
    				->all()
    	;
    	if(count($result)>0){
    		return true;
    	}else{
    		return false;
    	}
    	
    }

    /**
     * 得到讲师的级别取值范围
     * @param null $id 级别id，为null时返回全部
     * @param null $type name,value，为null时，返回全部
     * @param bool $withCache
     * @return array string
     */
    public function getTeacherLevels($id=null,$type=null,$company_id=null,$withCache=true)
    {
        if(!$company_id){$company_id=$this->company_id;}
            $result = array();
            $dictionaryService = new DictionaryService();
            $dics = $dictionaryService->getDictionariesByCategory('teacher_level',$company_id,$withCache);
           // $result[0] = array('name'=>Yii::t('common', 'no_setting'),'value'=>'0');
            foreach ($dics as $dic) {
               // $result[$dic['dictionary_value']] = $dic['dictionary_name'];
                $result[$dic['kid']] = array('name'=>$dic['dictionary_name'],'value'=>$dic['dictionary_value']);
            } 

        if($id===null)
        {
            return $result;
        }
        if($type===null)
        {
            return $result[$id];
        }
        return $result[$id][$type];
    }


    public function getTeacherTypes($id=null)
    {
        $a=array(
            0=>Yii::t('common','teacher_type_internal'),
            1=>Yii::t('common','teacher_type_external'),
           //2=>Yii::t('common','teacher_type_assistant'),
        );
        if($id===null)
        {
            return $a;
        }
        return $a[$id];
    }
}
