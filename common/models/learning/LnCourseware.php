<?php

namespace common\models\learning;

use common\models\framework\FwDomain;
use common\base\BaseActiveRecord;
use common\helpers\TStringHelper;
use Yii;use yii\helpers\Html;


/**
 * This is the model class for table "{{%ln_courseware}}".
 *
 * @property string $kid
 * @property string $file_id
 * @property string $company_id
 * @property string $component_id
 * @property string $courseware_category_id
 * @property string $courseware_name
 * @property string $courseware_code
 * @property string $vendor
 * @property string $vendor_id
 * @property string $catelog
 * @property string $courseware_type
 * @property string $embed_code
 * @property string $is_display_pc
 * @property string $is_display_mobile
 * @property string $is_allow_download
 * @property string $resource_version
 * @property integer $courseware_time
 * @property integer $default_credit
 * @property string $embed_url
 * @property integer $start_at
 * @property integer $end_at
 * @property string $entrance_address
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property LnFiles $lnFile
 * @property LnComponent $lnComponent
 * @property LnResourceDomain[] $lnResourceDomains
 * @property LnModRes[] $lnModRes
 */
class LnCourseware extends BaseActiveRecord
{
    public $modResId = null;

    const DISPLAY_PC_NO = "0";
    const DISPLAY_PC_YES = "1";

    const DISPLAY_MOBILE_NO = "0";
    const DISPLAY_MOBILE_YES = "1";

    const ALLOW_DOWNLOAD_NO = "0";
    const ALLOW_DOWNLOAD_YES = "1";

    const COURSEWARE_TYPE_LOCAL = "0";
    const COURSEWARE_TYPE_URL = "1";
    const COURSEWARE_TYPE_EMBED_CODE = "2";
    const COURSEWARE_TYPEE_OTHER = "3";

