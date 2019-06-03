<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 4/15/2015
 * Time: 2:23 PM
 */

namespace backend\controllers;


use backend\base\BaseBackController;
use backend\services\UserService;
use common\services\framework\DictionaryService;
use common\models\framework\FwUser;
use common\base\BaseActiveRecord;
use common\helpers\TStringHelper;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class UserInfoController extends BaseBackController {

    //public $enableCsrfValidation = false;//yii默认表单csrf验证，如果post不传入csrf参数会报错！
    public $layout  = 'frame';

    public function actionInfoIndex()
    {
        return $this->render('index',
            [ 'formType' => 'info']);
    }

    public function actionChangePasswordIndex()
    {
        return $this->render('index',
            [ 'formType' => 'change-password']);
    }

    public function actionSettingIndex()
    {
        return $this->render('index',
            [ 'formType' => 'setting']);
    }


    public function actionThumbIndex()
    {
        return $this->render('index',
            [ 'formType' => 'thumb']);
    }

    public function actionSetting()
    {
        $this->layout = 'list';

        $id = Yii::$app->user->getId();
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->save()) {
                $sessionLanguageKey = "Language_" . $id;
                $sessionThemeKey = "Theme_" . $id;
                Yii::$app->session->set($sessionLanguageKey, $model->language);
                Yii::$app->session->set($sessionThemeKey, $model->theme);

                return ['result' => 'success'];
            }
            else {
                return ['result' => 'failure'];
            }
        }
        else {
            $dictionaryService = new DictionaryService();
            $languageModel = $dictionaryService->getDictionariesByCategory('language');
            $themeModel = $dictionaryService->getDictionariesByCategory('theme');
            $timezoneModel = $dictionaryService->getDictionariesByCategory('timezone');
            $model->password_hash = '';
            return $this->renderAjax('setting', [
                'model' => $model,
                'languageModel' => $languageModel,
                'themeModel' => $themeModel,
                'timezoneModel' => $timezoneModel,
            ]);
        }
    }

    public function actionInfo()
    {
        $this->layout = 'list';

        $id = Yii::$app->user->getId();
        $model = $this->findModel($id);
        $model->setScenario("info");
        $oldPasswordHash =  $model->password_hash;
        $oldEmail = $model->email;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->validate()) {
                $model->password_hash = $oldPasswordHash;

                if ($oldEmail != $model->email && $model->email != $model->email_repeat)
                {
                    return ['result' => 'other', 'message' => Yii::t('common', 'email_repeat_error')];
                }

                $userService = new UserService();
                if ($userService->isExistSameUserName($model->kid, $model->user_name)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'user_name')])];
                }
                else if (!empty($model->email) && $userService->isExistSameEmail($model->kid, $model->email)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'email')])];
                } else if ($model->save()) {
                    return ['result' => 'success'];
                } else {
                    return ['result' => 'failure'];
                }
            }
            else {
                return ['result' => 'failure'];
            }
        }
        else {
            $dictionaryService = new DictionaryService();
            $genderModel = $dictionaryService->getDictionariesByCategory('gender');

            $model->email_repeat = $model->email;
            return $this->renderAjax('info', [
                'model' => $model,
                'genderModel' => $genderModel,
            ]);
        }
    }

    public function actionChangePassword()
    {
        $this->layout = 'list';

        $id = Yii::$app->user->getId();
        $model = $this->findModel($id);
        $model->setScenario("change-password");
        $oldPasswordHash =  $model->password_hash;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if (TStringHelper::CheckPasswordStrength($model->password_hash) < 2) {
                return ['result' => 'other', 'message' => Yii::t('common', 'password_format')];
            } else {
                if ($model->validate()) {
                    $checkOldPassword = Yii::$app->security->validatePassword($model->password_old, $oldPasswordHash);

                    if (!$checkOldPassword) {
                        return ['result' => 'other', 'message' => Yii::t('common', 'password_old_error')];
                    }

                    if ($model->password_hash != $model->password_repeat) {
                        return ['result' => 'other', 'message' => Yii::t('common', 'password_repeat_error')];
                    }

                    $model->setPassword($model->password_hash);
                    $model->last_pwd_change_at = time();
                    $model->last_pwd_change_reason = FwUser::PASSWORD_CHANGE_REASON_CHANGE;

                    if ($model->save()) {
                        return ['result' => 'success'];
                    } else {
                        return ['result' => 'failure'];
                    }
                } else {
                    return ['result' => 'failure'];
                }
            }
        }
        else {
            $model->password_old = '';
            $model->password_hash = '';
            $model->password_repeat = '';
            return $this->renderAjax('change-password', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwUser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FwUser::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common','data_not_exist'));
        }
    }

    public function actionThumb()
    {
        $this->layout = 'list';
        $id = Yii::$app->user->getId();
        $model = $this->findModel($id);
//        $this->layout = 'modalWin';
        return $this->render('thumb', [
            'model' => $model,
        ]);
    }

    public function actionUpload(){

       // $physicalPath = Yii::$app->basePath."/../upload/temp/";
        $physicalPath = rtrim(Yii::getAlias("@upload/temp/"), '/\\') . "/";
        $logicalPath="/upload/temp/";
        if(!empty($_FILES)){

            //得到上传的临时文件流
            $tempFile = $_FILES['myfile']['tmp_name'];

            $type = $_FILES['myfile']["type"];

            //得到文件原名
            $fileName = $_FILES["myfile"]["name"];
            $fileParts = pathinfo($_FILES['myfile']['name']);

            $fileError  = $_FILES["myfile"]["error"];
            $fileSize  = $_FILES["myfile"]["size"];

            //允许的文件后缀
            $fileTypes = array(
                'image/jpg',
                'image/jpeg',
                'image/png',
                'image/pjpeg',
                'image/gif',
                'image/bmp',
                'image/x-png');

            if ($fileError)
            {
                $info = Yii::t('common','upload_error');
//                $status=0;
//                $data='';
            }
            else if (!in_array($type,$fileTypes))
            {
                $info = Yii::t('common','file_type_error');
//                $status=0;
//                $data='';
            }
            else {
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
                                $img_r = $this->imageCreateFromBMP($src);
                                break;
                            case "image/x-ms-bmp":
                                $img_r = $this->imageCreateFromBMP($src);
                                break;
                            case "image/x-bmp":
                                $img_r = $this->imageCreateFromBMP($src);
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
                    $info = Yii::t('common','upload_error');
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
    public function actionCutPic(){
        if(Yii::$app->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;

            $yiiBasePath = Yii::$app->basePath."/..";
            $srcLogicalPath="/upload/temp/";
            $destPhysicalPath = Yii::$app->basePath."/../upload/thumb/";
            $destLogicalPath="/upload/thumb/";

            $targ_w = $targ_h = 150;
            $jpeg_quality = 100;
            $src =Yii::$app->request->post('f');
            $src=$yiiBasePath.$src;//真实的图片路径

            $type = getimagesize($src)["mime"];

            $extension = "jpg";
            switch ($type)
            {
                case "image/jpg": $img_r = imagecreatefromjpeg($src);break;
                case "image/jpeg": $img_r = imagecreatefromjpeg($src);break;
                case "image/pjpeg":$img_r = imagecreatefromjpeg($src);break;
                case "image/x-png":$img_r = imagecreatefrompng($src);break;
                case "image/png":$img_r = imagecreatefrompng($src);break;
                case "image/gif":$img_r = imagecreatefromgif($src);break;
                case "image/bmp":$img_r = $this->imageCreateFromBMP($src);break;
                case "image/x-ms-bmp":$img_r = $this->imageCreateFromBMP($src);break;
                case "image/x-bmp":$img_r = $this->imageCreateFromBMP($src);break;
                default: $img_r = imagecreatefromjpeg($src);break;
            }

           // $img_r = imagecreatefrompng($src);
            $ext=$destLogicalPath.time().".".$extension;//生成的引用路径
            $dst_r = imagecreatetruecolor( $targ_w, $targ_h );

            imagecopyresampled($dst_r,$img_r,0,0,Yii::$app->request->post('x'),Yii::$app->request->post('y'),
                $targ_w,$targ_h,Yii::$app->request->post('w'),Yii::$app->request->post('h'));

            $img=$yiiBasePath.$ext;//真实的图片路径

            if(imagejpeg($dst_r,$img,$jpeg_quality)){
                $id = Yii::$app->user->getId();
                //更新用户头像
                $model = FwUser::findOne($id);
                $model->thumb = $ext;
                if ($model->save()) {
                    $arr['status'] = 1;
                    $arr['data'] = $ext;
                    $arr['info'] = Yii::t('common', 'crop_ok');
//                echo json_encode($arr);
                }
                else {
                    $arr['status']=0;
                }
                return $arr;
            }else{
                $arr['status']=0;
//                echo json_encode($arr);
                return $arr;
            }
        }
    }

    public function actionClearPic()
    {
        if(Yii::$app->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            $id = Yii::$app->user->getId();
            //更新用户头像
            $model = FwUser::findOne($id);
            $model->thumb = null;
            $model->save();

            return ['result' => 'success'];
        }
    }

    private function imageCreateFromBMP($filePath)
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
            'Vheader_size/Vwidth/Vheight/vplanes/'.
            'vbits_per_pixel/Vcompression/Vsize_bitmap/'.
            'Vhoriz_resolution/Vvert_resolution/Vcolors_used/Vcolors_important',
            fread($fileHandle, 40)
        );
        $bmp['colors'] = pow(2, $bmp['bits_per_pixel']);
        if ($bmp['size_bitmap'] == 0) {
            $bmp['size_bitmap'] = $file['file_size'] - $file['bitmap_offset'];
        }
        $bmp['bytes_per_pixel'] = $bmp['bits_per_pixel'] / 8;
        $bmp['bytes_per_pixel2'] = ceil($bmp['bytes_per_pixel']);
        $bmp['decal'] =  $bmp['width'] * $bmp['bytes_per_pixel'] / 4;
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
                    $color[1] = $palette[$color[1]+1];
                } else if ($bmp['bits_per_pixel'] == 8) {
                    $color = unpack('n', $vide . substr ($image, $p, 1));
                    $color[1] = $palette[$color[1]+1];
                } else if ($bmp['bits_per_pixel'] ==4) {
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

}