<?php 
    session_start();
    include "func.php";
    require_once __DIR__ . "/vendor/autoload.php";
    $m = \cfg\connect();
    if (is_null($m)) {
        echo "NULL";
        echo "Unable To Connect To Database";die();
    }
    $tz = "WIB";
    if(isset($_SESSION["timezone"])){
        $tz = $_SESSION["timezone"];
    }
    $_SESSION["download"] = false;
?>
<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>RADAC Wave Recorder</title>
    <meta name="description" content="Logger">
    <meta name="author" content="trihart4nto@gmail.com">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" integrity="sha384-gfdkjb5BdAXd+lj+gudLWI+BXq4IuLW5IT+brZEZsLFm++aCMlF1V92rMkPaX4PP" crossorigin="anonymous">
    <script type="text/javascript" src="/node_modules/jquery/dist/jquery.js"></script>

    <link rel="stylesheet" href="/node_modules/highcharts/css/highcharts.css">
    <script src="/node_modules/highcharts/highcharts.js"></script>
    <script src="/node_modules/highcharts/modules/exporting.js"></script>
    <script src="/node_modules/highcharts/modules/export-data.js"></script>
    <script src="/node_modules/highcharts/modules/windbarb.js"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.min.css">
    <script src="/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    
    <!-- <link rel="stylesheet" href="https://unpkg.com/leaflet@1.2.0/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.2.0/dist/leaflet.js"></script>  -->

    <script src="/node_modules/moment/moment.js"></script>
    <script src="/node_modules/moment-timezone/builds/moment-timezone-with-data.js"></script>
    <script src="/node_modules/vue/dist/vue.min.js"></script>  



    <link rel="stylesheet" href="my.css">
</head>

