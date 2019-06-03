<?php

namespace common\models\learning;

use common\models\framework\FwCompany;
use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_certification_template}}".
 *
 * @property string $kid
 * @property string $company_id
 * @property string $file_path
 * @property string $template_url
 * @property string $template_code
 * @property string $template_name
 * @property string $seal_url
 * @property string $print_type
 * @property string $print_orientation
 * @property string $is_auto_certify
 * @property string $is_print_score
 * @property string $is_email_user
 * @property string $is_email_teacher
 * @property string $is_display_certify_date
 * @property integer $sequence_number
 * @property integer $seal_top
 * @property integer $seal_left
 * @property integer $name_top
 * @property integer $name_left
 * @property integer $name_size
 * @property string $name_font
 * @property string $name_color
 * @property integer $score_top
 * @property integer $score_left
 * @property integer $score_size
 * @property string $score_font
 * @property string $score_color
 * @property integer $certify_date_top
 * @property integer $certify_date_left
 * @property integer $certify_date_size
 * @property string $certify_date_font
 * @property string $certify_date_color
 * @property string $certification_display_name
 * @property integer $certification_name_top
 * @property integer $certification_name_left
 * @property integer $certification_name_size
 * @property string $certification_name_font
 * @property string $certification_name_color
 * @property integer $serial_number_top
 * @property integer $serial_number_left
 * @property integer $serial_number_size
 * @property string $serial_number_font
 * @property string $serial_number_color
 * @property string $share_flag
 * @property string $status
 * @property string $certification_img_url
 * @property string $description
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 *
 * @property FwCompany $fwCompany
 * @property LnCertification[] $lnCertifications
 */
class LnCertificationTemplate extends BaseActiveRecord
{
    const PRINT_TYPE_A4 = "0";
    const PRINT_TYPE_ENVOLOPE = "1";

    const IS_AUTO_CERTIFY_NO = "0";
    const IS_AUTO_CERTIFY_YES = "1";

    const IS_PRINT_SCORE_NO = "0";
    const IS_PRINT_SCORE_YES = "1";

    const IS_EMAIL_USER_NO = "0";
    const IS_EMAIL_USER_YES = "1";

    const IS_EMAIL_TEACHER_NO = "0";
    const IS_EMAIL_TEACHER_YES = "1";

    const IS_DISPLAY_CERTIFY_DATE_NO = "0";
    const IS_DISPLAY_CERTIFY_DATE_YES = "1";

