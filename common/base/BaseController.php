<?php

namespace common\base;

use common\models\framework\FwCompany;
use common\models\framework\FwUser;
use common\services\framework\DictionaryService;
use common\services\framework\SystemInfoService;
use common\services\framework\UserService;
use common\helpers\TBaseHelper;
use Yii;
use yii\base\Exception;
use yii\base\Theme;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;

/**
 * BaseController
 */
class BaseController extends Controller
{
    public $defaultPageSize;

    const DELETE_FLAG_NO = "0";
    const DELETE_FLAG_YES = "1";

    const STATUS_FLAG_TEMP = "0";
    const STATUS_FLAG_NORMAL = "1";
    const STATUS_FLAG_STOP = "2";


	public function init()
	{
        if ($this->defaultPageSize == null)
        {
            if (isset(Yii::$app->params['defaultPageSize'])) {
                $this->defaultPageSize = Yii::$app->params['defaultPageSize'];
            }
            else {
                $this->defaultPageSize = 10;
            }
        }

        $language = TBaseHelper::getLanguage();
        Yii::$app->language = $language;

        $systemInfoService = new SystemInfoService();
        $systemInfoModel = $systemInfoService->getSystemInfo();

        $systemVersion = "";
        if (!empty($systemInfoModel)) {
            $systemVersion = $systemInfoModel->system_version . "." . $systemInfoModel->build_version;
        }
        Yii::$app->version = $systemVersion;
	}


	public function actions()
	{
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
			'captcha' => [
				'class' => 'yii\captcha\CaptchaAction',
				'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
			],
            'page' => [
                'class' => 'yii\web\ViewAction',
            ],
		];
	}

    public function beforeAction($action)
    {
        //为了性能，Ajax模式的Get请求，不做记录
        if (!Yii::$app->user->getIsGuest() && !(Yii::$app->request->isAjax && Yii::$app->request->isGet)) {
            $id = Yii::$app->user->getId();


            if (!empty(Yii::$app->request->getQueryParam("lang"))) {
                $sessionKey = "Language_" . $id;
                $language = Yii::$app->request->getQueryParam("lang");

                Yii::$app->session->set($sessionKey, $language);
            }

            $currentTime = time();
            $sessionKey = "lastActionAt";
            $lastActionAt = null;
            if (Yii::$app->session->has($sessionKey))
            {
                $lastActionAt = Yii::$app->session->get($sessionKey);
            }

            //为了性能，最后更新时间，2分钟只记录一次
            if (empty($lastActionAt) || ($currentTime - $lastActionAt) > 120 ) {
                Yii::$app->session->set($sessionKey,$currentTime);
                $login = false;


                $currentDate = date("Y-m-d", $currentTime);
                $user = Yii::$app->user->identity;
                $commonUserService = new UserService();
                if ($user->status == FwUser::STATUS_FLAG_STOP) {
                    Yii::$app->user->logout();
                } else if (!empty($user->valid_start_at) && $currentTime < $user->valid_start_at) {
                    $commonUserService->keepOffline($id);
                    Yii::$app->user->logout();
                } else if (!empty($user->valid_end_at) && $currentTime > $user->valid_end_at) {
                    $commonUserService->keepOffline($id);
                    Yii::$app->user->logout();
                } else {
                    if (!empty($user->last_action_at) && $user->last_action_at != 0) {
                        $lastDate = date("Y-m-d", $user->last_action_at);

                        if ($lastDate != $currentDate) {
                            $user->last_action_at = $currentTime;
                            FwUser::removeFromCacheByKid($id);
                            $login = true;
                        }
                    }
                    $commonUserService->keepOnline($id, $login);
                }
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * 优先取个人配置的默认主题(如果已经登陆的话)
     * 其次取企业的默认主题(如果已登陆的话取个人对应的企业,否则取二级域名对应的企业)
     * 最后取系统参数字典中配置的默认主题
     * 如果都没有设置,则不启用主题
     * @param $directory
     */
    public function setTheme($directory) {
        try {
            $theme = TBaseHelper::getTheme();

            if (!empty($theme)) {
                Yii::$app->view->theme = new Theme([
                    'pathMap' => ['@' . $directory . '/views' => '@' . $directory . '/themes/' . $theme]
                ]);
            }
        }
        catch (Exception $e) {
            $errMsg = $e->getMessage();
        }
    }

    public function render($view, $params = []) {
        $this->attachActionParameter($params);
        return parent::render($view, $params);
    }

    public function renderPartial($view, $params = []) {
        $this->attachActionParameter($params);
        return parent::renderPartial($view, $params);
    }


    public function renderAjax($view, $params = []) {
        $this->attachActionParameter($params);
        return parent::renderAjax($view, $params);
    }

    /**
     * 计算名字中含有横杠的情况
     * @param $name
     * @return string
     */
    private function calculateDashName($name) {
        $result = "";
        $pos = strrpos($name,"-");
        if ($pos === false) {
            $result = ucwords($name);
        }
        else {
            $temp = explode("-",$name);
            foreach ($temp as $single) {
                $result .= ucwords($single);
            }
        }

        return $result;
    }

    /**
     * 为action附加参数
     * @param $params
     */
    private function attachActionParameter(&$params) {
        //计算基础目录位置
        $catalog = str_replace("eln_", "", $this->systemFlag);
        $controllerId = $this->id;
        $pos = strrpos($controllerId,"/");


        //计算controller的目录层级
        if ($pos === false) {
            $tempClassName = $controllerId;
            $className = $this->calculateDashName($tempClassName);
        }
        else {
            $subCatalog = substr($controllerId, 0, $pos);
            $catalog = $catalog . "\\" . $subCatalog;
            $tempClassName = substr($controllerId, $pos + 1);
            $className = $this->calculateDashName($tempClassName);
        }

        $actionName = $this->calculateDashName($this->action->id);

        $withCache = true;//如果启用缓存,只要缓存里认为有,就不进行验证强制调用;否则强制不调用.
        $cacheKey = "AttActParam_CA_" . $catalog . "_CL_" . $className . "_AC_" .$actionName;

        $result = BaseActiveRecord::loadFromCache($cacheKey, $withCache, $hasCache);

        $baseService = @"\\common\\services\\interfaces\\controller\\" . $catalog . "\\" . $className . "Interface";
        $baseAction = "attach" . $actionName . "Params";

        if (!$hasCache) {
            $result = false;
            if(class_exists($baseService)){
                if(method_exists($baseService, $baseAction)){
                    $result = true;
                }
            }

            BaseActiveRecord::saveToCache($cacheKey, $result, null, BaseActiveRecord::DURATION_YEAR, $withCache);//因为代码不会随意变化,所以让它一年有效
        }


        if ($result) {
            $baseServiceInstance = new $baseService;
            $baseServiceInstance->$baseAction($params);
        }
    }
}
