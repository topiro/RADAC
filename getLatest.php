<?php
session_start();
include "func.php";
require_once __DIR__ . "/vendor/autoload.php";
$tz = $_SESSION['timezonestr'];
$ret=[];
function wind_cardinals($deg) {
    // $deg = floatval($deg);
	$cardinalDirections = array(
		'N' => array(348.75, 360.0),
		'N' => array(0.0, 11.25),
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
    // $docList = ["H10","Hm0","Th0","Tm02"];
    $docList = ["Hm0","Th0","Tm02"];
    $now = new \DateTime('NOW', new \DateTimeZone($tz));
    $mongoDate = new \MongoDB\BSON\UTCDateTime($now->getTimestamp() * 1000);
    $tmp=[];
    foreach($docList as $k=>$doc){
        $res = $m->$doc->findOne([
            "date" => ['$lte' => $mongoDate],
            "value"=> ['$ne'=>"NaN"]
            // "value"=>"777777"
        ], [
            "sort" => ["date" => -1],
            "limit" => 1
        ]);
        $d = $res->date->toDateTime()->setTimezone(new \DateTimeZone($tz));
        // $resArray = $res->toArray();
        $v=round($res->value,2);
        if($doc=="Hm0"){
            $v=round($v/100,2);
        }else{
            $v=$v;
        }
        switch($doc){
            case "Hm0":
                $dataUnit="m";
                break;
            case "H10":
                $dataUnit="cm";
                break;
            case "Th0":
                $dataUnit="°";
                break;
            case "Tm02":
                $dataUnit="s";
                break;
            default:
                $dataUnit="";
        }
        if($doc == "Th0"){
            $dir = wind_cardinals($v);
            $tmp[$doc]=["date"=>$d->format("d/m/Y H:i:s"),"val"=>$v,"type"=>"cardinal","cardinal"=>$dir,"dataUnit"=>$dataUnit];
        }else{
            $tmp[$doc]=["date"=>$d->format("d/m/Y H:i:s"),"val"=>$v,"type"=>"scalar","dataUnit"=>$dataUnit];
        }
        
    }    
    $ret["data"]=$tmp;
    $ret["status"] = 1;
} catch (\Exception $e) {
    $ret["error"] = $e->getMessage();
    $ret["status"] = 0;
} finally {
    echo json_encode($ret);
};
