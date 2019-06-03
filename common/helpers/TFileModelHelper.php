<?php
namespace common\helpers;

use backend\services\CompanyService;
use common\models\framework\FwDictionary;
use common\models\framework\FwUser;
use common\models\learning\LnComponent;
use common\models\learning\LnCourse;
use common\models\learning\LnFiles;
use common\models\learning\LnHomeworkFile;
use common\services\framework\DictionaryService;
use components\widgets\TFlowplayer;
use components\widgets\TH5player;
use common\services\learning\ComponentService;
use common\services\learning\FileService;
use Exception;
use stdClass;
use Yii;
use yii\helpers\Html;
use common\services\framework\ExternalSystemService;


class TFileModelHelper{

    public $ExtractPath = '/upload/filedir/';//文件上传相对路径，解压后目录
    public $TempPath = '/upload/temp/';//文件上传相对路径，临时文件目录
    public $OriginPath = '/upload/originfile/';//文件上传相对路径，原文件目录
    public $BackupFilePath = '/upload/backupfile/';//文件上传相对路径，备份文件目录
    public $HomeworkPath = '/upload/homework/';//作业文件上传相对路径，作业文件目录
    public $ScormOriginPath = '/upload/scorm/originfile/';//scorm文件上传相对路径，原文件目录
    public $ScormBackupFilePath = '/upload/scorm/backupfile/';//scorm文件上传相对路径，备份文件目录
    public $AiccOriginPath = '/upload/aicc/originfile/';//aicc文件上传相对路径，原文件目录
    public $AiccBackupFilePath = '/upload/aicc/backupfile/';//aicc文件上传相对路径，备份文件目录

    public $ExtractPhysicalPath = '@upload/filedir/';//文件上传绝对路径，解压后目录
    public $OriginPhysicalPath = '@upload/originfile/';//文件上传绝对路径，原文件目录
    public $BackupFilePhysicalPath = '@upload/backupfile/';//文件上传绝对路径，备份文件目录
    public $HomeworkPhysicalPath = '@upload/homework/';//作业文件上传绝对路径，作业文件目录
    public $ExaminationQuestionPhysicalPath = '@upload/examination-question/';//作业文件上传绝对路径，作业文件目录
    public $AudiencePhysicalPath = '@upload/audience/';//作业文件上传绝对路径，作业文件目录
    public $ScormOriginPhysicalPath = '@upload/scorm/originfile/';//scorm文件上传绝对路径，原文件目录
    public $ScormBackupFilePhysicalPath = '@upload/scorm/backupfile/';//scorm文件上传绝对路径，备份文件目录
    public $AiccOriginPhysicalPath = '@upload/aicc/originfile/';//aicc文件上传绝对路径，原文件目录
    public $AiccBackupFilePhysicalPath = '@upload/aicc/backupfile/';//aicc文件上传绝对路径，备份文件目录

    public $mediatype = [
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'PlayOffice',
        'application/msword'=>'PlayOffice',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'PlayOffice',
        'application/vnd.ms-excel'=>'PlayOffice',
        'application/x-excel'=>'PlayOffice',
        'application/vnd.ms-powerpoint'=>'PlayOffice',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'PlayOffice',
        'application/x-shockwave-flash'=>'PlayVideo',
        'video/mp4'=>'PlayVideo',
        'video/x-flv'=>'PlayVideo',
        'video/avi'=>'PlayVideo',
        'video/x-ms-wmv'=>'PlayVideo',
        'audio/mp3'=>'PlayAudio',
        'audio/mpeg'=>'PlayAudio',
        'application/pdf'=>'PlayPdf',
        'application/octet-stream' => 'PlayScorm',
        'application/x-zip-compressed' => 'PlayScorm',
    ];


    /**
     * 删除fileModel
     * @param $file_id
     * @return bool|int
     */
    public function deleteFileModel($file_id){
        if($fileModel = LnFiles::findOne($file_id)){
            $fullPath = Yii::$app->basePath.'/..'.$fileModel->file_dir;
            //注意，要避免file_dir为空时，程序误把根目录删除掉了
            if($fileModel['file_dir'] && strstr($fileModel['file_dir'],$this->RelativePath)){
                TFileHelper::removeDir($fullPath);
            }
            return $fileModel->delete();
        }
    }

    //视频播放
    private function PlayVideo($fileModel,$fullpath){
        return TFlowplayer::widget([
            'width'=>702,
            'height'=>395,
            'path'=>$fullpath,
        ]);
    }

    /**
     * 音频播放
     * @param $name
     * @param $fullpath
     * @return string
     * @throws \Exception
     */
    private function PlayAudio($fileModel,$fullpath){
        $audioList = [
            'audioList' => [
                    [
                        'name'=>$fileModel->file_name,
                        'url'=>$fullpath,
                    ]
                ]
        ];
        return TH5player::widget($audioList);
    }

    /**
     * word文档播放
     * @param $name
     * @param $fullpath
     */
    private function PlayOffice($fileModel,$fullpath){
        header("Content-Type:text/html;charset=utf8 ");
        echo '<div style="padding: 20px">Office文档暂时还不能在线播放</div>';
    }


    private function PlayScorm($fileModel,$fullpath){
        header("Content-Type:text/html;charset=utf8 ");
        $file_dir = $fileModel->file_dir.'res/index.html';
        echo '<iframe src="'.$file_dir.'" width="100%" height="100%"></iframe>';
    }

