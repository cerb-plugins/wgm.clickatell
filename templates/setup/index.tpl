<h2>{'wgm.clickatell.common'|devblocks_translate}</h2>

<form action="javascript:;" method="post" id="frmSetupClickatell" onsubmit="return false;">
<input type="hidden" name="c" value="config">
<input type="hidden" name="a" value="handleSectionAction">
<input type="hidden" name="section" value="clickatell">
<input type="hidden" name="action" value="saveJson">
<input type="hidden" name="_csrf_token" value="{$session.csrf_token}">

<fieldset>
	<legend>API Credentials</legend>
	
	<b>API ID:</b><br>
	<input type="text" name="api_id" value="{$credentials.id}" size="24"><br>
	<br>
	
	<b>Username:</b><br>
	<input type="text" name="api_user" value="{$credentials.user}" size="24"><br>
	<br>
	
	<b>{'common.password'|devblocks_translate|capitalize}:</b><br>
	<input type="password" name="api_pass" value="{$credentials.pass}" size="24"><br>
	<br>

	<div class="status"></div>	

	<button type="button" class="submit"><span class="glyphicons glyphicons-circle-ok" style="color:rgb(0,180,0);"></span> {'common.save_changes'|devblocks_translate|capitalize}</button>	
</fieldset>

</form>

<script type="text/javascript">
$('#frmSetupClickatell BUTTON.submit')
	.click(function(e) {
		genericAjaxPost('frmSetupClickatell','',null,function(json) {
			$o = $.parseJSON(json);
			if(false == $o || false == $o.status) {
				Devblocks.showError('#frmSetupClickatell div.status',$o.error);
			} else {
				Devblocks.showSuccess('#frmSetupClickatell div.status',$o.message);
			}
		});
	})
;
</script>