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
    <script type="text/javascript">            
        window.jQuery = jQuery;
        window.$ = jQuery;
    </script>
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
        <button v-on:click="vmain.toggleMenu()" class="btn btn-primary btn-sm" style="color:#FFFFFF"><span class="navbar-toggler-icon togler-red"></span></button>
    </nav>
    <div class="container-fluid" id="main">
        <div v-if="showMenu == true" class="bg-primary" id="menu">
            <div class="text-right" style="margin-bottom:10px;">
                <button type="button" v-on:click="toggleMenu()" class="btn btn-danger">close Menu</button>
            </div>        
            <a class="btn btn-primary btn-block mybtn" href="/downloaddta.php">Download Data</a>        
        </div>
        <div class="row" style="margin-bottom:10px">
            <div class="col-sm-4">
                <label for="tzselect">Time Zone</label>
                <select class="form-control form-control-lg" id="tzselect" v-model="timezone">
                    <option value="UTC">UTC</option>
                    <option value="WIB">WIB</option>
                    <option value="WITA">WITA</option>
                    <option value="WIT">WIT</option>
                </select>
            </div>
            
            <div class="col-sm-4">
                Fetch Time : !vue fetchTime vue!
            </div>
            <div class="col-sm-4" style="margin-bottom:30px">
                Device Location
                <iframe style="display:block;width:100%;" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2042209.903118572!2d116.44985152050329!3d-1.3619248933353487!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMcKwMTgnNDMuMiJTIDExN8KwMzcnMTIuMCJF!5e0!3m2!1sid!2sid!4v1546068343836" width="300px" height="100%" frameborder="0" style="border:0" allowfullscreen></iframe>
            </div>
        </div>
        <div class="row" style="margin-bottom:10px;">
            <div class="col-sm-12 bg-primary">
                <h3 style="color:white">Latest Data</h3>                
            </div>            
        </div>
        <div class="row" style="margin-bottom:20px;">
            
            <template v-if="latest.empty != true" v-for="(item,index) in latest.data">
                <div v-if="item.type == 'scalar'" class="col-sm-4" style="margin-bottom:10px;">
                    <div class="card text-center" style="width:100%;height:250px;padding-top:10px">
                        <h5 class="card-subtitle ">!vue graphList[index].title vue!</h5>
                        <div>
                            <img class="card-img-top" src="/images/trans.png" style="width:10vh;height:10vh">
                        </div>
                        <h2 class="card-title ">!vue item.val vue! !vue item.dataUnit vue!</h2>
                        <p class="card-text">updated on !vue item.date vue!</p>
                        
                    </div>
                </div>

                <div v-if="item.type == 'cardinal'" class="col-sm-4" style="margin-bottom:10px">
                    <div class="card text-center" style="width:100%;height:250px;padding-top:10px">
                        <h5 class="card-subtitle ">!vue graphList[index].title vue!</h5>                        
                        <div :id="'latest_'+index">
                            <img class="card-img-top" src="/images/wind.png" style="width:10vh;height:10vh">
                        </div>                        
                       <h2 class="card-title ">!vue item.val vue! !vue item.dataUnit vue!</h2>
                        <p class="card-text">Wave From !vue item.cardinal vue!</p>                        
                        <p class="card-text">updated on !vue item.date vue!</p>
                        
                    </div>
                </div>

            </template>
            
        </div>

        <div v-if="metReady == 1" class="row" style="margin-bottom:10px">
                <div class="col-sm-11 bg-primary">
                    <h3 style="color:white">!vue graphList['MET'].title vue!</h3>
                </div>
                <div class="col-sm-1 bg-primary text-right">
                    <button class="btn btn-primary" v-on:click="toggleGraph(graphList.MET)">
                    <i class="fas fa-2x" :class="[graphList.MET.hidden ? 'fa-caret-down':'fa-caret-up']"></i>
                    </button>
                </div>
            </div>
        <transition name="slide-fade" mode="out-in">    
            <div v-if="metReady == 1" class="row" v-show="graphList.MET.hidden == false">
                <div class="col-sm-12">
                    <button type="button" class="btn btn-primary" v-on:click="drawMeteo(3)">Last 3 Hour</button>
                    <button type="button" class="btn btn-primary" v-on:click="drawMeteo(6)">Last 6 Hour</button>
                    <button type="button" class="btn btn-primary" v-on:click="drawMeteo(12)">Last 12 Hour</button>
                    <button type="button" class="btn btn-primary" v-on:click="drawMeteo(24)">Last 24 Hour</button>
                </div>
            </div>
        </transition>
        <transition name="slide-fade" mode="out-in">
            <div v-if="metReady == 1" class="row" style="margin-bottom:20px;" v-show="graphList.MET.hidden == false">
                <div class="col-sm-12 col-xs-12">
                    <div v-if="metReady == 1" class="graph" :id="'chart_MET'">....</div>
                    <div v-if="metReady == 2" class="graph">ERROR</div>
                </div>                            
            </div>
        </transition>


        <template v-if="graphEmpty == false" v-for="(item,index) in graphList">
            <div v-if="item.displayChart == true" class="row" style="margin-bottom:10px">
                <div class="col-sm-11 bg-primary">
                    <h3 style="color:white">!vue item.title vue!</h3>
                </div>
                <div class="col-sm-1 bg-primary text-right">
                    <button class="btn btn-primary" v-on:click="toggleGraph(item)">
                        <i class="fas fa-2x" :class="[item.hidden ? 'fa-caret-down':'fa-caret-up']"></i>
                    </button>
                </div>
            </div>
            <transition name="slide-fade" mode="out-in">
            <div v-if="item.displayChart == true" class="row" v-show="item.hidden == false">
                <div class="col-sm-12">
                    <button type="button" class="btn btn-primary" v-on:click="loadData(index,1)">Last 1 Hour</button>
                    <button type="button" class="btn btn-primary" v-on:click="loadData(index,3)">Last 3 Hour</button>
                    <button type="button" class="btn btn-primary" v-on:click="loadData(index,6)">Last 6 Hour</button>
                    <button type="button" class="btn btn-primary" v-on:click="loadData(index,24)">Last 24 Hour</button>
                </div>
            </div>
            </transition>
            <transition name="slide-fade" mode="out-in">
            <div v-if="item.displayChart == true" class="row" v-show="item.hidden == false" style="margin-bottom:20px;">
                <div class="col-sm-12 col-xs-12">
                    <div v-if="item.graph.status == 1" class="graph" :id="'chart_'+index">PLEASE WAIT PREPARING CHART</div>
                    <div v-if="item.graph.status == 2" class="graph">ERROR</div>
                </div>                            
            </div>
            </transition>
        </template>

    </div>
    </body>
    <script type="text/javascript">
    vheader=null;
    vmain=null;
    $(window).on("load",function(e){
        Vue.options.delimiters = ['!vue', 'vue!'];
        window.Highcharts = Highcharts;
        window.moment = moment;
        // window.L = L;
        Highcharts.setOptions({
            global: {
                useUTC: false
            }
        });
        var lineConfig = {
            title: {
                align:"left",
                text: ''
            },
            subtitle: {
                text: ''
            },
            xAxis: {
                type: 'datetime',
                gridLineWidth: 1
            },
            yAxis: {
                title: {
                    // "textAlign": 'right',
                    // "rotation": 0,
                    // x: 30,
                    // y: -140,
                    text: ''
                },
                labels:{}
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle'
            },
            chart: {
                type: 'spline',
                // marginBottom: 70,
                marginRight: 40,
                marginTop: 50,
                scrollablePlotArea: {
                    minWidth: 600
                },
            },
            credits: {
                href: '',
                enabled:false
            },
            series: []
        };
        var charts={};
        function createLineChart(id,doc,hgo){
            let useGraph = vmain.graphList[doc];
            if(useGraph.graph.status!==1){
                return false;
            }
            let conf = $.extend(true, {}, lineConfig);
            // conf.title.text = useGraph.title+" Last "+hgo+" Hour(s)";
            conf.subtitle.text = "";
            conf.yAxis.title.text = useGraph.title+" Last "+hgo+" Hour(s)";
            // conf.yAxis.labels.format = "{value} "+useGraph.dataUnit;
            conf.yAxis.labels.formatter= function() {
                return this.value+" "+useGraph.dataUnit
            }
            conf.tooltip = {
                formatter: function () {
                    var t = moment(this.x).format("DD-MM-YYYY HH:mm");
                    return t + "<br/>" + this.series.name + ':' + this.y + " " + useGraph.dataUnit;
                }
            };
            conf.series.push({
                "showInLegend": false,
                "name": useGraph.title,
                "data": useGraph.graph.data,
                "type": "spline",
            });

            // console.log("chart_" + id);
            charts[id] = Highcharts.chart("chart_" + id, conf);
            
        }
        function createMeteo(hgo){
            console.log("createMeteo");
            doc = "MET";
            id = "MET";
            let useGraph = vmain.graphList[doc];
            if(useGraph.graph.status!==1){
                return false;
            }
            let conf = $.extend(true, {}, meteoConfig);
            // conf.title.text = "Wave Height & Direction Last "+hgo+" Hour(s)";
            conf.subtitle.text = "";

            conf.yAxis.labels = {
                format: '{value} m'
            }
            conf.yAxis.title.text = "Wave Height & Direction Last "+hgo+" Hour(s)";    
            // conf.tooltip = {
            //     formatter: function () {

            //         return "TEST";
            //     }
            // };
            conf.series.push({
                "showInLegend": false,
                "type": 'spline',
                "name": "Wave Height",
                "id": 'waveheights',
                "data": useGraph.graph.dataWH,
                "tooltip": {
                    "valueSuffix": ' m'
                }
            });
            conf.series.push({
                "showInLegend": false,
                "name": "Wave Direction",
                "type": 'windbarb',
                "id": 'windbarbs',
                "color": Highcharts.getOptions().colors[1],
                "lineWidth": 1.5,
                "data": useGraph.graph.dataWD,
                "vectorLength": 18,
                "yOffset": -15,
                "tooltip": {
                    "pointFormat": ' <span style="color:{point.color}">\u25CF</span> '+
                    '{series.name}: <b>{point.direction} ° </b> from {point.cardinal}'
                }
            });
            // console.log("chart_" + id);
            charts[id] = Highcharts.chart("chart_" + id, conf);
        };
        function updateLineChart(id, doc, hgo) {
            let useGraph = vmain.graphList[doc];
            console.log(useGraph.activeHour);
            // let hgo2use = useGraph.activeHour;
            if(useGraph.graph.status!==1){
                return false;
            }
            if (charts[id] !== undefined) {
                // alert("updateChart");
                xchart = charts[id];
                // xchart.title = useGraph.title+" Last "+hgo+" Hour(s)";
                var series = xchart.series[0];
                // var date = moment(time.VALUE, "DD-MM-YYYY HH:mm");
                // if (data.VALUE === null) {
                //     return false;
                // }
                // series.addPoint([date.valueOf(), parseFloat(data.VALUE)], false, true);
                // xchart.setTitle({text: useGraph.title+" Last "+hgo+" Hour(s)"});
                xchart.yAxis[0].update({
                    title:{
                        text:useGraph.title+" Last "+hgo+" Hour(s)"
                    }
                });
                series.setData(useGraph.graph.data,true);
                // xchart.redraw();
            }
            charts[id].hideLoading();
        }
        function updateMeteo(hgo){
            doc = "MET";
            let useGraph = vmain.graphList[doc];
            let hgo2use = useGraph.activeHour;
            if(useGraph.graph.status!==1){
                return false;
            }
            if (charts[id] !== undefined) {
                xchart = charts[id];
                
                var series1 = xchart.get('waveheights');
                var series2 = xchart.get('windbarbs');
                // xchart.setTitle({text: "Wave Height and Direction Last "+hgo2use+" Hour(s)"});
                // xchart.options.yAxis[0].title.text = "Wave Height & Direction Last "+hgo+" Hour(s)";
                xchart.yAxis[0].update({
                    title:{
                        text:"Wave Height & Direction Last "+hgo+" Hour(s)"
                    }
                });
                // console.log(useGraph.graph.data);
                series1.setData(useGraph.graph.dataWH,false);
                series2.setData(useGraph.graph.dataWD,false);
                xchart.redraw();
            }
            charts[id].hideLoading();
        }
        $.fn.rotationDegrees = function () {
            var matrix = this.css("-webkit-transform") ||
                    this.css("-moz-transform") ||
                    this.css("-ms-transform") ||
                    this.css("-o-transform") ||
                    this.css("transform");
            if (typeof matrix === 'string' && matrix !== 'none') {
                var values = matrix.split('(')[1].split(')')[0].split(',');
                var a = values[0];
                var b = values[1];
                var angle = Math.round(Math.atan2(b, a) * (180 / Math.PI));
            } else {
                var angle = 0;
            }
            return angle;
        };
        $.fn.animateRotate = function (angle, duration, easing, complete) {
            var args = $.speed(duration, easing, complete);
            var step = args.step;
            return this.each(function (i, e) {
                args.complete = $.proxy(args.complete, e);
                args.step = function (now) {
                    $.style(e, 'transform', 'rotate(' + now + 'deg)');
                    if (step)
                        return step.apply(e, arguments);
                };
                var start = $(this).rotationDegrees();
                if (start < 0) {
                    start = 360 + start;
                }
                $({deg: start}).animate({deg: angle}, args);
            });
        };
        var meteoConfig= {
            chart: {
                // renderTo: this.container,
                // marginBottom: 70,
                marginRight: 40,
                marginTop: 50,
                plotBorderWidth: 1,
                // height: 310,
                alignTicks: false,
                scrollablePlotArea: {
                    minWidth: 600
                },
                events: {
                    render: function () {
                        if($(".justsomeboxforwind").length>0){
                            $(".justsomeboxforwind").remove();
                        }
                        var chart = this;
                        var xAxis = chart.xAxis[0],
                            x,
                            pos,
                            max,
                            isLong,
                            isLast,
                            i;
                        var tick = 5*60*1000;
                        // console.log(xAxis);
                        for (pos = xAxis.min, max = xAxis.max, i = 0; pos <= max + tick; pos += tick, i += 1) {
                            // Get the X position
                            // isLast = pos === max + tick;
                            // x = Math.round(xAxis.toPixels(pos)) + (isLast ? 0.5 : -0.5);
                            x = Math.round(xAxis.toPixels(pos+tick));
                            // console.log(x);
                            // Draw the vertical dividers and ticks
                            if (this.resolution > tick) {
                                isLong = pos % this.resolution === 0;
                            } else {
                                isLong = i % 2 === 0;
                            }
                            chart.renderer.path([
                                'M', x, chart.plotTop + chart.plotHeight + (isLong ? 0 : 28),
                                'L', x, chart.plotTop + chart.plotHeight + 32, 'Z']
                                )
                                .attr({
                                    'stroke': chart.options.chart.plotBorderColor,
                                    'stroke-width': 1,
                                    'class':"justsomeboxforwind"
                                })
                                .add();
                        }
                    }
                }
            },
            title: {
                // align:"left",
                text: "",
            },
            subtitle: {
                text: ''
            },
            tooltip: {
                shared: true,
                useHTML: true,
                headerFormat:'<small>{point.x:%A, %b %e, %H:%M} - {point.point.to:%H:%M}</small><br>'
            },
            yAxis: {
                title: {
                    // "textAlign": 'right',
                    // "rotation": 90,
                    // x: 30,
                    // y: -140,
                    text: ''
                }
            },
            xAxis: [{ // Bottom X axis
                type: 'datetime',
                tickInterval: 2 * (1000*60*5), // two hours
                minorTickInterval: (1000*60*5), // one hour
                tickLength: 0,
                gridLineWidth: 1,
                gridLineColor: (Highcharts.theme && Highcharts.theme.background2) || '#F0F0F0',
                startOnTick: false,
                endOnTick: false,
                minPadding: 0,
                maxPadding: 0,
                offset: 30,
                showLastLabel: true,
                // labels: {
                //     format: '{value:%H}'
                // },
                crosshair: true
            }, { // Top X axis
                linkedTo: 0,
                type: 'datetime',
                tickInterval: 24 * 3600 * 1000,
                labels: {
                    format: '{value:<span style="font-size: 12px; font-weight: bold">%a</span> %b %e}',
                    align: 'left',
                    x: 3,
                    y: -5
                },
                opposite: true,
                tickLength: 20,
                gridLineWidth: 1
            }],
            // xAxis: {
            //     type: 'datetime',
            //     offset: 40
            // },
            credits: {
                href: '',
                enabled:false
            },
            plotOptions: {
                series: {
                    pointInterval: 1000*60*10
                }
            },
            series: []
        }

        vheader = new Vue({
            el: "#mainhead"            
            });
        vmain = new Vue({
            el: "#main",
            data:{
                graphList:{},
                graphEmpty:true,
                timezone:"<?= $tz ?>",
                latest:{"empty":true,"data":{}},
                chartTimeout:1*60*100,
                fetchTime:"",
                nix: 0,
                showMenu:false
            },
            watch:{
                timezone:function(newd,oldd){
                    if(newd!==oldd){
                        if(!this.graphEmpty){
                            t = this;
                            t.nix = moment().toDate().getTime();
                            $.each(t.graphList,function(k,v){
                                // $.each(v.graph,function(k2,v2){
                                    t.loadData(k,v.activeHour,t.nix);
                                    // loadArray.push(k,k2);
                                // });
                            });
                            t.fetchLatest();
                            t.drawMeteo(this.graphList["MET"].activeHour);
                        }
                    }
                },
                graphList:function(newd,oldd){
                    if($.isEmptyObject(newd)){
                        this.graphEmpty=true;
                    }else{
                        this.graphEmpty=false;
                    }
                },
                latest:function(ne,ol){
                    console.log("CHANGES");
                }
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
                toggleGraph:function(obj){
                    if(obj.hidden==true){
                        obj.hidden=false;
                    }else{
                        obj.hidden=true
                    }
                },
                loadData:function(doc,hgo){                    
                    t = this;
                    // console.log(this.graphList[doc]["displayChart"]);
                    if(this.graphList[doc]["displayChart"] == false){
                        return true;                        
                    }
                    // console.log("LOAD",doc);
                    // console.log(hgo);
                    if(hgo==0){
                        // console.log("getActive",doc);
                        hgo=t.graphList[doc]["activeHour"];
                    }
                    // console.log(hgo);
                    let data={
                        "doc":doc,
                        "hgo":hgo,
                        "tz":this.timezone
                    }
                    if(typeof charts[doc] !== "undefined"){
                        charts[doc].showLoading();
                    }
                    // console.log("CHECK TIMEOUT");
                    if (t.graphList[doc]["graph"]["timeout"]!==null){
                        clearTimeout(t.graphList[doc]["graph"]["timeout"]);
                        t.graphList[doc]["graph"]["timeout"]=null;
                    }
                    // console.log("FetchData",doc);
                    $.get("getData2.php",data,function(res){                        
                        console.log("Reply Is Come",doc);
                        if(res.status==1){
                            t.fetchTime=res.fetchTime;
                            let series=[]
                            if(res.dataTotal>0){
                                $.each(res.data,function(k,v){
                                    let time = moment(v[0],"YYYY-MM-DD HH:mm:ss");
                                    let val = parseFloat(v[1]);
                                    let tmp =[time.valueOf(),val];
                                    series.push(tmp)
                                });
                            }
                            if(series.length>0){
                                t.graphList[doc]["graph"]["data"]=series;
                                // t.graphList[doc]["graph"]["dataUnit"]=res.dataUnit;                            
                                t.graphList[doc]["graph"]["status"]=1;
                            }else{
                                t.graphList[doc]["graph"]["status"]=0;
                            }
                            if(t.graphList[doc]["graph"]["status"]==1 && t.graphList[doc]["displayChart"] == true){
                                t.$nextTick(function(){
                                    if(typeof charts[doc] === "undefined"){
                                        console.log("Create");
                                        createLineChart(doc,doc,hgo);
                                    }else{
                                        // update line chart
                                        console.log("Update");
                                        updateLineChart(doc,doc,hgo);
                                    }
                                    t.graphList[doc]["graph"]["timeout"]=setTimeout(function(){
                                        console.log("GET NEW Data "+doc+" "+hgo);
                                        t.nix = moment().toDate().getTime();
                                        t.loadData(doc,0);
                                    },1000*60*1);
                                });
                            }
                        }else{
                            t.graphList[doc]["graph"]["status"]=2;
                        }
                        console.log("SET ACTIVE HOUR",doc,hgo);
                        t.graphList[doc]["activeHour"]=hgo;
                        t.graphList[doc]["tts"]=t.nix;
                    },"JSON")
                },
                fetchLatest:function(){
                    let data = {}
                    let t = this;
                    console.log("GET NEW LATEST");
                    $.get("getLatest.php",data,function(res){
                        if(res.status==1){
                            t.latest.data=res.data;
                            t.latest.empty=false;
                            $.each(t.latest.data,function(k,v){
                                if(v.type=="cardinal"){
                                    t.$nextTick(function(){
                                        $("#latest_"+k).animateRotate(parseFloat(v.val),600,"swing");
                                    });
                                }                            
                            });
                        }else{
                            t.latest.empty=true;
                        }
                        t.$nextTick(function(){
                            setTimeout(t.fetchLatest,1000*20);
                        });
                    },"JSON");
                },
                drawMeteo:function(hgo){
                    let t = this;
                    doc="MET";
                    if(hgo==0){
                        hgo=t.graphList[doc]["activeHour"];
                    }
                    let data={
                        "tz":this.timezone,
                        "hgo":hgo
                    };

                    if(typeof charts[doc] !== "undefined"){
                        charts[doc].showLoading();
                    }
                    if (t.graphList[doc]["graph"]["timeout"]!==null){
                        clearTimeout(t.graphList[doc]["graph"]["timeout"]);                        
                        t.graphList[doc]["graph"]["timeout"]=null;
                    }
                    console.log("FETCH METEO");
                    $.get("getMeteogram.php",data,function(res){                        
                        console.log("METEO Replied");
                        if(res.status==1){
                            let series1 = [];
                            if(res.dataWHTotal > 0){
                                $.each(res.dataWH,function(k,v){
                                    let time = moment(k,"YYYY-MM-DD HH:mm:ss");
                                    let to = moment(v[0],"YYYY-MM-DD HH:mm:ss");
                                    let val = parseFloat(v[1]);
                                    let tmp ={"x":time.valueOf(),"y":val,"to":to};
                                    series1.push(tmp)
                                })
                            }
                            let series2 = [];
                            if(res.dataWDTotal > 0){
                                $.each(res.dataWD,function(k,v){
                                    let time = moment(k,"YYYY-MM-DD HH:mm:ss");
                                    let dir = parseFloat(v[1]);
                                    let val = v[2] == null ? null : parseFloat(v[2]);
                                    let tmp ={"x":time.valueOf(),"value":val,"direction":dir,"cardinal":v[3]};
                                    series2.push(tmp)
                                });
                            }

                            if(series1.length>0 && series2.length>0 ){
                                t.graphList[doc]["graph"]["dataWH"]=series1;
                                t.graphList[doc]["graph"]["dataWD"]=series2;
                                t.graphList[doc]["graph"]["status"]=1;
                            }else{
                                t.graphList[doc]["graph"]["status"]=0;
                            }
                            if(t.graphList[doc]["graph"]["status"]==1){
                                t.$nextTick(function(){
                                    if(typeof charts[doc] === "undefined"){
                                        console.log("create METEO");
                                        createMeteo(hgo);
                                    }else{
                                        console.log("update METEO");
                                        updateMeteo(hgo);
                                    }
                                });
                                console.log("Schedule METEO",hgo);
                                t.graphList[doc]["graph"]["timeout"]=setTimeout(function(){
                                        console.log("GET NEW METEO");
                                        t.drawMeteo(0)
                                },1000*60*1);
                            }
                        }else{
                            t.graphList[doc]["graph"]["status"]=2;
                        }
                        t.graphList[doc]["activeHour"]=hgo;
                    },"JSON");
                    // if(typeof charts["MET"]!=="undefined"){

                    // }else{
                    //     createMeteo();
                    // }
                }
            },
            computed:{
                metReady:function(){
                    if(typeof this.graphList["MET"]!=="undefined"){
                        return 1;
                    }else{
                        return 0;
                    }
                }
            },
            mounted: function () {
                graphList={};
                // let egraph={1:{"status":-1},3:{"status":-1},6:{"status":-1},24:{"status":-1}}
                // graphList["H10"]={"title":"Water Level","graph":egraph};
                // graphList["Hm0"]={"title":"Water Height","graph":egraph};
                // graphList["Th0"]={"title":"Direction","graph":egraph};
                // graphList["Tm02"]={"title":"Wave Period","graph":egraph};
                let egraph={"status":-1,"timeout":null}

                graphList["MET"]={"title":"Wave Height & Direction","graph":egraph,"dataUnit":"","displayChart":false,"tts":0,"hidden":false};
                graphList["Tm02"]={"title":"Wave Period","graph":egraph,"dataUnit":"s","displayChart":true,"tts":0,"hidden":false};
                graphList["Hm0"]={"title":"Wave Height","graph":egraph,"dataUnit":"m","displayChart":false,"tts":0,"hidden":false};
                graphList["H10"]={"title":"Water Level","graph":egraph,"dataUnit":"cm","displayChart":true,"tts":0,"hidden":true};
                graphList["Th0"]={"title":"Direction","graph":egraph,"dataUnit":"°","displayChart":false,"tts":0,"hidden":false};   

                this.graphList = graphList;
                
                t = this;
                t.$nextTick(function(){
                    t.nix = moment().toDate().getTime();
                    $.each(t.graphList,function(k,v){
                        t.loadData(k,1,t.nix);                        
                    });
                    t.fetchLatest();
                    t.drawMeteo(3);
                });
            }
        })

    });
    </script>
    <!-- trihart4nto@gmail.com ,
     linkedin.com/in/tri-hartanto-8bb727151-->
<!-- </body> -->
</html>
