<?php

namespace common\models\social;

use common\base\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%so_share_user}}".
 *
 * @property string $kid
 * @property string $share_id
 * @property string $user_id
 * @property string $version
 * @property string $created_by
 * @property integer $created_at
 * @property string $created_from
 * @property string $updated_by
 * @property integer $updated_at
 * @property string $updated_from
 * @property string $is_deleted
 */
class SoShareUser extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%so_share_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['kid', 'share_id', 'user_id', 'created_by', 'created_at'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['kid', 'share_id', 'user_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
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
            'kid' => Yii::t('frontend', 'kid'),
            'qa_share_id' => Yii::t('frontend', 'share_id'),
            'user_id' => Yii::t('frontend', 'user_id'),
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
