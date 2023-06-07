<?php
define('DATE_FORMAT', 'y-m-d h:i:s');
function todatetime($a){
    $month_all = [['Январь'], ['Февраль'], ['Март'], ['Апрель'], ['Май'], ['Июнь'], ['Июль'], ['август', 'августа'], ['Сентябрь'], ['Октябрь'], ['Ноябрь'], ['Декабрь']];

    if(is_string($a)){
        if (preg_match('/[А-Яа-я]/', $a)){

            $date = explode(" ", $a);
            $day = $date[0];
            $yyyy = $date[2];
            $mm = 0;
            $month = mb_strtolower($date[1]);
            foreach ($month_all as $i => $m){
                if (in_array($month, $m)) {
                    $mm = $i+1;
                    if($i+1 < 10){
                        $mm = '0'.$mm;
                    }
                }
            }
            $date = $yyyy.'-'.$mm.'-'.$day;
        }


    }
    return $date;
}

