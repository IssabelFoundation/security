
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

    <tr class="letra12" id="name">
        <td align="left"><b>{$name.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        <td align="left">{$name.INPUT}</td>
    </tr>
    <tr {$type_style} class="letra12">
        <td align="left"><b>{$maxretry.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        <td align="left">{$maxretry.INPUT}</td>
    </tr>
    <tr {$type_style} class="letra12">
        <td align="left"><b>{$bantime.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        <td align="left">{$bantime.INPUT}</td>
    </tr>
    <tr {$type_style} class="letra12" >
        <td align="left"><b>{$ignoreip.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        <td align="left">{$ignoreip.INPUT}</td>
    </tr>
    <tr {$type_style} class="letra12">
        <td align="left"><b>{$enabled.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        <td align="left">{$enabled.INPUT}</td>
    </tr>
</table>
<input type="hidden" name="mode" value="{$MODE}">
<input type="hidden" name="idtemp" value="{$IDTEMP}">

