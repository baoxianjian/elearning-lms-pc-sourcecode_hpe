<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 11:04
 */

namespace frontend\widgets;

use common\helpers\TArrayHelper;
use components\widgets\BaseWidget;
use common\services\learning\CourseService;
use Yii;

class CourseLibrary extends BaseWidget
{
    private $view = '@frontend/views/widget/course-library.php';

    public $params = [];

    public function init()
    {
        $currentTime = time();
        $sessionKey = "CourseLibraryData";
        $courseLibrary = [];
        $lastLoadAt = null;
        if (Yii::$app->session->has($sessionKey)) {
            $courseLibrary = Yii::$app->session->get($sessionKey);
            $lastLoadAt = $courseLibrary["lastLoadAt"];
        }

        //为了性能，最后更新时间，60秒只读取一次
        if (empty($lastLoadAt) || ($currentTime - $lastLoadAt) > 0) {
            // 排除推荐课程
            $rSessionKey = "RecommendCourseData";

            if (Yii::$app->session->has($rSessionKey)) {
                $recommendCourse = Yii::$app->session->get($rSessionKey);
            }

            if ($recommendCourse) {
                $recommendData = $recommendCourse["data"];
                $kids = TArrayHelper::get_array_key($recommendData, 'kid');
            }

            $service = new CourseService();

            $data = $service->getNewCoursesList(3, false, $kids);

            $courseLibrary["lastLoadAt"] = $currentTime;
            $courseLibrary["data"] = $data;

            Yii::$app->session->set($sessionKey, $courseLibrary);
        } else {
            $data = $courseLibrary["data"];
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