<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 11:04
 */

namespace frontend\widgets;

use common\helpers\TTimeHelper;
use components\widgets\BaseWidget;
use common\services\learning\CourseService;
use Yii;

class ContinueLearning extends BaseWidget
{
    private $view = '@frontend/views/widget/continue-learning.php';

    public $params = [];

    public function init()
    {
        $currentTime = time();
        $sessionKey = "ContinueLearningData";
        $continueLearning = [];
        $lastLoadAt = null;
        if (Yii::$app->session->has($sessionKey))
        {
            $continueLearning = Yii::$app->session->get($sessionKey);
            $lastLoadAt = $continueLearning["lastLoadAt"];
        }

        //为了性能，最后更新时间，30秒只读取一次
        if (empty($lastLoadAt) || ($currentTime - $lastLoadAt) > 30 ) {
            $uid = Yii::$app->user->getId();
            $service = new CourseService();

            $data = $service->getLastLearnCourse($uid);

            $continueLearning["lastLoadAt"] = $currentTime;
            $continueLearning["data"] = $data;

            Yii::$app->session->set($sessionKey,$continueLearning);
        }
        else {
            $data = $continueLearning["data"];
        }

        if ($data != null) {
            $this->params['data'] = $data['data'];
            $this->params['time'] = $data['learning_duration'] < 60 ? Yii::t('frontend', 'less_one_minute') : TTimeHelper::learningTimeToStr($data['learning_duration']);
        }

    }

    public function run()
    {
        echo $this->renderFile($this->view, $this->params);
    }
}