{if $NO_HOSTNAME_NOTICE ne ''}
<br/>
<div class="alert alert-danger">
{$NO_HOSTNAME_NOTICE}
</div>

{else}

<div class="box-header well span5">

{if $valueemail eq ''}
    <h3>{$INSTALLNEW}</h3>
{else}
       <div id="varstat" class="alert alert-success" align="center">
           <strong>{$HASDATA}</strong>
       </div>
{/if}

<div class='row'>
<div class='col-md-6'>
    <label for="domain">{$DOMAIN}</label>
</div>
<div class='col-md-6'>
    <input type="text" placeholder="your.domain.com" id="domain" name="domain" class="text ui-widget-content ui-corner-all form-control col-md-6" value="{$valuedomain}"/>
</div>
</div>
<br/>
<div class='row'>
<div class='col-md-6'>
    <label for="email">{$EMAIL}</label>
</div>
<div class='col-md-6'>
<input type="text" placeholder="myemail@mycompany.com" id="email" name="email" class="text ui-widget-content ui-corner-all form-control col-md-6" value="{$valueemail}"/>
</div>
</div>
{if $valueemail eq ''}
<br/>
<div class='row'>
<div class='col-md-6'>
    <label for="staging">{$STAGING}</label>
</div>
<div class='col-md-6'>
<input type="checkbox" name="staging" value="--test-cert" id="staging" />
</div>
</div>
{/if}
<br/>

{if $valueemail eq ''}
    <input name="btninstall" type="submit" id="btninstall" value="{$INSTALL}" class="btn btn-primary" />
{/if}

</div>

<div id="loading1"></div>
<div id="output"></div>

{if $valueemail ne ''}
    <div class="box-header well span5" data-original-title>
        <h3>{$RENEWCERT}</h3>
        <input name="btnrenew" id="btnrenew" type="submit" value="{$RENEW}" class="btn btn-primary" />
    </div>
{/if}

<div class="box-header well span5" data-original-title>
    <h3>{$USAGE}:</h3>
        <ul class="list-group">
            <li class="list-group-item list-group-item-danger">{$STEP1}</li>
            <li class="list-group-item list-group-item-danger">{$STEP2}</li>
            <li class="list-group-item list-group-item-danger">{$STEP3}</li>
            <li class="list-group-item list-group-item-danger">{$STEP4}</li>
            <li class="list-group-item list-group-item-danger">{$STEP5}</li>
            <li class="list-group-item list-group-item-success">{$STEP6}</li>
            <li class="list-group-item list-group-item-warning">{$STEP7}</li>
            <li class="list-group-item list-group-item-success">{$STEP8}</li>
            <li class="list-group-item list-group-item-success">{$STEP9}</li>
            <li class="list-group-item list-group-item-success">{$STEP10}</li>
            <li class="list-group-item list-group-item-warning">{$STEP11}</li>
        </ul>
</div>
{/if}