    const PRINT_ORIENTATION_PORTRAIT = "0";//纵向
    const PRINT_ORIENTATION_LANDSCAPE = "1";//横向

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_certification_template}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_code','template_name','template_url','name_top', 'name_left', 'name_size',
                'score_top', 'score_left', 'score_size','certify_date_top', 'certify_date_left','certify_date_size','sequence_number'], 'required', 'on' => 'manage'],
            [['sequence_number',  'created_at', 'updated_at'], 'integer'],
            [['kid', 'company_id', 'template_code', 'template_name','created_by', 'updated_by'], 'string', 'max' => 50],
            [['file_path','template_url', 'seal_url', 'certification_img_url'], 'string', 'max' => 500],
            [['certification_display_name', 'name_font', 'score_font', 'certify_date_font', 'certification_name_font', 'serial_number_font'], 'string', 'max' => 100],
            [['name_color', 'score_color', 'certify_date_color', 'certification_name_color', 'serial_number_color'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],
            [['description'], 'string'],

            [['sequence_number'],'integer','min' => 1],
            [['seal_top', 'seal_left','name_top', 'name_left', 'score_top', 'score_left', 'certification_name_top', 'certification_name_left', 'serial_number_top', 'serial_number_left'], 'integer','min' => 0],
            [['seal_top', 'seal_left','name_top', 'name_left', 'score_top', 'score_left', 'certification_name_top', 'certification_name_left', 'serial_number_top', 'serial_number_left'], 'default', 'value'=> 0],

            [['name_size', 'score_size', 'certify_date_size', 'certification_name_size', 'serial_number_size'], 'integer','min' => 1],
            [['name_size', 'score_size', 'certify_date_size', 'certification_name_size', 'serial_number_size'], 'default', 'value'=> 16],

            [['version'], 'number'],
            [['version'], 'default', 'value'=> 1],

            [['is_deleted'], 'string', 'max' => 1],
            [['is_deleted'], 'in', 'range' => [self::DELETE_FLAG_NO, self::DELETE_FLAG_YES]],

            [['print_type'], 'string', 'max' => 1],
            [['print_type'], 'in', 'range' => [self::IS_AUTO_CERTIFY_NO, self::IS_AUTO_CERTIFY_YES]],

            [['print_orientation'], 'string', 'max' => 1],
            [['print_orientation'], 'in', 'range' => [self::PRINT_ORIENTATION_PORTRAIT, self::PRINT_ORIENTATION_LANDSCAPE]],

            [['is_auto_certify'], 'string', 'max' => 1],
            [['is_auto_certify'], 'in', 'range' => [self::PRINT_TYPE_A4, self::PRINT_TYPE_ENVOLOPE]],

            [['is_print_score'], 'string', 'max' => 1],
            [['is_print_score'], 'default', 'value'=> self::IS_PRINT_SCORE_NO],
            [['is_print_score'], 'in', 'range' => [self::IS_PRINT_SCORE_NO, self::IS_PRINT_SCORE_YES]],

            [['is_email_user'], 'string', 'max' => 1],
            [['is_email_user'], 'default', 'value'=> self::IS_EMAIL_USER_NO],
            [['is_email_user'], 'in', 'range' => [self::IS_EMAIL_USER_NO, self::IS_EMAIL_USER_YES]],

            [['is_email_teacher'], 'string', 'max' => 1],
            [['is_email_teacher'], 'default', 'value'=> self::IS_EMAIL_TEACHER_NO],
            [['is_email_teacher'], 'in', 'range' => [self::IS_EMAIL_TEACHER_NO, self::IS_EMAIL_TEACHER_YES]],

            [['is_display_certify_date'], 'string', 'max' => 1],
            [['is_display_certify_date'], 'default', 'value'=> self::IS_DISPLAY_CERTIFY_DATE_NO],
            [['is_display_certify_date'], 'in', 'range' => [self::IS_DISPLAY_CERTIFY_DATE_NO, self::IS_DISPLAY_CERTIFY_DATE_YES]],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],
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
            'template_code' => Yii::t('common', 'template_code'),
            'template_name' => Yii::t('common', 'template_name'),
            'file_path' => Yii::t('common', 'file_path'),
            'template_url' => Yii::t('common', 'template_url'),
            'seal_url' => Yii::t('common', 'seal_url'),
            'print_type' => Yii::t('common', 'print_type'),
            'print_orientation' => Yii::t('common', 'print_orientation'),
            'is_auto_certify' => Yii::t('common', 'is_auto_certify'),
            'is_print_score' => Yii::t('common', 'is_print_score'),
            'is_email_user' => Yii::t('common', 'is_email_user'),
            'is_email_teacher' => Yii::t('common', 'is_email_teacher'),
            'is_display_certify_date' => Yii::t('common', 'is_display_certify_date'),
            'sequence_number' => Yii::t('common', 'sequence_number'),
            'seal_top' => Yii::t('common', 'seal_top'),
            'seal_left' => Yii::t('common', 'seal_left'),
            'name_top' => Yii::t('common', 'name_top'),
            'name_left' => Yii::t('common', 'name_left'),
            'name_size' => Yii::t('common', 'name_size'),
            'name_font' => Yii::t('common', 'name_font'),
            'name_color' => Yii::t('common', 'name_color'),
            'score_top' => Yii::t('common', 'score_top'),
            'score_left' => Yii::t('common', 'score_left'),
            'score_size' => Yii::t('common', 'score_size'),
            'score_font' => Yii::t('common', 'score_font'),
            'score_color' => Yii::t('common', 'score_color'),
            'certify_date_top' => Yii::t('common', 'certify_date_top'),
            'certify_date_left' => Yii::t('common', 'certify_date_left'),
            'certify_date_size' => Yii::t('common', 'certify_date_size'),
            'certify_date_font' => Yii::t('common', 'certify_date_font'),
            'certify_date_color' => Yii::t('common', 'certify_date_color'),
            'certification_display_name' => Yii::t('common', 'certification_display_name'),
            'certification_name_top' => Yii::t('common', 'certification_name_top'),
            'certification_name_left' => Yii::t('common', 'certification_name_left'),
            'certification_name_size' => Yii::t('common', 'certification_name_size'),
            'certification_name_font' => Yii::t('common', 'certification_name_font'),
            'certification_name_color' => Yii::t('common', 'certification_name_color'),
            'serial_number_top' => Yii::t('common', 'serial_number_top'),
            'serial_number_left' => Yii::t('common', 'serial_number_left'),
            'serial_number_size' => Yii::t('common', 'serial_number_size'),
            'serial_number_font' => Yii::t('common', 'serial_number_font'),
            'serial_number_color' => Yii::t('common', 'serial_number_color'),
            'status' => Yii::t('common', 'status'),
            'certification_img_url' => Yii::t('common', 'certification_img_url'),
            'description' => Yii::t('common', 'description'),
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

    public function getCompanyName()
    {
        $companyModel = FwCompany::findOne($this->company_id);

        if ($companyModel != null)
            return $companyModel->company_name;
        else
            return "";
    }

    public function getPrintTypeName()
    {
        $result = $this->print_type;

        if ($result == self::PRINT_TYPE_A4)
            return Yii::t('common','print_type_a4');
        else
            return Yii::t('common','print_type_envolope');
    }

    public function getPrintOrientationName()
    {
        $result = $this->print_orientation;

        if ($result == self::PRINT_ORIENTATION_PORTRAIT)
            return Yii::t('common','print_orientation_portrait');
        else
            return Yii::t('common','print_orientation_landscape');
    }

    public function getIsAutoCertifyName()
    {
        $result = $this->is_auto_certify;

        if ($result == self::IS_AUTO_CERTIFY_NO)
            return Yii::t('common','no');
        else
            return Yii::t('common','yes');
    }

    public function getIsPrintScoreName()
    {
        $result = $this->is_print_score;

        if ($result == self::IS_PRINT_SCORE_NO)
            return Yii::t('common','no');
        else
            return Yii::t('common','yes');
    }

    public function getIsEmailUserName()
    {
        $result = $this->is_email_user;

        if ($result == self::IS_EMAIL_USER_NO)
            return Yii::t('common','no');
        else
            return Yii::t('common','yes');
    }

    public function getIsEmailTeacherName()
    {
        $result = $this->is_email_teacher;

        if ($result == self::IS_EMAIL_TEACHER_NO)
            return Yii::t('common','no');
        else
            return Yii::t('common','yes');
    }

    public function getIsDisplayCertifyDateName()
    {
        $result = $this->is_display_certify_date;

        if ($result == self::IS_DISPLAY_CERTIFY_DATE_NO)
            return Yii::t('common','no');
        else
            return Yii::t('common','yes');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwCompany()
    {
        return $this->hasOne(FwCompany::className(), ['kid' => 'company_id'])
            ->onCondition([FwCompany::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCertifications()
    {
        return $this->hasMany(LnCertification::className(), ['certification_template_id' => 'kid'])
            ->onCondition([LnCertification::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
