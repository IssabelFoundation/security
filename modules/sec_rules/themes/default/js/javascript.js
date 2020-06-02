var changeRule = false;
var port = {};
var table;
var geoiptble;
var mainContentWidth;

$(document).ready(function(){

    at();
    showElementByTraffic();
    showElementByProtocol();
    warningGeoip();

    $('.fielform').css("border-color","#FFF");

    $(document).on('change','#id_protocol',function(){
        console.log('id protocol change');
        var valor = $('#id_protocol option:selected').val();
        var arrAction          = new Array();
        arrAction["action"]    = "getPorts";
        arrAction["menu"]      = "sec_rules";
        arrAction["rawmode"]   = "yes";
        arrAction["protocol"]  =  valor;
        request("index.php",arrAction,false,
                function(arrData,statusResponse,error)
                {
                    var html = "";
                    $('#port_in').html("");
                    $('#port_out').html("");
                    var key = "";
                    for(key in arrData){
                        valor = arrData[key];
                        html += "<option value = "+key+">"+valor+"</option>";
                    }
                    $('#port_in').html(html).trigger("chosen:updated");;
                    $('#port_out').html(html).trigger("chosen:updated");;
                }
            );
    });

    $(document).on('show.bs.modal', function(e) {
        $('.main-content').css('width', mainContentWidth);
    });

    $('#selectall').on('change', function(e) {
        var isChecked = $(this).prop('checked');
        $('input[name="selectedRow[]"]').prop('checked', isChecked); 
        return true;
    });

    $('#selectallgeo').on('change', function(e) {
        var isChecked = $(this).prop('checked');
        $('input[name="selectedRowGeo[]"]').prop('checked', isChecked); 
        return true;
    });

    // new event handler for cancel as form is inside a modal now
    $(document).on('click',"[name*='cancel']", function(e) {
        e.preventDefault();
        $('#editRule').modal('hide');
        return false;
    });

    $(document).on('click',".activate", function(e) {
        $("body").addClass('waiting');
        $.ajax({
            url : '/index.php?menu=sec_rules&action=Activate&rawmode=yes&id='+$(e.target).data('id'),
            type : 'GET',
        }).always(function(data) { 
           table.ajax.reload(); 
           geoiptable.ajax.reload();
           $("body").removeClass('waiting');
        });

    });

    $(document).on('click',".deactivate", function(e) {
        console.log('body add class waiting');
        $("body").addClass('waiting');
        $.ajax({
            url : '/index.php?menu=sec_rules&action=Desactivate&rawmode=yes&id='+$(e.target).data('id'),
            type : 'GET',
        }).always(function(data) { 
             table.ajax.reload(); 
             geoiptable.ajax.reload();
             $("body").removeClass('waiting');
             console.log('body removeclass waiting');
        });

    });

    $(document).on('click',".editrule", function(e) {
       $("body").addClass('waiting');

       url = "index.php?menu=sec_rules&action=edit&rawmode=yes&id="+$(e.target).data('id');
       $.ajax({
          url : url,
          type : 'GET',
          success: function(data) {
             $('#ruleedit').html(data);
             styleForm();
          }
       }).always(function() { $('body').removeClass('waiting');} );
    });

});

function warningGeoip() {
    if($('#hasgeoip').val()=='no') {
        toastr['warning'](_tr('The firewall does not have support for GeoIP Localization'));
    }
}

