<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
        <td align="left">
            <input class="button" type="submit" name="save" value="{$SAVE}">&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr class="letra12" id="ip_address">
        <td align="left" width="150px"><b>{$ip_address.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        <td align="left">{$ip_address.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$note.LABEL}: </b></td>
        <td align="left">{$note.INPUT}</td>
    </tr>
</table>
<input type="hidden" name="mode" value="{$MODE}">
<input type="hidden" name="idtemp" value="{$IDTEMP}">
