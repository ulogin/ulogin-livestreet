{assign var="noSidebar" value=true}
{include file='header.tpl'}

<div class="ulogin-admin">
{literal}
<style>
.lite-note {color:gray;}
</style>
{/literal}

	<h1>{$aLang.ulogin_admin_title}</h1>
	<form action="{router page='ulogin/admin'}" method="POST">
		
			<div id="registration_type">
				<span class="lite-note">{$aLang.ulogin_choose_registration_type}</span>
				<p>
					<label for="registration_type"><b>{$aLang.ulogin_one_click_registration}:</b></label>
					<input type="radio" name="registration_type" {if $one_click_registration}checked{/if} value="one_click_registration"/>
					<span class="lite-note">{$aLang.ulogin_one_click_registration_notaion}</span>
				</p>
			
				<p>
					<label for="registration_type"><b>{$aLang.ulogin_full_registration}</b>:</label>
					<input type="radio" name="registration_type" {if not $one_click_registration}checked{/if} value="full_registration"/>
					<span class="lite-note">{$aLang.ulogin_full_registration_notaion}</span>
				</p>
			</div>

			<input type="hidden" name="admin_submit" value="1">

			<input type="submit" class="button" value="{$aLang.ulogin_save}" />
	</form>

</div>
{include file='footer.tpl'}
