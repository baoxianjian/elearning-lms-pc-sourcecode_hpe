<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/24/15
 * Time: 4:34 PM
 */

namespace components\widgets;


//use yii\grid\GridView;
use kartik\base\Config;
use kartik\grid\GridView;
use kartik\grid\Module;
use kartik\widgets\DatePicker;
use Yii;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class TDatePicker extends DatePicker{


    public $type = TDatePicker::TYPE_COMPONENT_PREPEND;

    public $options = [];

    public $pluginOptions = [
        'autoclose'=>true,
        'format' => 'yyyy-mm-dd',
        'todayHighlight' => true
    ];

    public $pickerButton = [];

    public $removeButton = [];

    public function init()
    {
        if ($this->options == null) {
            $this->options = [
                'placeholder' => Yii::t('common', 'select_more')
            ];
        }
        $this->pickerButton = [
            'title' => Yii::t('common', 'select_date')
        ];

        $this->removeButton = [
            'title' => Yii::t('common', 'clear_date')
        ];

        parent::init();
    }




}