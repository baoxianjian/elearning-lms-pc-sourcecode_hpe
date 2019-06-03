<?php

namespace common\models\learning;

use common\models\learning\LnCourseware;
use common\base\BaseActiveRecord;
use common\helpers\TStringHelper;
use Yii;
use yii\helpers\Html;
use common\models\learning\LnCourse;


/**
 * This is the model class for table "{{%ln_courseware_book}}".
 *
 * @property string $kid
 * @property string $courware_id
 * @property string $book_name
 * @property string $isbn_no
 * @property string $author_name
 * @property string $publisher_name
 * @property string $original_book_name
 * @property string $translator
 * @property string $publisher_date
 * @property string $price
 * @property string $page_number
 * @property string $brinding_layout
 * @property string $description
 * @property string $image_url
 * @property string $external_url
 * @property string $external_id
 * @property string $external_date_type
 * @property string $status
 * @property string $version
 * @property string $created_by
 * @property string $created_at
 * @property string $updated_by
 * @property string $updated_at
 * @property string $is_deleted
 *
 * @property LnFiles $lnFile
 * @property LnComponent $lnComponent
 * @property LnResourceDomain[] $lnResourceDomains
 * @property LnModRes[] $lnModRes
 */
class LnCoursewareBook extends BaseActiveRecord
{
    public $modResId = null;

    const BOOK_TYPE_TEMP = "0";
    const BOOK_TYPE_OPEN = "1";
    const BOOK_TYPE_OFF = "2";

    const EXTERNAL_DATE_TYPE_ON = "0";
    const EXTERNAL_DATE_TYPE_OFF = "1";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_courseware_book}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['courware_id', 'book_name'], 'required','on'=>'manage'],
            [['created_at','external_id','updated_at','isbn_no'], 'integer'],
            [['kid', 'courware_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['book_name', 'publisher_name'], 'string', 'max' => 500],
            [['external_url','image_url','binding_layout','price'], 'string', 'max' => 500],
            [['description','courware_id'], 'string'],

            [['version'], 'integer'],
            [['version'], 'default', 'value'=> 1],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::BOOK_TYPE_TEMP, self::BOOK_TYPE_OPEN, self::BOOK_TYPE_OFF]],
            [['status'], 'default', 'value'=> self::BOOK_TYPE_OPEN],

            [['external_date_type'], 'string', 'max' => 1],
            [['external_date_type'], 'in', 'range' => [self::EXTERNAL_DATE_TYPE_ON, self::EXTERNAL_DATE_TYPE_OFF]],
            [['external_date_type'], 'default', 'value'=> self::EXTERNAL_DATE_TYPE_ON],

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
            'courware_id' => Yii::t('common', 'courware_id'),
            'book_name' => Yii::t('common', 'book_name'),
            'isbn_no' => Yii::t('common', 'isbn_no'),
            'author_name' => Yii::t('common', 'author_name'),
            'publisher_name' => Yii::t('common', 'publisher_name'),
            'original_book_name' => Yii::t('common', 'original_book_name'),
            'translator' => Yii::t('common', 'translator'),
            'publisher_date' => Yii::t('common', 'publisher_date'),
            'price' => Yii::t('common', 'price'),
            'page_number' => Yii::t('common', 'page_number'),
            'binding_layout' => Yii::t('common', 'binding_layout'),
            'image_url' => Yii::t('common', 'image_url'),
            'external_url' => Yii::t('common', 'external_url'),
            'external_id' => Yii::t('common', 'external_id'),
            'external_date_type' => Yii::t('common', 'external_date_type'),
            'status' => Yii::t('common', 'status'),
            'version' => Yii::t('common', 'book_version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'is_deleted' => Yii::t('common', 'is_deleted'),

     ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCourseware()
    {
        return $this->hasOne(LnCourseware::className(), ['courware_id' => 'kid'])
            ->onCondition([LnCourseware::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /*获取课件名称*/
    public function getCoursewareName($courseware_id){
        $find = LnCourseware::findOne($courseware_id);
        return $find->courseware_name;
    }
    /*
     * 设置课程编号
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



}
