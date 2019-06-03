<?php
namespace common\helpers;

use yii;

class TTimeHelper
{
    const DATE_FORMAT_1 = 'Y年m月d日 H:i';

    public static function getCurrentTime()
    {
        date_default_timezone_set('PRC');
        return date('Y-m-d H:i:s', time());
    }

    public static function showTime($time, $format = 'Y-m-d')
    {
        echo date($format, strtotime($time));
    }

    public static function toDateTime($time, $format = 'Y-m-d H:i:s')
    {
        return date($format, $time);
    }

    public static function toDate($time, $format = 'Y-m-d')
    {
        return date($format, $time);
    }

    public static function toTime($time, $format = 'H:i:s')
    {
        return date($format, $time);
    }

    /**
     * 取得当天 开始时间
     * @return string
     */
    public static function getCurrentDayStart($time = "")
    {
        if ($time) {
            return date('Y-m-d', $time) . ' 00:00:00';
        } else {
            return date('Y-m-d') . ' 00:00:00';
        }

    }

    /**
     * 取得当天 结束时间
     * @return string
     */
    public static function getCurrentDayEnd($time = "")
    {
        if ($time) {
            return date('Y-m-d', $time) . ' 23:59:59';
        } else {
            return date('Y-m-d') . ' 23:59:59';
        }

    }

    /**
     * 取得当月第一天
     * @return string
     */
    public static function getCurrentMonthFirstDay()
    {
        return date('Y-m') . '-01 00:00:00';
    }


    /**
     * 取得次月第一天
     * @return string
     */
    public static function getNextMonthFirstDay()
    {
        return date('Y') . '-' . (date('m') + 1) . '-01 00:00:00';
    }

    /**
     * 取得当年第一天
     * @return string
     */
    public static function getCurrentYearFirstDay()
    {
        return date('Y') . '-01-01 00:00:00';
    }

    /**
     * 取得次年第一天
     * @return string
     */
    public static function getNextYearFirstDay()
    {
        return (date('Y') + 1) . '-01-01 00:00:00';
    }

    /**
     * 获取两月前第一天
     * @return string
     */
    public static function getLastTwoMonthFirstDay()
    {
        return date('Y-m', strtotime('-2 month')) . '-01 00:00:00';
    }

    /**
     * 获取两月后的最后一天
     * @return string
     */
    public static function getNextTwoMonthLastDay()
    {
        $time = strtotime(date('Y-m', strtotime('+3 month')) . '-01 00:00:00') - 1;
        return date('Y-m-d H:i:s', $time);
    }

    /**
     * 时间差计算并转汉字
     * @param Timestamp $time
     * @return String Time Elapsed
     */
    public static function timeDiffToStr($time)
    {
        $year = floor($time / 60 / 60 / 24 / 365);
        $time -= $year * 60 * 60 * 24 * 365;
        $month = floor($time / 60 / 60 / 24 / 30);
        $time -= $month * 60 * 60 * 24 * 30;
        $week = floor($time / 60 / 60 / 24 / 7);
        $time -= $week * 60 * 60 * 24 * 7;
        $day = floor($time / 60 / 60 / 24);
        $time -= $day * 60 * 60 * 24;
        $hour = floor($time / 60 / 60);
        $time -= $hour * 60 * 60;
        $minute = floor($time / 60);
        $time -= $minute * 60;
        $second = $time;
        $elapse = '';

        $unitArr = array('年' => 'year', '个月' => 'month', '周' => 'week', '天' => 'day',
            '小时' => 'hour', '分钟' => 'minute', '秒' => 'second'
        );

        foreach ($unitArr as $cn => $u) {
            if ($$u > 0) {
                $elapse = $$u . $cn;
                break;
            }
        }

        return $elapse;
    }

