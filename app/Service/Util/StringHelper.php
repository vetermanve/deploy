<?php
namespace Service\Util;

    
class StringHelper
{

    /**
     * сколько времени назад
     *
     * @param $time
     *
     * @return mixed|string
     */
    public static function lvdateBack($time)
    {
        if(!is_numeric($time)) {
            $time = strtotime($time);
        }

        $sec = time()-$time;

        $days = floor($sec / 86400);
        $hours = floor($sec / 3600);
        $minutes = floor(($sec - ($hours * 3600)) / 60);
        $hoursRus = ''; // 1,21 час, 2,22,23,24,25 часа, часов
        $minsRus = '';

        if ($days) {
            return self::plural( $days, ' день назад',' дня назад',' дней назад');
        }
        if ($hours) {
            return self::plural($hours, ' час назад',' часа назад',' часов назад', $hours);
        }
        if ($minutes) {
            return self::plural($minutes, ' минуту назад',' минуты назад',' минут назад', $minutes);
        }
        if ($sec > 0 && !$hours && !$minutes) {
            return $sec . ' сек. назад';
        }
        if ($sec == 0) {
            return 'только что';
        }
    }

    public static function plural($count, $form1, $form2 = null, $form3 = null, $nullForm = null, $printNumber = true)
    {
        if(!$count && !is_null($nullForm)){
            return ($printNumber ? (int) $count.' ' : '') . $nullForm;
        }
        $form2 = is_null($form2) ? $form1 : $form2;
        $form3 = is_null($form3) ? $form2 : $form3;

        return self::getNumberDependedString($count, array($form1, $form2, $form3), $printNumber);
    }

    private static function getNumberDependedString($number, $titles, $printNumber = true)
    {
        $absNumber = abs($number);
            $cases = array (2, 0, 1, 1, 1, 2);
            $form = $titles[ ($absNumber % 100 > 4 && $absNumber % 100 < 20) ?  2 : $cases[min($absNumber % 10, 5)] ];

        return ($printNumber ? $number . ' ' : '') . $form;
    }

}
 