<?php
session_start();
include "func.php";
require_once __DIR__ . "/vendor/autoload.php";
$ret = [];
function wind_cardinals($deg) {
	$cardinalDirections = array(
		'N' => array(348.75, 360),
		'N' => array(0, 11.25),
		'NNE' => array(11.25, 33.75),
		'NE' => array(33.75, 56.25),
		'ENE' => array(56.25, 78.75),
		'E' => array(78.75, 101.25),
		'ESE' => array(101.25, 123.75),
		'SE' => array(123.75, 146.25),
		'SSE' => array(146.25, 168.75),
		'S' => array(168.75, 191.25),
		'SSW' => array(191.25, 213.75),
		'SW' => array(213.75, 236.25),
		'WSW' => array(236.25, 258.75),
		'W' => array(258.75, 281.25),
		'WNW' => array(281.25, 303.75),
		'NW' => array(303.75, 326.25),
		'NNW' => array(326.25, 348.75)
	);
	foreach ($cardinalDirections as $dir => $angles) {
        if ($deg >= $angles[0] && $deg < $angles[1]) {
            $cardinal = $dir;
        }
    }
    return $cardinal;
}
try {
    $m = \cfg\connect();
    if (is_null($m)) {
        throw new \Exception("Unable To Connect To Database");
    }
    if (!isset($_GET['tz'])) {
        $tz = "Asia/Jakarta";
    } else {
        $stz = $_GET['tz'];
        switch ($stz) {
            case "WIB":
                $tz = "Asia/Jakarta";
                break;
            case "WITA":
                $tz = "Asia/Makassar";
                break;
            case "WIT":
                $tz = "Asia/Jayapura";
                break;
            case "UTC":
                $tz = "UTC";
                break;
            default:
                $tz = "UTC";
        }
        $_SESSION["timezone"]=$stz;
        $_SESSION["timezonestr"]=$tz;
    }
    if (!isset($_GET['hgo'])) {
        $hgo = 1;
    }else{
        $hgo = $_GET['hgo'];
    }
    $now = new \DateTime('NOW', new \DateTimeZone($tz));
    $hourAgo = $now->modify("-$hgo Hours");
    $minute = floor($hourAgo->format("i")/10)*10;
    $hourAgo->setTime($hourAgo->format("H"), $minute, 0);
    $mongoDate = new \MongoDB\BSON\UTCDateTime($hourAgo->getTimestamp() * 1000);
    $dc="Hm0";
    $res = $m->$dc->find([
        "date" => ['$gte' => $mongoDate],
        "value"=> ['$ne'=>"NaN"]
        // "value"=>"777777"
    ], [
        "sort" => ["date" => 1],
    ]);
    $dc="Th0";
    $res2 = $m->$dc->find([
        "date" => ['$gte' => $mongoDate],
        "value"=> ['$ne'=>"NaN"]
        // "value"=>"777777"
    ], [
        "sort" => ["date" => 1],
    ]);

    $resArray = $res->toArray();
    $resCount = count($resArray);
    $resArray2 = $res2->toArray();
    $resCount2 = count($resArray2);
    $dataWaveHeight = [];
    $dataWaveHeightGrouped = [];
    $dataWaveDirection = [];
    //GROUP DATA BY 2 Hour DOIT TOMOROW
    if ($resCount > 0 && $resCount2 > 0) {
        // foreach ($resArray as $k => $v) {
        //     $d = $v->date->toDateTime()->setTimezone(new \DateTimeZone($tz));
        //     $val = $v->value;
        //     $val = round($val/100,2);
        //     array_push($dataWaveHeight, [
        //         "udate"=>$d->format("Y-m-d H:i:s"), 
        //         "value"=>$val,
        //         "UTCdate"=>$v->date->toDateTime()->format("Y-m-d H:i:s")]);
        // }
        // foreach ($resArray2 as $k => $v) {
        //     $d = $v->date->toDateTime()->setTimezone(new \DateTimeZone($tz));
        //     $val = $v->value;
        //     $val = round($val,2);
        //     array_push($dataWaveDirection, [$d->format("Y-m-d H:i:s"), $val,$v->date->toDateTime()->format("Y-m-d H:i:s")]);
        // }

        //GROUP TO 10 Minutes
        // if(count($dataWaveHeight>0)){
        //     foreach ($dataWaveHeight as $k=>$v){
        //         // if(!isset)
        //     }
        // }
        $avg=[];
        foreach ($resArray as $k => $v) {
            $d = $v->date->toDateTime()->setTimezone(new \DateTimeZone($tz));
            $tmpd = $d;
            $tmpminute = floor($d->format("i")/10)*10;
            $tmpd->setTime($tmpd->format("H"),$tmpminute,0);
            $key = $tmpd->format("Y-m-d H:i:s"); 
            $val = $v->value;
            $val = round($val/100,2);
            if(!isset($avg[$key])){
                $tmp = $tmpd;
                $tmp->modify("+10 minutes");
                $avg[$key]=[];
                $avg[$key]["to"]=$tmp->format("Y-m-d H:i:s");
                $avg[$key]["data"]=[];
            }
            array_push($avg[$key]["data"],$val);
            // array_push($dataWaveHeight, [
            //     "udate"=>$d->format("Y-m-d H:i:s"), 
            //     "value"=>$val,
            //     "UTCdate"=>$v->date->toDateTime()->format("Y-m-d H:i:s")]);
        }
        $processedAvg=[];
        foreach($avg as $k=>$v){
            $el = count($v["data"]);
            if($el>0){
                $processedAvg[$k]=[$v["to"],round(array_sum($v["data"])/$el,2)];
            }
        }
        $dataWaveHeight=$processedAvg;

        $avg=[];
        foreach ($resArray2 as $k => $v) {
            $d = $v->date->toDateTime()->setTimezone(new \DateTimeZone($tz));
            $tmpd = $d;
            $tmpminute = floor($d->format("i")/10)*10;
            $tmpd->setTime($tmpd->format("H"),$tmpminute,0);
            $key = $tmpd->format("Y-m-d H:i:s"); 
            $val = $v->value;
            $val = round($val,2);
            if(!isset($avg[$key])){
                $tmp = $tmpd;
                $tmp->modify("+10 minutes");
                $avg[$key]=[];
                $avg[$key]["to"]=$tmp->format("Y-m-d H:i:s");
                $avg[$key]["data"]=[];
            }
            array_push($avg[$key]["data"],$val);
            // array_push($dataWaveHeight, [
            //     "udate"=>$d->format("Y-m-d H:i:s"), 
            //     "value"=>$val,
            //     "UTCdate"=>$v->date->toDateTime()->format("Y-m-d H:i:s")]);
        }
        $processedAvg=[];
        foreach($avg as $k=>$v){
            if(!isset($dataWaveHeight[$k])){
                // echo "NO DATA ".$k."<br/>";
                $el = count($v["data"]);
                if($el>0){
                    $deg = round(array_sum($v["data"])/$el,2);
                    $card = wind_cardinals($deg);
                    $processedAvg[$k]=[$v["to"],$deg,null,$card];
                }
            }else{
                // echo "ADD DATA ".$k."<br/>";
                $el = count($v["data"]);
                if($el>0){
                    $deg = round(array_sum($v["data"])/$el,2);
                    $card = wind_cardinals($deg);
                    $processedAvg[$k]=[$v["to"],$deg,0.5,$card];
                }
            } 
            
        }
        $dataWaveDirection=$processedAvg;
    }
    // $ret["avg"]=$avg;
    // $ret["processedavg"]=$processedAvg;
    $ret["dataWH"] = $dataWaveHeight;
    $ret["dataWHTotal"] = count($dataWaveHeight);
    $ret["dataWHUnit"] = "m";
    $ret["dataWD"] = $dataWaveDirection;
    $ret["dataWDTotal"] = count($dataWaveDirection);
    $ret["dataWDUnit"] = "deg";
    $ret["status"] = 1;
}catch (\Exception $e) {
    $ret["error"] = $e->getMessage();
    $ret["status"] = 0;
} finally {
    echo json_encode($ret);
}