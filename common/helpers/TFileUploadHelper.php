<?php
namespace common\helpers;

use common\eLearningLMS;
use components\widgets\TFlowplayer;
use components\widgets\TH5player;
use common\services\learning\FileService;
use Yii;

class TFileUploadHelper
{
    public $uploadFilePath = '/upload/';//文件上传相对路径

    public $uploadPhysicalPath = '@upload/';//文件上传绝对路径

    function path_info($filepath)
    {
        $path_parts = array();
        $path_parts ['dirname'] = rtrim(substr($filepath, 0, strrpos($filepath, '/')), "/") . "/";
        $path_parts ['basename'] = ltrim(substr($filepath, strrpos($filepath, '/')), "/");
        $path_parts ['extension'] = substr(strrchr($filepath, '.'), 1);
        $path_parts ['filename'] = ltrim(substr($path_parts ['basename'], 0, strrpos($path_parts ['basename'], '.')), "/");
        return $path_parts;
    }

    public function UploadFile($file, $baseDir, $uploadBatch = null, $fileTypes = null, $allowSameFile = false)
    {
        if ($uploadBatch == null)
            $uploadBatch = date("YmdHis");
        $tempFile = $file['tmp_name'];
        $fileParts = $this->path_info($file['name']);

        $fileMd5 = md5_file($tempFile);

        $filename = $fileParts['filename'];

        if (empty($filename))
            $filename = $file['name'];

        $fileExtension = strtolower($fileParts['extension']);

        /* 检查文件是否合法 */
        if (empty($file)) {
            return ['result' => '404_FILE_NOT_FOUND'];
        }

        if ($fileTypes && !in_array($fileExtension, $fileTypes)) {
            return ['result' => 'FORBIDDEN_FILE_TYPE'];
        }

        //采用sha1加密的方式保存文件
        $hashfilename = $fileMd5;

        $relativePath = $this->uploadFilePath . $baseDir . substr($hashfilename, 0, 2) . '/';//前2位MD5码相同的文件，存入一个目录。（为了安全及分类）

        $absolutePath = $this->uploadPhysicalPath . $baseDir . substr($hashfilename, 0, 2) . '/';//前2位MD5码相同的文件，存入一个目录。（为了安全及分类）

        $uploadFilePath = $relativePath . $hashfilename . '.' . $fileExtension;

        $uploadAbsolutePath = rtrim(Yii::getAlias($absolutePath), '/\\') . "/";//上传原文件所在目录绝对路径

        //检查并创建文件夹
        TFileHelper::check_exist_dir($uploadAbsolutePath);

        $targetFile = $uploadAbsolutePath . $hashfilename . '.' . $fileExtension;

        //保存原文件
        move_uploaded_file($tempFile, $targetFile);

        $result = [
            'result' => 'Completed',
            'file_path' => $uploadFilePath,
            'file_name' => $filename,
        ];

        return $result;
    }
}