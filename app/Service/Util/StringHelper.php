<?php

namespace Service\Util;

class StringHelper
{
    /**
     * How much time ago formatter
     *
     * @param string|integer $time
     *
     * @return mixed|string
     */
    public static function howMuchAgo($time): string
    {
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        $sec = time() - $time;

        $days = floor($sec / 86400);
        $hours = floor($sec / 3600);
        $minutes = floor(($sec - ($hours * 3600)) / 60);

        if ($days) {
            return self::plural( $days, ' day ago',' days ago');
        }
        if ($hours) {
            return self::plural($hours, ' hour ago',' hours ago', null, $hours);
        }
        if ($minutes) {
            return self::plural($minutes, ' minute ago',' minutes ago', null, $minutes);
        }
        if ($sec > 0) {
            return $sec . ' sec ago';
        }

        return $sec === 0 ? 'right now' : '';
    }

    public static function plural($count, $form1, $form2 = null, $form3 = null, $nullForm = null, $printNumber = true)
    {
        if (!$count && !is_null($nullForm)){
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