    /**
     * 学习时间转换文字（忽略秒）
     * @param Timestamp $time
     * @return String Time Elapsed
     */
    public static function learningTimeToStr($time)
    {
        $year = floor($time / 60 / 60 / 24 / 365);
        $time -= $year * 60 * 60 * 24 * 365;
        $month = floor($time / 60 / 60 / 24 / 30);
        $time -= $month * 60 * 60 * 24 * 30;
        $week = floor($time / 60 / 60 / 24 / 7);
        $time -= $week * 60 * 60 * 24 * 7;
        $day = floor($time / 60 / 60 / 24);
        $time -= $day * 60 * 60 * 24;
        $hour = floor($time / 60 / 60);
        $time -= $hour * 60 * 60;
        $minute = floor($time / 60);
        $time -= $minute * 60;
        $second = $time;
        $elapse = '';

        $unitArr = array('年' => 'year', '个月' => 'month', '周' => 'week', '天' => 'day',
            '小时' => 'hour', '分钟' => 'minute'
        );

        foreach ($unitArr as $cn => $u) {
            if ($$u > 0) {
                $elapse = $$u . $cn;
                break;
            }
        }

        return $elapse;
    }

    /**
     * 经理时间转换文字
     * @param Timestamp $time
     * @return String Time Elapsed
     */
    public static function managerTimeToStr($time)
    {

        $day = floor($time / 60 / 60 / 24);
        $time -= $day * 60 * 60 * 24;
        $hour = floor($time / 60 / 60);
        $time -= $hour * 60 * 60;
        $minute = floor($time / 60);


        if ($day > 0) {
            return $day . '天 ' . $hour . '小时';
        } else {
            return $hour . '小时 ' . $minute . '分钟';
        }


    }

    /**
     * 取得课程过期标签（空、快过期、已过期）
     * @param $end_time 课程截止时间
     * @return string ''、'快过期'、'已过期'
     */
    public static function getCourseExpiredTag($end_time)
    {
        $current = time();

        if ($end_time == 0) {
            return '';
        }
        if ($end_time < $current) {
            return Yii::t('frontend', 'expired');
        } else {
            $end_date = date('Y-m-d', $end_time);
            $current_date = date('Y-m-d', $current);

            $date = floor((strtotime($end_date) - strtotime($current_date)) / 86400);

            return $date <= 3 ? Yii::t('frontend', 'fast_expired') : '';
        }
    }

    /**
     * 取得课程截止时间
     * @param $end_time 课程截止时间
     * @return string '无','xxxx-xx-xx xx:xx:xx'
     */
    public static function getCourseEndtime($end_time)
    {
        if ($end_time == 0) {
            return '无';
        }

        return self::toDateTime($end_time);
    }

    /**
     * 校验日期格式是否正确
     *
     * @param string $date 日期
     * @param string $formats 需要检验的格式数组
     * @return boolean
     */
    public static function checkDate($date, $formats = array("Y-m-d", "Y/m/d"))
    {
        $unixTime = strtotime($date);
        if (!$unixTime) { //strtotime转换不对，日期格式显然不对。
            return false;
        }
        //校验日期的有效性，只要满足其中一个格式就OK
        foreach ($formats as $format) {
            if (date($format, $unixTime) == $date) {
                return true;
            }
        }

        return false;
    }

    /**
     * 持续时间转换
     * @param $m 分钟
     * @return string
     */
    public static function timeConvert($m)
    {
        if ($m < 60) {
            return $m . '分钟';
        }
        $h = $m / 60;
        if ($h < 12) {
            return $h . '小时';
        }
        if ($h === 12) {
            return '0.5天';
        }
        $d = $h / 24;

        return $d . '天';
    }

    /**
     * 课程有效期计算
     * @param $start_time 开始时间
     * @param $end_time 结束时间
     * @return string
     */
    public static function CourseValidity($start_time, $end_time)
    {
        $result = '';
        if (!empty($end_time)) {
            $result = self::toDate($start_time) . '~' . self::toDate($end_time);
        } else {
            $result = '永久';
        }

        return $result;
    }
    
    
    /**
    * 时间格式化
    * add by baoxianjian 10:17 2016/1/19
    * @param string $time
    * @param int $style
    */
    public static function FormatTime($time,$style=0)
    {
        if(empty($time)) return "";
        $format_list=array(0=>'Y-m-d',
                 1=>'Y-m-d H',
                 2=>'Y-m-d H:i',
                 3=>'Y-m-d H:i:s',
                 4=>'H:i'
        );
        if(!$format_list[$style]){$style=0;}                                            
        return date($format_list[$style], $time); 
    }

    /**
     * 得到整天的时间戳
     * add by baoxianjian 16:01 2016/5/10
     * @return int
     */
    public static function getDateInt($time=0)
    {
        if(!$time){$time=time();}
        return strtotime(date('Y-m-d',$time));
    }
}