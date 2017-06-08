
<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
        <td align="left">
        {if $MODE eq "new"}
            <input class="button" type="submit" name="save" value="{$SAVE}">&nbsp;
        {/if}
        {if $MODE eq "edit"}
            <input class="button" type="submit" name="save" value="{$SAVE}">&nbsp;
        {/if}
        {if $MODE eq "view"}
            <input class="button" type="submit" name="edit" value="{$EDIT}">&nbsp;
        {/if}
            <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
	{if $MODE ne "view"}
	    <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
	{/if}
    </tr>
</table>


<table class="tabForm" style="font-size: 16px;" width="100%" >
<!--
    <tr class="letra12" id="name">
        <td align="left" width="10%"><b>{$protocol.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        <td align="left">{$protocol.INPUT}</td>
    </tr>
 -->
    <tr class="letra12" id="name">
            <td align="left" width="10%"><b>Protocol: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
			<td align="left">
            <input type="checkbox" name="protocol" id="pro_sip" value="pro_sip" {$check_pro_sip} disabled="disabled"/>
            SIP &nbsp;&nbsp;&nbsp;
            <input type="checkbox" name="protocol" id="pro_iax2" value="pro_iax2" {$check_pro_iax2}  checked="checked" disabled="disabled"/>
            IAX2 &nbsp;&nbsp;&nbsp;
            <input type="checkbox" name="protocol" id="pro_https" value="pro_https" {$check_pro_https} disabled="disabled"/>
            HTTPS &nbsp;&nbsp;&nbsp;
            <input type="checkbox" name="protocol" id="pro_http" value="pro_http" {$check_pro_http}  checked="checked" disabled="disabled" />
            HTTP &nbsp;&nbsp;&nbsp;
            </td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$ip.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        <td align="left">{$ip.INPUT}</td>
    </tr>
    <tr {$port_style} class="letra12" id="port">
        <td align="left"><b>{$netmask.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        <td align="left">{$netmask.INPUT}</td>
    </tr>
    <tr {$type_style} class="letra12" id="type">
        <td align="left"><b>{$availability.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        <td align="left">{$availability.INPUT}</td>
    </tr>
</table>
<input type="hidden" name="mode" value="{$MODE}">
<input type="hidden" name="idtemp" value="{$IDTEMP}">

