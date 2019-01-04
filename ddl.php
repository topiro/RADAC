<?php
session_start();
include "func.php";
require_once __DIR__ . "/vendor/autoload.php";
$ret = [];
function array2csv(array &$array)
{
   if (count($array) == 0) {
     return null;
   }
   ob_start();
   $df = fopen("php://output", 'w');
//    fputcsv($df, array_keys(reset($array)));
   foreach ($array as $row) {
      fputcsv($df, $row);
   }
   fclose($df);
   return ob_get_clean();
}
function download_send_headers($filename) {
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    // header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download  
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}
try {
        $m = \cfg\connect();
        if (is_null($m)) {
            throw new \Exception("Unable To Connect To Database");
        }
        // "start":start,
        //                 "end":end,
        //                 "timezone":this.timeZone,
        //                 "doc":this.selectedData
        if(!isset($_SESSION["download"])||$_SESSION["download"]==false){
            throw new \Exception("Download Expired, Please Try Requesting New Download");
        }
        $data = $_SESSION["data"];
        if(!isset($data["start"])||!isset($data["end"])||
        !isset($data["timezone"])||!isset($data["doc"])){
            throw new \Exception("incomplete input");
        }
        $stz = $data["timezone"];
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
                $tz = null;
        }
        if($tz==null){
            throw new \Exception("Unable TO Detect TimeZone Please Try Request New Download");
        }

        $format = "Y-m-d H:i:s";
        $start = \DateTime::createFromFormat($format, $data["start"],new \DateTimeZone($tz));
        $end = \DateTime::createFromFormat($format, $data["end"],new \DateTimeZone($tz));
        
        if($start>$end){
            throw new \Exception("Invalid Date");
        }
        $doc = $data["doc"];
        
        $_SESSION["download"] = false;
        $mongoDateStart = new \MongoDB\BSON\UTCDateTime($start->getTimestamp() * 1000);
        $mongoDateEnd = new \MongoDB\BSON\UTCDateTime($end->getTimestamp() * 1000);

        $res = $m->$doc->find([
            "date" => ['$gte' => $mongoDateStart,'$lte' => $mongoDateEnd],
            // "value"=>"777777"
        ], [
            "sort" => ["date" => 1],
        ]);
        $resArray = $res->toArray();
        // var_dump($resArray);
        $resCount = count($resArray);
        $data = [
            ["DateTime ($stz)","value"]
        ];
        if ($resCount > 0) {
            foreach ($resArray as $k => $v) {
                $d = $v->date->toDateTime()->setTimezone(new \DateTimeZone($tz));
                $val = $v->value;
                if($doc=="Hm0"){
                    $val=$val/100;
                }
                $val=round($val,2);
                $push = [$d->format("Y-m-d H:i:s"), $val];
                // echo $push[0]." ".$push[1]."<br/>";
                array_push($data, $push);
            }  
            // var_dump($data);  
            $filename=$doc."_".$stz."_".$start->format("YmdHis")."_".$end->format("YmdHis").".csv";
            download_send_headers($filename);
            echo array2csv($data);
            // die();
        }else{
            throw new \Exception("Empty Result");
        }
    } catch (\Exception $e) {
        $ret["error"] = $e->getMessage();
        $ret["status"] = 0;
        echo json_encode($ret);
    } 
