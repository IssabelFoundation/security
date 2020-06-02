<input type='hidden' id='firewall_desactivado' value='{$FIRSTTIME}'>
<input type='hidden' id='changes_pending' value='{$EXECUTED}'>
<input type='hidden' id='hasgeoip' value='{$HASGEOIP}'>

<script>
var lang={};
{foreach key=key item=item from=$LANG}
    lang["{$key}"]="{$item}";
{/foreach}
</script>

<table id='geoiptable' class='table' width="100%" >
  <thead>
    <tr>
      <th><input type='checkbox' name='selectallgeo' id='selectallgeo' /></th>
      <th></th>
      <th>{$LANG.Traffic}</th>
      <th>{$LANG.Target}</th>
      <th>{$LANG.Interface}</th>
      <th>{$LANG['IP Source']}</th>
      <th>{$LANG['IP Destiny']}</th>
      <th>{$LANG.Protocol}</th>
      <th>{$LANG.Details}</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
  </tr>
  </thead>
  <tbody>
    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
  </tbody>
</table>

<table id='rulestable' class='table' width="100%" >
  <thead>
    <tr>
      <th><input type='checkbox' name='selectall' id='selectall' /></th>
      <th></th>
      <th>{$LANG.Traffic}</th>
      <th>{$LANG.Target}</th>
      <th>{$LANG.Interface}</th>
      <th>{$LANG['IP Source']}</th>
      <th>{$LANG['IP Destiny']}</th>
      <th>{$LANG.Protocol}</th>
      <th>{$LANG.Details}</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
  </tr>
  </thead>
  <tbody>
    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
  </tbody>
</table>

<div class="modal fade" id="editRule">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" >
      <div class="modal-body" id='ruleedit'>
      </div>
    </div>
  </div>
</div>
