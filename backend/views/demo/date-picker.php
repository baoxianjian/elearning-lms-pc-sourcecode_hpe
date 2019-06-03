<?php
use components\widgets\TDatePicker;
use yii\helpers\Html;
?>
<?= TDatePicker::widget([
    'attribute' => 'date',
    'name' => 'date',
    'model' => 'date',
]);?>