<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\learning\LnCoursewareCategory */

?>
<div class="courseware-category-update">

<!--    <h1>--><?//= Html::encode($this->title) ?><!--</h1>-->

    <?= $this->render('_form', [
        'model' => $model,
        'companyModel' => $companyModel,
    ]) ?>

</div>
