<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/3/25
 * Time: 10:00
 */

namespace common\base;

use common\eLearningLMS;
use Yii;
use yii\base\Object;

class BaseService extends Object
{
    public $systemKey = "PC";
    
    protected function execute($sql, $inputParams = null, &$outParams = null)
    {
        return eLearningLMS::execute($sql,$inputParams,$outParams);
    }

    protected function queryAll($sql,$params = null)
    {
        return eLearningLMS::queryAll($sql,$params);
    }

    protected function queryOne($sql)
    {
        return eLearningLMS::queryOne($sql);
    }

    protected function count($sql)
    {
        return eLearningLMS::execute($sql);
    }
}