<!--script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/maps.js"></script>
<script src="https://cdn.amcharts.com/lib/4/geodata/continentsLow.js"></script>
<script src="https://cdn.amcharts.com/lib/4/geodata/worldLow.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
<script src="https://cdn.amcharts.com/lib/4/geodata/data/countries2.js"></script-->

<script src="modules/sec_geoip_map/themes/default/vendor/amcharts/core.js"></script>
<script src="modules/sec_geoip_map/themes/default/vendor/amcharts/maps.js"></script>
<script src="modules/sec_geoip_map/themes/default/vendor/amcharts/geodata/continentsLow.js"></script>
<script src="modules/sec_geoip_map/themes/default/vendor/amcharts/geodata/worldLow.js"></script>
<script src="modules/sec_geoip_map/themes/default/vendor/amcharts/themes/animated.js"></script>
<script src="modules/sec_geoip_map/themes/default/vendor/amcharts/geodata/data/countries2.js"></script>
<script src="modules/sec_geoip_map/themes/default/vendor/amcharts/geodata/lang/{$lang}.js"></script>

<script>

var lang={};
{foreach key=key item=item from=$LANG}
    lang["{$key}"]="{$item}";
{/foreach}

{literal}

am4core.ready(function() {

    resize();
    // Themes begin
    am4core.useTheme(am4themes_animated);
    // Themes end

    // Create map instance
    chart = am4core.create("chartdiv", am4maps.MapChart);
    //var interfaceColors = new am4core.InterfaceColorSet();

    try {
        chart.geodata = am4geodata_worldLow;
    }
    catch (e) {
        chart.raiseCriticalError(new Error("Map geodata could not be loaded. Please download the latest <a href=\"https://www.amcharts.com/download/download-v4/\">amcharts geodata</a> and extract its contents into the same directory as your amCharts files."));
    }

    // Set projection
    chart.projection = new am4maps.projections.Orthographic();
    chart.panBehavior = "rotateLongLat";
    chart.padding(20,20,20,20);

    // Add zoom control
    chart.zoomControl = new am4maps.ZoomControl();

{/literal}
    chart.geodataNames = am4geodata_lang_{$lang};
{literal}
    var homeButton = new am4core.Button();
    homeButton.events.on("hit", function(){
      mapPolygon=polygonSeries.getPolygonById(home);
      //rotateTo(mapPolygon,mapPolygon.visualLongitude*-1,mapPolygon.visualLatitude*-1);
      rotateTo(mapPolygon,homeLongitude*-1,homeLatitude*-1);
    });

    homeButton.icon = new am4core.Sprite();
    homeButton.padding(7, 5, 7, 5);
    homeButton.width = 30;
    homeButton.icon.path = "M16,8 L14,8 L14,16 L10,16 L10,10 L6,10 L6,16 L2,16 L2,8 L0,8 L8,0 L16,8 Z M16,8";
    homeButton.marginBottom = 10;
    homeButton.parent = chart.zoomControl;
    homeButton.insertBefore(chart.zoomControl.plusButton);

    chart.backgroundSeries.mapPolygons.template.polygon.fill = am4core.color("#006994");
    chart.backgroundSeries.mapPolygons.template.polygon.fillOpacity = 1;
    chart.deltaLongitude = 20;
    chart.deltaLatitude = -20;

    // limits vertical rotation
    chart.adapter.add("deltaLatitude", function(delatLatitude){
        return am4core.math.fitToRange(delatLatitude, -90, 90);
    })

    
    // Create map polygon series SHADOW
    var shadowPolygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
    shadowPolygonSeries.geodata = am4geodata_continentsLow;

    try {
        shadowPolygonSeries.geodata = am4geodata_continentsLow;
    }
    catch (e) {
        shadowPolygonSeries.raiseCriticalError(new Error("Map geodata could not be loaded. Please download the latest <a href=\"https://www.amcharts.com/download/download-v4/\">amcharts geodata</a> and extract its contents into the same directory as your amCharts files."));
    }

    shadowPolygonSeries.useGeodata = true;
    shadowPolygonSeries.dx = 2;
    shadowPolygonSeries.dy = 2;
    shadowPolygonSeries.mapPolygons.template.fill = am4core.color("#000");
    shadowPolygonSeries.mapPolygons.template.fillOpacity = 0.2;
    shadowPolygonSeries.mapPolygons.template.strokeOpacity = 0;
    shadowPolygonSeries.fillOpacity = 0.1;
    shadowPolygonSeries.fill = am4core.color("#000");

    // Create map polygon series
    polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
    polygonSeries.useGeodata = true;

    polygonSeries.calculateVisualCenter = true;
    polygonSeries.tooltip.background.fillOpacity = 0.2;
    polygonSeries.tooltip.background.cornerRadius = 5;

    var template = polygonSeries.mapPolygons.template;
    template.nonScalingStroke = true;
    template.fill = am4core.color(none_color);
    template.stroke = am4core.color("#e2c9b0");

    polygonSeries.calculateVisualCenter = true;
    template.propertyFields.id = "id";
    template.tooltipPosition = "fixed";
    template.fillOpacity = 1;

    var graticuleSeries = chart.series.push(new am4maps.GraticuleSeries());
    graticuleSeries.mapLines.template.stroke = am4core.color("#fff");
    graticuleSeries.fitExtent = false;
    graticuleSeries.mapLines.template.strokeOpacity = 0.2;
    graticuleSeries.mapLines.template.stroke = am4core.color("#fff");

    var measelsSeries = chart.series.push(new am4maps.MapPolygonSeries())
    measelsSeries.tooltip.background.fillOpacity = 1;
    measelsSeries.tooltip.background.cornerRadius = 5;
    measelsSeries.tooltip.autoTextColor = false;
    measelsSeries.tooltip.label.fill = am4core.color("#000");
    measelsSeries.tooltip.dy = -5;

    var measelTemplate = measelsSeries.mapPolygons.template;
    measelTemplate.fill = am4core.color(f2b_color);
    measelTemplate.strokeOpacity = 0;
    measelTemplate.fillOpacity = 0.75;
    measelTemplate.tooltipPosition = "fixed";

    imageSeriesHome = chart.series.push(new am4maps.MapImageSeries());
    var imageSeriesHomeTemplate = imageSeriesHome.mapImages.template;
    var marker = imageSeriesHomeTemplate.createChild(am4core.Image);
    marker.href = "modules/sec_geoip_map/images/gota-issabel.png";
    marker.width = 40;
    marker.height = 40;
    marker.nonScaling = true;
    marker.tooltipText = "{title}";
    marker.horizontalCenter = "middle";
    marker.verticalCenter = "bottom";

    // Set property fields
    imageSeriesHomeTemplate.propertyFields.latitude = "latitude";
    imageSeriesHomeTemplate.propertyFields.longitude = "longitude";

    // Click
    template.events.on("hit", function(ev) {
        iso2 = ev.target.dataItem.dataContext.id
        if(blockState[iso2]==1) {
            polygonSeries.getPolygonById(iso2).fill = am4core.color(none_color);
            blockState[iso2]=-1;
            $.get( "index.php?menu=sec_geoip_map&rawmode=yes&noneblock="+iso2, function( data  ) {
                console.log(data);
            });
        } else  
        if(blockState[iso2]==-1) {
            polygonSeries.getPolygonById(iso2).fill = am4core.color(unblocked_color);
            blockState[iso2]=0;
            $.get( "index.php?menu=sec_geoip_map&rawmode=yes&unblock="+iso2, function( data  ) {
                console.log(data);
            });
        } else {
            blockState[iso2]=1;
            polygonSeries.getPolygonById(iso2).fill = am4core.color(blocked_color);
            $.get( "index.php?menu=sec_geoip_map&rawmode=yes&block="+iso2, function( data  ) {
                console.log(data);
            });
        }
    });

    polygonSeries.events.on("inited", function () {

        lineSeries = chart.series.push(new am4maps.MapLineSeries());
        lineSeries.mapLines.template.strokeWidth = 0.2;
        lineSeries.mapLines.template.stroke = am4core.color("#000");

        $.get( "index.php?menu=sec_geoip_map&rawmode=yes&f2b=1", function( data ) {
            f2b = JSON.parse(data);
            polygonSeries.mapPolygons.each(function (mapPolygon) {
                iso = mapPolygon.dataItem.dataContext.id;
                blockState[iso]=-1;
                if(f2b.hasOwnProperty(iso)) {
                    value = f2b[iso];
                    var polygon = measelsSeries.mapPolygons.create();
                    polygon.multiPolygon = am4maps.getCircle(mapPolygon.visualLongitude, mapPolygon.visualLatitude, Math.max(1, Math.log(value) * Math.LN10 / 2 ));
                    polygon.tooltipText = mapPolygon.dataItem.dataContext.name + ": " + value;
                    mapPolygon.dummyData = polygon;
                    polygon.events.on("over", function () {
                        mapPolygon.isHover = true;
                    })
                    polygon.events.on("out", function () {
                        mapPolygon.isHover = false;
                    })
                } else {
                    // Country tooltip with no fail2ban blocks
                    mapPolygon.tooltipText = mapPolygon.dataItem.dataContext.name;
                    mapPolygon.tooltip.background.fillOpacity = 1;
                    mapPolygon.tooltip.background.cornerRadius = 5;
                }
            })
        })

        $.get( "index.php?menu=sec_geoip_map&rawmode=yes&blocked=1", function( data ) {
            countries = data.split(",");
            for(var i=0; i < countries.length; i++) {
                if(typeof(am4geodata_data_countries2[countries[i]])=="object") {
                    pais = polygonSeries.getPolygonById(countries[i]);
                    if(typeof(pais)!='undefined') {
                       pais.fill = am4core.color(blocked_color);
                    }
                    blockState[countries[i]]=1;
                }
            }
        });

        $.get( "index.php?menu=sec_geoip_map&rawmode=yes&pass=1", function( data ) {
            countries = data.split(",");
            for(var i=0; i < countries.length; i++) {
                if(typeof(am4geodata_data_countries2[countries[i]])=="object") {
                    pais = polygonSeries.getPolygonById(countries[i]);
                    if(typeof(pais)!='undefined') {
                        pais.fill = am4core.color(unblocked_color);
                    }
                    blockState[countries[i]]=0;
                }
            }
        });

        $.when(
            $.ajax( "index.php?menu=sec_geoip_map&rawmode=yes&homecountry=1" ),
        ).done(function(data) {
            ret = data.split(",");
            home     = ret[0];
            publicip = ret[1];
            $.ajax("//ipwhois.app/json/" + publicip).done(function(data) {
                if(data.success) {
                    homeLatitude  = parseFloat(data.latitude);
                    homeLongitude = parseFloat(data.longitude);
                } else {
                    homePolygon   = polygonSeries.getPolygonById(home);
                    homeLatitude  = homePolygon.visualLatitude;
                    homeLongitude = homePolygon.visualLongitude;
                }
                imageSeriesHome.addData({
                    "latitude":  parseFloat(homeLatitude),
                    "longitude": parseFloat(homeLongitude),
                    "title": lang["My Issabel"]
                })
  
            })
            .fail(function() {
                homePolygon   = polygonSeries.getPolygonById(home);
                homeLatitude  = homePolygon.visualLatitude;
                homeLongitude = homePolygon.visualLongitude;
                imageSeriesHome.addData({
                    "latitude":  homeLatitude,
                    "longitude": homeLongitude,
                    "title": "My Issabel"
                })
            })
        });
    }); // end inited
    // check for attacks
    const interval = setInterval(function() {
        // method to be executed;
        if (enableAnimations == 1) {
            $.get("index.php?menu=sec_geoip_map&rawmode=yes&getattacks=1",function(data) {
                if(data.length>0) {
                    for(i=0;i<data.length;i++) {
                       attack = data[i];
                       var countryname = chart.geodataNames[attack.source];
                       atacar(attack.source,home,attack.hour,countryname);
                    } 
                }
            });
        }
    }, 5000);

    var label = chart.createChild(am4core.Label)
    label.text = "🟡  "+lang.blockedLegend;
    label.fontSize = 12;
    label.align = "left";
    label.valign = "bottom"
    label.fill = am4core.color("#000");
    label.background = new am4core.RoundedRectangle()
    label.background.cornerRadius(5,5,5,5);
    label.padding(10,10,10,10);
    label.marginLeft = 20;
    label.marginBottom = 30;
    label.background.strokeOpacity = 0.3;
    label.background.stroke =am4core.color("#927459");
    label.background.fill = am4core.color("#f9e3fe");
    label.background.fillOpacity = 0.6;


}); // end am4core.ready()

{/literal}
</script>
<div class='stars'></div>
<div class='twinkling'></div>
<div id="chartdiv" class='chart' style='z-index:1;'></div>