    private function PlayPdf($fileModel,$fullpath){
        header("Pragma: public"); // required
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false); // required for certain browsers
        header("Content-Type: ".$fileModel->mime_type);
        ob_clean();
        flush();
        readfile($fullpath);
    }

    public function OutputValidFilename($fileName)
    {
        $str1 = "\\";
        $str2 = "/";
        $str3 = ":";
        $str4 = ",";
        $str5 = "*";
        $str6 = "?";
        $str7 = "#";
        $str8 = "\"";
        $str9 = "<";
        $str10 = ">";
        $str11 = "|";

        $invalidFileNames = array($str1,$str2,$str3,$str4,$str5,$str6,$str7,$str8,$str9,$str10,$str11);
        foreach ($invalidFileNames as $invalidFileName)
        {
            $fileName = str_replace($invalidFileName,"_",$fileName);
        }

        return $fileName;
    }

    /**
     * 加防盗链后的文件地址
     * @param $filePath
     * @return string
     */
    public function secureLink($filePath){
        $fileSecretExpire = isset(Yii::$app->params['fileSecretExpire']) ? intval(Yii::$app->params['fileSecretExpire']) : 300;
        $expire = time() + $fileSecretExpire;# 下载到期时间
        $secret = isset(Yii::$app->params['fileSecretKey']) ? Yii::$app->params['fileSecretKey'] : 'elearning-hpe'; # 密钥
        # 用文件路径、密钥、过期时间生成加密串
        $md5 = base64_encode(md5($secret . $filePath . $expire, true));
        $md5 = strtr($md5, '+/', '-_');
        $md5 = str_replace('=', '', $md5);
        # 加密后的地址
        $params = 'st='.$md5.'&e='.$expire;
        return strpos($filePath, '?') !== false ? $filePath.'&'.$params : $filePath.'?'.$params;
    }

    /**
     * 根据文件ID获取下载安全链接
     * @param $fileId
     * @return array|string
     */
    public static function getFileSecureLink($fileId){
        $fileModel = LnFiles::findOne($fileId);
        if (empty($fileModel)){
            return "";
        }else{
            return self::secureLink($fileModel['backup_file_path']);
        }
    }

    /**
     * 文档播放
     * @param $file_id
     * @param bool $download
     */
    public  function Play($file_id,$download = false,$fileName = null){
        if($fileModel = LnFiles::findOne($file_id)){

//            $userId = Yii::$app->user->getId();
//            $companyId = FwUser::findOne($userId)->company_id;
//            $fileService = new FileService();
//            $resoureUrl = $fileService->GetResourceUrl($companyId);
//
//            if (!empty($resoureUrl)) {
                $resoureUrl = $_SERVER['DOCUMENT_ROOT'];
//            }

            $fullPath = $resoureUrl . $fileModel['backup_file_path'];
            if(file_exists($fullPath) ){
                if($download){
                    $downloadFileName = $fileModel->file_name;
                    if (!empty($fileName))
                    {
                        $fileName = $this->OutputValidFilename($fileName);
                        if (!empty($fileModel->file_extension))
                        {
                            $downloadFileName = $fileName . '.' . $fileModel->file_extension;
                        }
                        else
                        {
                            $downloadFileName = $fileName;
                        }
                    }
                    $downloadFileName = iconv('utf-8', 'GBK', $downloadFileName);
                    if( headers_sent() ){
                        die('Headers Sent');
                    }
                    if(ini_get('zlib.output_compression')){
                        ini_set('zlib.output_compression', 'Off');
                    }
                    header("Pragma: public"); // required
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Cache-Control: private",false); // required for certain browsers
                    header("Content-Type: ".$fileModel->mime_type);
//                    header("Content-Disposition: attachment; filename=\"". urlencode($downloadFileName)."\";" ); header("Content-Transfer-Encoding: binary");//urlencode是解决乱码问题
                    header("Content-Disposition: attachment; filename=\"". $downloadFileName."\";" ); header("Content-Transfer-Encoding: binary");//urlencode是解决乱码问题
                    header("Content-Length: ".$fileModel->file_size);
                    ob_clean();
                    flush();
                    readfile($fullPath);
                }else{
                    $playMethod = $this->mediatype[$fileModel->mime_type];
                    if (empty($playMethod))
                    {
                        echo Yii::t('common','media_type_error');
                    }
                    else {
                        if (method_exists(new TFileModelHelper(), $playMethod)) {


                            echo $this->$playMethod($fileModel, Yii::$app->urlManager->hostInfo . $fileModel['file_path']);
                        }
                    }
                }
            } else{
                echo Yii::t('common','file_not_found');
            }
        }
    }
    
     /**
     * 作业文档播放
     * @param $file_id
     * @param bool $download
     */
    public  function HomeworkPlay($file_id,$download = false,$fileName = null){
        if($fileModel = LnHomeworkFile::findOne($file_id)){
            $fullPath = $_SERVER['DOCUMENT_ROOT'].$fileModel['file_url'];
            if(file_exists($fullPath) ){
                if($download){
                    $downloadFileName = $fileModel->file_name;
                    if (!empty($fileName))
                    {
                        $fileName = $this->OutputValidFilename($fileName);
                        if (!empty($fileModel->file_extension))
                        {
                            $downloadFileName = $fileName . '.' . $fileModel->file_extension;
                        }
                        else
                        {
                            $downloadFileName = $fileName;
                        }
                    }
                    if( headers_sent() ){
                        die('Headers Sent');
                    }
                    if(ini_get('zlib.output_compression')){
                        ini_set('zlib.output_compression', 'Off');
                    }
                    header("Pragma: public"); // required
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Cache-Control: private",false); // required for certain browsers
                    header("Content-Type: ".$fileModel->mime_type);
//                    header("Content-Disposition: attachment; filename=\"". urlencode($downloadFileName)."\";" ); header("Content-Transfer-Encoding: binary");//urlencode是解决乱码问题
                    header("Content-Disposition: attachment; filename=\"". $downloadFileName."\";" ); header("Content-Transfer-Encoding: binary");//urlencode是解决乱码问题
                    header("Content-Length: ".$fileModel->file_size);
                    ob_clean();
                    flush();
                    readfile($fullPath);
                }else{
                    $playMethod = $this->mediatype[$fileModel->mime_type];
                    if (empty($playMethod))
                    {
                        echo Yii::t('common','media_type_error');
                    }
                    else {
                        if (method_exists(new TFileModelHelper(), $playMethod)) {
                            echo $this->$playMethod($fileModel, Yii::$app->urlManager->hostInfo . $fileModel['file_path']);
                        }
                    }
                }
            } else{
                echo Yii::t('common','file_not_found');
            }
        }
    }

    /**
     * 判断目录是否符合Scorm特性，如果是返回内容，否则返回false
     * @param $scormPath
     * @return bool|string
     */
    public function isScormDir($scormPath){
        $filePath = Yii::$app->basePath.'/..' . $scormPath.'imsmanifest.xml';
        if(file_exists($filePath)) {
            $originContent = file_get_contents($filePath);
            $isUTF8 = TStringHelper::isUTF8($originContent);
            if (!$isUTF8)
                $originContent = iconv("gb2312", "utf-8//IGNORE",$originContent);

            if ($originContent != null && $originContent != "") {
                return $originContent;
            }
        }
        return false;
    }


    /**
     * 判断目录是否符合AICC特性，如果是返回内容，否则返回false
     * @param $aiccPath
     * @return bool|string
     */
    public function isAiccDir($aiccPath){
        $result = false;
        $packagedir = Yii::$app->basePath.'/..' . $aiccPath ;
        if (is_dir($packagedir)) {
            if ($handle = opendir($packagedir)) {
                while (($file = readdir($handle)) !== false) {
                    $ext = substr($file,strrpos($file,'.'));
                    if (strtolower($ext) == '.crs') {
                        $filePath = $packagedir . $file;
                        if(file_exists($filePath)) {
                            $originContent = file_get_contents($filePath);
                            $isUTF8 = TStringHelper::isUTF8($originContent);
                            if (!$isUTF8)
                                $originContent = iconv("gb2312", "utf-8//IGNORE",$originContent);

                            if ($originContent != null && $originContent != "") {
                                $result = $originContent;
                            }
                        }

                        break;
                    }
                }
                closedir($handle);
            }
        }

        return $result;
    }


    /**
     * 判断目录是否符合Html非标课件特性，如果是返回入口地址，否则返回false
     * @param $htmlCoursewarePath
     * @return bool|null
     */
    public function isHtmlCoursewareDir($htmlCoursewarePath){
        $entrance_address = null;
        $componentService = new ComponentService();
        $htmlCourseware = $componentService->getCompoentByComponentCode("html-courseware", LnComponent::RESOURCE_CODE);
        if (!empty($htmlCourseware)) {
            $feature_content = $htmlCourseware->feature_content;
            if (!empty($feature_content)) {
                $filePath = Yii::$app->basePath.'/..' . $htmlCoursewarePath;
                $file_list = TFileHelper::getFiles($filePath);
                foreach ($file_list as $item_file) {
                    if (empty($entrance_address)) {
                        if (is_file($filePath . $item_file)) {
                            $uploadHtmlChildFileName = explode('.', $item_file);
                            if (!empty($feature_content)) {
                                $result = stripos($feature_content, end($uploadHtmlChildFileName));
                                if (!is_bool($result)) {
                                    $entrance_address = $item_file;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (empty($entrance_address)) {
            $entrance_address = false;
        }

        return $entrance_address;
    }

    /**
     * 返加课件标题与版本等信息
     * @param $manifestInfo
     * @return bool
     */
    public function GetScormBasicInfo($manifestInfo){
        $cha = mb_detect_encoding($manifestInfo);
        $manifestInfo = iconv($cha,"UTF-8",$manifestInfo);
        if ($manifestInfo != null) {
            $scorm = new stdClass();
            $xml = new \SimpleXMLElement($manifestInfo);
            $scorm->entry = strval($xml->resources->resource['href']);
            $scorm->title = strval($xml->organizations->organization->title);
            $scorm->version = strval($xml->metadata->schemaversion);

            return $scorm;
        }else{
            return null;
        }
    }

    /**
     * 返加课件标题与版本等信息
     * @param $manifestInfo
     * @return bool
     */
    public function GetAiccBasicInfo($manifestInfo){
        if ($manifestInfo != null) {
            $scorm = new stdClass();

            $rows = explode("\r\n", $manifestInfo);
            foreach ($rows as $row) {
                if (preg_match("/^(.+)=(.+)$/",$row,$matches)) {
                    switch (strtolower(trim($matches[1]))) {
//                        case 'course_id':
//                            $scorm->id = trim($matches[2]);
//                            break;
                        case 'course_title':
                            $scorm->title = trim($matches[2]);
                            break;
                        case 'version':
                            $scorm->version = 'AICC_'.trim($matches[2]);
                            break;
                    }
                }
            }

            return $scorm;
        }else{
            return null;
        }
    }

    function path_info($filepath)
    {
        $path_parts = array();
        $path_parts ['dirname'] = rtrim(substr($filepath, 0, strrpos($filepath, '/')),"/")."/";
        $path_parts ['basename'] = ltrim(substr($filepath, strrpos($filepath, '/')),"/");
        $path_parts ['extension'] = substr(strrchr($filepath, '.'), 1);
        $path_parts ['filename'] = ltrim(substr($path_parts ['basename'], 0, strrpos($path_parts ['basename'], '.')),"/");
        return $path_parts;
    }


    /**
     * 上传课件文件
     * @param array $file 上传文件
     * @param null $uploadBatch 上传批次
     * @param string $transferType 可以限制文件传输类型
     * @param null $fileTypes 无效的文件类型，默认为空
     * @param bool|false $allowSameFile 是否允许相同文件，为了避免上传重复文件，默认为不允许
     * @return array
     */
    public function UploadFile($file, $uploadBatch = null, $transferType = LnComponent::TRANSFER_TYPE_NORMAL, $fileTypes = null, $allowSameFile = false){

        if ($uploadBatch == null)
            $uploadBatch = date("YmdHis");

        $tempFile = $file['tmp_name'];
        $fileParts = $this->path_info($file['name']);

        $fileMd5 = md5_file($tempFile);

        $filename = $fileParts['filename'];

        if (empty($filename))
            $filename = $file['name'];

        $fileExtension = strtolower($fileParts['extension']);

        $skipSaveFile = false;//是否跳过保存文件环节
        $isScorm = false; //判断是否为scorm课件，如果是的话，需要用解压后的目录作为课件目录，否则用原文件目录
        $isAicc = false; //判断是否为scorm课件，如果是的话，需要用解压后的目录作为课件目录，否则用原文件目录
        $isHtmlCourseware = false; //判断是否为Html非标课件，如果是的话，需要用解压后的目录作为课件目录，否则用原文件目录

        $fileService = new FileService();

        /*第1步：检查文件是否合法*/
        if(empty($file)){
            return ['result' => '404_FILE_NOT_FOUND'];
        }

        if($fileTypes && !in_array($fileExtension, $fileTypes) ){
            return ['result' => 'FORBIDDEN_FILE_TYPE'];
        }

        /*第2步：如果不允许相同文件，则需要检查文件是否已经存在*/
        if(!$allowSameFile){
            $fileModel = $fileService->getSameFileByMd5($fileMd5);
            if($fileModel != null){
                //这里还需要补充检查文件是否真的存在
                $destDir = Yii::$app->basePath.'/..' . $fileModel->file_dir;
                $dirExist = is_dir($destDir);

                $destPath = Yii::$app->basePath.'/..' . $fileModel->file_path;
                $fileExist = file_exists($destPath);

                if ($dirExist && $fileExist) {
                    $skipSaveFile = true;
                }
            }
        }

        /*第3步：如果文件不存在，保存文件*/
        if (!$skipSaveFile) {
            //采用sha1加密的方式保存文件
            $hashfilename = $fileMd5;//sha1($file['name'].time());

            $OriginRelativePath = $this->OriginPath.substr($hashfilename,0,2).'/';//前2位MD5码相同的文件，存入一个目录。（为了安全及分类）
            $BackupRelativePath = $this->BackupFilePath.substr($hashfilename,0,2).'/';
            $ExtractRelativePath = $this->ExtractPath.substr($hashfilename,0,2).'/';
            $ScormOriginRelativePath = $this->ScormOriginPath.substr($hashfilename,0,2).'/';
            $ScormBackupRelativePath = $this->ScormBackupFilePath.substr($hashfilename,0,2).'/';
            $AiccOriginRelativePath = $this->AiccOriginPath.substr($hashfilename,0,2).'/';
            $AiccBackupRelativePath = $this->AiccBackupFilePath.substr($hashfilename,0,2).'/';

            $OriginAbsolutePath = $this->OriginPhysicalPath.substr($hashfilename,0,2).'/';//前2位MD5码相同的文件，存入一个目录。（为了安全及分类）
            $BackupAbsolutePath = $this->BackupFilePhysicalPath.substr($hashfilename,0,2).'/';
            $ExtractAbsolutePath = $this->ExtractPhysicalPath.substr($hashfilename,0,2).'/';
            $ScormOriginAbsolutePath = $this->ScormOriginPhysicalPath.substr($hashfilename,0,2).'/';
            $ScormBackupAbsolutePath = $this->ScormBackupFilePhysicalPath.substr($hashfilename,0,2).'/';
            $AiccOriginAbsolutePath = $this->AiccOriginPhysicalPath.substr($hashfilename,0,2).'/';
            $AiccBackupAbsolutePath = $this->AiccBackupFilePhysicalPath.substr($hashfilename,0,2).'/';

            $uploadOriginFilePath  = $OriginRelativePath . $hashfilename . '.' . $fileExtension;
            $uploadBackupFilePath  = $BackupRelativePath . $hashfilename . '.' . $fileExtension;
            $uploadScormOriginFilePath  = $ScormOriginRelativePath . $hashfilename . '.' . $fileExtension;
            $uploadScormBackupFilePath  = $ScormBackupRelativePath . $hashfilename . '.' . $fileExtension;
            $uploadAiccOriginFilePath  = $AiccOriginRelativePath . $hashfilename . '.' . $fileExtension;
            $uploadAiccBackupFilePath  = $AiccBackupRelativePath . $hashfilename . '.' . $fileExtension;


//            $uploadOriginAbsolutePath  = Yii::$app->basePath.'/..' . $OriginRelativePath; //上传原文件所在目录绝对路径
//            $uploadBackupAbsolutePath  = Yii::$app->basePath.'/..' . $BackupRelativePath; //上传备份文件所在目录绝对路径
//            $uploadExtractAbsolutePath  = Yii::$app->basePath.'/..' . $ExtractRelativePath . $hashfilename . '/'; //上传解压文件所在目录绝对路径

            $uploadOriginAbsolutePath = rtrim(Yii::getAlias($OriginAbsolutePath), '/\\') . "/";//上传原文件所在目录绝对路径
            $uploadBackupAbsolutePath = rtrim(Yii::getAlias($BackupAbsolutePath), '/\\') . "/";//上传备份文件所在目录绝对路径
            $uploadExtractAbsolutePath = rtrim(Yii::getAlias($ExtractAbsolutePath), '/\\') ."/" . $hashfilename ."/";//上传解压文件所在目录绝对路径

            $uploadScormOriginAbsolutePath = rtrim(Yii::getAlias($ScormOriginAbsolutePath), '/\\') . "/";//SCORM上传原文件所在目录绝对路径
            $uploadScormBackupAbsolutePath = rtrim(Yii::getAlias($ScormBackupAbsolutePath), '/\\') . "/";//SCORM上传备份文件所在目录绝对路径
            $uploadAiccOriginAbsolutePath = rtrim(Yii::getAlias($AiccOriginAbsolutePath), '/\\') . "/";//Aicc上传原文件所在目录绝对路径
            $uploadAiccBackupAbsolutePath = rtrim(Yii::getAlias($AiccBackupAbsolutePath), '/\\') . "/";//Aicc上传备份文件所在目录绝对路径

            //检查并创建文件夹
            TFileHelper::check_exist_dir($uploadOriginAbsolutePath);
            TFileHelper::check_exist_dir($uploadBackupAbsolutePath);

            $targetOrginFile = $uploadOriginAbsolutePath . $hashfilename .'.' . $fileExtension;
            $targetBackupFile = $uploadBackupAbsolutePath . $hashfilename . '.' .$fileExtension;

            //保存原文件
            move_uploaded_file($tempFile, $targetOrginFile);

            //保存备份文件
            copy($targetOrginFile, $targetBackupFile);
        }

        $manifestInfo = null;
        $entranceAddress = null;

        /*第4步：如果是zip文件需要进行解压缩*/
        if (!$skipSaveFile) {
            if ($fileExtension == "zip")//如果是zip文件，先做解压缩
            {
                TFileHelper::unzip($targetOrginFile,$uploadExtractAbsolutePath);

                $manifestInfo = $this->isScormDir($ExtractRelativePath . $hashfilename . '/');

                if ($manifestInfo != false) {
                    /*scorme文件移动到scorm文件目录下，然后删除移动前的文件夹*/
                    TFileHelper::check_exist_dir($uploadScormOriginAbsolutePath);
                    TFileHelper::check_exist_dir($uploadScormBackupAbsolutePath);
                    $targetScormOrginFile = $uploadScormOriginAbsolutePath . $hashfilename .'.' . $fileExtension;
                    $targetScormBackupFile = $uploadScormBackupAbsolutePath . $hashfilename . '.' .$fileExtension;
                    if (copy($targetOrginFile, $targetScormOrginFile)){
                        TFileHelper::removeDir($uploadOriginAbsolutePath);
                        $uploadOriginFilePath = $uploadScormOriginFilePath;
                    }
                    if (copy($targetBackupFile, $targetScormBackupFile)){
                        TFileHelper::removeDir($uploadBackupAbsolutePath);
                        $uploadBackupFilePath = $uploadScormBackupFilePath;
                    }
                    $isScorm = true;
                }
                else {
                    $manifestInfo = $this->isAiccDir($ExtractRelativePath . $hashfilename . '/');

                    if ($manifestInfo != false) {
                        TFileHelper::check_exist_dir($uploadAiccOriginAbsolutePath);
                        TFileHelper::check_exist_dir($uploadAiccBackupAbsolutePath);
                        $targetAiccOrginFile = $uploadAiccOriginAbsolutePath . $hashfilename .'.' . $fileExtension;
                        $targetAiccBackupFile = $uploadAiccBackupAbsolutePath . $hashfilename . '.' .$fileExtension;
                        if (copy($targetOrginFile, $targetAiccOrginFile)){
                            TFileHelper::removeDir($uploadOriginAbsolutePath);
                            $uploadOriginFilePath = $uploadAiccOriginFilePath;
                        }
                        if (copy($targetBackupFile, $targetAiccBackupFile)){
                            TFileHelper::removeDir($uploadBackupAbsolutePath);
                            $uploadBackupFilePath = $uploadAiccBackupFilePath;
                        }
                        $isAicc = true;
                    }
                    else {
                        $entranceAddress = $this->isHtmlCoursewareDir($ExtractRelativePath . $hashfilename . '/');
                        if ($entranceAddress != false) {
                            $isHtmlCourseware = true;
                        }
                    }
                }
            }
        }

        //非scorm/Aicc课件时，要把manifestInfo清空
        if (!$isScorm && !$isAicc) {
            $manifestInfo = null;
        }

        //非Html非标课件时，要把entranceAddress清空
        if (!$isHtmlCourseware) {
            $entranceAddress = null;
        }

        /*第5步：如果是Scorm文件，需要特殊处理*/
        if (!$skipSaveFile) {
            if ($isScorm) {
                $scorm = $this->GetScormBasicInfo($manifestInfo);

                if ($scorm != null) {
                    $coursewareName = $scorm->title ? $scorm->title : $filename;
                } else {
                    $coursewareName = $filename;
                }
            }
            else if ($isAicc) {
                $scorm = $this->GetAiccBasicInfo($manifestInfo);

                if ($scorm != null) {
                    $coursewareName = $scorm->title ? $scorm->title : $filename;
                } else {
                    $coursewareName = $filename;
                }
            }
            else {
                $coursewareName = $filename;
            }
        } else {
            $coursewareName = $fileModel->file_title;
        }

        /*第6步：如果不存在FileModel，则需要新建*/
        if (!isset($fileModel) || $fileModel == null) {

            $componentService = new ComponentService();

            if ($isScorm)
            {
                $fileDir = $ExtractRelativePath . $hashfilename . '/';
                $componentModel = $componentService->getCompoentByComponentCode("scorm", LnComponent::RESOURCE_CODE);
                if (!empty($componentModel))
                    $componentId = $componentModel->kid;
            }
            else if ($isAicc)
            {
                $fileDir = $ExtractRelativePath . $hashfilename . '/';
                $componentModel = $componentService->getCompoentByComponentCode("aicc", LnComponent::RESOURCE_CODE);
                if (!empty($componentModel))
                    $componentId = $componentModel->kid;
            }
            else if ($isHtmlCourseware)
            {
                $fileDir = $ExtractRelativePath . $hashfilename . '/';
                $componentModel = $componentService->getCompoentByComponentCode("html-courseware", LnComponent::RESOURCE_CODE);
                if (!empty($componentModel))
                    $componentId = $componentModel->kid;
            }
            else
            {
                $fileDir = dirname($uploadOriginFilePath) . '/';
                $componentModel = $componentService->getCompoentByFileType($fileExtension, LnComponent::RESOURCE_CODE, $transferType, ["scorm","aicc","html-courseware"]);
                if (!empty($componentModel))
                    $componentId = $componentModel->kid;
            }

            if (!isset($componentId))
            {
                $componentModel = $componentService->getCompoentByComponentCode("other", LnComponent::RESOURCE_CODE);
                $componentId = $componentModel->kid;
            }
            if (isset($componentId)) {
                /*获取默认值*/
                $findOne = LnComponent::findOne($componentId);

                $fileModel = new LnFiles();
                $fileModel->component_id = $componentId;
                $fileModel->file_title = $coursewareName;
                $fileModel->file_name = $file['name'];
                $fileModel->file_md5 = $fileMd5;
                $fileModel->file_dir = $fileDir;
                $fileModel->file_path = $uploadOriginFilePath;
                $fileModel->backup_file_path = $uploadBackupFilePath;
                $fileModel->file_size = strval($file['size']);
                $fileModel->mime_type = $file['type'];
                $fileModel->upload_batch = $uploadBatch;
                $fileModel->status = LnFiles::STATUS_FLAG_NORMAL;
                $fileModel->manifest_info = $manifestInfo;
                $fileModel->entrance_address = $entranceAddress;
                if ($fileExtension == null)
                    $fileExtension = "";
                $fileModel->file_extension = $fileExtension;
                $fileModel->format_transfer_status = LnFiles::FORMAT_TRANSFER_STATUS_COMPLETED;

                if (!empty($findOne)) {
                    if ($findOne->component_code == "rtmp") {
                        //如果是新建的RTMP文件，则默认是格式待转换状态
                        $fileModel->format_transfer_status = LnFiles::FORMAT_TRANSFER_STATUS_WAITING;
                    }
                    else if ($findOne->component_code == "office") {
                        //如果是新建的Office文件，则根据系统参数判断是否要进行格式转换
                        $dictionaryService = new DictionaryService();
                        $isConvertOffice = $dictionaryService->getDictionaryValueByCode("system","is_convert_office");
                        if ($isConvertOffice == FwDictionary::YES) {
                            $fileModel->format_transfer_status = LnFiles::FORMAT_TRANSFER_STATUS_WAITING;
                        }
                    }
                }
                $fileModel->needReturnKey = true;
                if ($fileModel->save()) {
                    if($findOne->component_code == "office"){
                    	if (!empty($fileModel->kid) && isset($isConvertOffice) && $isConvertOffice == FwDictionary::YES){
                    		$this->office2Pdf($fileModel->kid);
                    	}
                    	 
                    }
                }
            }
        }
        else {
            /*获取默认值*/
            $findOne = LnComponent::findOne($fileModel['component_id']);
        }


        /*第7步：将处理结果返回*/
        if (empty($fileModel->kid)){
            return ['result' => 'FILE_UPLOAD_FAILURE'];
        }
        else {
            $result = [
                'result' => 'Completed',
                'file_id' => $fileModel->kid,
                'file_path' => isset($uploadOriginFilePath) ? $uploadOriginFilePath : $fileModel->file_path,
                'courseware_name' => $coursewareName,
                'entrance_address' => $fileModel->entrance_address,
                'file_icon' => $findOne->icon,
                'file_name' => $fileModel->file_name,
                'component_id' => $fileModel->component_id,
                'is_display_pc' => $findOne->is_display_pc,
                'is_display_mobile' => $findOne->is_display_mobile,
                'is_allow_download' => $findOne->is_allow_download,
                'default_time' => $findOne->default_time ? $findOne->default_time : '',
                'default_credit' => $findOne->default_credit ? $findOne->default_credit : '',
            ];

            return $result;
        }
    }
    
    /**
     * office文件调用java转换接口
     * @param unknown $file_id
     * @throws Exception
     */
    public function office2Pdf($file_id){
    	$externalSystemService = new ExternalSystemService();
    	$url = $externalSystemService->getExternalSystemInfoByExternalSystemCode("elearning-office2pdf-service")->api_address;
    	
    	//$url="http://localhost:32051/msgCenter/rest/office2Pdf/transfPdf";
    	$path=Yii::getAlias('@webroot');
        $data_string= json_encode(array('file_id'=>$file_id,'path'=>$path ));

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data_string)));

        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        ob_end_clean();

        if($return_code!=200){
    		throw new Exception("office2Pdf error");
    	}
    }
    
    /**
     * 上传作业课件文件
     * @param array $homeworkType 上传文件
     * @param array $file 上传文件
     * @param null $uploadBatch 上传批次
     * @param null $fileTypes 无效的文件类型，默认为空
     * @return array
     */
    public function UploadHomeworkFile($userId, $companyId, $homeworkType = "0", $file, $uploadBatch = null, $id ,$fileTypes = null,$course_id = '0',$course_reg_id = '0',$mod_id = '0',$mod_res_id = '0',$courseactivity_id = '0',$component_id = '0',$course_complete_id = '0',$res_complete_id = '0',$course_attempt_number=0){
        if ($uploadBatch == null)
            $uploadBatch = date("YmdHis");

        $tempFile = $file['tmp_name'];
        $fileParts = $this->path_info($file['name']);

        $fileMd5 = md5_file($tempFile);

        $filename = $fileParts['filename'];

        if (empty($filename))
            $filename = $file['name'];

        $fileExtension = strtolower($fileParts['extension']);

        /*第1步：检查文件是否合法*/
        if(empty($file)){
            return ['result' => '404_FILE_NOT_FOUND'];
        }

        if($fileTypes && !in_array($fileExtension, $fileTypes) ){
            return ['result' => 'FORBIDDEN_FILE_TYPE'];
        }

        //采用sha1加密的方式保存文件
        $hashfilename = $fileMd5;//sha1($file['name'].time());

        $HomeworkRelativePath = $this->HomeworkPath.substr($hashfilename,0,2).'/';//前2位MD5码相同的文件，存入一个目录。（为了安全及分类）

        $HomeworkAbsolutePath = $this->HomeworkPhysicalPath.substr($hashfilename,0,2).'/';//前2位MD5码相同的文件，存入一个目录。（为了安全及分类）

        $uploadHomeworkFilePath  = $HomeworkRelativePath . $hashfilename . '.' . $fileExtension;

        $uploadHomeworkAbsolutePath = rtrim(Yii::getAlias($HomeworkAbsolutePath), '/\\') . "/";//上传原文件所在目录绝对路径

        //检查并创建文件夹
        TFileHelper::check_exist_dir($uploadHomeworkAbsolutePath);

        $targetHomeworkFile = $uploadHomeworkAbsolutePath . $hashfilename .'.' . $fileExtension;

        //保存原文件
        move_uploaded_file($tempFile, $targetHomeworkFile);

        $coursewareName = $filename;
        $fileModel = new LnHomeworkFile();
        $fileModel->homework_id = $id;
        $fileModel->user_id = $userId;
        $fileModel->course_id = $course_id;
        $fileModel->course_reg_id = $course_reg_id;
        $fileModel->mod_id = $mod_id;
        $fileModel->mod_res_id = $mod_res_id;
        $fileModel->courseactivity_id = $courseactivity_id;
        $fileModel->component_id = $component_id;
        $fileModel->course_complete_id = $course_complete_id;
        $fileModel->res_complete_id = $res_complete_id;
        $fileModel->company_id = $companyId;
        $fileModel->homework_file_type = "$homeworkType";
        $fileModel->file_url = $uploadHomeworkFilePath;
        $fileModel->file_name = $file['name'];
        $fileModel->file_md5 = $fileMd5;
        $fileModel->file_size = strval($file['size']);
        $fileModel->mime_type = $file['type'];
        $fileModel->file_extension = $fileExtension;
        $fileModel->course_attempt_number = $course_attempt_number;
        $fileModel->needReturnKey = true;
        $fileModel->save();
        /*第7步：将处理结果返回*/
        if (empty($fileModel->kid)){
            return ['result' => 'FILE_UPLOAD_FAILURE'];
        }else {
            $result = [
                'result' => 'Completed',
                'file_id' => $fileModel->kid,
                'file_url' => $uploadHomeworkFilePath,
                'courseware_name' => $coursewareName,
                'file_name' => $fileModel->file_name,
            ];

            return $result;
        }
    }

    /**
     * 移动附件
     */
    public function moveFile($originfile, $newPath){
        /*判断原文件是否存在*/
        if (!file_exists(Yii::$app->basePath.'/..'.$originfile)){
            return false;
        }else{
            $filename = strrchr($originfile, '/');
            if (!copy(Yii::$app->basePath.'/..'.$originfile, Yii::$app->basePath.'/..'.$newPath.$filename)){
                return false;
            }else{
                return $newPath.$filename;
            }
        }
    }

    /**
     * 取得图像信息
     * @static
     * @access public
     * @param string $image 图像文件名
     * @return mixed
     */
    public function getImageInfo($img) {
        $imageInfo = getimagesize($img);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($img);
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "size" => $imageSize,
                "mime" => $imageInfo['mime']
            );
            return $info;
        } else {
            return false;
        }
    }

    /**
     * 生成缩略图
     * @static
     * @access public
     * @param string $image  原图
     * @param string $mode 操作
     * @param string $thumbname 缩略图文件名
     * @param string $maxWidth  宽度
     * @param string $maxHeight  高度
     * @param string $quality 质量
     * @return void
     */
    public function image_resize($image, $width = 200, $height = 200, $mode = 'scale', $thumbname = '', $quality = '100')
    {
        try {
            $imageValue = $this->getImageInfo(Yii::$app->basePath . '/..'.$image);
            $sourceWidth = $imageValue['width']; //原图宽
            $sourceHeight = $imageValue['height']; //原图高
            $thumbWidth = $width; //缩略图宽
            $thumbHeight = $height; //缩略图高
            $_x = 0;
            $_y = 0;
            $w = $sourceWidth;
            $h = $sourceHeight;
            if ($mode == 'scale') {
                if ($sourceWidth <= $thumbWidth && $sourceHeight <= $thumbHeight) {
                    $_x = floor(($thumbWidth - $sourceWidth) / 2);
                    $_y = floor(($thumbHeight - $sourceHeight) / 2);
                    $thumbWidth = $sourceWidth;
                    $thumbHeight = $sourceHeight;
                } else {
                    if ($thumbHeight * $sourceWidth > $thumbWidth * $sourceHeight) {
                        $thumbHeight = floor($sourceHeight * $width / $sourceWidth);
                        $_y = floor(($height - $thumbHeight) / 2);
                    } else {
                        $thumbWidth = floor($sourceWidth * $height / $sourceHeight);
                        $_x = floor(($width - $thumbWidth) / 2);
                    }
                }
            } elseif ($mode == 'crop') {
                if ($sourceHeight < $thumbHeight) { //如果原图尺寸小于当前尺寸
                    $thumbWidth = floor($thumbWidth * $sourceHeight / $thumbHeight);
                    $thumbHeight = $sourceHeight;
                }
                if ($sourceWidth < $thumbWidth) {
                    $thumbHeight = floor($thumbHeight * $sourceWidth / $thumbWidth);
                    $thumbWidth = $sourceWidth;
                }

                $s1 = $sourceWidth / $sourceHeight; //原图比例
                $s2 = $width / $height; //新图比例
                if ($s1 == $s2) {

                } elseif ($s1 > $s2) { //全高度
                    $y = 0;
                    $ax = floor($sourceWidth * ($thumbHeight / $sourceHeight));
                    $x = ($ax - $thumbWidth) / 2;
                    $w = $thumbWidth / ($thumbHeight / $sourceHeight);

                } else { //全宽度
                    $x = 0;
                    $ay = floor($sourceHeight * ($thumbWidth / $sourceWidth)); //模拟原图比例高度
                    $y = ($ay - $thumbHeight) / 2;
                    $h = $thumbHeight / ($thumbWidth / $sourceWidth);
                }

            }
            $type = strtolower($imageValue['type']);
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            $source = $createFun(Yii::$app->basePath . '/..'.$image);
            //创建缩略图
            if ($type != 'gif' && function_exists('imagecreatetruecolor'))
                $thumb = imagecreatetruecolor($width, $height);
            else
                $thumb = imagecreate($width, $height);

            imagefill($thumb, 0, 0, imagecolorallocate($thumb, 255, 255, 255));
            imagecopyresampled($thumb, $source, 0, 0, $x, $y, $width, $height, $w, $h);

            $thumbname = $thumbname ? $thumbname : $image . '_' . $width . 'x' . $height . strrchr($image,'.');
            $imageFun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
            /*$imageFun($thumb, Yii::$app->basePath . '/..'. $thumbname, $quality);//对部分图片生成失败*/
            $imageFun($thumb, Yii::$app->basePath . '/..'. $thumbname);
            imagedestroy($thumb);
            imagedestroy($source);
            return $thumbname;
        } catch (Exception $ex) {
            return false;
        }
    }
    
    /**
     * 上传临时文件
     * @param unknown $file
     * @return string
     */
    public function importExaminationQuestionFile($file, $extension = ['xls']){
    	/*第1步：检查文件是否合法*/
    	if(empty($file)){
    		return ['result' => 'fail', 'errmsg' => '404_FILE_NOT_FOUND'];
    	}
    	$tempFile = $file['tmp_name'];
    	$fileParts = $this->path_info($file['name']);
    	$fileExtension = strtolower($fileParts['extension']);
    	if (!in_array($fileExtension, $extension)){
    		return ['result' => 'fail', 'errmsg' => Yii::t('common', 'extension_error')];
    	}
    	$fileMd5 = md5_file($tempFile);
    	
    	$filename = $fileParts['filename'];
    	
    	if (empty($filename))
    		$filename = $file['name'];
    	
    	$uploadTempPath = rtrim(Yii::getAlias($this->TempPath), '/\\') . "/";
    	TFileHelper::check_exist_dir(Yii::$app->basePath.'../'.$uploadTempPath);
    	$tempFilePath = $uploadTempPath . $fileMd5 . '.' .$fileExtension;
    	if (file_exists(Yii::$app->basePath.'../'.$tempFilePath)){
    		return ['result' => 'success', 'errmsg' => $tempFilePath, 'basename' => $fileParts['basename'], 'md5' => $fileMd5];
    	}
    	if (move_uploaded_file($tempFile, Yii::$app->basePath.'/../'.$tempFilePath)){
    		return ['result' => 'success', 'errmsg' => $tempFilePath, 'basename' => $fileParts['basename'], 'md5' => $fileMd5];
    	}else{
    		return ['result' => 'fail', 'errmsg' => Yii::t('common', 'upload_error')];
    	}    	    	
    }
    
    /**
     * 导入题库文件后，备份并读取文件
     **/
    public function copyExaminationQuestionFile($file, $fileName = ''){
    	$uploadBackPath = rtrim(Yii::getAlias($this->ExaminationQuestionPhysicalPath), '/\\') . "/";
    	TFileHelper::check_exist_dir($uploadBackPath);
    	$fileParts = $this->path_info($file);
    	$newFileName = $fileParts['basename'];
    	if (!empty($fileName)){
    		$newFileName = iconv('UTF-8', 'GBK', $fileName);
    	}
    	$newFile = $uploadBackPath . $newFileName;
    	copy(Yii::$app->basePath.'/../'.$file, $newFile);
    	return $newFileName;
    }

    /**
     * 导入受众文件后，备份并读取文件
     **/
    public function copyAudienceFile($file, $fileName = null){
    	$uploadBackPath = rtrim(Yii::getAlias($this->AudiencePhysicalPath), '/\\') . "/";
    	TFileHelper::check_exist_dir($uploadBackPath);
    	$fileParts = $this->path_info($file);
    	$newFileName = $fileParts['basename'];
    	if (!empty($fileName)){
    		$newFileName = iconv('UTF-8', 'GBK', $fileName);
    	}
    	$newFile = $uploadBackPath . $newFileName;
    	copy(Yii::$app->basePath.'/../'.$file, $newFile);
    	return $newFileName;
    }

}