function createMsg(tittle,message_data,button_tittle,aviso){
    if(tittle && message_data){
        $("#message_error").remove();
        if(document.getElementById("neo-contentbox-maincolumn")){
            var message= "<div class='div_msg_errors' id='message_error'>" +
                        "<div style='float:left;'>" +
                        "<b style='color:red;'>&nbsp;&nbsp;"+tittle+": </b>" +
                        "</div>" +
                        "<div style='text-align:right; padding:5px'>" +
                        "<input type='button' onclick='hide_message_error();' value='"+button_tittle+"'/>" +
                        "</div>" +
                        "<div style='position:relative; top:-12px; padding: 0px 5px'>" +
                        message_data +
                        "</div>" +
                    "</div>";

            $(".neo-module-content:first").prepend(message);
            document.getElementById("msg_status").style.border = "1px solid #AAA";
            $("#msg_status").html(aviso);
                setTimeout('$("#msg_status").html("")',300);
                setTimeout('document.getElementById("msg_status").style.border = ""',300);
        }
        else if(document.getElementById("elx-blackmin-content")){
            var message = "<div class='ui-state-highlight ui-corner-all' id='message_error'>" +
                        "<p>" +
                        "<span style='float: left; margin-right: 0.3em;' class='ui-icon ui-icon-info'></span>" +
                        "<span id='issabel-callcenter-info-message-text'>"+ tittle + ": " + message_data +"</span>" +
                        "</p>" +
                    "</div>";
            $("#elx-blackmin-content").prepend(message);
            $("#msg_status").html("<span style='color:white;'>"+aviso+"</span>");
                setTimeout('$("#msg_status").html("")',300);
        }
        else{
            $(".message_board").remove();
            var message= "<div id='message_error'><table width='100%'><tr><td align='left'><b style='color:red;'>" +
                    tittle + ": </b>" + message_data + "</td> <td align='right'><input type='button' onclick='hide_message_error();' value='" +
                    button_tittle+ "'/></td></tr></table></div>";
            $("body > table > tbody > tr > td:last").prepend(message);
            $("#msg_status").html("<span style='color:red;'>"+aviso+"</span>");
                setTimeout('$("#msg_status").html("")',300);
        }
    }
}

function showElementByTraffic()
{
    var traffic = document.getElementById('id_traffic');

    if(traffic){
        if( traffic.value == 'INPUT' ){
            document.getElementById('id_interface_in').style.display = '';
            document.getElementById('id_interface_out').style.display = 'none';
        }
        else if( traffic.value == 'OUTPUT' ){
            document.getElementById('id_interface_in').style.display = 'none';
            document.getElementById('id_interface_out').style.display = '';
        }
        else if( traffic.value == 'FORWARD' ){
            document.getElementById('id_interface_in').style.display = '';
            document.getElementById('id_interface_out').style.display = '';
        }
    }
}

