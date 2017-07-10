$(function(){

//generate certificate
$("#btninstall").click(function() {
    $("#loading1").html("<div align=center><br><br><img src='modules/sec_letsencrypt/themes/default/ajaxl.gif'/></div><br><br>");
    $.ajax({
        type: "POST",
        url: "modules/sec_letsencrypt/installcert.php",
        data:{   
            staging: $("#staging:checked").val(),
            domain: $("#domain").val(),
            email: $("#email").val()
        },
        dataType: "json", 
        success: function(data) {
            if ( data == '0' ){
                $('#loading1').html("");
                alert("Please complete all fields");
            } else {
                $('#loading1').html("");
                $("#domain").val(data.domain);
                $("#email").val(data.email);
                $('#output').attr("class","alert alert-warning");
                $('#output').html(data.result);
            }
        }
    });
});

// Renew Certificate
$("#btnrenew").click(function() {
    $("#loading1").html("<div align=center><br><br><img  src='modules/sec_letsencrypt/themes/default/ajaxl.gif'/></div><br><br>");
    $.ajax({
        type: "POST",
        url: "modules/sec_letsencrypt/installcert.php",
        data:{   
            renew: 1
        },
        dataType: "json", 
        success: function(data){
            $('#loading1').html("");          
            $('#output').attr("class","alert alert-warning");
            $('#output').html(data.result);
        }
     });
});

});

