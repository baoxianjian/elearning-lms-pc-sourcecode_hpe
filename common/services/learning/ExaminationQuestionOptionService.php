<?php

namespace common\services\learning;

use Yii;
use yii\data\ActiveDataProvider;
use Exception;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use common\models\learning\LnExamQuestionOption;


class ExaminationQuestionOptionService extends LnExamQuestionOption
{
    /*获取版本*/
    public function getExaminationQuestionOptionVersion($kid=null){
        if (empty($kid)) return date('Ymd') . '001';
        $model = new LnExamQuestionOption();
        $result = $model->findOne($kid);
        $option_version = $result->option_version;
        if (substr($option_version, 0, 8) == date('Ymd')) {
            $last_version = substr($option_version, -3);
            return date('Ymd') . sprintf("%03d", intval($last_version) + 1);
        } else {
            return date('Ymd') . '001';
        }
    }

}