<?php

namespace DAO;


use DateInterval;
use DateTime;
use DateTimeZone;
use Models\JsonCode;

class WorkShift
{
    public $position;
    public $nbr_shift;
    public $nextDay;
    public $pos_day_inArray;
    public $curr_day;
    /**
     * WorkShift constructor.
     */
    public function __construct()
    {
    }

    public function checkDay($day){
        $week = array("MON", "TUE", "WED", "THU", "FRI", "SAT", "SUN");
        if (in_array($day,$week)){
            return true;
        }else{
            return false;
        }

    }

    public function workDays($day){
        $tab_workdays[]=strtoupper($day);
        return $tab_workdays;
    }


     public function verifyTime($date)
    {
        if (preg_match("/^([01]?[0-9]|2[0-3])\:+[0-5][0-9]$/", $date)){
            return true;
        }else{
            return false;
        }
    }

    public function numberOfShift($tab){

    $taille = count($tab);
    if ($taille > 3){
        return false;
    }else{
        return true;
    }
}

    public function isValide_Shift($tab){
        $validity = true;
        foreach($tab as $day => $x_value) {

               $data = $this->workDays($day);

               if (in_array($day,$data) ){
                   return false;
               }
                if ($this->checkDay(strtoupper($day))) {
                    if ($this->numberOfShift($x_value)) {
                        for ($row = 0; $row < count($x_value); $row++) {
                            for ($col = 0; $col < count($x_value[$row]); $col++) {

                                if ($x_value[$row][$col] != null) {

                                    $res_verTime = $this->verifyTime($x_value[$row][$col]);

                                    if ($res_verTime != "true") {
                                        return false;
                                    }
                                } else {

                                    return false;
                                }
                            }
                        } //END For
                    }else{
                        return false;
                    }
                } else {
                    return false;
                }

        }
        return array($validity);
    }

    public function deleteShiftInvalid($tab){
        for ($row = 0; $row < count($tab); $row++) {
            for ($col = 0; $col < count($tab[$row]); $col++) {


                $val =   $tab[$row][$col];

                if ($val == null){
                    array_splice($tab, $row, 1);
                   // $res = new JsonCode(0,"check the".$row."shift");
                    //echo json_encode($res);
                }
              //  echo   json_encode($tab[$row][$col]);

            }
        }
        return $tab;
    }


    public function comparing_day($date){

        //$date = '2014-02-25';
       var_dump(date('D', strtotime($date)));
       /* $tHolder = '12-05-12';
        $voteDate = date("y-m-d", strtotime($tHolder));
        $today = date("y-m-d", strtotime("today"));*/
    }


    public function dayInShift($day,$today){
        $data = $this->workDays($day);
        if ($res = in_array(strtoupper($today),$data)){
           return true;
        }else{
            return false;
        }
    }
   public function getDayFromDate($date){
       return(date('D', strtotime($date)));
    }

    function validateDate($date)
    {
        $format = 'Y-m-d';
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public function openingTime($tab){
        $status = 'closed';
        try {
            $nowTime =  date('H:i:s');
           // echo json_encode( $nowTime);
        } catch (\Exception $e) {
        }

        $currentDateTime = date('Y-m-d H:i:s');
        $today = $this->getDayFromDate($currentDateTime);

        foreach($tab as $day => $x_value) {


            if ($res = $this->dayInShift($day,$today)){
               $this->pos_day_inArray = array_search($day, array_keys($tab)) + 1;
                $allKeys = array_keys($tab);
//            echo json_encode($this->pos_day_inArray);
//            echo json_encode(count($tab));
                if (count($tab)>$this->pos_day_inArray){
                    $this->nextDay = $allKeys[$this->pos_day_inArray];
                    $this->curr_day = $allKeys[$this->pos_day_inArray-1];
                }

                for ($row = 0; $row < count($x_value); $row++) {

                    $open =  ($x_value[$row][0]);
                    $openTime = date('H:i', strtotime($open));
                    $close = ($x_value[$row][1]);
                    $closeTime = date('H:i', strtotime($close));


                    if($closeTime <= $openTime){
                        $date = new DateTime();
                        $closeTime = $date->add(new DateInterval("P1D"));

                    }

                    if ($nowTime > $openTime && $nowTime < $closeTime){
                        $status = 'open';

                        if(date('H:i', strtotime($close)) <= $openTime){
                            $start_date = new DateTime($nowTime,new DateTimeZone('Pacific/Nauru'));
                            $end_date = new DateTime(date('H:ia', strtotime($close)), new DateTimeZone('Pacific/Nauru'));
                        }else{
                            $start_date = new DateTime($nowTime,new DateTimeZone('Pacific/Nauru'));
                            $end_date = new DateTime($closeTime, new DateTimeZone('Pacific/Nauru'));
                        }

                        $interval = $start_date->diff($end_date);
                        $hours   = $interval->format('%h');
                        $minutes = $interval->format('%i');
                        $will_close = 'Will close in: '.$hours.' hours and '.$minutes.' minutes';
                        echo json_encode($will_close);

                    }else{
                        $this->nbr_shift = count($x_value);
                        $this->position = $x_value[$row];


                    }

                }

                }

        }

        if ($status == 'closed'){

            echo json_encode( $this->position);
            echo json_encode( $this->nextDay);
            if ($this->nextDay ==null){
                $status ='soon ';
            }else{
                $start_date = new DateTime($nowTime,new DateTimeZone('Pacific/Nauru'));
                $open_date = new DateTime(date('H:ia', strtotime($tab[$this->curr_day][0][0])), new DateTimeZone('Pacific/Nauru'));
                $interval = $start_date->diff($open_date);
                $hours   = $interval->format('%h');
                $minutes = $interval->format('%i');
                $will_close = 'Will open in: '.$hours.' hours and '.$minutes.' minutes';
                echo json_encode($will_close );
            }

        }
    echo $status;
        return $status;
    }


}
