<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_courseware_scorm_relate}}".
 *
 * @property string $kid
 * @property string $courseware_id
 * @property string $scorm_id
 * @property integer $version
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
 * @property LnCourseware $lnCourseware
 * @property LnCoursewareScorm $lnCoursewareScorm
 */
class LnCoursewareScormRelate extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_courseware_scorm_relate}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['courseware_id', 'scorm_id'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'courseware_id', 'scorm_id', 'created_by', 'created_from', 'created_ip', 'updated_by', 'updated_from', 'updated_ip'], 'string', 'max' => 50],
            [['is_deleted'], 'string', 'max' => 1],

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
            'courseware_id' => Yii::t('common', 'courseware_id'),
            'scorm_id' => Yii::t('common', 'scorm_id'),
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
    public function getLnCourseware()
    {
        return $this->hasOne(LnCourseware::className(), ['kid' => 'courseware_id'])
            ->onCondition([LnCourseware::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLnCoursewareScorm()
    {
        return $this->hasOne(LnCoursewareScorm::className(), ['kid' => 'scorm_id'])
            ->onCondition([LnCoursewareScorm::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
