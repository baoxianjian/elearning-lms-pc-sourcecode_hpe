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
use Yii;
use yii\bootstrap\ButtonDropdown;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class TPagination extends Pagination{


    public $pageSizeLimit = [1, 200];

}