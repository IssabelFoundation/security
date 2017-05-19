<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
        <td align="left"><input class="button" type="submit" name="save" value="{$SAVE}">&nbsp; <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<br />
<div class="tabForm" style="font-size: 16px; height: 110px" width="100%">
    <table style="font-size: 16px;" width="100%" cellspacing="0" cellpadding="8">
            <!--*****************************************-->
            <tr class="letra12">
                <td align="left" width="11%"><b>{$Extension.LABEL}: </b></td>
                <td align="left">{$Extension.INPUT}</td>
            </tr>
            <tr style = '{$DISPLAY}' class="letra12">
                <td align="left"><b>{$Current_Secret.LABEL}: <span  class="required">*</span></b></td>
                <td align="left">{$Current_Secret.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left"><b>{$New_Secret.LABEL}: <span  class="required">*</span></b></td>
                <td align="left">{$New_Secret.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left"><b>{$Confirm_New_Secret.LABEL}: <span  class="required">*</span></b></td>
                <td align="left">{$Confirm_New_Secret.INPUT}</td>
            </tr>
    </table>
</div>