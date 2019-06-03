<?php

namespace common\models\social;

use Yii;
use common\base\BaseActiveRecord;
use common\models\framework\FwUser;

/**
 * This is the model class for table "{{%so_share}}".
 *
 * @property string $kid
 * @property string $user_id
 * @property string $title
 * @property string $content
 * @property string $type
 * @property string $obj_id
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class SoShare extends BaseActiveRecord
{
    const SHARE_TYPE_RECORD = '1';
    const SHARE_TYPE_COURSE = '2';
    const SHARE_TYPE_QUESTION = '3';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%so_share}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'title', 'content'], 'required'],
            [['content'], 'string'],
            [['version', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'user_id', 'obj_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 200],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['type'], 'string', 'max' => 1],
            [['type'], 'default', 'value' => self::SHARE_TYPE_RECORD],
            [['type'], 'in', 'range' => [self::SHARE_TYPE_RECORD, self::SHARE_TYPE_COURSE, self::SHARE_TYPE_QUESTION]],

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
            'kid' => Yii::t('frontend', 'kid'),
            'user_id' => Yii::t('frontend', 'user_id'),
            'title' => Yii::t('frontend', 'title'),
            'content' => Yii::t('frontend', 'content'),
            'type' => Yii::t('frontend', 'type'),
            'obj_id' => Yii::t('frontend', 'obj_id'),
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
    public function getFwUser()
    {
        return $this->hasOne(FwUser::className(), ['kid' => 'user_id'])
            ->onCondition([FwUser::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }
}
