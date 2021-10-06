<?php

namespace App\Traits;

trait TimeagoTrait
{
    public function timeago($timestamp)
    {
        $time_ago = strtotime($timestamp);
        $current_time    = time();
        $time_difference = $current_time - $time_ago;
        $seconds         = $time_difference;

        $minutes = round($seconds / 60); // value 60 is seconds
        $hours   = round($seconds / 3600); //value 3600 is 60 minutes * 60 sec
        $days    = round($seconds / 86400); //86400 = 24 * 60 * 60;
        $weeks   = round($seconds / 604800); // 7*24*60*60;
        $months  = round($seconds / 2629440); //((365+365+365+365+366)/5/12)*24*60*60
        $years   = round($seconds / 31553280); //(365+365+365+365+366)/5 * 24 * 60 * 60

        if ($seconds <= 60) {

            return "Just now";
        } else if ($minutes <= 60) {

            if ($minutes == 1) {

                return "1m ago";
            } else {

                return $minutes . "m ago";
            }
        } else if ($hours <= 24) {

            if ($hours == 1) {

                return "1hr ago";
            } else {

                return $hours . "hrs ago";
            }
        } else if ($days <= 7) {

            if ($days == 1) {

                return "1d ago";
            } else {

                return $days . "d ago";
            }
        } else {
            return getdate($time_ago)['month'] . " " . getdate($time_ago)['wday'] . ", " . getdate($time_ago)['year'];
        }
    }
}