<body>
    <nav class="navbar navbar-dark bg-primary" style="margin-bottom:10px " id="mainhead">
        <a class="navbar-brand" href="#">RADAC Wave Recorder</a>
        <button v-on:click="vmain.toggleMenu()" class="btn btn-primary btn-sm" style="color:#ff0000"><span class="navbar-toggler-icon togler-red"></span></button>
    </nav>
    <div class="container-fluid h-100" id="main">
        <div v-if="showMenu == true" class="bg-primary" id="menu">
            <div class="text-right" style="margin-bottom:10px;">
                <button type="button" v-on:click="toggleMenu()" class="btn btn-danger">close Menu</button>
            </div>        
            <a class="btn btn-primary btn-block mybtn" href="/">Logger</a>        
            <a class="btn btn-primary btn-block mybtn" href="/downloaddta.php">Download Data</a>        
        </div>
    
        
        <div class="row" style="margin-bottom:10px">
            <div class="col-sm-6">
                <div class="form-group row">
                    <label for="tanggalMulai col-sm-12 col-md-3">Tanggal Mulai</label>
                    <div class="col-sm-12 col-md-4">
                        <input v-model="startDate" type="date" class="form-control" id="tanggalMulai">                        
                    </div>                
                    <div class="col-sm-12 col-md-4">                        
                        <input v-model="startTime" type="time" class="form-control" id="waktuMulai">
                    </div>                
                </div>
                <div class="form-group row">
                    <label for="tanggalAkhir col-sm-12 col-md-3 ">Tanggal Akhir</label>
                    <div class="col-sm-12 col-md-4">
                        <input v-model="endDate" type="date" class="form-control" id="tanggalAkhir">                        
                    </div>           
                    <div class="col-sm-12 col-md-4">
                        <input v-model="endTime" type="time" class="form-control" id="waktuAkhir">
                    </div>                
                </div>
            </div>        
            <div class="col-sm-6">
                <div class="form-group row">
                    <label for="dataSelect" class="col-sm-12 col-md-2">Data</label>
                    <div class="col-sm-12 col-md-9">
                        <select class="form-control" id="dataSelect" v-model="selectedData">
                            <template v-for="(item,index) in dataList">
                                <option v-bind:value="index">!vue item vue!</option>
                            </template>
                        </select>
                    </div>                
                </div>
                <div class="form-group row">
                    <label for="timezoneSelect" class="col-sm-12 col-md-2">TimeZone</label>
                    <div class="col-sm-12 col-md-9">
                        <select class="form-control" id="timezoneSelect" v-model="timeZone">
                            <template v-for="(item,index) in timezoneList">
                                <option v-bind:value="index">!vue item vue!</option>
                            </template>
                        </select>
                    </div>                
                </div>
            </div>
        </div>  
        <div class="row">
            <div class="col-sm-11 text-right">
                <button type="button" class="btn btn-primary" v-on:click="getData()">Get Data</button>
            </div>
        </div>
        <div v-if="showLoad == true" class="row flex-grow-1 console">
            <div class="col-12 text-center">
                <h3>Preparing Your Data</h3>
                <div class="cssload-thecube">
                    <div class="cssload-cube cssload-c1"></div>
                    <div class="cssload-cube cssload-c2"></div>
                    <div class="cssload-cube cssload-c4"></div>
                    <div class="cssload-cube cssload-c3"></div>
                </div>        
            </div>
        </div>
        <!-- <div class="row flex-grow-1 console">
            <div class="col-12">
                <template v-for="p in process">
                    <div>
                        !vue p vue!
                    </div>
                </template>
            </div>
        </div> -->
    </div>
    </body>
    <script type="text/javascript">
        var vheader = null;
        var vmain = null;
        $(window).on("load",function(e){
        Vue.options.delimiters = ['!vue', 'vue!'];
        window.$ = $;
        window.jQuery = jQuery;
        window.moment = moment;
        vheader = new Vue({
            el: "#mainhead"            
            });
        vmain = new Vue({
            el: "#main",
            data:{
                showMenu:false,
                dataList:{
                    "H10":"Water Level",
                    "Hm0":"Wave Height",
                    "Th0":"Wave Direction",
                    "Tm02":"Wave Period"                    
                },
                timezoneList:{
                    "UTC":"UTC",
                    "WIB":"WIB",
                    "WITA":"WITA",
                    "WIT":"WIT"
                },
                // selectedData:"H10",
                // startDate:"2018-12-24",
                // startTime:"00:00",
                // endDate:"2018-12-31",
                // endTime:"00:00",
                selectedData:null,
                startDate:null,
                startTime:"00:00",
                endDate:null,
                endTime:"00:00",

                timeZone:null,
                
                showLoad:false,
                processInterval:null,
                process:[]
            },
            watch:{

            },
            methods: {
                toggleMenu:function(){
                    if(this.showMenu == false){
                        this.showMenu = true;
                    }else{
                        this.showMenu = false
                    }
                },
                isObjEmpty:function(obj){
                    if($.isEmptyObject(newd)){
                        return true; 
                    }else{
                        return false; 
                    }
                },
                getData:function(){
                    if(this.startData == null || this.endData == null){
                        alert("Invalid Time");
                        return true;
                    }
                    if(this.timeZone == null || this.selectedData == null){
                        alert("Complete The Form");
                        return true;
                    }
                    let start = moment(this.startData, 'YYYY-MM-DD HH:mm:ss');
                    let end = moment(this.endData, 'YYYY-MM-DD HH:mm:ss');
                    if(start>end){
                        alert("Start Date lebih dari End Data");
                        return true;
                    }
                    let data = {
                        "start":this.startData,
                        "end":this.endData,
                        "timezone":this.timeZone,
                        "doc":this.selectedData
                    }
                    // console.log(data);
                    let t = this;
                    t.showLoad = true;
                    $.get("/prepareData.php",data,function(res){
                        t.showLoad = false;
                        if(res.status==1){
                            // window.open('/ddl.php', '_blank');
                            window.location.href='/ddl.php';
                            return false;
                        }else{
                            alert(res.error);
                        }
                    },"JSON");
                }
            },
            computed:{
                startData:function(){
                    if(this.startDate !== null && this.startTime !== null){
                        return this.startDate+" "+this.startTime+":00";
                    }else{
                        return null;
                    }
                },
                endData:function(){
                    if(this.endDate !== null && this.endTime !== null){
                        return this.endDate+" "+this.endTime+":00";
                    }else{
                        return null;
                    }
                }
            },
            mounted: function () {
                
            }
        })

    });
    </script>
</html>
