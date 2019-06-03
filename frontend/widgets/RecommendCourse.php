<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 11:04
 */

namespace frontend\widgets;

use components\widgets\BaseWidget;
use common\services\learning\CourseService;
use Yii;

class RecommendCourse extends BaseWidget
{
    private $view = '@frontend/views/widget/recommend-course.php';

    public $params = [];

    public function init()
    {
        $currentTime = time();
        $sessionKey = "RecommendCourseData";
        $recommendCourse = [];
        $lastLoadAt = null;
        if (Yii::$app->session->has($sessionKey)) {
            $recommendCourse = Yii::$app->session->get($sessionKey);
            $lastLoadAt = $recommendCourse["lastLoadAt"];
        }

        //为了性能，最后更新时间，60秒只读取一次
        if (empty($lastLoadAt) || ($currentTime - $lastLoadAt) > 60) {
            $uid = Yii::$app->user->getId();
            $service = new CourseService();

            $data = $service->getRecommendCourse($uid, 3);

            $recommendCourse["lastLoadAt"] = $currentTime;
            $recommendCourse["data"] = $data;

            Yii::$app->session->set($sessionKey, $recommendCourse);
        } else {
            $data = $recommendCourse["data"];
        }

        if ($data != null) {
            $this->params['data'] = $data;
        }
    }

    public function run()
    {
        echo $this->renderFile($this->view, $this->params);
    }
}