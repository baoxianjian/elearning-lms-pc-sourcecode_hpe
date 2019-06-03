<?php

namespace common\models\social;

use common\models\learning\LnCourse;
use Yii;
use common\base\BaseActiveRecord;
use common\models\framework\FwUser;

/**
 * This is the model class for table "{{%so_collect}}".
 *
 * @property string $kid
 * @property string $type
 * @property string $object_id
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
class SoCollect extends BaseActiveRecord
{
    const TYPE_QUESTION = '1';
    const TYPE_COURSE = '2';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%so_collect}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'object_id', 'user_id', 'status', 'start_at'], 'required'],
            [['created_at', 'updated_at', 'start_at', 'end_at'], 'integer'],
            [['kid', 'object_id', 'user_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['type'], 'string', 'max' => 1],
            [['type'], 'default', 'value' => self::TYPE_QUESTION],
            [['type'], 'in', 'range' => [self::TYPE_QUESTION, self::TYPE_COURSE]],

            [['status'], 'string', 'max' => 1],
            [['status'], 'default', 'value' => self::STATUS_FLAG_NORMAL],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

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
            'kid' => Yii::t('frontend', 'kid'),
            'type' => Yii::t('frontend', 'type'),
            'object_id' => Yii::t('frontend', 'object_id'),
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

//    public function beforeValidate()
//    {
//        $result = $this->findOne(['user_id' => $this->user_id, 'object_id' => $this->object_id, 'type' => $this->type]);
//        if ($result) {
//            $this->addError($this->object_id, '已收藏');
//            return false;
//        } else {
//            return true;
//        }
//    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFwUser()
    {
        return $this->hasOne(FwUser::className(), ['kid' => 'user_id'])
            ->onCondition([FwUser::realTableName() . '.is_deleted' => self::DELETE_FLAG_NO]);
    }

    /*判断是否收藏*/
    public function isCollect($object_id)
    {
        $uid = Yii::$app->user->getId();
        $findOne = SoCollect::findOne(['object_id' => $object_id, 'user_id' => $uid], false);
        if ($findOne) {
            return true;
        } else {
            return false;
        }
    }

    /*获取收藏text*/
    public function getCollectText($object_id)
    {

    }

    /**
     * 添加收藏
     */
    public function addCollect($user_id, $obj_id, $type = SoCollect::TYPE_COURSE)
    {
        $collect = new SoCollect();
        $collect->user_id = $user_id;
        $collect->object_id = $obj_id;
        $collect->type = $type;
        $collect->status = SoCollect::STATUS_FLAG_NORMAL;
        $collect->start_at = time();
        if ($collect->save()) {
            if ($type == SoCollect::TYPE_QUESTION) {
                SoQuestion::addFieldNumber($obj_id, "collect_num");
            }
            return true;
        } else {
            return false;
        }
    }
}
