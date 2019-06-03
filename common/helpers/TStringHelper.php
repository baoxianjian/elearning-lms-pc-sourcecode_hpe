<?php
namespace common\helpers;


use common\models\framework\FwUser;
use common\models\learning\LnCourse;
use common\models\learning\LnExamination;
use common\models\learning\LnInvestigation;
use common\models\message\MsTask;
use common\models\message\MsTaskItem;
use common\models\message\MsTimeline;
use common\services\interfaces\service\ToolInterface;
use yii\helpers\Html;

class TStringHelper
{
    public static function blank($count)
    {
        $count = intval($count);
        return str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $count);
    }

    public static function parse2Array($str, $itemSep = "\r\n", $valueSep = '=>')
    {
        $source = explode($itemSep, $str);
        if (empty($source)) {
            return [];
        }

        $items = [];

        foreach ($source as $itemString) {
            if (empty($itemString)) {
                continue;
            }

            $itemArray = explode($valueSep, $itemString);
            $count = count($itemArray);
            if ($count == 0) {
                continue;
            }

            if ($count == 2) {
                $items[$itemArray[0]] = $itemArray[1];
            } else {
                $items[$itemArray[0]] = $itemArray[0];
            }
        }
        return $items;
    }

    public static function quotString($str)
    {
        return '\'' . $str . '\'';
    }

    public static function  isNullOrEmpty($var)
    {
        if (!isset($var)) {
            return true;
        }
        if ($var === '') {
            return true;
        }
        return false;
    }

    public static function startWith($str, $needle)
    {
        return strpos($str, $needle) === 0;
    }


    /**
     * 第一个是原串,第二个是部份串
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function endWith($haystack, $needle) {

        $length = strlen($needle);
        if($length == 0)
        {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

    /**
     * 中文截取，支持gb2312,gbk,utf-8,big5
     * @param $str 要截取的字串
     * @param int $length 截取长度
     * @param string $charset 编码:utf-8¦gb2312¦gbk¦big5
     * @param int $start 截取起始位置
     * @param null $suffix 尾缀
     * @return string
     */
    public static function subStr($str, $length = -1, $charset = "", $start = 0, $suffix = null)
    {
        if ($length < 1) {
            return $str;
        }
        if ($str == null || empty($str)) {
            return '';
        }

        if (empty($charset)) {
            $charset = 'utf-8';
        }

        if (function_exists('mb_substr')) {
            if (mb_strlen($str, $charset) <= $length)
                return $str;
            $slice = mb_substr($str, $start, $length, $charset);
        } else {
            $re['utf-8'] = "/[\x01-\x7f]¦[\xc2-\xdf][\x80-\xbf]¦[\xe0-\xef][\x80-\xbf]{2}¦[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]¦[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]¦[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]¦[\x81-\xfe]([\x40-\x7e]¦\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            if (count($match[0]) <= $length)
                return $str;
            $slice = join("", array_slice($match[0], $start, $length));
        }
        if ($suffix != null && !empty($suffix)) {
            return $slice . $suffix;
        }

        return $slice;
    }

    public static function Thumb($thumb, $gender)
    {
        if (!empty($thumb)) {
            return $thumb;
        }

        // 默认男头像
        $result = '/static/common/images/man.jpeg';

        if ($gender === FwUser::GENDER_FEMALE) {
            $result = '/static/common/images/woman.jpeg';
        }

        return $result;
    }

    public static function PositionName($position)
    {
        return $position ? $position : '员工';
    }

    public static function Theme(LnCourse $course, $is_big = false)
    {
        $default = '/static/frontend/images/course_theme_big.png';// : '/static/frontend/images/course_theme_small.png';

        $result = '';

        if ($is_big) {
            $result = empty($course->theme_url) ? $default : $course->theme_url;
        } else {
            $result = empty($course->theme_url) ? $default : $course->theme_url . '_378x225.jpg';
        }

        return $result;
    }

    public static function RecordText($type)
    {
        $text = '';
        if ($type === MsTimeline::OBJECT_TYPE_COURSE) {
            $text = '课程';
        } elseif ($type === MsTimeline::OBJECT_TYPE_QUESTION) {
            $text = '问答';
        } elseif ($type === MsTimeline::OBJECT_TYPE_RECORD_WEB) {
            $text = '网页';
        } elseif ($type === MsTimeline::OBJECT_TYPE_RECORD_EVENT) {
            $text = '事件';
        } elseif ($type === MsTimeline::OBJECT_TYPE_RECORD_BOOK) {
            $text = '书籍';
        } elseif ($type === MsTimeline::OBJECT_TYPE_RECORD_EXP) {
            $text = '经验';
        }
        return $text;
    }

    public static function CourseType($type)
    {
        $text = '';
        if ($type === LnCourse::COURSE_TYPE_ONLINE) {
            $text = '在线课程';
        } elseif ($type === LnCourse::COURSE_TYPE_FACETOFACE) {
            $text = '面授课程';
        }
        return $text;
    }

    public static function TaskPushStatus($task_status, $complete_type, $push_prepare_at = null)
    {
        $text = '';
        if ($task_status === '0') {
            $text = '待推送';
            if ($push_prepare_at) {
                $text .= '(' . TTimeHelper::toDateTime($push_prepare_at, 'Y-m-d H:i') . ')';
            }
        } elseif ($task_status === '1') {
            $text = '进行中';
        } elseif ($task_status === '2') {
            if ($complete_type === '0') {
                $text = '未完成';
            } elseif ($complete_type === '1') {
                $text = '全部成功';
            } elseif ($complete_type === '2') {
                $text = '全部失败';
            } elseif ($complete_type === '3') {
                $text = '部分失败';
            }
        }
        return $text;
    }

    /**
     * 判断字符串是否为UTF-8
     * @param $str
     * @return bool
     */
    public static function isUTF8($str)
    {
        if ($str === mb_convert_encoding(mb_convert_encoding($str, "UTF-32", "UTF-8"), "UTF-8", "UTF-32")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * UTF-8 字符串截取
     * @param $str 待截取的字符串
     * @param $start 截取开始位置
     * @param $num 截取长度
     * @param null $suffix 尾缀
     * @return string
     */
    public static function utf8_substr($str, $start, $num, $suffix = null)
    {
        $res = '';      //存储截取到的字符串
        $cnt = 0;       //计数器，用来判断字符串是否走到$start位置
        $t = 0;         //计数器，用来判断字符串已经截取了$num的数量
        for ($i = 0; $i < strlen($str); $i++) {
            if (ord($str[$i]) > 127) {    //非ascii码时
                if ($cnt >= $start) {     //如果计数器走到了$start的位置
                    $res .= $str[$i] . $str[++$i] . $str[++$i]; //utf-8是三字节编码，$i指针连走三下，把字符存起来
                    $t = $t + 2;              //计数器++，表示我存了几个字符串了到$num的数量就退出了
                } else {
                    $i++;               //如果没走到$start的位置，那就只走$i指针，字符不用处理
                    $i++;
                }
                $cnt++;
            } else {
                if ($cnt >= $start) {     //acsii码正常处理就好
                    $res .= $str[$i];
                    $t++;
                }
                $cnt++;
            }
            if ($num <= $t) {
                $i++;
                if ($str[$i] && $suffix != null && !empty($suffix)) {
                    $res .= $suffix;
                }
                break;       //ok,我要截取的数量已经够了，我不贪婪，我退出了
            }
        }
        return $res;
    }

    public static function GetCourseTime(LnCourse $course)
    {
        $text = '';
        if ($course->course_type === LnCourse::COURSE_TYPE_ONLINE) {
            if (empty($course->end_time)) {
                $text = '永久';
            } else {
                if (date('Y', $course->start_time) === date('Y', $course->end_time)) {
                    $text = date('Y年m月d日', $course->start_time) . '-' . date('m月d日', $course->end_time);
                } else {
                    $text = date('Y年m月d日', $course->start_time) . '-' . date('Y年m月d日', $course->end_time);
                }
            }
        } elseif ($course->course_type === LnCourse::COURSE_TYPE_FACETOFACE) {
            if (date('Y', $course->open_start_time) === date('Y', $course->open_end_time)) {
                $text = date('Y年m月d日', $course->open_start_time) . '-' . date('m月d日', $course->open_end_time);
            } else {
                $text = date('Y年m月d日', $course->open_start_time) . '-' . date('Y年m月d日', $course->open_end_time);
            }
        }
        return $text;
    }

    /**
     * 高亮标签处理
     * @param $str
     * @return mixed
     */
    public static function HighlightDecode($str)
    {
        $tag=self::getHighlightTag();
        $search = [Html::encode($tag['pre']), Html::encode($tag['post'])];
        $replace = [$tag['pre'], $tag['post']];

        return str_replace($search, $replace, $str);
    }
     
    /**
    * 得到高亮标签
    *  add by baoxianjian 11:25 2015/12/31
    */
    public static function getHighlightTag($type=null,$style=null)
    {
        if(!$style)
        {
            if(defined('HIGHLIGHT_STYLE'))
            {
                 $style=HIGHLIGHT_STYLE;
            }
            else
            {
               $style=0; 
            }
        }
        
        $tag=array(
               0=> array('pre'=>'<font class="highlight-color">','post'=>'</font>'),
               1=> array('pre'=>'<font color="#FF0000">','post'=>'</font>')
              );
        if($type)
        {
            return $tag[$style][$type];
        }                         
        return $tag[$style];
    }

    public static function escape($string, $encoding = "UTF-8")
    {
        $return = "";
        for ($x = 0; $x < mb_strlen($string, $encoding); $x++) {
            $str = mb_substr($string, $x, 1, $encoding);
            if (strlen($str) > 1) { // 多字节字符
                $return .= "%u" . strtoupper(bin2hex(mb_convert_encoding($str, "UCS-2", $encoding)));
            } else {
                $return .= "%" . strtoupper(bin2hex($str));
            }
        }
        return $return;
    }



    /**
     * 此函数将utf8编码字串转为unicode编码字符串
     * 参数 str ,utf8编码的字符串。
     * 参数 order,存放数据格式，是big endian还是little endian，默认的unicode存放次序是little.
     * 如："大"的unicode码是 5927。little方式存放即为：27 59 。big方式则顺序不变：59 27.
     * little 存放格式文件的开头均需有FF FE。big 存放方式的文件开头为 FE FF。否则。将会产生严重混乱。
     * 本函数只转换字符，不负责增加头部。
     * iconv转换过来的字符串是 big endian存放的。
     * 返回 ucs2string , 转换过的字符串。
     * 感谢唠叨（xuzuning）
     */
    public static function utf8ToUnicode($str, $order="little")
    {
        $ucs2string ="";
        $n=strlen($str);
        for ($i=0;$i<$n ;$i++ ) {
            $v = $str[$i];
            $ord = ord($v);
            if( $ord<=0x7F){ //  0xxxxxxx
                if ($order=="little") {
                    $ucs2string .= $v.chr(0);
                }
                else {
                    $ucs2string .= chr(0).$v;
                }
            }
            elseif ($ord<0xE0 && ord($str[$i+1])>0x80) {  //110xxxxx 10xxxxxx
                $a = (ord($str[$i]) & 0x3F )<<6;
                $b =  ord($str[$i+1]) & 0x3F ;
                $ucsCode = dechex($a+$b);   //echot($ucsCode);
                $h = intval(substr($ucsCode,0,2),16);
                $l  =  intval(substr($ucsCode,2,2),16);
                if ($order=="little") {
                    $ucs2string   .= chr($l).chr($h);
                }
                else {
                    $ucs2string   .= chr($h).chr($l);
                }
                $i++;
            }elseif ($ord<0xF0  && ord($str[$i+1])>0x80  && ord($str[$i+2])>0x80) { //1110xxxx 10xxxxxx 10xxxxxx
                $a = (ord($str[$i]) & 0x1F)<<12;
                $b = (ord($str[$i+1]) & 0x3F )<<6;
                $c =  ord($str[$i+2]) & 0x3F ;
                $ucsCode = dechex($a+$b+$c);   //echot($ucsCode);
                $h = intval(substr($ucsCode,0,2),16);
                $l  =  intval(substr($ucsCode,2,2),16);
                if ($order=="little") {
                    $ucs2string   .= chr($l).chr($h);
                }
                else {
                    $ucs2string   .= chr($h).chr($l);
                }
                $i +=2;
            }
        }
        return $ucs2string;
    } // end func

    /*
    * 此函数将unicode编码字串转为utf8编码字符串
    * 参数 str ,unicode编码的字符串。
    * 参数 order ,unicode字串的存放次序，为big endian还是little endian.
    * 返回 utf8string , 转换过的字符串。
    *
    */
    public static function unicodeToUtf8($str, $order="little")
    {
        $utf8string ="";
        $n=strlen($str);
        for ($i=0;$i<$n ;$i++ ) {
            if ($order=="little") {
                $val = dechex(ord($str[$i+1])).dechex(ord($str[$i]));
            }
            else {
                $val = dechex(ord($str[$i])).dechex(ord($str[$i+1]));
            }
            $val = intval($val,16); //由于上次的.连接，导致$val变为字符串，这里得转回来。
            $i++; //两个字节表示一个unicode字符。
            $c = "";
            if($val < 0x7F){        // 0000-007F
                $c .= chr($val);
            }elseif($val < 0x800) { // 0080-0800
                $c .= chr(0xC0 | ($val / 64));
                $c .= chr(0x80 | ($val % 64));
            }else{                // 0800-FFFF
                $c .= chr(0xE0 | (($val / 64) / 64));
                $c .= chr(0x80 | (($val / 64) % 64));
                $c .= chr(0x80 | ($val % 64));
                //echot($c);
            }
            $utf8string .= $c;
        }
        return $utf8string;
    } // end func



    /*
    * 将utf8编码的字符串编码为unicode 码型，等同escape
    * 之所以只接受utf8码，因为只有utf8码和unicode之间有公式转换，其他的编码都得查码表来转换。
    * 不知道查找utf8码的正则是否完全正确。迷茫ing
    * 虽然调用utf2ucs对每个字符进行码值计算。效率过低。然而，代码清晰，要是把那个计算过程嵌入。
    * 代码就不太容易阅读了。
    */
    public static function utf8Escape($str) {
        preg_match_all("/[\xC0-\xE0].|[\xE0-\xF0]..|[\x01-\x7f]+/",$str,$r);
        //prt($r);
        $ar = $r[0];
        foreach($ar as $k=>$v) {
            $ord = ord($v[0]);
            if( $ord<=0x7F)
                $ar[$k] = rawurlencode($v);
            elseif ($ord<0xE0) { //双字节utf8码
                $ar[$k] = "%u". self::utf2ucs($v);
            }
            elseif ($ord<0xF0) { //三字节utf8码
                $ar[$k] = "%u". self::utf2ucs($v);
            }
        }//foreach
        return join("",$ar);
    }

    /**
     *
     * 把utf8编码字符转为ucs-2编码
     * 参数 utf8编码的字符。
     * 返回 该字符的unicode码值。知道了码值，你就可以使用chr将字符弄出来了。
     *
     *  原理：unicode转为utf-8码的算法是。头部固定位或。
    该过程的逆向算法就是这个函数了，头部固定位反位与。
     */

    public static function utf2ucs($str){
        $n=strlen($str);
        if ($n=3) {
            $highCode = ord($str[0]);
            $midCode = ord($str[1]);
            $lowCode = ord($str[2]);
            $a   = 0x1F & $highCode;
            $b   = 0x7F & $midCode;
            $c   = 0x7F & $lowCode;
            $ucsCode = (64*$a + $b)*64 + $c;
        }
        elseif ($n==2) {
            $highCode = ord($str[0]);
            $lowCode = ord($str[1]);
            $a   = 0x3F & $highCode;  //0x3F是0xC0的补数
            $b   = 0x7F & $lowCode;  //0x7F是0x80的补数
            $ucsCode = 64*$a + $b;
        }
        elseif($n==1) {
            $ucsCode = ord($str);
        }
        return dechex($ucsCode);
    }

    public static function timeSecondToHMS($second){
        $h = floor($second / 3600);
        $m = floor(($second - $h * 3600) / 60);
        $s = $second - $h * 3600 - $m * 60;
        $str = ($h ? $h .'小时' : '').($m ? $m . '分' : ''). ($s ? $s .'秒' : '');
        return $str;
    }

    public static function GetObjecTypeText($type)
    {
        $result = '';

        if ($type === MsTimeline::OBJECT_TYPE_EXAM) {
            $result = '[考试]';
        } elseif ($type === MsTimeline::OBJECT_TYPE_SURVEY) {
            $result = '[调查]';
        }

        return $result;
    }

    public static function GetTaskItemTypeText($type)
    {
        $result = '';

        if ($type === MsTaskItem::ITEM_TYPE_COURSE) {
            $result = '[课程]';
        } elseif ($type === MsTaskItem::ITEM_TYPE_EXAM) {
            $result = '[考试]';
        } elseif ($type === MsTaskItem::ITEM_TYPE_SURVEY) {
            $result = '[调查]';
        }

        return $result;
    }

    /**
     * 字符串中添加空格
     * @param $str 字符串
     * @param string $encoding 编码
     * @return string 带空格字符串
     */
    public static function StringAddBlank($str, $encoding = 'utf-8')
    {
        $blank_str = '';

        for ($i = 0; $i < mb_strlen($str); $i++) {
            $blank_str .= mb_substr($str, $i, 1, $encoding) . ' ';
        }

        return trim($blank_str);
    }

    /**
     * 二维数组乱序
     * @param $arr
     * @param null $key
     * @param null $value
     * @return array|bool
     */
    public static function disorder($arr, $key = null, $value = null){
        if (!is_array($arr)) return $arr;
        if (empty($key)) return shuffle($arr);
        $result = [];
        $result2 = [];
        foreach ($arr as $k => $v){
            if ((empty($value) && empty($v[$key])) || $v[$key] == $value ){
                $result2[] = $k;
            }else{
                $result[] = $v;
            }
        }
        shuffle($result);
        if ($result2){
            foreach ($result2 as $item){
                array_splice($result, $item, 0, array($arr[$item]));
            }
        }
        return $result;
    }

    /**
     * 数组转字符串（组合in查询条件）
     * @param $str_array 字符串数组
     * @return string
     */
    public static function ArrayToString($str_array)
    {
        $kids = "";

        if (!empty($str_array) && count($str_array) > 0) {
            foreach ($str_array as $key) {
                $kids = $kids . "'" . $key . "',";
            }
        }

        $kids = rtrim($kids, ",");

        return $kids;
    }

    /**
     * 获取考试发布状态文字
     * @param string $status 发布状态
     * @return string
     */
    public static function GetExamReleaseStatusText($status)
    {
        $result = '';

        if ($status === LnExamination::RELEASE_STATUS_NO) {
            $result = '未发布';
        } elseif ($status === LnExamination::RELEASE_STATUS_YES) {
            $result = '已发布';
        } elseif ($status === LnExamination::RELEASE_STATUS_END) {
            $result = '已结束';
        }

        return $result;
    }

    /**
     * 获取调查类型文字
     * @param $type 调查类型
     * @return string
     */
    public static function GetInvestigationTypeText($type)
    {
        $result = '';

        if ($type === LnInvestigation::INVESTIGATION_TYPE_SURVEY) {
            $result = '问卷';
        } elseif ($type === LnInvestigation::INVESTIGATION_TYPE_VOTE) {
            $result = '投票';
        }

        return $result;
    }

    /**
     * 获取调查回答类型文字
     * @param $type 回答类型
     * @return string
     */
    public static function GetInvestigationAnswerTypeText($type)
    {
        $result = '';

        if ($type === LnInvestigation::ANSWER_TYPE_REALNAME) {
            $result = '实名';
        } elseif ($type === LnInvestigation::ANSWER_TYPE_ANONYMOUS) {
            $result = '匿名';
        }

        return $result;
    }

    /**
     * 检查密码强度
     * @param string $str 密码字符串
     * @return int
     */
    public static function CheckPasswordStrength($str)
    {
        if (strlen($str) < 6) {
            return 0;
        }

        $score = 0;
        if (preg_match("/[0-9]+/", $str)) {
            $score++;
        }

        if (preg_match("/[a-zA-Z]+/", $str)) {
            $score++;
        }
        if (preg_match("/[~|.|,|<|>|;|:|'|\"|_|\-|+|=|*|!|@|#|$|%|^|&|(|)]+/", $str)) {
            $score++;
        }
        return $score;
    }

    /**
     * @param $string
     * @param $low::false 安全别级低
     */
    public static function clean_xss($string, $low = true)
    {
        if (! is_array ( $string ))
        {
            $string = trim ( $string );
            //$string = strip_tags ( $string );
            $string = htmlspecialchars ( $string );
            if ($low)
            {
                return $string;
            }
            $string = str_replace ( array ('"', "\\", "'", "/", "..", "../", "./", "//" ), '', $string );
            $no = '/%0[0-8bcef]/';
            $string = preg_replace ( $no, '', $string );
            $no = '/%1[0-9a-f]/';
            $string = preg_replace ( $no, '', $string );
            $no = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
            $string = preg_replace ( $no, '', $string );
            return $string;
        }
        $keys = array_keys ( $string );
        foreach ( $keys as $key )
        {
            $string [$key] = self::clean_xss ( $string [$key] , $low);
        }
        return $string;
    }

    /**
     * 返回课程状态
     * @param $courseId
     * @return string
     */
    public static function getCourseStatus($courseId){
        $model = LnCourse::findOne($courseId);
        if ($model->status == LnCourse::STATUS_FLAG_NORMAL) {
            if ($model->course_type == LnCourse::COURSE_TYPE_ONLINE) {
                $currentTime = time();

                if (!empty($model->start_time) && empty($model->end_time)) {
                    if ($currentTime < $model->start_time) {
                        return '待开课';
                    } else {
                        return '授课中';
                    }
                } else if (!empty($model->start_time) && !empty($model->end_time)) {
                    if ($currentTime < $model->start_time) {
                        return '待开课';
                    } else if ($currentTime > $model->end_time) {
                        return '已过期';
                    } else {
                        return '授课中';
                    }
                }else{
                    return '--';
                }
            } else {
                if ($model->open_status == LnCourse::COURSE_NOT_START) {
                    return '待开课';
                } else if ($model->open_status == LnCourse::COURSE_START) {
                    return '授课中';
                } else {
                    return '已完成';
                }
            }
        }else if ($model->status == LnCourse::STATUS_FLAG_TEMP){
            return '临时课程';
        }else{
            return '停用';
        }
    }

    /**
     * @param $html
     * @return mixed
     */
    public static function OutPutBr($html){
        return str_replace(array("\r\n"),array("<br />"), $html);
    }

    /**
     * 将查询数据简化
     * @param $object
     * @return array|object
     */
    public static function getObjectArray($object){
        if (empty($object)) return [];
        if (!is_object($object)) return $object;
        if (!empty($object->attributes)){
            $attribute = (object)$object->attributes;
        }else{
            $attribute = $object;
        }
        return $attribute;
    }

    /**
     * 生成二维码图片保存到本地
     * @param $string
     * @param string $type
     * @return string
     */
    public static function genQRCode($string, $type = 'course'){
        if ($type == 'course'){
            $url = \Yii::$app->urlManager->getHostInfo();
            $text = $url . \Yii::$app->urlManager->createUrl(['resource/course/scan-view', 'code' => $string]);
        }else{
            $text = $string;
        }
        $outfile = '/upload/qrcode/'.md5($string).'.png';
        if (!file_exists(\Yii::$app->basePath.'/..'.$outfile)){
            ToolInterface::genQRCode($text, QR_ECLEVEL_L, 3, 4, \Yii::$app->basePath.'/..'.$outfile);
        }
        return $outfile;
    }


}