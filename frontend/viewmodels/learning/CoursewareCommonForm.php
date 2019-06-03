<?php
namespace frontend\viewmodels\learning;

use yii\base\Model;
use Yii;


class CoursewareCommonForm extends Model
{
    public $domain_id;
    public $start_at;
    public $end_at;
    public $supplier;
    public $is_display_pc;
    public $is_display_mobile;
    public $courseware_desc;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['domain_id'], 'required'],
            [['courseware_desc','supplier'], 'string'],
            /*[['catelog'], 'string', 'max' => 100],*/
            [['is_display_pc','is_display_mobile'], 'integer'],
            [['start_at','end_at'], 'date'],
        ];

    }

    public function attributeLabels()
    {
        return [
            'domain_id' => Yii::t('common', 'relate_{value}', ['value'=>Yii::t('common','domain')]),
            'supplier' => Yii::t('common', 'supplier'),
            'is_display_pc' => Yii::t('common', 'is_display_pc'),
            'is_display_mobile' => Yii::t('common', 'is_display_mobile'),
            /*'catelog' => Yii::t('common', 'catelog'),*/
            'courseware_desc' => Yii::t('common', 'courseware_desc'),
            'start_at'=> Yii::t('common', 'start_time'),
            'end_at'=> Yii::t('common', 'end_time'),
        ];
    }
}
