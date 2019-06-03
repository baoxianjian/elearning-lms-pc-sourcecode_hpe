<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/10
 * Time: 0:43
 */

namespace frontend\widgets;

use common\services\framework\CompanyMenuService;
use common\services\framework\UserService;
use common\services\learning\CourseService;
use components\widgets\BaseWidget;
use Yii;

class UserPanel extends BaseWidget
{
    private $view = '@frontend/views/widget/user-panel.php';

    public $params = [];

    public function init()
    {
        $currentTime = time();
        $sessionKey = "UserCourseCountData";
        $userCourseCount = [];
        $lastLoadAt = null;

        $uid = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;
        if (Yii::$app->session->has($sessionKey))
        {
            $userCourseCount = Yii::$app->session->get($sessionKey);
            $lastLoadAt = $userCourseCount["lastLoadAt"];
        }

        //为了性能，最后更新时间，30秒只读取一次
        if (empty($lastLoadAt) || ($currentTime - $lastLoadAt) > 30 ) {

            $service = new CourseService();
            $data = $service->getCourseStatusCount($uid);

            $userCourseCount["lastLoadAt"] = $currentTime;
            $userService = new UserService();

            $data['integral'] = $userService->getUserIntegral($uid, $companyId);

            $userCourseCount["data"] = $data;

            Yii::$app->session->set($sessionKey,$userCourseCount);
        }
        else {
            $data = $userCourseCount["data"];
        }

        $companyMenuService = new CompanyMenuService();
        $menu = $companyMenuService->getCompanyMenuByType($companyId,"tool-box");

        $this->params['menu'] = $menu;

        $this->params['reg_count'] = $data[0];
        $this->params['done_count'] = $data[1];
        $this->params['doing_count'] = $data[0] - $data[1];
        $this->params['integral'] = $data['integral'] ? $data['integral'] : 0;

        $this->params['thumb'] = Yii::$app->user->identity->getThumb();
    }

    public function run()
    {
        echo $this->renderFile($this->view, $this->params);
    }
}