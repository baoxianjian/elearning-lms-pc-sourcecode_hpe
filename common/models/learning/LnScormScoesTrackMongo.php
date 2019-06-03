<?php

namespace common\models\learning;

use common\base\BaseActiveRecordMongoDB;
use yii;

/**
 * This is the model class for collection "eln_ln_scorm_scoes_track".
 *
 * @property \MongoId|string $_id
 * @property string $scorm_sco_id
 * @property string $scorm_id
 * @property string $course_reg_id
 * @property string $course_complete_id
 * @property string $course_id
 * @property string $courseware_id
 * @property string $mod_id
 * @property string $mod_res_id
 * @property string $user_id
 * @property integer $attempt
 * @property array $elementlist
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
 */
class LnScormScoesTrackMongo extends BaseActiveRecordMongoDB
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'eln_ln_scorm_scoes_track';
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'scorm_sco_id',
            'scorm_id',
            'course_reg_id',
            'course_id',
            'courseware_id',
            'course_complete_id',
            'mod_id',
            'mod_res_id',
            'user_id',
            'attempt',
            'elementlist',
            'version',
            'created_by',
            'created_at',
            'created_from',
            'created_ip',
            'updated_by',
            'updated_at',
            'updated_from',
            'updated_ip',
            'is_deleted',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scorm_sco_id', 'scorm_id', 'user_id', 'attempt'], 'required'],
            [['attempt', 'created_at', 'updated_at'], 'integer'],
            [['scorm_sco_id', 'scorm_id','course_reg_id','course_id',
                'course_complete_id','courseware_id','mod_id','mod_res_id', 
                'user_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

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
            '_id' => Yii::t('common', 'kid'),
            'scorm_sco_id' => Yii::t('common', 'scorm_sco_id'),
            'scorm_id' => Yii::t('common', 'scorm_id'),
            'course_reg_id' => Yii::t('common', 'course_reg_id'),
            'course_complete_id' => Yii::t('common', 'course_complete_id'),
            'course_id' => Yii::t('common', 'course_id'),
            'courseware_id' => Yii::t('common', 'courseware_id'),
            'mod_id' => Yii::t('common', 'mod_id'),
            'mod_res_id' => Yii::t('common', 'mod_res_id'),
            'user_id' => Yii::t('common', 'user_id'),
            'attempt' => Yii::t('common', 'attempt'),
            'version' => Yii::t('common', 'version'),
            'created_by' => Yii::t('common', 'created_by'),
            'created_at' => Yii::t('common', 'created_at'),
            'created_from' => Yii::t('common', 'created_from'),
            'created_ip' => Yii::t('common', 'created_ip'),
            'updated_by' => Yii::t('common', 'updated_by'),
            'updated_at' => Yii::t('common', 'updated_at'),
            'updated_from' => Yii::t('common', 'updated_from'),
            'updated_ip' => Yii::t('common', 'updated_ip'),
            'is_deleted' => Yii::t('common', 'is_deleted')
        ];
    }
}
