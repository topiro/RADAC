<?php
session_start();
include "func.php";
require_once __DIR__ . "/vendor/autoload.php";
$ret = [];
try {
        $m = \cfg\connect();
        if (is_null($m)) {
            throw new \Exception("Unable To Connect To Database");
        }
        // "start":start,
        //                 "end":end,
        //                 "timezone":this.timeZone,
        //                 "doc":this.selectedData
        
        if(!isset($_GET["start"])||!isset($_GET["end"])||!isset($_GET["timezone"])||!isset($_GET["doc"])){
            throw new \Exception("incomplete input");
        }
        $stz = $_GET["timezone"];
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

        $format = "Y-m-d H:i:s";
        $start = \DateTime::createFromFormat($format, $_GET["start"],new \DateTimeZone($tz));
        $end = \DateTime::createFromFormat($format, $_GET["end"],new \DateTimeZone($tz));
        
        if($start>$end){
            throw new \Exception("Invalid Date");
        }
        $doc = $_GET["doc"];

        $mongoDateStart = new \MongoDB\BSON\UTCDateTime($start->getTimestamp() * 1000);
        $mongoDateEnd = new \MongoDB\BSON\UTCDateTime($end->getTimestamp() * 1000);

        $doc = $_GET["doc"];
        $res = $m->$doc->find([
            "date" => ['$gte' => $mongoDateStart,'$lte' => $mongoDateEnd],
            // "date" => ['$gte' => $mongoDateStart],
            // "date" => ['$lte' => $mongoDateEnd],
            // "value"=>"777777"
        ], [
            "sort" => ["date" => 1],
            "limit" => 1
        ]);
        $resArray = $res->toArray();
        // var_dump($resArray);
        $resCount = count($resArray);
        $data = [];
        if ($resCount > 0) {
            // foreach ($resArray as $k => $v) {
            //     $d = $v->date->toDateTime()->setTimezone(new \DateTimeZone($tz));
            //     $val = $v->value;
            //     if($doc=="Hm0"){
            //         $val=$val/100;
            //     }
            //     $val=round($val,2);
            //     array_push($data, [$d->format("Y-m-d H:i:s"), $val]);
            // } 
            $_SESSION["download"] = true;
            $_SESSION["data"] = [
                "start"=>$start->format($format),
                "end"=>$end->format($format),
                "timezone"=>$stz,
                "stringTimezone"=>$tz,
                "doc"=>$doc
            ];       
        }else{
            throw new \Exception("Empty Result");
        }
        // var_dump($_SESSION["data"]);
        $ret["tz"]=$stz;
        $ret["tzString"]=$tz;
        $ret["data"]=$data;
        $ret["status"] = 1;
    } catch (\Exception $e) {
        $ret["error"] = $e->getMessage();
        $ret["status"] = 0;
    } finally {
        echo json_encode($ret);
    }
