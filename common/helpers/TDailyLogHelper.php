<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 5/2/15
 * Time: 12:57 PM
 */

namespace common\helpers;

use common\models\framework\FwUser;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\log\Target;
use yii\web\Request;

/**
 * FileTarget records log messages in a file.
 *
 * The log file is specified via [[logFile]]. If the size of the log file exceeds
 * [[maxFileSize]] (in kilo-bytes), a rotation will be performed, which renames
 * the current log file by suffixing the file name with '.1'. All existing log
 * files are moved backwards by one place, i.e., '.2' to '.3', '.1' to '.2', and so on.
 * The property [[maxLogFiles]] specifies how many history files to keep.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class TDailyLogHelper extends Target
{
    /**
     * @var string log file path or path alias. If not set, it will use the "@runtime/logs/app.log" file.
     * The directory containing the log files will be automatically created if not existing.
     */
    public $logFile;
    /**
     * @var bool whether log files should be rotated when they reach a certain [[maxFileSize|maximum size]].
     * Log rotation is enabled by default. This property allows you to disable it, when you have configured
     * an external tools for log rotation on your server.
     * @since 2.0.3
     */
    public $enableRotation = true;
    /**
     * @var integer maximum log file size, in kilo-bytes. Defaults to 10240, meaning 10MB.
     */
    public $maxFileSize = 51200; // in KB
    /**
     * @var integer number of log files used for rotation. Defaults to 5.
     */
    public $maxLogFiles = 500;
    /**
     * @var integer the permission to be set for newly created log files.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * If not set, the permission will be determined by the current environment.
     */
    public $fileMode;
    /**
     * @var integer the permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;
    /**
     * @var boolean Whether to rotate log files by copy and truncate in contrast to rotation by
     * renaming files. Defaults to `true` to be more compatible with log tailers and is windows
     * systems which do not play well with rename on open files. Rotation by renaming however is
     * a bit faster.
     *
     * The problem with windows systems where the [rename()](http://www.php.net/manual/en/function.rename.php)
     * function does not work with files that are opened by some process is described in a
     * [comment by Martin Pelletier](http://www.php.net/manual/en/function.rename.php#102274) in
     * the PHP documentation. By setting rotateByCopy to `true` you can work
     * around this problem.
     */
    public $rotateByCopy = true;

    /**
     * The 'datePattern' parameter.
     * Determines how date will be formatted in file name.
     * @var string
     */
    protected $datePattern = "Ymd";

    /**
     * Current date which was used when opening a file.
     * Used to determine if a rollover is needed when the date changes.
     * @var string
     */
    protected $currentDate;

    /**
     * Determines target file. Replaces %s in file path with a date.
     */
    protected function getTargetFile() {
        $eventDate = $this->getDate(time());

        // Initial setting of current date
        if (!isset($this->currentDate)) {
            $this->currentDate = $eventDate;
        }
        // Check if rollover is needed
        else if ($this->currentDate !== $eventDate) {
            $this->currentDate = $eventDate;
        }


        $file = Yii::$app->getRuntimePath() . '/logs/%s.log';
        return str_replace('%s', $this->currentDate, $file);
    }

    /** Renders the date using the configured <var>datePattern<var>. */
    protected function getDate($timestamp = null) {
        return date($this->datePattern, $timestamp);
    }

    /**
     * Initializes the route.
     * This method is invoked after the route is created by the route manager.
     */
    public function init()
    {
        parent::init();
//        if ($this->logFile === null) {
//            $this->logFile = Yii::$app->getRuntimePath() . '/logs/app.log';
//        } else {
//            $this->logFile = Yii::getAlias($this->logFile);
//        }
        $this->logFile = $this->getTargetFile();

        $logPath = dirname($this->logFile);
        if (!is_dir($logPath)) {
            FileHelper::createDirectory($logPath, $this->dirMode, true);
        }
        if ($this->maxLogFiles < 1) {
            $this->maxLogFiles = 1;
        }
        if ($this->maxFileSize < 1) {
            $this->maxFileSize = 1;
        }
    }

    /**
     * Writes log messages to a file.
     * @throws InvalidConfigException if unable to open the log file for writing
     */
    public function export()
    {
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";
        if (($fp = @fopen($this->logFile, 'a')) === false) {
            throw new InvalidConfigException("Unable to append to log file: {$this->logFile}");
        }
        @flock($fp, LOCK_EX);
        if ($this->enableRotation) {
            // clear stat cache to ensure getting the real current file size and not a cached one
            // this may result in rotating twice when cached file size is used on subsequent calls
            clearstatcache();
        }
        if ($this->enableRotation && @filesize($this->logFile) > $this->maxFileSize * 1024) {
            $this->rotateFiles();
            @flock($fp, LOCK_UN);
            @fclose($fp);
            @file_put_contents($this->logFile, $text, FILE_APPEND | LOCK_EX);
        } else {
            @fwrite($fp, $text);
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }
        if ($this->fileMode !== null) {
            @chmod($this->logFile, $this->fileMode);
        }
    }

    /**
     * Rotates log files.
     */
    protected function rotateFiles()
    {
        $file = $this->logFile;
        for ($i = $this->maxLogFiles; $i >= 0; --$i) {
            // $i == 0 is the original log file
            $rotateFile = $file . ($i === 0 ? '' : '.' . $i);
            if (is_file($rotateFile)) {
                // suppress errors because it's possible multiple processes enter into this section
                if ($i === $this->maxLogFiles) {
                    @unlink($rotateFile);
                } else {
                    if ($this->rotateByCopy) {
                        @copy($rotateFile, $file . '.' . ($i + 1));
                        if ($fp = @fopen($rotateFile, 'a')) {
                            @ftruncate($fp, 0);
                            @fclose($fp);
                        }
                    } else {
                        @rename($rotateFile, $file . '.' . ($i + 1));
                    }
                }
            }
        }
    }

    /**
     * Returns a string to be prefixed to the given message.
     * If [[prefix]] is configured it will return the result of the callback.
     * The default implementation will return user IP, user ID and session ID as a prefix.
     * @param array $message the message being exported.
     * The message structure follows that in [[Logger::messages]].
     * @return string the prefix string
     */
    public function getMessagePrefix($message)
    {
        if ($this->prefix !== null) {
            return call_user_func($this->prefix, $message);
        }

        if (Yii::$app === null) {
            return '';
        }

        $request = Yii::$app->getRequest();
        $ip = $request instanceof Request ? TNetworkHelper::getClientRealIP() : '-';

        /* @var $user \yii\web\User */
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        if ($user && ($identity = $user->getIdentity(false))) {
            $userID = $identity->getId();
        } else {
            $userID = '-';
        }

        if ($user && ($identity = $user->getIdentity(false))) {
            $userName = FwUser::findOne($identity->getId())->user_name;
        } else {
            $userName = '-';
        }

        /* @var $session \yii\web\Session */
        $session = Yii::$app->has('session', true) ? Yii::$app->get('session') : null;
//        $sessionID = $session && $session->getIsActive() ? $session->getId() : '-';
        $sessionID = $session ? $session->getId() : '-';

        return "[$ip][$userID][$userName][$sessionID]";
    }
}
