<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/3/27
 * Time: 11:49
 */

namespace frontend\controllers;

use common\models\framework\FwUser;
use common\models\learning\LnCertification;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnCourseEnroll;
use common\models\learning\LnCourseware;
use common\models\learning\LnFiles;
use common\models\learning\LnInvestigationOption;
use common\models\learning\LnModRes;
use common\models\learning\LnRecord;
use common\models\learning\LnResourceDomain;
use common\models\learning\LnTeacher;
use common\models\message\MsTimeline;
use common\models\social\SoAudience;
use common\models\social\SoQuestion;
use common\models\social\SoRecord;
use common\models\social\SoShare;
use common\services\framework\PointRuleService;
use common\services\framework\TagService;
use common\services\learning\CourseEnrollService;
use common\services\learning\InvestigationService;
use common\services\learning\ResourceService;
use common\services\message\PushMessageService;
use common\services\social\AudienceCategoryService;
use common\services\social\AudienceManageService;
use common\base\BaseActiveRecord;
use common\crpty\AES;
use common\crpty\CryptErrorCode;
use common\services\learning\CourseService;
use common\models\message\MsMessage;
use common\models\social\SoUserAttention;
use common\services\framework\UserDomainService;
use common\helpers\TArrayHelper;
use common\helpers\TURLHelper;
use components\widgets\TPagination;
use frontend\base\BaseFrontController;
use common\services\social\CollectService;
use common\services\message\MessageService;
use common\services\social\QuestionService;
use common\services\learning\RecordService;
use common\services\common\SearchService;
use common\services\social\ShareService;
use common\services\learning\TeacherManageService;
use common\services\message\TimelineService;
use common\services\social\UserAttentionService;
use common\services\framework\UserService;
use common\services\learning\CertificationService;
use frontend\viewmodels\DownloadForm;
use frontend\viewmodels\message\SendMailForm;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use Yii;
use common\models\framework\FwDomain;
use common\models\framework\FwOrgnization;
use common\models\framework\FwPosition;
use common\models\framework\FwUserPosition;
use common\services\framework\DictionaryService;

