<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/17/15
 * Time: 12:48 PM
 */

namespace app\base;


use common\models\framework\FwActionLog;
use common\models\framework\FwActionLogFilter;
use common\base\BaseFilter;
use Yii;

class BaseAppFilter extends BaseFilter{

    private $systemFlag = 'eln_app';
    private $startTime;

    public function beforeAction($action)
    {
        $this->startTime = microtime(true);

        return parent::beforeAction($action);
    }


    public function afterAction($action,$result)
    {
        $endTime = microtime(true);
        $duration_time = $endTime - $this->startTime;



        return parent::afterAction($action,$result);
    }
}