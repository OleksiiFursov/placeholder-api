<?php


class Filter{

    function generate_date($date, $column)
    {
        if(!is_array($date)){
            if ($date === 'today') {
                $date = date('Y-m-d');
            }
            return [$column => $date];
        }else{
            $date[0] = $date[0] ?? false;
            $date[1] = $date[1] ?? false;

            if (!$date[1] && $date[0]) {
                $date = [$date[0], $date[0]];
            }else if (!$date[0] && $date[1]) {
                $date = [$date[1], $date[1]];
            }

            if ($date[1] < $date[0]) {
                $date = [$date[1], $date[0]];
            }
        }
        return [
            [$column, '~CAST("' . $date[0] .(strlen($date[0])<11? ' 00:00:00': '').'" AS DATETIME)', '>='],
            [$column, '~CAST("' . $date[1] .(strlen($date[0])<11? ' 23:59:59': '').'" AS DATETIME)', '<=']
        ];
    }

    function date(&$filters, $column='date', &$sql=null){

        if ($date = remove_item($filters, $column, remove_item($filter, 'date'))) {

          $res = $this->generate_date($date, $column);

            if($sql){
                return $sql->where($res);
            }else{
                return $res;
            }

        }
    }
}