function showElementByProtocol()
{
    var protoc = document.getElementById('id_protocol');

    if(protoc){
        if( protoc.value == 'TCP' ){
            document.getElementById('id_port_in').style.display = '';
            document.getElementById('id_port_out').style.display = '';
            document.getElementById('id_type_icmp').style.display = 'none';
            document.getElementById('id_established').style.display = 'none';
            document.getElementById('id_related').style.display = 'none';
            document.getElementById('id_geoipcountries').style.display = 'none';
            document.getElementById('id_geoipcontinents').style.display = 'none';
            document.getElementById('id_id_ip').style.display = 'none';
        }
        else if( protoc.value == 'UDP' ){
            document.getElementById('id_port_in').style.display = '';
            document.getElementById('id_port_out').style.display = '';
            document.getElementById('id_type_icmp').style.display = 'none';
            document.getElementById('id_established').style.display = 'none';
            document.getElementById('id_related').style.display = 'none';
            document.getElementById('id_geoipcountries').style.display = 'none';
            document.getElementById('id_geoipcontinents').style.display = 'none';
            document.getElementById('id_id_ip').style.display = 'none';
        }
        else if( protoc.value == 'ICMP' ){
            document.getElementById('id_port_in').style.display = 'none';
            document.getElementById('id_port_out').style.display = 'none';
            document.getElementById('id_type_icmp').style.display = '';
            document.getElementById('id_established').style.display = 'none';
            document.getElementById('id_related').style.display = 'none';
            document.getElementById('id_geoipcountries').style.display = 'none';
            document.getElementById('id_geoipcontinents').style.display = 'none';
            document.getElementById('id_id_ip').style.display = 'none';
        }
        else if( protoc.value == 'IP' ){
            document.getElementById('id_port_in').style.display = 'none';
            document.getElementById('id_port_out').style.display = 'none';
            document.getElementById('id_type_icmp').style.display = 'none';
            document.getElementById('id_established').style.display = 'none';
            document.getElementById('id_related').style.display = 'none';
            document.getElementById('id_geoipcountries').style.display = 'none';
            document.getElementById('id_geoipcontinents').style.display = 'none';
            document.getElementById('id_id_ip').style.display = '';
        }
        else if( protoc.value == 'GEOIP' ){
            document.getElementById('id_port_in').style.display = 'none';
            document.getElementById('id_port_out').style.display = 'none';
            document.getElementById('id_type_icmp').style.display = 'none';
            document.getElementById('id_established').style.display = 'none';
            document.getElementById('id_related').style.display = 'none';
            document.getElementById('id_geoipcountries').style.display = '';
            document.getElementById('id_geoipcontinents').style.display = '';
            document.getElementById('id_id_ip').style.display = 'none';
        }
        else if( protoc.value == 'ALL' ){
            document.getElementById('id_port_in').style.display = 'none';
            document.getElementById('id_port_out').style.display = 'none';
            document.getElementById('id_type_icmp').style.display = 'none';
            document.getElementById('id_established').style.display = 'none';
            document.getElementById('id_related').style.display = 'none';
            document.getElementById('id_geoipcountries').style.display = 'none';
            document.getElementById('id_geoipcontinents').style.display = 'none';
            document.getElementById('id_id_ip').style.display = 'none';
        }
        else if( protoc.value == 'STATE' ){
            document.getElementById('id_port_in').style.display = 'none';
            document.getElementById('id_port_out').style.display = 'none';
            document.getElementById('id_type_icmp').style.display = 'none';
            document.getElementById('id_id_ip').style.display = 'none';
            var state = document.getElementById('state');
            var input_ = state.getElementsByTagName('input');
            var established_check = false;
            var related_check = false;
            if(input_[0].value == ""){
                 established_check = false;
                 related_check = false;
            }else{
                var tmp = input_[0].value.split(",");
                if(tmp[0]=="Established"){
                     established_check = true;
                    if(tmp[1]=="Related")
                         related_check = true;
                }else if(tmp[0]=="Related")
                        related_check = true;
            }
            var established = document.getElementById('id_established');
            established.style.display = '';
            var checkbox1 = established.getElementsByTagName("input");
            checkbox1[0].checked = established_check;
            if(established_check)
                document.getElementById('established').value = "on";
            else
                document.getElementById('established').value = "off";
            var related = document.getElementById('id_related');
            related.style.display = '';
            var checkbox2 = related.getElementsByTagName("input");
            checkbox2[0].checked = related_check;
            if(related_check)
                document.getElementById('related').value = "on";
            else
                document.getElementById('related').value = "off";
        }
    }
}

function appendModal() {
    $('body').prepend("<div class='modal fade' id='xeditRule'> <div class='modal-dialog modal-lg'> <div class='modal-content' > <div class='modal-body' id='ruleedit'> </div> </div> </div> </div>");
}

function at() {
  $.ajax({
    url : '/index.php?menu=sec_rules&action=getPorts&protocol=TCP&rawmode=yes',
    type : 'GET',
    dataType:'json',
    success : function(data) { 

      for(var prop in data.message) {
          port[prop]=data.message[prop];
      }

      $.ajax({
        url : '/index.php?menu=sec_rules&action=getPorts&protocol=UDP&rawmode=yes',
        type : 'GET',
        dataType:'json',
        success : function(data) { 
            for(var prop in data.message) {
                port[prop]=data.message[prop];
            }
            dt();
        }
      })
    },
    error : function(request,error)
    {
        console.log("Request: "+JSON.stringify(request));
    }
});    
}

