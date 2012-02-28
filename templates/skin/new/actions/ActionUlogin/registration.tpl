{include file='header.light.tpl'}
<div style="width: 100%; margin-left: 15%;">
	<div style="float: left;" class="lite-center">
	
		{if $bLoginError}
			<p class="system-messages-error">{$aLang.user_login_bad}</p>
		{/if}

		<form action="{router page='ulogin/login'}" method="POST">
				<h3>{$aLang.ulogin_already_a_member}</h3>
				<div class="lite-note"><a href="{router page='registration'}">{$aLang.user_registration}</a><label for="login-input">{$aLang.user_login}</label></div>
				<p><input type="text" class="input-text" name="bind_login" tabindex="1" id="login-input"/></p>
				<div class="lite-note"><a href="{router page='login'}reminder/" tabindex="-1">{$aLang.user_password_reminder}</a><label for="password-input">{$aLang.user_password}</label></div>
				<p><input type="password" name="bind_password" class="input-text" tabindex="2" id="password-input"/></p>
				{hook run='form_login_end'}
				<div class="lite-note">
					<button type="submit" class="button"><span><em>{$aLang.user_login_submit}</em></span></button>
					<label for="" class="input-checkbox"><input type="checkbox" name="bind_remember" {if $_aRequest.bind_remember}checked{/if} tabindex="3" >{$aLang.user_login_remember}</label>
				</div>
				<input type="hidden" name="submit_bind">
		</form>
		
		{if $oConfig->GetValue('general.reg.invite')} 	
			<br><br>		
			<form action="{router page='registration'}invite/" method="POST">
				<h3>{$aLang.registration_invite}</h3>
				<div class="lite-note"><label for="invite_code">{$aLang.registration_invite_code}:</label></div>
				<p><input type="text" class="input-text" name="invite_code" id="invite_code"/></p>				
				<input type="submit" name="submit_invite" value="{$aLang.registration_invite_check}">
			</form>
		{/if}
	</div>
	<div class="lite-center register">
		<form action="{router page='ulogin/registration'}" method="POST">
			<h3>{$aLang.ulogin_new_user}</h3>
			<label for="login">{$aLang.registration_login}:</label><br />
			<p><input type="text" class="input-text" name="login" id="login" value="{if $_aRequest.login}{$_aRequest.login}{else}{$user_info.nickname}{/if}"/>
			<span class="input-note">{$aLang.registration_login_notice}</span></p>
			
			<label for="email">{$aLang.registration_mail}:</label><br />
			<p><input type="text" class="input-text" id="email" name="mail" value="{if $_aRequest.mail}{$_aRequest.mail}{else}{$user_info.email}{/if}"/>
			<span class="input-note">{$aLang.registration_mail_notice}</span></p>
			
			<label for="pass">{$aLang.registration_password}:</label><br />
			<p><input type="password" class="input-text" id="pass" value="" name="password"/><br />
			<span class="input-note">{$aLang.registration_password_notice}</span></p>
			
			<label for="repass">{$aLang.registration_password_retry}:</label><br />
			<p><input type="password" class="input-text"  value="" id="repass" name="password_confirm"/></p>
			
			{hook run='form_registration_end'}
			<div class="lite-note">
				<button type="submit" name="submit_register" class="button" style="float: none;"><span><em>{$aLang.registration_submit}</em></span></button>
			</div>		
		</form>
	</div>
	<br><br><br>
</div>
{include file='footer.light.tpl'}