    const ENTRY_MODE_UPLOAD = "0";
    const ENTRY_MODE_INPUT = "1";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_courseware}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'component_id', 'courseware_name'], 'required','on'=>'manage'],
            [[ 'default_credit', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'file_id', 'company_id',  'component_id', 'courseware_category_id', 'vendor_id', 'courseware_code', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['courseware_name', 'courseware_code', 'courseware_desc', 'vendor'], 'string', 'max' => 500],
            [['start_at', 'end_at'],'safe'],
            [['embed_url','entrance_address'], 'string', 'max' => 500],
            [['courseware_desc','embed_code'], 'string'],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['resource_version'], 'integer'],
            [['resource_version'], 'default', 'value'=> 1],

            [['courseware_type'], 'string', 'max' => 1],
            [['courseware_type'], 'in', 'range' => [self::COURSEWARE_TYPE_LOCAL, self::COURSEWARE_TYPE_URL, self::COURSEWARE_TYPE_EMBED_CODE,self::COURSEWARE_TYPEE_OTHER]],
            [['courseware_type'], 'default', 'value'=> self::COURSEWARE_TYPE_LOCAL],

            [['is_display_pc'], 'string', 'max' => 1],
            [['is_display_pc'], 'in', 'range' => [self::DISPLAY_PC_NO, self::DISPLAY_PC_YES]],
            [['is_display_pc'], 'default', 'value'=> self::DISPLAY_PC_YES],

            [['is_display_mobile'], 'string', 'max' => 1],
            [['is_display_mobile'], 'in', 'range' => [self::DISPLAY_MOBILE_NO, self::DISPLAY_MOBILE_YES]],
            [['is_display_mobile'], 'default', 'value'=> self::DISPLAY_MOBILE_NO],

            [['is_allow_download'], 'string', 'max' => 1],
            [['is_allow_download'], 'in', 'range' => [self::ALLOW_DOWNLOAD_NO, self::ALLOW_DOWNLOAD_YES]],
            [['is_allow_download'], 'default', 'value'=> self::ALLOW_DOWNLOAD_NO],

            [['entry_mode'], 'string', 'max' => 1],
            [['entry_mode'], 'in', 'range' => [self::ENTRY_MODE_UPLOAD, self::ENTRY_MODE_INPUT]],
            [['entry_mode'], 'default', 'value'=> self::ENTRY_MODE_UPLOAD],

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
            'file_id' => Yii::t('common', 'file_id'),
            'component_id' => Yii::t('common', 'component_id'),
            'courseware_category_id' => Yii::t('common', 'courseware_category_id'),
            'courseware_name' => Yii::t('common', 'courseware_name'),
            'courseware_desc' => Yii::t('common', 'courseware_desc'),
            'courseware_code' => Yii::t('common', 'courseware_code'),
            'vendor' => Yii::t('common', 'vendor'),
            'vendor_id' => Yii::t('common', 'vendor_id'),
            'courseware_type' => Yii::t('common', 'courseware_type'),
            'embed_url' => Yii::t('common', 'embed_url'),
            'embed_code' => Yii::t('common', 'embed_code'),
            'resource_version' => Yii::t('common', 'courseware_version'),
            'courseware_time' => Yii::t('common', 'courseware_time'),
            'default_credit' => Yii::t('common', 'courseware_default_credit'),
            'file_path' => Yii::t('common', 'file_path'),
            'start_at' => Yii::t('common', 'timestart'),
            'end_at' => Yii::t('common', 'timeend'),
            'entrance_address' => Yii::t('common', 'entrance_address'),
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'is_deleted' => Yii::t('common', 'is_deleted'),
            'is_display_pc' => Yii::t('common', 'is_display_pc'),
            'is_display_mobile' => Yii::t('common', 'is_display_mobile'),

        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnFile()
    {
        return $this->hasOne(LnFiles::className(), ['kid' => 'file_id'])
            ->onCondition([LnFiles::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnComponent()
    {
        return $this->hasOne(LnComponent::className(), ['kid' => 'component_id'])
            ->onCondition([LnComponent::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnResourceDomains()
    {
        return $this->hasMany(LnResourceDomain::className(), ['resource_id' => 'kid'])
            ->onCondition([LnResourceDomain::realTableName() . '.resource_type' => LnResourceDomain::RESOURCE_TYPE_COURSEWARE])
            ->onCondition([LnResourceDomain::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnModRes()
    {
        return $this->hasMany(LnModRes::className(), ['mod_id' => 'kid'])
            ->onCondition([LnModRes::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * 获取多媒体文件链接
     * @return string
     */
    public function getFileLink($show_name = ""){
        $file = LnFiles::findOne($this->file_id);
        return Html::a($show_name ? $show_name : $this->courseware_name,['resource/courseware/view','id'=>$this->kid,'download'=>true],['target'=>'_blank']);
    }

    /**
     * 获取多媒体文件链接
     * @return string
     */
    public function getFileName(){
        $file = LnFiles::findOne($this->file_id);
        return $file->file_name;
    }

    /**
     * 获取课件组件图标
     * @return string
     */
    public function getCoursewareIcon(){
        $component = LnComponent::findOne($this->component_id);
        return $component->icon;
    }

    /**
     * 获取课件组件标题
     * @return string
     */
    public function getCoursewareTitle(){
        $component = LnComponent::findOne($this->component_id);
        return $component->title;
    }

    /**
     * @param $domainList
     * @return string
     */
    public function getDomainNameByText($domainList="",$length = ""){
        $str = array();
        if (empty($domainList)){
            $resourceDomain = LnResourceDomain::findAll(['resource_id'=>$this->kid,'status'=>LnResourceDomain::STATUS_FLAG_NORMAL], false);
            if (!empty($resourceDomain)){
                foreach ($resourceDomain as $val){
                    $domainList[] = $val->domain_id;
                }
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

    /*
     * 课程版本号
     * 规则：日期+sprintf("%03d", course_version);
     * @param string $courseId
     * @return string
     */
    public function getCoursewareVersion($courseId="")
    {
        if (empty($courseId)) return date('Ymd') . '001';
        $lncourse = new LnCourseware();
        $condition = ['kid' => $courseId];
        $result = $lncourse->findOne($condition,false);
        $course_version = $result->resource_version;
        if (substr($course_version, 0, 8) == date('Ymd')) {
            $last_version = substr($course_version, -3);
            return date('Ymd') . sprintf("%03d", intval($last_version) + 1);
        } else {
            return date('Ymd') . '001';
        }
    }

    /*
     * 设置课件编号
     * 规则：日期+sprintf("%03d", $count);
     * @param string $courseId
     * @return string
     */
    public function setCoursewareCode(){
        $start_at = strtotime(date('Y-m-d'));
        $end_at = $start_at+86399;
        $count = $this->find()->where("created_at>".$start_at)->andWhere("created_at<".$end_at)->count();
        $count = $count+1;/*默认成1开始*/
        return date('Ymd').sprintf("%03d", $count);
    }

    /**
     * 获取文件类型
     */
    public function getCoursewareComponentTitle(){
        $component = LnComponent::findOne($this->component_id);
        return $component->title;
    }

    /*获取分类名称*/
    public function getCoursewareCategoryText(){
        $category = LnCoursewareCategory::findOne($this->courseware_category_id);
        if($category){
            return $category->category_name;
        }
        else
        {
            return "";
        }
    }

    /*获取课件名称*/
    public function getCoursewareName($courseware_id){
        $find = LnCourseware::findOne($courseware_id);
        return $find->courseware_name;
    }

    /**
     * 判断课件是否被引用
     * @param $id
     * @return bool
     */
    public function IsUsed($id = null){
        if (empty($id)) $id = $this->kid;
        $count = LnModRes::find(false)->andFilterWhere(['courseware_id' => $id, 'res_type' => LnModRes::RES_TYPE_COURSEWARE, 'publish_status' => LnModRes::PUBLIC_STATUS_YES])->count();
        if ($count > 0){
            return true;
        }else{
            return false;
        }
    }


}