function dt() {
    $.fn.dataTable.Buttons.defaults.dom.button.className = 'btn';

/*    if($('#geocount').val()>0) {
        domstring = "r<'h3'>t";
    } else {
        domstring = "Br<'h3'>t";
    }
*/
    domstring = "r<'h3'>t";

    table = $('#rulestable').DataTable( {
        dom: domstring,
        bSort: false,
        preDrawCallback: function( settings ) {
           $('.applyrules').hide();
           if($('#firewall_desactivado').val()=='yes') {
              $('.deactivatefw').hide();
              $('.activatefw').show();
           } else {
              $('.deactivatefw').show();
              $('.activatefw').hide();
           }
           if($('#changes_pending').val()=='yes') {
              $('.applyrules').show();
           } else {
              $('.applyrules').hide();
           }
        },
        drawCallback: function() {
            $('#rulestable_wrapper').find('.h3').html(_tr('Rules')).css('clear','both').css('padding-top','10px');
        },
        ajax: {
           url: '/index.php?menu=sec_rules&action=getRules&rawmode=yes',
           dataSrc: "message"
        },
        order: [[1,'asc']],
        pageLength: 100,
        columnDefs: [ 
            {targets: [1,2,3,4,5,6,7,8], className: 'reorder'},
            {
               orderable: true,
               className: 'xselect-checkbox',
               targets: 0
            } 
        ],
        rowReorder: {
            dataSrc: 1,
            selector: '.reorder'
        },
        columns: [
            { 
               data: 'id', 
               render: function(data,type,row,meta) {
                   return "<input type=\"checkbox\" class=\"form-control selcheck\" name=\"selectedRow[]\" value=\""+row.id+"\" >";
               }
             },
            { 
              data: 'rule_order'
            },
            { 
               data: 'traffic',
               render: function(data,type,row,meta) {
                   if(data=='INPUT') {
                       return "<img src='modules/sec_rules/themes/default/img/input.png' style='height:16px;' alt='input' data-toggle='tooltip' title='"+_tr(data)+"' />";
                   } else if(data=='OUTPUT') {
                       return "<img src='modules/sec_rules/themes/default/img/output.png' style='height:16px;' alt='output' data-toggle='tooltip' title='"+_tr(data)+"' />";
                   } else {
                       return "<img src='modules/sec_rules/themes/default/img/forward.png' style='height:16px;' alt='forward' data-toggle='tooltip' title='"+_tr(data)+"' />";
                   }
               }
            },
            { 
               data: 'target', 
               render: function(data,type,row,meta) {
                   if(data=='ACCEPT') {
                       return "<img src='modules/sec_rules/images/target_accept.gif' style='_width:16px;' alt='accept' data-toggle='tooltip' title='"+_tr(data)+"' />";
                   } else {
                       return "<img src='modules/sec_rules/images/target_drop.gif' style='_width:16px;' alt='drop' data-toggle='tooltip' title='"+_tr(data)+"' />";
                   }
               }
            },
            { 
               data: 'eth_in',
               render: function(data,type,row,meta) {
                   ret ='';
                   if(row.eth_in!='') {
                       ret += _tr('INPUT')+': '+row.eth_in;
                   }
                   if(row.eth_out!='') {
                       if(ret!='') { ret+='<br>'; }
                       ret += _tr('OUTPUT')+': '+row.eth_out;
                   }
                   return ret;
               }
            },
            { data: 'ip_source' },
            { data: 'ip_destiny' },
            { 
               data: 'protocol',
               render: function(data,type,row,meta) {
                   if(data=='GEOIP') {
                       return "<img src='modules/sec_rules/themes/default/img/globe.png' style='height:16px;' alt='GEOIP' data-toggle='tooltip' title='"+_tr(data)+"' />";
                   }
                   return data;
               }
            },
            { 
               data: 'dport',
               render: function(data,type,row,meta) {
                   ret = '';
                   if(row.protocol=='ICMP') {
                       ret += "Tipo: "+_tr(row.icmp_type);
                   } else if(row.protocol=='GEOIP') {
                       if(row.continents!="") {
                           ret += _tr("Continents")+": "+row.continents;
                       }
                       if(row.countries!="") {
                           if(ret!="") { ret+="<br>"; }
                           ret += _tr("Countries")+": "+row.countries;
                       }
                   } else if(row.protocol=='ALL') {
                       ret += '';
                   } else if(row.protocol=='STATE') {
                       ret += row.state;
                   } else {
                       sport = port.hasOwnProperty(row.sport)?port[row.sport]:row.sport;
                       dport = port.hasOwnProperty(row.dport)?port[row.dport]:row.dport;
                       ret += _tr("Port Source")+": "+sport;
                       ret += "<br>";
                       ret += _tr("Port Destine")+": "+dport;
                       if(sport=="" && dport=="") ret = "";
                   }
                   return ret;
               }
            },
            { 
               data: 'activated', 
               render: function(data,type,row,meta) {
                  if(data=='1') {
                       return "<img data-id='"+row.id+"' class='deactivate' src='modules/sec_rules/themes/default/img/on.png' alt='deactivate' data-toggle='tooltip' title='"+_tr('Desactivate')+"'/>";
                   } else {
                       return "<img data-id='"+row.id+"' src='modules/sec_rules/themes/default/img/off.png' class='activate' alt='activate' data-toggle='tooltip' title='"+_tr('Activate')+"'/>";
                   }
               }
            },
            {
               data: 'id',
               render: function(data,type,row,meta) {
                   return "<img data-id='"+row.id+"' class='editrule' src='modules/sec_rules/themes/default/img/edit.png' alt='edit' />";
               }
 
            }
        ]
    } );

    geoiptable = $('#geoiptable').DataTable( {
        dom: "Br<'h3'>t",
        bSort: false,
        preDrawCallback: function( settings ) {
           $('.applyrules').hide();
           if($('#firewall_desactivado').val()=='yes') {
              $('.deactivatefw').hide();
              $('.activatefw').show();
           } else {
              $('.deactivatefw').show();
              $('.activatefw').hide();
           }
           if($('#changes_pending').val()=='yes') {
              $('.applyrules').show();
           } else {
              $('.applyrules').hide();
           }
        },
        drawCallback: function() {
            $('#geoiptable_wrapper').find('.h3').html(_tr('GeoIP')).css('clear','both').css('padding-top','10px');
        },
        ajax: {
           url: '/index.php?menu=sec_rules&action=getRulesGEOIP&rawmode=yes',
           dataSrc: "message"
        },
        order: [[1,'asc']],
        pageLength: 100,
        columnDefs: [ 
            {targets: [1,2,3,4,5,6,7,8], className: 'reorder'},
            {
               orderable: true,
               className: 'xselect-checkbox',
               targets: 0
            } 
        ],
        rowReorder: {
            dataSrc: 'rule_reorder',
            selector: '.reorder'
        },
        columns: [
            { 
               data: 'id', 
               render: function(data,type,row,meta) {
                   return "<input type=\"checkbox\" class=\"form-control selcheck\" name=\"selectedRowGeo[]\" value=\""+row.id+"\" >";
               }
             },
            { 
              data: 'rule_order',
              render: function(data,type,row,meta) {
                 return data-100000;
              }
            },
            { 
               data: 'traffic',
               render: function(data,type,row,meta) {
                   if(data=='INPUT') {
                       return "<img src='modules/sec_rules/themes/default/img/input.png' style='height:16px;' alt='input' data-toggle='tooltip' title='"+_tr(data)+"' />";
                   } else if(data=='OUTPUT') {
                       return "<img src='modules/sec_rules/themes/default/img/output.png' style='height:16px;' alt='output' data-toggle='tooltip' title='"+_tr(data)+"' />";
                   } else {
                       return "<img src='modules/sec_rules/themes/default/img/forward.png' style='height:16px;' alt='forward' data-toggle='tooltip' title='"+_tr(data)+"' />";
                   }
               }
            },
            { 
               data: 'target', 
               render: function(data,type,row,meta) {
                   if(data=='ACCEPT') {
                       return "<img src='modules/sec_rules/images/target_accept.gif' style='_width:16px;' alt='accept' data-toggle='tooltip' title='"+_tr(data)+"' />";
                   } else {
                       return "<img src='modules/sec_rules/images/target_drop.gif' style='_width:16px;' alt='drop' data-toggle='tooltip' title='"+_tr(data)+"' />";
                   }
               }
            },
            { 
               data: 'eth_in',
               render: function(data,type,row,meta) {
                   ret ='';
                   if(row.eth_in!='') {
                       ret += _tr('INPUT')+': '+row.eth_in;
                   }
                   if(row.eth_out!='') {
                       if(ret!='') { ret+='<br>'; }
                       ret += _tr('OUTPUT')+': '+row.eth_out;
                   }
                   return ret;
               }
            },
            { data: 'ip_source' },
            { data: 'ip_destiny' },
            { 
               data: 'protocol',
               render: function(data,type,row,meta) {
                   if(data=='GEOIP') {
                       return "<img src='modules/sec_rules/themes/default/img/globe.png' style='height:16px;' alt='GEOIP' data-toggle='tooltip' title='"+_tr(data)+"' />";
                   }
                   return data;
               }
            },
            { 
               data: 'dport',
               render: function(data,type,row,meta) {
                   ret = '';
                   if(row.protocol=='ICMP') {
                       ret += "Tipo: "+_tr(row.icmp_type);
                   } else if(row.protocol=='GEOIP') {
                       if(row.continents!="") {
                           ret += _tr("Continents")+": "+row.continents;
                       }
                       if(row.countries!="") {
                           if(ret!="") { ret+="<br>"; }
                           ret += _tr("Countries")+": "+row.countries;
                       }
                   } else if(row.protocol=='ALL') {
                       ret += '';
                   } else if(row.protocol=='STATE') {
                       ret += row.state;
                   } else {
                       sport = port.hasOwnProperty(row.sport)?port[row.sport]:row.sport;
                       dport = port.hasOwnProperty(row.dport)?port[row.dport]:row.dport;
                       ret += _tr("Port Source")+": "+sport;
                       ret += "<br>";
                       ret += _tr("Port Destine")+": "+dport;
                       if(sport=="" && dport=="") ret = "";
                   }
                   return ret;
               }
            },
            { 
               data: 'activated', 
               render: function(data,type,row,meta) {
                  if(data=='1') {
                       return "<img data-id='"+row.id+"' class='deactivate' src='modules/sec_rules/themes/default/img/on.png' alt='deactivate' data-toggle='tooltip' title='"+_tr('Desactivate')+"'/>";
                   } else {
                       return "<img data-id='"+row.id+"' src='modules/sec_rules/themes/default/img/off.png' class='activate' alt='activate' data-toggle='tooltip' title='"+_tr('Activate')+"'/>";
                   }
               }
            },
            {
               data: 'id',
               render: function(data,type,row,meta) {
                   return "<img data-id='"+row.id+"' class='editrule' src='modules/sec_rules/themes/default/img/edit.png' alt='edit' />";
               }
 
            }
        ],
       buttons: [
        {
          className: 'btn-danger',
          text: '<i class="fa fa-eraser"></i> '+_tr('Delete'),
          action: function ( e, dt, node, config ) {

              if(confirmSubmit(_tr('Are you sure you wish to delete the Rule?'))) {
                delrule = [];
                $('input[name="selectedRowGeo[]"]').each(function(el) {
                     if($(this).prop('checked')==true) {
                        delrule.push($(this).val());
                     } 
                });
                $('input[name="selectedRow[]"]').each(function(el) {
                     if($(this).prop('checked')==true) {
                        delrule.push($(this).val());
                     } 
                });
                if(delrule.length>0) {
                $.ajax({
                    url: "index.php",
                    type: 'POST',
                    data: { menu: "sec_rules", action: "deleterules", rawmode: "yes", rules: delrule.join(',')},
                    dataType: 'json',
                }).always(function(data) { 
                    if(data.status=="success") {
                        toastr['success'](_tr(data.message),"").css('top',$(window).scrollTop());
                    } else {
                        toastr['error'](_tr(data.message),"").css('top',$(window).scrollTop());
                    }
                    table.ajax.reload();
                    geoiptable.ajax.reload();
                });
                } else {
                    toastr['warning'](_tr('No rules were selected'),'').css('top',$(window).scrollTop());
                }

              }
          }
        },
        {
            className: 'btn-default deactivatefw',
            text: _tr('Desactivate FireWall'),
            action: function ( e, dt, node, config ) {
                $("body").addClass('waiting');
                $.ajax({
                    url: "index.php",
                    type: 'POST',
                    data: { menu: "sec_rules", desactivatefirewall: "1", rawmode: "yes" },
                    dataType: 'json'
                }).always(function(ret) { 
                     if(ret.status=='success') {
                         $('.deactivatefw').hide();
                         $('.activatefw').show();
                         toastr['success'](_tr(ret.message)).css('top',$(window).scrollTop());
                         $('#firewall_desactivado').val('yes');
                     } else {
                         toastr['error'](_tr(ret.message)).css('top',$(window).scrollTop());
                     }
                     $("body").removeClass('waiting');
                });
            }
            
        },
        {
            className: 'btn-default activatefw',
            text: _tr('Activate FireWall'),
            action: function ( e, dt, node, config ) {
                $("body").addClass('waiting');
                $.ajax({
                    url: "index.php",
                    type: 'POST',
                    data: { menu: "sec_rules", activatefirewall: "1", rawmode: "yes" }
                }).always(function(data) {
                     ret = JSON.parse(data);
                     if(ret.status=='success') {
                         $('.deactivatefw').show();
                         $('.activatefw').hide();
                         toastr['success'](_tr(ret.message)).css('top',$(window).scrollTop());
                         $('#firewall_desactivado').val('no');
                     } else {
                         toastr['error'](_tr(ret.message)).css('top',$(window).scrollTop());
                     }
                     $("body").removeClass('waiting');
                });
            }
            
        },
         {
            className: 'btn-default applyrules',
            text: _tr('Apply Changes'),
            action: function ( e, dt, node, config ) {
                $("body").addClass('waiting');
                $.ajax({
                    url: "index.php",
                    type: 'POST',
                    data: { menu: "sec_rules", activatefirewall: "1", rawmode: "yes" }
                }).always(function(data) {
                     ret = JSON.parse(data);
                     if(ret.status=='success') {
                         toastr['success'](_tr(ret.message)).css('top',$(window).scrollTop());
                         checkExecute(); 
                     } else {
                         toastr['error'](_tr(ret.message),_tr(ret.title)).css('top',$(window).scrollTop());
                     }
                     $("body").removeClass('waiting');
                });
            }
            
        },
 
        {
          className: 'btn-default',
          text: _tr('New Rule'),
          action: function ( e, dt, node, config ) {
               url = "index.php?menu=sec_rules&new=1&rawmode=yes";
               $.ajax({
                  url : url,
                  type : 'GET',
                  success: function(data) {
                     $('#ruleedit').html(data);
                     styleForm();
                  }
               }); 
 
          }
        },
       ]
    } );

    table.on( 'draw', function ( e, diff, edit ) {
        $('[data-toggle="tooltip"]').tooltip();
    });

    table.on( 'xhr', function ( e, diff, edit ) {
        $('#rulestable').find('tbody').css('opacity','1');
        checkExecute(); 
    });

    table.on( 'row-reorder', function ( e, diff, edit ) {

       if(diff.length==0) { return; }

        $('#rulestable').find('tbody').css('opacity','.5');

        $.ajax({
            url: 'index.php',
            type: 'POST',
            data: { menu: "sec_rules", action: "sort", rawmode: "yes", json: JSON.stringify(diff)},
            dataType: 'json'
        }).always(function(data) { 
             table.ajax.reload();
             geoiptable.ajax.reload();
        });

    });
 
    geoiptable.on( 'draw', function ( e, diff, edit ) {
        $('[data-toggle="tooltip"]').tooltip();
    });

    geoiptable.on( 'xhr', function ( e, diff, edit ) {
        $('#geoiptable').find('tbody').css('opacity','1');
        checkExecute(); 
    });

    geoiptable.on( 'row-reorder', function ( e, diff, edit ) {

       if(diff.length==0) { return; }

        $('#geoiptable').find('tbody').css('opacity','.5');

        $.ajax({
            url: 'index.php',
            type: 'POST',
            data: { menu: "sec_rules", action: "sort", geoip: "1", rawmode: "yes", json: JSON.stringify(diff)},
            dataType: 'json'
        }).always(function(data) { 
             geoiptable.ajax.reload();
        });

    });
 
}

