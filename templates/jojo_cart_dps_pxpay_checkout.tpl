<form name="paymentform" method="post" action="{$SECUREURL}/{$languageurlprefix}cart/process/{$token}/">
    <h2>##Pay by credit card##</h2>
    <input type="hidden" name="token" id="token" value="{$token}" />
    <input type="hidden" name="handler" value="dps_pxpay" />
    <p>##Click the button below to be redirected to our secure payment provider.##</p>
    <div style="text-align: center;">
        <input type="submit"  class="btn btn-primary" name="pay" id="pay" value="##Pay by Credit card##" onclick="if (true){ldelim}$('#pay').attr('disabled',true);paymentform.submit();{rdelim}else{ldelim}return false;{rdelim}" />
    </div>
</form>
