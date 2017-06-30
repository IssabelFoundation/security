function hideField(opcion)
{
    if(opcion == "TCP" || opcion == "UDP"){
        document.getElementById('port').style.display='';
        document.getElementById('type').style.display='none';
        document.getElementById('code').style.display='none';
        document.getElementById('protocol_number').style.display='none';   
    }else if(opcion == "ICMP"){
        document.getElementById('port').style.display='none';
        document.getElementById('type').style.display='';
        document.getElementById('code').style.display='';
        document.getElementById('protocol_number').style.display='none';
    }else{
        document.getElementById('port').style.display='none';
        document.getElementById('type').style.display='none';
        document.getElementById('code').style.display='none';
        document.getElementById('protocol_number').style.display='';
    }
}

$(document).ready(function () {
    $("#ignoreipfield").on('keydown', function (e) {
        if(jQuery.inArray(e.keyCode, [ 8, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 190, 191, 32, 188 ])==-1) {
            e.preventDefault();
            return false;
        }
    });
});
