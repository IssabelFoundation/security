<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
        {if $mode eq 'input'}
        <td align="left">
            <input class="button" type="submit" name="update_advanced_security_settings" value="{$SAVE}">&nbsp;&nbsp;
        </td>
        {/if}
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr>
	<td  width="50%" valign='top'>
	    <table>
		<tr class="letra12">
		    <td align="left"><b class='form-label-style'>{$subtittle1}</b></td>
		</tr>
		<tr class="letra12">
		    <td align="left" >
                        <b>{$status_ipbx_frontend.LABEL}:</b>
                    </td>
		    <td align="left" ><input type="hidden" name="oldstatus_ipbx_frontend" id="oldstatus_ipbx_frontend" value="{if $value_ipbx_frontend}1{else}0{/if}" /><input type="checkbox" name="status_ipbx_frontend" id="status_ipbx_frontend" {if $value_ipbx_frontend}checked="checked"{/if} /></td>
		</tr>
        <tr class="letra12">
            <td align="left" ><b>{$status_anonymous_sip.LABEL}:</b></td>
            <td align="left" ><input type="hidden" name="oldstatus_anonymous_sip" id="oldstatus_anonymous_sip" value="{if $value_anonymous_sip}1{else}0{/if}" /><input type="checkbox" name="status_anonymous_sip" id="status_anonymous_sip" {if $value_anonymous_sip}checked="checked"{/if} /></td>
        </tr>
	    </table>
	</td>
	<td width="50%" valign='top'>
	    <table>
		<tr class="letra12">
		    <td align="left"><b class='form-label-style'>{$subtittle2}</b></td>
		</tr>
		<tr class="letra12">
		    <td align="left" >
                        <b>{$ipbx_password.LABEL}:</b>                        
                    </td>
		    <td align="left" >{$ipbx_password.INPUT}</td>
		</tr>
		<tr class="letra12">
		    <td align="left" ><b>{$ipbx_confirm_passwrod.LABEL}:</b></td>
		    <td align="left" >{$ipbx_confirm_passwrod.INPUT}</td>
		</tr>
	    </table>
	</td>
    </tr>
</table>
