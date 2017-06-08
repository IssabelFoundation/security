$(document).ready(function(){
    $('input[name=chkoldavailability]').iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#availability").val($input.is(":checked") ? "on" : "off");
        }
    }).trigger("change");
});
