<?php

namespace common\models\social;

use Yii;
use common\base\BaseActiveRecord;

/**
 * This is the model class for table "{{%so_user_attention}}".
 *
 * @property string $kid
 * @property string $user_id
 * @property string $attention_id
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
class SoUserAttention extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%so_user_attention}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'attention_id', 'start_at'], 'required'],
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'integer'],
            [['kid', 'user_id', 'attention_id', 'created_by', 'updated_by'], 'string', 'max' => 50],
            [['created_from','updated_from'], 'string', 'max' => 50],

            [['version'], 'number'],
            [['version'], 'default', 'value' => 1],

            [['status'], 'string', 'max' => 1],
            [['status'], 'in', 'range' => [self::STATUS_FLAG_TEMP, self::STATUS_FLAG_NORMAL, self::STATUS_FLAG_STOP]],

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
            'attention_id' => Yii::t('frontend', 'attention_id'),
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

    /*text*/
    public function getAttentionText($attention_id){
        $uid = Yii::$app->user->getId();
        $isA = $this->isAttention($uid, $attention_id);
        if ($isA){
            return '取消关注';
        }else{
            return '关注';
        }
    }

    /*判断是否关注*/
    public function isAttention($uid, $attention_id){
        $findOne = $this->findOne(['user_id'=>$uid, 'attention_id'=>$attention_id,'status'=>$this::STATUS_FLAG_NORMAL],false);
        if (!empty($findOne)){
            return true;
        }else{
            return false;
        }
    }
    /*添加关注*/
    public function addAttention($uid, $attention_id, $status){
        $findOne = $this->findOne(['user_id'=>$uid, 'attention_id'=>$attention_id],false);
        if ($findOne){
            $model = SoUserAttention::findOne($findOne->kid);
            $model->status = $status;
            $res = $model->update();
        }else{
            $model = new SoUserAttention();
            $model->user_id = $uid;
            $model->attention_id = $attention_id;
            $model->start_at = time();
            $res = $model->save();
        }
        if ($res !== false){
            return true;
        }else{
            return false;
        }
    }
}
