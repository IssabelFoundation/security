$(document).ready(function(){

    setSelectedDomain();
    changeActivateDefault();
    $('#SMTP_Server').change(function(){
        var domain = $('#SMTP_Server option:selected').val();
        if(domain == "custom"){
            $('input[name=relayhost]').val("");
            $('input[name=port]').val("");
        }else{
            $('input[name=relayhost]').val(domain);
            $('input[name=port]').val("587");
        }
    });
    $('input[name=chkoldhanguponpolarityswitch]').iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#hanguponpolarityswitch").val($input.is(":checked") ? "on" : "off");
        }
    }).trigger("change");

    $('input[name=chkoldenddialkey]').iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#enddialkey").val($input.is(":checked") ? "on" : "off");
        }
    }).trigger("change");


    $('input[name=chkoldsendpolarityrev]').iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#sendpolarityrev").val($input.is(":checked") ? "on" : "off");
        }
    }).trigger("change");


    $('input[name=chkoldchecksipbinded]').iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#checksipbinded").val($input.is(":checked") ? "on" : "off");
        }
    }).trigger("change");

    $('input[name=chkoldflashwink]').iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#flashwink").val($input.is(":checked") ? "on" : "off");
        }
    }).trigger("change");

    $('input[name=chkoldciddisplay]').iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#ciddisplay").val($input.is(":checked") ? "on" : "off");
        }
    }).trigger("change");

    $('input[name=chkoldusecallerid]').iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#usecallerid").val($input.is(":checked") ? "on" : "off");
        }
    }).trigger("change");

    $('input[name=chkoldhidecallerid]').iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#hidecallerid").val($input.is(":checked") ? "on" : "off");
        }
    }).trigger("change");

    $('input[name=chkoldbusydetect]').iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#busydetect").val($input.is(":checked") ? "on" : "off");
        }
    }).trigger("change");

    $('input[name=chkoldsilencedetect]').iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#silencedetect").val($input.is(":checked") ? "on" : "off");
        }
    }).trigger("change");

    $('input[name=chkoldfaxecm]').iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#faxecm").val($input.is(":checked") ? "on" : "off");
        }
    }).trigger("change");

    $('input[name=chkoldecho]').iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#echo").val($input.is(":checked") ? "on" : "off");
        }
    }).trigger("change");
    $('input[name=chkoldhandleirregularcid]').iButton({                                  
        labelOn: "On",                                                     
        labelOff: "Off",                                                   
        change: function ($input){                                         
            $("#handleirregularcid").val($input.is(":checked") ? "on" : "off");          
        }                                                                  
    }).trigger("change"); 

//	changeActivateDefault();
//	setSelectedDomain();

    $('#SMTP_Server').change(function(){
        var server = $('#SMTP_Server option:selected').text();
        var example = "";

        if(server=="GMAIL" || server=="HOTMAIL"){
            $('input[name=chkoldautentification]').prop("checked", true);
            $('#autentification').val("on");
        }else{
            $('input[name=chkoldautentification]').prop("checked", false);
            $('#autentification').val("off");
        }
        if(server=="GMAIL" || server=="HOTMAIL" || server=="YAHOO"){
            example = "example@"+server.toLowerCase()+".com";
	    $('.validpass').show();
	}else{
            example = "example@domain.com";
	    $('.validpass').hide();
	}
        $('#example').text(example);
    });
});


function setSelectedDomain(){

    $('#SMTP_Server option').each(function(){
        var dominio = $('input[name=relayhost]').val();
        var relay   = $(this).text();
        var server  = "";
        if(/smtp\.gmail\.com/.test(dominio))
            server = "GMAIL";
        if(/smtp\.mail\.yahoo\.com/.test(dominio))
            server = "YAHOO";
        if(/smtp\.live\.com/.test(dominio))
            server = "HOTMAIL";
        if(relay==server){
            var example = "example@"+server.toLowerCase()+".com";
            $(this).attr("selected", "selected");
            $('#example').text(example);
        }
    });

    var server = $('#SMTP_Server option:selected').val();
	if(server=="custom")
		$('.validpass').hide();
    else
		$('.validpass').show();

}

// cambia el estado del hidden "status" de on a off
function changeActivateDefault()
{
    var flash_wink = $('#flashwink').val();
    if(flash_wink=="on"){
        $("input[name=chkoldflashwink]").prop("checked", true);
        $("#flashwink").val("on");
    }else{
        $("input[name=chkoldflash_wink]").prop("checked", false);
        $("#flashwink").val("off");
    }

    var pound_end = $('#pound_end').val();
    if(pound_end=="on"){
        $("input[name=chkoldpound_end]").prop("checked", true);
        $("#pound_end").val("on");
    }else{
        $("input[name=chkoldpound_end]").prop("checked", false);
        $("#pound_end").val("off");
    }



}

