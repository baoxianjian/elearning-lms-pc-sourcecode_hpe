<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/9/15
 * Time: 11:23 AM
 */

namespace components\widgets;


use yii\bootstrap\Modal;

class TModal extends Modal{

    public $size = "modal-lg";

    public $closeButton = [
        'class' => 'btn btn-danger btn-sm pull-right',
        ];

}