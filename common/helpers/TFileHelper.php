<?php
namespace common\helpers;

use common\models\learning\LnFiles;
use common\eLearningLMS;
use components\widgets\TFlowplayer;
use components\widgets\TH5player;
use Yii;
class TFileHelper{

	public static function buildPath($pathes, $withStart = false, $withEnd = false)
	{
		$ret = '';

		foreach ($pathes as $path)
		{
			$ret .= $path . DIRECTORY_SEPARATOR;
		}
		if ($withStart)
		{
			$ret = DIRECTORY_SEPARATOR . $ret;
		}
		if (!$withEnd)
		{
			$ret = rtrim($ret, DIRECTORY_SEPARATOR);
		}
		return $ret;
	}

	public static function isDir($path)
	{
		return is_dir($path);
	}
	public static function exist($path)
	{
		if (is_array($path))
		{
			$path = self::buildPath($path);
		}
		eLearningLMS::info($path);
		return file_exists($path);
	}
	public static function getFiles($path, $prefix = null)
	{
		if (is_array($path))
		{
			$path = self::buildPath($path);
		}

		if(!is_dir($path))
		{
			//var_dump($path);
			return [];
		}

		$files = scandir($path);
		if ($prefix == null)
		{
			return $files;
		}

		$ret = [];
		foreach ($files as $file)
		{
			if (strpos($file, $prefix) === 0)
			{
				$ret[] = $file;
			}
		}

		return $ret;
	}

	public static function createFile($filePath, $content)
	{}

	public static function removeFile($filePath)
	{}

	public static function readFile($filePath)
	{
		if (is_array($filePath))
		{
			$filePath = self::buildPath($filePath);
		}

		return file_get_contents($filePath);
	}

	public static function writeFile($filePath, $content, $mode = 'w')
	{
		if (is_array($filePath))
		{
			$filePath = self::buildPath($filePath);
		}

		$f = fopen($filePath, $mode);
		fwrite($f, $content);
		fclose($f);
	}

	public static function createDir($dirPath)
	{}

    /**
     * 遍历删除文件目录
     * @param $dirName
     * @param bool $andDir
     */
	public static function removeDir($dirName,$andDir = true)
	{
        if ( $handle = opendir( "$dirName" ) ) {

            while ( false !== ( $item = readdir( $handle ) ) ) {

                if ( $item != "." && $item != ".." ) {

                    if ( is_dir( "$dirName/$item" ) ) {

                        self::removeDir( "$dirName/$item" );

                    } else {
                        unlink( "$dirName/$item" );
                    }
                }
            }

            closedir( $handle );
            if($andDir){
                rmdir( $dirName );
            }
        }
    }

    /**
     * 检查目录是否存在，如果不存在，则创建
     * @param $dir
     * @param bool $create
     * @param int $mode
     * @return bool
     */

    public static function check_exist_dir($dir, $create = true, $mode = 0755)
    {
        if (is_dir($dir) || @mkdir($dir,$mode)){
            return true;
        }
        if (!self::check_exist_dir(dirname($dir),$mode)){
            return false;
        }
        return @mkdir($dir,$mode);
    }

    /**
     * 解压zip文件包，默认指定到当前目录
     * @param $zipPath
     * @param null $dirPath
     * @return null|string
     */
    public static function unzip($zipPath,$dirPath = null){
        $zip = new \ZipArchive();

        $zipFullPath = $zipPath;
        $fileInfo = pathinfo($zipFullPath);

        if($dirPath == null){
            $dirPath .= $fileInfo['filename'];
        }

        $res = $zip->open($zipFullPath);
        if ($res === TRUE) {
            self::check_exist_dir($dirPath);
            $zip->extractTo($dirPath);
            $zip->close();

            return $dirPath;
        } else {
            return 'failed, code:' . $res;
        }
    }

    public static function writeCachedSitesFile($company_id, $oldDomain, $newDomain, $is_delete = false)
    {
        $exists = false;
        $cachedSites = Yii::$app->params['cachedSites'];
        if (isset($cachedSites) && count($cachedSites) > 0) {
            $exists = array_key_exists($company_id, $cachedSites);
        }

        if ($exists) {
            if (empty($newDomain) && $oldDomain != $newDomain) {
                $is_delete = true; //如果新域名变成空，需要删除文件中的记录
            }
            if ($is_delete) {
                self::deleteSite($company_id);
            } else {
                if (!empty($newDomain) && $oldDomain != $newDomain) {
                    self::updateSite($company_id, $newDomain);
                }
            }
        } else {
            if (!empty($newDomain)) {
                self::addSite($company_id, $newDomain);
            }
        }
    }

    public static function addSite($company_id, $url)
    {
        $filename = '../common/config/sites-local.php';
        $fp = fopen($filename, 'a');
        if ($fp) {
            $data = "\$cachedSites['$company_id'] = '$url';" . PHP_EOL;
            if (fwrite($fp, $data)) {
                //
            } else {
                die('文件不可写');
            }
            fclose($fp);
        }
    }

    public static function updateSite($company_id, $url)
    {
        $filename = '../common/config/sites-local.php';

        $fp = fopen($filename, 'r+');

        $content = '';
        if ($fp) {
            while (!feof($fp)) {
                $temp = fgets($fp);

                if (stripos($temp, $company_id)) {
                    $new = "\$cachedSites['$company_id'] = '$url';" . PHP_EOL;
                    $content .= $new;
                } else {
                    $content .= $temp;
                }
            }
            fclose($fp);
        }
        file_put_contents($filename, $content);
    }

    public static function deleteSite($company_id)
    {
        $filename = '../common/config/sites-local.php';

        $fp = fopen($filename, 'r+');

        $content = '';
        if ($fp) {
            while (!feof($fp)) {
                $temp = fgets($fp);

                if (!stripos($temp, $company_id)) {
                    $content .= $temp;
                }
            }
            fclose($fp);
        }
        file_put_contents($filename, $content);
    }

    /**
     * 更新license配置文件
     * @param $license
     * @return int
     */
    public static function updateLicense($license){
        $filename = Yii::$app->basePath.'/../common/config/params-local.php';
        $fp = require $filename;
        $fp['license'] = $license;
        $con = var_export($fp, true);
        $con = "<?php\nreturn $con;\n"; //生成配置文件内容
        //$con = str_replace(array('array (', ')'), array('[', ']'), $con);
        return @file_put_contents($filename,$con); //写入./config.php中
    }
}