<?php
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
define('ARRAY_SIZE',20000);
function QuickSort($arr,$low,$high)
{
    if($low>$high)
        return ;
    $begin=$low;
    $end=$high ;
    $key=$arr[$begin];
    while($begin<$end)
    {
        while($begin<$end&&$arr[$end]>=$key)
            --$end ;
        $arr[$begin]=$arr[$end];
        while($begin<$end&&$arr[$begin]<=$key)
            ++$begin;
        $arr[$end]=$arr[$begin];

    }
    $arr[$begin]=$key;
    QuickSort($arr,$low,$begin-1);
    QuickSort($arr,$begin+1,$high);
}
$time_start = microtime(true);
$arr=array();
for($i=0;$i<ARRAY_SIZE;$i++)
{
    array_push($arr,rand(1,ARRAY_SIZE));
}
QuickSort($arr,0,ARRAY_SIZE-1);
$time_end = microtime(true);
$resultTime = ($time_end - $time_start);
echo Yii::t('common', 'action_start_at')."：" . $time_start . "<br>";
echo Yii::t('common', 'end_time')."：" . $time_end. "<br>";
echo "排序" . ARRAY_SIZE . "条数据，执行耗时：" . strval($resultTime) . "秒<br>";
echo "<br>";
?>

<?
$startTime = microtime(true);
$count = 10000000;
$result = 0;
for ($i = 0; $i < $count; $i++) {
    $result += $result;
}

$endTime = microtime(true);
$resultTime = ($endTime - $startTime);
echo Yii::t('common', 'action_start_at')."：" . $startTime . "<br>";
echo Yii::t('common', 'end_time')."：" . $endTime. "<br>";
echo "循环" . $count . "条数据，执行耗时：" .$resultTime . "秒";
?>