function checkExecute() {
    setTimeout(function(){ 
    $.ajax({
        url: 'index.php',
        type: 'POST',
        data: { menu: "sec_rules", action: "isexecute", rawmode: "yes"},
        dataType: 'json'
    }).always(function(data) {
        if(data.message=='yes') {
            $('.applyrules').hide();
        } else {
            if(!$(".activatefw").is(":visible")){
                $('.applyrules').show();
                $('#changes_pending').val('yes');
            } else {
                $('.applyrules').hide();
                $('#changes_pending').val('no');
            }
        }
    });
    }, 500);
}

function styleForm() {

     showElementByTraffic();
     showElementByProtocol();
     $("#id_traffic").chosen({"disable_search": true});
     $("#interface_in").chosen({"disable_search": true});
     $("#interface_out").chosen({"disable_search": true});
     $("#id_protocol").chosen({"disable_search": true});
     $("#target").chosen({"disable_search": true});
     $("#port_in").chosen({"disable_search": true});
     $("#port_out").chosen({"disable_search": true});
     $("#type_icmp").chosen({"disable_search": true});
     $("#id_ip").chosen({"disable_search": true});
     $("SELECT[name='geoipcountries[]']").chosen({group_selectable:true});
     $("SELECT[name='geoipcontinents[]']").chosen({group_selectable:true});
     $('.chosen-container').css({"width": "200px"});
     $('#geoipcontinents___chosen').css({"width": "300px"});
     $('#geoipcontinents___chosen').find('.chosen-drop').css({"width": "300px"});
     $('#geoipcontinents___chosen').find('.chosen-choices > li > input').css('width','300px');
     $('#geoipcountries___chosen').css({"width": "300px"});
     $('#geoipcountries___chosen').find('.chosen-drop').css({"width": "300px"});
     $('#geoipcountries___chosen').find('.chosen-choices > li > input').css('width','300px');

     mainContentWidth = $('.main-content').css('width');
     $('#editRule').modal('show').css('top',$(window).scrollTop());
}

$(document).on('submit','form', function(e) {
    e.preventDefault();
    ele = $(e.target);
    var url = "index.php?menu=sec_rules&rawmode=yes&save=submit";
    $.ajax({
         type: "POST",
         url: url,
         data: ele.serialize()+"&save=submit", 
         dataType: 'json',
         success: function(data) {
              if(data.status=="error") {
                 toastr['error'](_tr(data.message),_tr(data.title)).css('top',$(window).scrollTop());
              } else {
                 toastr['success'](_tr(data.message),_tr(data.title)).css('top',$(window).scrollTop());
                 $('#editRule').modal('hide');
                 table.ajax.reload();
                 geoiptable.ajax.reload();
              }
         }
     });

});

function _tr(texto) {
   if(typeof(lang[texto])=='undefined') {
       return texto;
   } else {
       return lang[texto];
   }
}
