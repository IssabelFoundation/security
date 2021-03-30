var imageSeriesHome;
var home;
var homeLatitude;
var homeLongitude;
var attacklog;
var blockState       = {};
var latestAttacks    = [];
var blocked_color    = "rgb(211,93,110)";
var unblocked_color  = "rgb(90,164,105)";
var none_color       = "rgb(164,164,165)";
var f2b_color        = "rgb(255,255,0)";
var enableAnimations = 0;
var chart;
var polygonSeries;
var lineSeries;

// Resize map on browser viewport resize
function resize() {
    wh = $(window).height();
    wh = wh - 120;
    $('#chartdiv').height(wh);
}

$( window  ).resize(function() {
    resize();
});

// Konami Easter Egg Code

var pattern = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];
var current = 0;

var keyHandler = function (event) {

    // If the key isn't in the pattern, or isn't the current key in the pattern, reset
    if (pattern.indexOf(event.key) < 0 || event.key !== pattern[current]) {
        current = 0;
        return;
    }

    // Update how much of the pattern is complete
    current++;

    if (pattern.length === current) {

        current = 0;
        enableAnimations=1;
        showAttackLegend();
    }
}

// Rotation function
//var animation;
function rotateTo(mapPolygon, long, lat) {
    animation = chart.animate([{
        property: "deltaLongitude",
        to: long
    }, {
        property: "deltaLatitude",
        to: lat
    }], 2000).events.on("animationended",function() { chart.zoomToMapObject(mapPolygon);});
}

function showAttackLegend() {
    var label = chart.createChild(am4core.Label);
    label.text = "🚀  "+lang.attackLegend;
    label.fontSize = 12;
    label.align = "left";
    label.valign = "bottom"
    label.fill = am4core.color("#000");
    label.background = new am4core.RoundedRectangle()
    label.background.cornerRadius(5,5,5,5);
    label.padding(10,10,10,10);
    label.marginLeft = 20;
    label.marginBottom = 80;
    label.background.strokeOpacity = 0.3;
    label.background.stroke =am4core.color("#927459");
    label.background.fill = am4core.color("#f9e3fe");
    label.background.fillOpacity = 0.6;

    attacklog = chart.createChild(am4core.Label);
    attacklog.fontSize = 12;
    attacklog.fontFamily = 'Arial';
    attacklog.minWidth = '300';
    attacklog.align = "left";
    attacklog.valign = "top"
    attacklog.fill = am4core.color("#000");
    attacklog.background = new am4core.RoundedRectangle()
    attacklog.background.cornerRadius(5,5,5,5);
    attacklog.padding(10,10,10,10);
    attacklog.marginLeft = 20;
    attacklog.marginBottom = 280;
    attacklog.background.strokeOpacity = 0.3;
    attacklog.background.stroke =am4core.color("#927459");
    attacklog.background.fill = am4core.color("#f9e3fe");
    attacklog.background.fillOpacity = 0.6;
}

function Channel(audio_uri) {
    this.audio_uri = audio_uri;
    this.resource = new Audio(audio_uri);
}

Channel.prototype.play = function() {
    // Try refreshing the resource altogether
    this.resource.play();
}

function Switcher(audio_uri, num) {
    this.channels = [];
    this.num = num;
    this.index = 0;

    for (var i = 0; i < num; i++) {
        this.channels.push(new Channel(audio_uri));
    }
}

Switcher.prototype.play = function() {
    this.channels[this.index++].play();
    this.index = this.index < this.num ? this.index : 0;
}

Sound = (function() {
    var self = {};

    self.playFire = function() {
        sfx_switcher_fire.play();
    }

    self.playPew = function() {
        sfx_switcher_pew.play();
    }

    self.playRico = function() {
        sfx_switcher_rico.play();
    }

    self.init = function() {
        sfx_switcher_fire   = new Switcher('modules/sec_geoip_map/sounds/fast_rocket.mp3', 10);
        sfx_switcher_pew    = new Switcher('modules/sec_geoip_map/sounds/mehackit_7.mp3', 10);
        sfx_switcher_rico   = new Switcher('modules/sec_geoip_map/sounds/ricochet.mp3', 10);
    }

    return self;
}());

function atacar(origen,destino,hora,nombre) {
    if(origen=='IP Address not found') {
        return
    }

    if(enableAnimations==0) return;

    if(origen==destino) {
        Sound.playRico();

        name = am4geodata_data_countries2[origen].country;
        latestAttacks.push(lang["{0}: Stopped attack from {1}"].format(hora,nombre));
        if(latestAttacks.length>5) {
            latestAttacks.shift();
        }
        attacklog.text = latestAttacks.join("\n");


        return
    }

    const now = Date.now();
    Sound.playFire();

    or = polygonSeries.getPolygonById(origen);
    ds = polygonSeries.getPolygonById(destino);
    coord = [
        { "latitude": or.visualLatitude, "longitude": or.visualLongitude  },
        { "latitude": homeLatitude, "longitude": homeLongitude  }
    ];
    let line = lineSeries.mapLines.create();
    line.line.strokeOpacity = 0; // A Germán no le gusta la línea
    line.multiGeoLine=[coord];

    distance = chart.projection.distance({ "latitude": or.visualLatitude, "longitude": or.visualLongitude   }, { "latitude": homeLatitude, "longitude": homeLongitude   });
    animduration = distance * 3000;

    let bomb = [];

    bullet = line.lineObjects.create();
    bullet.nonScaling = true;
    bullet.position = 0;
    bullet.horizontalCenter = "middle";
    bullet.verticalCenter = "middle";

    bomb[now] = bullet.createChild(am4core.Image);
    bomb[now].href = "modules/sec_geoip_map/images/misil.svg";
    bomb[now].width = 25;
    bomb[now].height = 25;
    bomb[now].verticalCenter = "middle";
    bomb[now].horizontalCenter = "middle";
    bomb[now].rotation = 45;

    var from = 0;
    var to = 0.9;
    var animation = bullet.animate({
      from: from,
      to: to,
      property: "position"
    }, animduration, am4core.ease.sinInOut);

    animation.events.on("animationended", endBomb)
    function endBomb() {
        bomb[now].href = "modules/sec_geoip_map/images/explosion.png";
        setTimeout(function() { line.line.dispose(); bomb[now].dispose(); }, 1000);
        Sound.playPew();

        name = am4geodata_data_countries2[origen].country;
        latestAttacks.push(lang["{0}: Stopped attack from {1}"].format(hora,nombre));
        if(latestAttacks.length>5) {
            latestAttacks.shift();
        }
        attacklog.text = latestAttacks.join("\n");
    }
}

if (!String.prototype.format) {
    String.prototype.format = function() {
        var args = arguments;
        return this.replace(/{(\d+)}/g, function(match, number) {
            return typeof args[number] != 'undefined'
                ? args[number]
                : match
                ;
        });
    };
}
// Listen for keydown events
document.addEventListener('keydown', keyHandler, false);

Sound.init();