class CommonController extends BaseFrontController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['except'] = ['download-code', 'pdf-view'];

        return $behaviors;
    }

    public function actionGetTodoDynamicMessage($current_time, $page, $time = null)
    {
        $id = Yii::$app->user->getId();

        $size = 10;
        $lineService = new TimelineService();

        $data = $lineService->getTodoByUid($id, $time, $size, $size < 1 ? 0 : ((int)$page - 1) * $size, $current_time);

        if ($page === '1' && ($data['data'] === null || count($data['data']) === 0)) {
            $service = new CourseService();

            $rSessionKey = "RecommendCourseData";

            if (Yii::$app->session->has($rSessionKey)) {
                $recommendCourse = Yii::$app->session->get($rSessionKey);
            }
            $kids1 = [];
            if ($recommendCourse) {
                $recommendData = $recommendCourse["data"];
                $kids1 = TArrayHelper::get_array_key($recommendData, 'kid');
            }

            $cSessionKey = "CourseLibraryData";

            if (Yii::$app->session->has($cSessionKey)) {
                $libCourse = Yii::$app->session->get($cSessionKey);
            }
            $kids2 = [];
            if ($libCourse) {
                $libCourse = $libCourse["data"];
                $kids2 = TArrayHelper::get_array_key($libCourse, 'kid');
            }
            // 排除推荐课程、课程库已显示的课程
            $kids = array_merge($kids1, $kids2);

            $data = $service->getNewCoursesList(12, false, $kids);

            return $this->renderAjax('todo-recommend', [
                'data' => $data,
            ]);
        } else {
            return $this->renderAjax('tab-student-course', [
                'data' => $data['data'],
            ]);
        }
    }

    public function actionGetTodoNewTimeline($start_time, $end_time, $time = null)
    {
        $this->layout = 'none';
        $uid = Yii::$app->user->getId();

        $lineService = new TimelineService();

        $data = $lineService->getTodoNewByUid($uid, $time, $start_time, $end_time);

        return $this->renderAjax('tab-student-course', [
            'data' => $data['data'],
        ]);
    }

    public function actionGetTodoTimelineOne($current_time, $page, $time = null)
    {
        $this->layout = 'none';
        $id = Yii::$app->user->getId();

        $size = 10;
        $lineService = new TimelineService();

        $data = $lineService->getTodoByUid($id, $time, 1, $size < 1 ? 0 : ((int)$page) * $size, $current_time);
        $result = null;
        if (!empty($data['data']) && count($data['data']) === 1) {
            $result = $data['data'][0];
        }

        return $this->renderAjax('tab-student-course-one', [
            'data' => $result,
        ]);
    }

    public function actionGetNewsDynamic($current_time, $page)
    {
        $this->layout = 'none';
        $uid = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;
        $createdTime = Yii::$app->user->identity->created_at;

        $size = 10;
        $lineService = new TimelineService();

        $data = $lineService->getNewsByUid($uid, $companyId, $createdTime, $size, $size < 1 ? 0 : ((int)$page - 1) * $size, $current_time);

        return $this->renderAjax('tab-student-news', [
            'data' => $data,
        ]);
    }

    public function actionGetNewsNewTimeline($start_time, $end_time)
    {
        $this->layout = 'none';
        $uid = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;

        $lineService = new TimelineService();

        $data = $lineService->getNewsNewByUid($uid, $companyId, $start_time, $end_time);

        return $this->renderAjax('tab-student-news', [
            'data' => $data,
        ]);
    }

    public function actionGetSocialDynamicMessage($current_time, $page)
    {
        $this->layout = 'none';
        $id = Yii::$app->user->getId();

        $size = 10;
        $lineService = new TimelineService();

        $data = $lineService->getSocialByUid($id, $size, $size < 1 ? 0 : ((int)$page - 1) * $size, $current_time);

        return $this->renderAjax('tab-student-social', [
            'data' => $data['data'],
        ]);
    }

    public function actionGetSocialNewTimeline($start_time, $end_time)
    {
        $this->layout = 'none';
        $id = Yii::$app->user->getId();

        $lineService = new TimelineService();

        $data = $lineService->getSocialNewByUid($id, $start_time, $end_time);

        return $this->renderAjax('tab-student-social', [
            'data' => $data['data'],
        ]);
    }

    public function actionGetQaDynamicMessage($current_time, $page)
    {
        $this->layout = 'none';
        $id = Yii::$app->user->getId();

        $size = 10;
        $lineService = new TimelineService();

        $data = $lineService->getQaByUid($id, $size, $size < 1 ? 0 : ((int)$page - 1) * $size, $current_time);

        return $this->renderAjax('tab-student-question', [
            'data' => $data['data'],
        ]);
    }

    public function actionGetQaNewTimeline($start_time, $end_time)
    {
        $this->layout = 'none';
        $id = Yii::$app->user->getId();

        $lineService = new TimelineService();

        $data = $lineService->getQaNewByUid($id, $start_time, $end_time);

        return $this->renderAjax('tab-student-question', [
            'data' => $data['data'],
        ]);
    }

    public function actionGetTimelineNewData($type, $time)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $uid = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;

        $service = new TimelineService();

        if ($type === MsTimeline::TIMELINE_TYPE_NEWS) {
            $data = $service->GetNewsNewDataCount($uid, $companyId, $time);
        } else {
            $data = $service->GetNewDataCount($uid, $type, $time);
        }
        return ['result' => 'success', 'count' => $data];
    }

    public function actionPopMessageCourse()
    {
//        $this->layout = 'none';
        $id = Yii::$app->user->getId();

        $msgService = new MessageService();
        $size = $this->defaultPageSize;
        $data = $msgService->getMessageAndPage($id, MsMessage::TYPE_TODO, $size);

        return $this->renderAjax('pop-message-course', $data);
    }

    public function actionPopMessageQuestion()
    {
//        $this->layout = 'none';
        $id = Yii::$app->user->getId();

        $msgService = new MessageService();
        $size = $this->defaultPageSize;
        $data = $msgService->getMessageAndPage($id, MsMessage::TYPE_QA, $size);

        return $this->renderAjax('pop-message-question', $data);
    }

    public function actionPopMessageNews()
    {
//        $this->layout = 'none';
        $uid = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;
        $createdTime = Yii::$app->user->identity->created_at;

        $msgService = new MessageService();
        $size = $this->defaultPageSize;
        $data = $msgService->getNewsMessageByUid($uid, $companyId, $createdTime, $size);

        return $this->renderAjax('pop-message-news', $data);
    }

    public function actionPopMessageSocial()
    {
//        $this->layout = 'none';
        $id = Yii::$app->user->getId();

        $msgService = new MessageService();
        $size = $this->defaultPageSize;
        $data = $msgService->getMessageAndPage($id, MsMessage::TYPE_SOCIAL, $size);

        return $this->renderAjax('pop-message-social', $data);
    }

    public function actionMarkRead()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $uid = Yii::$app->user->getId();
        $id = Yii::$app->request->post('id');

        $ids = explode(',', $id);
        // var_dump($ids);

        $service = new MessageService();

        if ($service->markRead($uid, $ids)) {
            $sessionKey = "MessageCountData";
            Yii::$app->session->remove($sessionKey);
            return ['result' => 'success'];
        } else {
            return ['result' => 'failure'];
        }
    }

    public function actionNewsMarkRead()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $uid = Yii::$app->user->getId();
        $id = Yii::$app->request->post('id');

        $ids = explode(',', $id);

        $service = new MessageService();

        if ($service->NewsMarkRead($uid, $ids)) {
            $sessionKey = "MessageCountData";
            Yii::$app->session->remove($sessionKey);
            return ['result' => 'success'];
        } else {
            return ['result' => 'failure'];
        }
    }

    public function actionSearchObject()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $uid = Yii::$app->user->getId();
        $key = Yii::$app->request->get('key');

        $service = new SearchService();
        $data = $service->searchByName($key, true);

        return $data;
    }

    public function actionSearchPeople($format = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $uid = Yii::$app->user->getId();

        if (!empty($format)) {
            $key = trim(Yii::$app->request->get('q'));
        } else {
            $key = trim(Yii::$app->request->get('key'));
        }

        if (empty($key)) {
            return '';
        }

        $service = new SearchService();
        $data = $service->SearchPeopleByName($key, true);

        $data = array('results' => $data);

        return $data;
    }

    public static function actionAttentionUser()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->user->getId();
        $uid = Yii::$app->request->post('uid');
        $model = new SoUserAttention();
        $model->user_id = $id;
        $model->attention_id = $uid;

        $service = new UserAttentionService();

        if ($service->IsRelationshipExist($model)) {
            $service->StopRelationship($model);
            return ['result' => 'success', 'status' => 0];
        } else {
            // 增加积分
            $pointRuleService = new PointRuleService();
            $pointResult = $pointRuleService->curUserCheckActionForPoint('Attention-People', 'Learning-Portal', $uid);

            $service->startRelationship($model);

            $msgService = new MessageService();
            $msgService->SendByCarePerson($uid);

            if ($pointResult['result'] === '1') {
                return ['result' => 'success', 'status' => '1', 'pointResult' => $pointResult];
            } else {
                return ['result' => 'success', 'status' => '1', 'pointResult' => false];
            }
        }
    }

    public function actionSetSubscribeSettingStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $uid = Yii::$app->user->getId();
        $typeId = Yii::$app->request->post('type_id');
        $status = Yii::$app->request->post('status');

        $service = new UserService();

        if ($service->setSubscribeSettingStatus($uid, $typeId, $status)) {
            $cacheKey = "SubscribeSettingList_" . $uid;
            Yii::$app->cache->delete($cacheKey);
            return ['result' => 'success'];
        } else {
            return ['result' => 'other', 'message' => Yii::t('frontend','config_wrong')];
        }
    }

    public function actionSearchCourse()
    {
        $this->layout = 'none';
        $user_id = Yii::$app->user->getId();
        $keyword = Yii::$app->request->getQueryParam('keyword');
        $domain_id = Yii::$app->request->getQueryParam('domain');
        $selected = Yii::$app->request->getQueryParam('selected');

        $company_id = Yii::$app->user->identity->company_id;

        $pageNo = Yii::$app->request->getQueryParam('page');
        $currentTime = time();
        //域搜索
        $domain_ids = array();
        $domain_ids[] = $domain_id;

        $domain = FwDomain::findOne($domain_id);

        $domainService = new UserDomainService();
        $domain_list = $domainService->getShareDomain($company_id, $domain->parent_domain_id);

        if ($domain_list !== null && count($domain_list) > 0) {
            foreach ($domain_list as $val) {
                $domain_ids[] = $val->kid;
            }
        }

        $service = new SearchService();
        $result = $service->SearchCourseByKeyword($user_id, $domain_ids, $keyword, $selected, LnCourse::COURSE_TYPE_ONLINE);

        return $this->render('search-course-result', [
            'data' => $result['data'],
            'page_id' => 'page1',
            'pages' => $result['page'],
            'pageNo' => $pageNo,
        ]);
    }


    public function actionMessageMenu()
    {
        $currentTime = time();
        $sessionKey = "MessageCountData";
        $messageCount = [];
        $lastLoadAt = null;
        if (Yii::$app->session->has($sessionKey)) {
            $messageCount = Yii::$app->session->get($sessionKey);
            $lastLoadAt = $messageCount["lastLoadAt"];
        }

        //为了性能，最后更新时间，60秒只读取一次
        if (empty($lastLoadAt) || ($currentTime - $lastLoadAt) > 60) {
            $uid = Yii::$app->user->getId();
            $companyId = Yii::$app->user->identity->company_id;
            $createdTime = Yii::$app->user->identity->created_at;

            $service = new MessageService();
            $data = [];
            $data['todoCount'] = $courseMessageCount = $service->getMessageCountByUid($uid, MsMessage::TYPE_TODO);
            $data['qaCount'] = $qaMessageCount = $service->getMessageCountByUid($uid, MsMessage::TYPE_QA);
            $data['newsCount'] = $newsMessageCount = $service->getNewsMessageCountByUid($uid, $companyId, $createdTime);
            $data['socialCount'] = $socialMessageCount = $service->getMessageCountByUid($uid, MsMessage::TYPE_SOCIAL);

            $messageCount["lastLoadAt"] = $currentTime;
            $messageCount["data"] = $data;

            Yii::$app->session->set($sessionKey, $messageCount);
        } else {
            $data = $messageCount["data"];
        }

        $count = $data['todoCount'] + $data['qaCount'] + $data['newsCount'] + $data['socialCount'];

        return $this->renderAjax('message-menu', [
            'courseMessageCount' => $data['todoCount'],
            'qaMessageCount' => $data['qaCount'],
            'newsMessageCount' => $data['newsCount'],
            'socialMessageCount' => $data['socialCount'],
            'count' => $count
        ]);
    }

    public function actionUpload()
    {
        // $physicalPath = Yii::$app->basePath."/../upload/temp/";
        $physicalPath = rtrim(Yii::getAlias("@upload/temp/"), '/\\') . "/";
        $logicalPath = "/upload/temp/";
        if (!empty($_FILES)) {

            //得到上传的临时文件流
            $tempFile = $_FILES['myfile']['tmp_name'];

            $type = $_FILES['myfile']["type"];

            //得到文件原名
            $fileName = $_FILES["myfile"]["name"];
            $fileParts = pathinfo($_FILES['myfile']['name']);

            $fileError = $_FILES["myfile"]["error"];
            $fileSize = $_FILES["myfile"]["size"];

            //允许的文件后缀
            $fileTypes = array(
                'image/jpg',
                'image/jpeg',
                'image/png',
                'image/pjpeg',
                'image/gif',
                'image/bmp',
                'image/x-png');

            if ($fileError) {
                $info = Yii::t('common', 'upload_error');
//                $status=0;
//                $data='';
            } else if (!in_array($type, $fileTypes)) {
                $info = Yii::t('common', 'file_type_error');
//                $status=0;
//                $data='';
            } else {
                //最后保存服务器地址
                if (!is_dir($physicalPath)) {
                    mkdir($physicalPath);
                }

                $extension = 'jpg';

//                switch ($type)
//                {
//                    case "image/jpg":$extension="jpg";break;
//                    case "image/jpeg":$extension="jpg";break;
//                    case "image/pjpeg":$extension="jpg";break;
//                    case "image/x-png":$extension="png";break;
//                    case "image/png":$extension="png";break;
//                    case "image/gif":$extension="gif";break;
//                    case "image/bmp":$extension="bmp";break;
//                    case "image/x-ms-bmp":$extension="bmp";break;
//                    case "image/x-bmp":$extension="bmp";break;
//                }

                $newFileName = time() . "." . $extension;
                if (move_uploaded_file($tempFile, $physicalPath . $newFileName)) {
                    $src = $physicalPath . $newFileName;
                    list($width, $height) = getimagesize($physicalPath . $newFileName); //获取原图尺寸
                    //缩放尺寸
                    if ($width > 400) {
                        $newwidth = 400;
                        $newheight = (400 / $width) * $height;
                        $type = getimagesize($src)["mime"];

                        switch ($type) {
                            case "image/jpg":
                                $img_r = imagecreatefromjpeg($src);
                                break;
                            case "image/jpeg":
                                $img_r = imagecreatefromjpeg($src);
                                break;
                            case "image/pjpeg":
                                $img_r = imagecreatefromjpeg($src);
                                break;
                            case "image/x-png":
                                $img_r = imagecreatefrompng($src);
                                break;
                            case "image/png":
                                $img_r = imagecreatefrompng($src);
                                break;
                            case "image/gif":
                                $img_r = imagecreatefromgif($src);
                                break;
                            case "image/bmp":
                                $img_r = $this->ImageCreateFromBMP($src);
                                break;
                            case "image/x-ms-bmp":
                                $img_r = $this->ImageCreateFromBMP($src);
                                break;
                            case "image/x-bmp":
                                $img_r = $this->ImageCreateFromBMP($src);
                                break;
                            default:
                                $img_r = imagecreatefromjpeg($src);
                                break;
                        }
                        $dst_im = imagecreatetruecolor($newwidth, $newheight);

                        imagecopyresampled($dst_im, $img_r, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                        imagejpeg($dst_im, $src, 100); //输出压缩后的图片
                        imagedestroy($dst_im);
                        imagedestroy($img_r);
                    }

                    $info = $logicalPath . $newFileName;
//                    $status = 1;
//                    $data = array('path' => Yii::$app->basePath, 'file' => $physicalPath . $newFileName);
                } else {
                    $info = Yii::t('common', 'upload_error');
//                    $status = 0;
//                    $data = '';
                }
            }
            echo $info;
        }

    }

    /**
     * @裁剪头像
     */
    public function actionCutPic()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $yiiBasePath = Yii::$app->basePath . "/..";
            $srcLogicalPath = "/upload/temp/";
            $destPhysicalPath = Yii::$app->basePath . "/../upload/thumb/";
            $destLogicalPath = "/upload/thumb/";

            $targ_w = $targ_h = 150;
            $jpeg_quality = 100;
            $src = Yii::$app->request->post('f');
            $src = $yiiBasePath . $src;//真实的图片路径

            $type = getimagesize($src)["mime"];

            $extension = "jpg";
            switch ($type) {
                case "image/jpg":
                    $img_r = imagecreatefromjpeg($src);
                    break;
                case "image/jpeg":
                    $img_r = imagecreatefromjpeg($src);
                    break;
                case "image/pjpeg":
                    $img_r = imagecreatefromjpeg($src);
                    break;
                case "image/x-png":
                    $img_r = imagecreatefrompng($src);
                    break;
                case "image/png":
                    $img_r = imagecreatefrompng($src);
                    break;
                case "image/gif":
                    $img_r = imagecreatefromgif($src);
                    break;
                case "image/bmp":
                    $img_r = $this->ImageCreateFromBMP($src);
                    break;
                case "image/x-ms-bmp":
                    $img_r = $this->ImageCreateFromBMP($src);
                    break;
                case "image/x-bmp":
                    $img_r = $this->ImageCreateFromBMP($src);
                    break;
                default:
                    $img_r = imagecreatefromjpeg($src);
                    break;
            }

            // $img_r = imagecreatefrompng($src);
            $ext = $destLogicalPath . time() . "." . $extension;//生成的引用路径
            $dst_r = ImageCreateTrueColor($targ_w, $targ_h);

            imagecopyresampled($dst_r, $img_r, 0, 0, Yii::$app->request->post('x'), Yii::$app->request->post('y'),
                $targ_w, $targ_h, Yii::$app->request->post('w'), Yii::$app->request->post('h'));

            $img = $yiiBasePath . $ext;//真实的图片路径

            if (imagejpeg($dst_r, $img, $jpeg_quality)) {
                $id = Yii::$app->user->getId();
                //更新用户头像
                $user = FwUser::findOne($id);
                $user->thumb = $ext;
                if ($user->save()) {

                    $arr['status'] = 1;
                    $arr['data'] = $ext;
                    $arr['info'] = Yii::t('common', 'crop_ok');
                } else {
                    $arr['status'] = 0;
                }
//                echo json_encode($arr);
                return $arr;
            } else {
                $arr['status'] = 0;
//                echo json_encode($arr);
                return $arr;
            }
        }
    }

    public function actionClearPic()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $id = Yii::$app->user->getId();
            //更新用户头像
            $user = FwUser::findOne($id);
            $user->thumb = null;
            $user->save();

            return ['result' => 'success'];
        }
    }

    private function ImageCreateFromBMP($filePath)
    {
        $fileHandle = fopen($filePath, 'rb');
        if (empty($fileHandle)) {
            return false;
        }

        $file = unpack(
            'vfile_type/Vfile_size/Vreserved/Vbitmap_offset',
            fread($fileHandle, 14)
        );

        if ($file['file_type'] != 19778) {
            return false;
        }

        $bmp = unpack(
            'Vheader_size/Vwidth/Vheight/vplanes/' .
            'vbits_per_pixel/Vcompression/Vsize_bitmap/' .
            'Vhoriz_resolution/Vvert_resolution/Vcolors_used/Vcolors_important',
            fread($fileHandle, 40)
        );
        $bmp['colors'] = pow(2, $bmp['bits_per_pixel']);
        if ($bmp['size_bitmap'] == 0) {
            $bmp['size_bitmap'] = $file['file_size'] - $file['bitmap_offset'];
        }
        $bmp['bytes_per_pixel'] = $bmp['bits_per_pixel'] / 8;
        $bmp['bytes_per_pixel2'] = ceil($bmp['bytes_per_pixel']);
        $bmp['decal'] = $bmp['width'] * $bmp['bytes_per_pixel'] / 4;
        $bmp['decal'] -= floor($bmp['width'] * $bmp['bytes_per_pixel'] / 4);
        $bmp['decal'] = 4 - (4 * $bmp['decal']);
        if ($bmp['decal'] == 4) {
            $bmp['decal'] = 0;
        }

        $palette = array();
        if ($bmp['colors'] < 16777216) {
            $palette = unpack(
                'V' . $bmp['colors'],
                fread($fileHandle, $bmp['colors'] * 4)
            );
        }
        $image = fread($fileHandle, $bmp['size_bitmap']);
        $vide = chr(0);
        $res = imagecreatetruecolor($bmp['width'], $bmp['height']);
        $p = 0;

        $y = $bmp['height'] - 1;
        while ($y >= 0) {
            $x = 0;
            while ($x < $bmp['width']) {
                if ($bmp['bits_per_pixel'] == 24) {
                    $color = unpack('V', substr($image, $p, 3) . $vide);
                } else if ($bmp['bits_per_pixel'] == 16) {
                    $color = unpack('n', substr($image, $p, 2));
                    $color[1] = $palette[$color[1] + 1];
                } else if ($bmp['bits_per_pixel'] == 8) {
                    $color = unpack('n', $vide . substr($image, $p, 1));
                    $color[1] = $palette[$color[1] + 1];
                } else if ($bmp['bits_per_pixel'] == 4) {
                    $color = unpack('n', $vide . substr($image, floor($p), 1));
                    if (($p * 2) % 2 == 0) {
                        $color[1] = ($color[1] >> 4);
                    } else {
                        $color[1] = ($color[1] & 0x0F);
                    }
                    $color[1] = $palette[$color[1] + 1];
                } else if ($bmp['bits_per_pixel'] == 1) {
                    $color = unpack('n', $vide . substr($image, floor($p), 1));
                    switch (($p * 8) % 8) {
                        case  0:
                            $color[1] = ($color[1] >> 7);
                            break;
                        case  1:
                            $color[1] = ($color[1] & 0x40) >> 6;
                            break;
                        case  2:
                            $color[1] = ($color[1] & 0x20) >> 5;
                            break;
                        case  3:
                            $color[1] = ($color[1] & 0x10) >> 4;
                            break;
                        case  4:
                            $color[1] = ($color[1] & 0x8) >> 3;
                            break;
                        case  5:
                            $color[1] = ($color[1] & 0x4) >> 2;
                            break;
                        case  6:
                            $color[1] = ($color[1] & 0x2) >> 1;
                            break;
                        case  7:
                            $color[1] = ($color[1] & 0x1);
                            break;
                    }
                    $color[1] = $palette[$color[1] + 1];
                } else {
                    return false;
                }
                imagesetpixel($res, $x, $y, $color[1]);
                $x++;
                $p += $bmp['bytes_per_pixel'];
            }
            $y--;
            $p += $bmp['decal'];
        }
        fclose($fileHandle);
        return $res;
    }

    /**
     * 通用文件下载
     */
    public function actionDownload()
    {
        $model = new DownloadForm();

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $obj_model = SoRecord::findOne($model->id);

            $file_name = $obj_model->attach_original_filename;
            $file_path = $_SERVER['DOCUMENT_ROOT'] . $obj_model->attach_url;

            if (!file_exists($file_path)) { //检查文件是否存在
                echo Yii::t('frontend', 'file_null');
                exit;
            } else {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . urlencode($file_name));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file_path));
                ob_clean();
                flush();
                readfile($file_path);
                exit;
            }
        }
    }

    public function actionPdfView($id, $hash)
    {
        $current_time = time();
        $referer = $_SERVER["HTTP_REFERER"];

        if (($referer !== null && !strpos($referer, 'pdf.worker.js')) || $hash === null || $hash === '') {
            exit;
        }

        $aes = new AES();
        $de_hash = $aes->decrypt($hash);
        if ($de_hash[0] !== CryptErrorCode::OK) {
            exit;
        }
        $data = base64_decode($de_hash[1]);
        $data_id = substr($data, 0, 36);
        $time = substr($data, 39);
        $diff = $current_time - intval($time);

        if ($id !== $data_id || $diff > 60) {
            exit;
        }

        $file = LnFiles::findOne($id);
        if ($file === null || substr($file->file_path, -3) !== 'pdf') {
            exit;
        }
        $file_name = $file->file_name;
        $file_path = $_SERVER['DOCUMENT_ROOT'] . $file->file_path;

        if (!file_exists($file_path)) { //检查文件是否存在
            echo "error:file not found!";
            exit;
        } else {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . urlencode($file_name));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            ob_clean();
            flush();
            readfile($file_path);
            exit;
        }
    }

    public function actionSocialShare()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = Yii::$app->request->post('id');

        $uid = Yii::$app->user->getId();

        if ($id != null || $id != '') {
            $service = new UserAttentionService();
            //获取所有关注对象
            $user_attention = $service->getAllUserId($uid);

            $timelineService = new TimelineService();
            // 推送动态
            $timelineService->PushTimelineByID($uid, $user_attention, $id);

            $messageService = new MessageService();
            // 推送消息
            $messageService->PushMessageByTimelineShare($uid, $user_attention, $id);

            // 保存分享记录
            $shareService = new ShareService();
            $shareService->SocialShare($uid, $user_attention, $id);
            return ['result' => 'success'];
        } else {
            return ['result' => 'failure'];
        }
    }

    public function actionGetUser($format = '', $course_id = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $companyId = Yii::$app->user->identity->company_id;
        $userService = new UserService();
        if (!empty($format)) {
            $word = trim(Yii::$app->request->get('q'));
        } else {
            $word = trim(Yii::$app->request->post('val'));
        }

        if (empty($word)) {
            return '';
        }

        $names = $userService->getLikeNameByValue($companyId, $word, $course_id);
        if (!empty($format) && !empty($names)) {
            $new_array = array();
            $dictionaryService = new DictionaryService();
            foreach ($names as $item) {
                $orgnization_name = FwOrgnization::findOne($item->orgnization_id)->orgnization_name;
                $location = $dictionaryService->getDictionaryNameByCode("location", $item->location);
                $new_array[] = array('kid' => $item->kid, 'title' => $item->getDisplayName(), 'location' => $location, 'orgnization' => $orgnization_name);
            }
            $names = array('results' => $new_array);
        }
        return $names ? $names : '';
    }

    /**
     * 课程动态查询讲师
     */
    public function actionGetTeacher($format = '', $companyId = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        //if (empty($val)) return ['result' => 'empty'];
        $teacherService = new TeacherManageService();
        if (!empty($format)) {
            $val = Yii::$app->request->get('q');
        } else {
            $val = Yii::$app->request->get('val');
        }
        $data = $teacherService->courseSearchTeacher($val, null, null, $companyId);

        if (!empty($format)) {
            $back = array();
            if (!empty($data)) {
                $lnTeacher = new LnTeacher();
                foreach ($data as $items) {
                    $type = $lnTeacher->getTeacherTypes($items['teacher_type']);
                    $back[] = array('kid' => $items['kid'], 'title' => $items['teacher_name'] . '(' . $items['email'] . ')_'.$type);
                }
                $result = array('results' => $back);
            } else {
                $result = $back;
            }
        } else {
            $result = ['result' => 'ok', 'data' => $data];
        }

        return $result;
    }

    /**
     * 课程动态查询证书
     */
    public function actionGetCertification($format = '', $companyId = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        //if (empty($val)) return ['result' => 'empty'];
        $request = Yii::$app->request->get();
        $param = [];
        if (isset($request['keyword'])) {
            $param['keyword'] = $request['keyword'];
        }
        if ($format) {
            $param['keyword'] = $request['q'];
        }
        if ($request['course_type'] == LnCourse::COURSE_TYPE_ONLINE) {
            $param['is_auto_certify'] = LnCertification::IS_AUTO_CERTIFY_YES;
        } else if ($request['course_type'] == LnCourse::COURSE_TYPE_FACETOFACE) {
            $param['is_auto_certify'] = LnCertification::IS_AUTO_CERTIFY_NO;
        }
        $param['companyId'] = $companyId;
        $service = new CertificationService();
        $data = $service->courseSearchCertification($param);
        if (!empty($format)) {
            $back = array();
            if (!empty($data)) {
                foreach ($data as $items) {
                    $back[] = array('kid' => $items['kid'], 'title' => $items['certification_name']);
                }
                $result = array('results' => $back);
            } else {
                $result = $back;
            }

        } else {
            $result = ['result' => 'ok', 'data' => $data];
        }
        return $result;
    }

    public function actionShareRecord()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $uid = Yii::$app->user->getId();
        $rid = Yii::$app->request->post('rid');
        if ($rid === null || $rid === '') {
            return ['result' => 'other', 'message' => Yii::t('frontend','record_id_null')];
        }
        $model = SoRecord::findOne($rid);

        if ($model !== null) {
            $share = new SoShare();
            $share->title = $model->title;
            $share->content = $model->content;
            $share->type = SoShare::SHARE_TYPE_RECORD;
            $share->obj_id = $model->kid;
            $share->user_id = $uid;
            $share->needReturnKey = true;
            $share->save();

            $attentionService = new UserAttentionService();
            //获取所有关注对象
            $user_attention = $attentionService->getAllUserId($uid);

            if (isset($user_attention) && $user_attention != null) {
                $user_array = ArrayHelper::map($user_attention, 'user_id', 'user_id');
                $user_array = array_keys($user_array);
            }
            ShareService::ShareUserSave($share, $user_array);

            $timelineService = new TimelineService();
            // 推送动态
            $timelineService->PushTimelineByShare($uid, $user_attention, $model);

            $messageService = new MessageService();
            // 推送消息
            $messageService->PushMessageByShare($uid, $user_attention, $model);

            return ['result' => 'success'];
        } else {
            return ['result' => 'failure'];
        }
    }

    public function actionPathShare()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $uid = Yii::$app->user->getId();
        $model = new SoShare();
        $model->user_id = $uid;
        $model->needReturnKey = true;
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $attentionService = new UserAttentionService();
            //获取所有关注对象
            $attentionUserList = $attentionService->getAllUserId($uid);
            if (isset($attentionUserList) && $attentionUserList != null && count($attentionUserList) > 0) {
                $attentionUserIdList = TArrayHelper::get_array_key($attentionUserList, 'user_id');

                ShareService::ShareUserSave($model, $attentionUserIdList);

                $timelineService = new TimelineService();
                $messageService = new MessageService();

                if ($model->type === SoShare::SHARE_TYPE_COURSE) {
                    // 时间轴添加
                    $timelineService->pushByShareCourse($uid, $attentionUserIdList, $model->obj_id, $model->content);

                    // 消息推送
                    $messageService->pushMessageByCourseShare($uid, $model, $attentionUserIdList);
                } elseif ($model->type === SoShare::SHARE_TYPE_QUESTION) {
                    SoQuestion::addFieldNumber($model->obj_id, "share_num");

                    // 时间轴添加
                    $timelineService->pushByShareQuestion($uid, $attentionUserIdList, $model->obj_id, $model->content);

                    // 消息推送
                    $messageService->pushMessageByQuestionShare($uid, $model, $attentionUserIdList);
                }
            }
            return ['result' => 'success'];
        } else {
            $errors = array_values($model->getErrors());
            $message = '';
            for ($i = 0; $i < count($errors); $i++) {
                $message .= $errors[$i][0] . '<br />';
            }

            return ['result' => 'other', 'message' => $message];
        }
    }

    public function actionDeleteTimeline()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $uid = Yii::$app->user->getId();
            $id = Yii::$app->request->post('id');
            $model = MsTimeline::findOne($id);
            if (empty($model)) {
                return ['result' => 'success'];
            } else {
                if ($model->owner_id !== $uid) {
                    return ['result' => 'other', 'message' => Yii::t('frontend','operation_limited')];
                }
                if ($model->delete()) {
                    return ['result' => 'success'];
                } else {
                    return ['result' => 'failure'];
                }
            }
        }
    }

    public function actionStickTimeline()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $uid = Yii::$app->user->getId();
            $id = Yii::$app->request->post('id');
            $model = MsTimeline::findOne($id);
            if (empty($model)) {
                return ['result' => 'failure'];
            } else {
                if ($model->owner_id !== $uid) {
                    return ['result' => 'other', 'message' => Yii::t('frontend','operation_limited')];
                }
                $service = new TimelineService();
                if ($service->StickTimeline($uid, $model)) {
                    return ['result' => 'success'];
                } else {
                    return ['result' => 'failure'];
                }
            }
        }
    }

    public function actionCancelStickTimeline()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $uid = Yii::$app->user->getId();
            $id = Yii::$app->request->post('id');
            $model = MsTimeline::findOne($id);
            if (empty($model)) {
                return ['result' => 'failure'];
            } else {
                if ($model->owner_id !== $uid) {
                    return ['result' => 'other', 'message' => Yii::t('frontend','operation_limited')];
                }
                $model->is_stick = 0;
                if ($model->save()) {
                    return ['result' => 'success'];
                } else {
                    return ['result' => 'failure'];
                }
            }
        }
    }

    public function actionPersonScoreList($cid, $self = true, $showHomework = false, $uid = null)
    {
        $user_id = $uid ? $uid : Yii::$app->user->getId();

        $courseModel = LnCourse::findOne($cid);

        if (!$courseModel) {
            exit();
        }

        $resourceService = new ResourceService();
        $result = $resourceService->getCourseResScoreDetail($cid, $user_id);

        return $this->renderAjax('person-score-list', [
            'courseModel' => $courseModel,
            'courseRes' => $result['data'],
            'pages' => $result['pages'],
            'scoreUid' => $user_id,
            'self' => $self,
            'showHomework' => $showHomework,
        ]);
    }

    public function actionPersonScoreDetail($courseId, $modResId)
    {
        $this->layout = 'none';

        $pageSize = $this->defaultPageSize;
        $courseService = new CourseService();

        $result = $courseService->GetEnrollUserList($courseId, $modResId);

        $count = $result->count();
        $pages = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);

        $datas = $result->offset($pages->offset)->limit($pages->limit)->all();

        $modResModel = LnModRes::findOne($modResId);
        if (!empty($modResModel)) {
            $isCourseware = $modResModel->res_type == LnModRes::RES_TYPE_COURSEWARE ? true : false;

            if ($isCourseware) {
                $itemName = LnCourseware::findOne($modResModel->courseware_id)->courseware_name;
            } else {
                $itemName = LnCourseactivity::findOne($modResModel->courseactivity_id)->activity_name;

            }
        }

        return $this->render('item-complete-info', [
            'datas' => $datas,
            'pages' => $pages,
            'courseId' => $courseId,
            'modResId' => $modResId,
            'itemName' => $itemName
        ]);
    }

    //获取调查详情
    public function actionPersonQuestionDetail($courseid, $modresid, $inkid, $uid = null)
    {
        $user_id = $uid ? $uid : Yii::$app->user->getId();

        $InvestigationService = new InvestigationService();
        $result = $InvestigationService->getQuestionairedetail($inkid, $user_id, $courseid, $modresid);

        foreach ($result as $k => $v) {
            $option[$k]['investigation_question_id'] = $v['investigation_question_id'];
            $option[$k]['investigation_option_id'] = $v['investigation_option_id'];
            $resultdata[$v['investigation_question_id']] = $v;
        }
        //  $questionaire[$k]['option_title'] = LnInvestigationOption::findOne($v['investigation_option_id'])->option_title;

        foreach ($option as $key => $val) {
            $resultdata[$val['investigation_question_id']]['sequence_number'][] = LnInvestigationOption::findOne($val['investigation_option_id'])->sequence_number;
            //	$resultdata[$val['investigation_question_id']]['option_title'][]=LnInvestigationOption::findOne($val['investigation_option_id'])->option_title;
        }
        $data = $InvestigationService->getInvestigation($inkid);

        return $this->renderAjax('person-question-detail', [
            'data' => $data,
            'resultdata' => $resultdata,
            'courseid' => $courseid,
            'modresid' => $modresid,
            'inkid' => $inkid
        ]);
    }

    public function actionGetUrlTitle($url)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $title = TURLHelper::getTitleByUrl($url);

        return ['result' => $title];
    }

    public function actionDownloadCode()
    {
        $hostUrl = Yii::$app->request->getHostInfo();
        $downloadUrl = $hostUrl . '/install/index.html';

        echo $this->qrCode($downloadUrl);
    }

    private function qrCode($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4, $saveandprint = false)
    {
        $enc = \QRencode::factory($level, $size, $margin);
        return $enc->encodePNG($text, $outfile, $saveandprint = false);
    }

    public function actionShareWeb($title = null, $url = null)
    {
        $this->layout = false;
        return $this->render('share-web', [
            'title' => $title,
            'url' => $url,
        ]);
    }

    public function actionJumpUrl($url, $objId = null, $type = null)
    {
        if ($objId !== null && $type !== null) {
            $code = '';
            switch ($type) {
                case SoRecord::RECORD_TYPE_WEB:
                    $code = 'Open-Shared-Page';
                    break;
                case SoRecord::RECORD_TYPE_EVENT:
                    $code = 'Open-Shared-Event';
                    break;
                case SoRecord::RECORD_TYPE_BOOK:
                    $code = 'Open-Shared-Book';
                    break;
            }

            $uid = Yii::$app->user->getId();

            $flag = true;

            $recordModel = SoRecord::findOne($objId);

            if ($recordModel) {
                if ($recordModel->user_id === $uid) {
                    $flag = false;
                }
            }

            if ($flag) {
                // 增加积分
                $pointRuleService = new PointRuleService();
                $pointRuleService->curUserCheckActionForPoint($code, 'Learning-Portal', $objId);
            }
        }

        $this->redirect($url);
    }

    public function actionGetDownloadPoint($objId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = SoRecord::findOne($objId);

        $code = '';
        switch ($model->record_type) {
            case SoRecord::RECORD_TYPE_WEB:
                $code = 'Download-Page';
                break;
            case SoRecord::RECORD_TYPE_EVENT:
                $code = 'Download-Event';
                break;
            case SoRecord::RECORD_TYPE_BOOK:
                $code = 'Download-Book';
                break;
            case SoRecord::RECORD_TYPE_EXP:
                $code = 'Download-Experience';
                break;
        }

        // 增加积分
        $pointRuleService = new PointRuleService();
        $pointResult = $pointRuleService->curUserCheckActionForPoint($code, 'Learning-Portal', $objId);

        if ($pointResult['result'] === '1') {
            return ['result' => 'success', 'pointResult' => $pointResult];
        } else {
            return ['result' => 'success', 'pointResult' => false];
        }
    }

    public function actionGetOpenUrlPoint($objId = null, $type = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($objId !== null && $type !== null) {
            $code = '';
            switch ($type) {
                case SoRecord::RECORD_TYPE_WEB:
                    $code = 'Open-Shared-Page';
                    break;
                case SoRecord::RECORD_TYPE_EVENT:
                    $code = 'Open-Shared-Event';
                    break;
                case SoRecord::RECORD_TYPE_BOOK:
                    $code = 'Open-Shared-Book';
                    break;
            }

            $uid = Yii::$app->user->getId();

            $recordModel = SoRecord::findOne($objId);

            if ($recordModel) {
                if ($recordModel->user_id === $uid) {
                    return ['result' => 'success'];
                } else {
                    // 增加积分
                    $pointRuleService = new PointRuleService();
                    $pointResult = $pointRuleService->curUserCheckActionForPoint($code, 'Learning-Portal', $objId);

                    if ($pointResult['result'] === '1') {
                        return ['result' => 'success', 'pointResult' => $pointResult];
                    } else {
                        return ['result' => 'success', 'pointResult' => false];
                    }
                }
            }
        }
        return ['result' => 'success'];
    }

    public function actionGetTagData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;

        $service = new TagService();
        $data = $service->getTagsByCategoryCode('course', $companyId);

        $userTagList = $service->getTagListByUserId($companyId, 'interest', $userId);

        return ['userTags' => $userTagList, 'publicTags' => $data];

    }

    /**
     * 课程分享
     * @return array
     */
    public function actionCourseShare()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isPost && Yii::$app->request->post()) {
            $userId = Yii::$app->user->getId();
            $title = Yii::$app->request->post('title');
            $content = Yii::$app->request->post('content');
            $courseId = Yii::$app->request->post('courseId');
            $atUsers = Yii::$app->request->post('users');

            $service = new ShareService();
            if ($service->CourseShare($userId, $courseId, $title, $content, $atUsers)) {
                return ['result' => 'success'];
            } else {
                return ['result' => 'failure'];
            }
        }
        return ['result' => 'failure'];
    }

    public function actionSendMail()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model = new SendMailForm();
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $userId = Yii::$app->user->getId();
                $companyId = Yii::$app->user->identity->company_id;

                $service = new PushMessageService();
                $result = $service->sendMail($companyId, $userId, $model);
                if ($result === true) {
                    return ['result' => 'success'];
                } else {
                    return ['result' => 'other', 'message' => $result];
                }
            }
        }
    }

    public function actionSaveToAudience()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $courseId = Yii::$app->request->post('course_id');
            $categoryId = Yii::$app->request->post('category_id');
            $title = Yii::$app->request->post('title');
            $desc = Yii::$app->request->post('description');

            $service = new AudienceManageService();
            $result = $service->addAudienceFromTeacher($courseId, $categoryId, $title, $desc);

            return $result;
        }

        if (Yii::$app->request->isAjax && Yii::$app->request->isGet) {
            $courseName = Yii::$app->request->queryParams['courseName'];
            $courseId = Yii::$app->request->queryParams['courseId'];

            $catlog = array();
            $companyId = array(Yii::$app->user->identity->company_id);

            $categoryService = new AudienceCategoryService();
            $category = $categoryService->GetAudienceCategoryByCompanyIdList($companyId);
            if (!empty($category)) {
                foreach ($category as $val) {
                    if (empty($val->parent_category_id)) {
                        $catlog['parent'][] = $val->attributes;
                    } else {
                        $catlog['sub'][$val->parent_category_id][] = $val->attributes;
                    }
                }
            }

            $enrollService = new CourseEnrollService();
            $count = $enrollService->getCourseRegularStudent($courseId, true);

            return $this->renderAjax('save-to-audience', [
                'catlog' => $catlog,
                'courseName' => $courseName,
                'courseId' => $courseId,
                'count' => $count,
            ]);
        }
    }

    /**
     * 下拉查讲受众
     * @return array|string
     */
    public function actionGetAudience()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $params = Yii::$app->request->getQueryParams();
        if (empty($params['q'])) {
            return '';
        }
        $params['keyword'] = $params['q'];
        $params['ownerId'] = Yii::$app->user->getId();
        $params['status'] = SoAudience::STATUS_FLAG_NORMAL;
        $resourceAudiencService = new AudienceManageService();
        $res = $resourceAudiencService->getSoAudienceList($params, false);
        $return = null;
        if (!empty($res['data'])) {
            $new_array = array();
            foreach ($res['data'] as $item) {
                $new_array[] = array('kid' => $item->kid, 'title' => urlencode($item->audience_name) . '(' . $item->audience_code . ')');
            }
            $new_array = array('results' => $new_array);
            $return = urldecode(json_encode($new_array));
        }
        return !empty($return) ? $return : '';
    }
}