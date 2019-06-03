<?php

namespace common\models\learning;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%ln_related_user}}".
 *
 * @property string $kid
 * @property string $learning_object_id
 * @property string $learning_object_type
 * @property string $company_id
 * @property string $user_id
 * @property string $status
 * @property integer $start_at
 * @property integer $end_at
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class LnRelatedUser extends BaseActiveRecord
{
    const OBJECT_TYPE_COURSE = 'course';
    const OBJECT_TYPE_EXAM = 'exam';
    const OBJECT_TYPE_SURVEY = 'investigation';


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ln_related_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['learning_object_id', 'learning_object_type', 'company_id', 'user_id', 'start_at'], 'required'],
            [['start_at', 'end_at', 'version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'learning_object_id', 'learning_object_type', 'company_id', 'user_id', 'created_by', 'created_from', 'updated_by', 'updated_from'], 'string', 'max' => 50],
            [['status'], 'string', 'max' => 1],
            [['created_from', 'updated_from'], 'string', 'max' => 50],

            [['version'], 'number'],
            [['version'], 'default', 'value' => 1],

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
            'kid' => Yii::t('common', 'Kid'),
            'learning_object_id' => Yii::t('common', 'learning_object_id'),
            'learning_object_type' => Yii::t('common', 'learning_object_type'),
            'company_id' => Yii::t('common', 'company_id'),
            'user_id' => Yii::t('common', 'user_id'),
            'status' => Yii::t('common', 'status'),
            'start_at' => Yii::t('common', 'start_at'),
            'end_at' => Yii::t('common', 'end_at'),
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
}
