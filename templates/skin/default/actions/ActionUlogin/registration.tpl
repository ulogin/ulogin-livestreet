{include file='header.light.tpl'}
<div style="width: 100%; margin-left: 15%;">
	<div style="float: left;" class="center">

		{if $bLoginError}
			<p class="system-messages-error">{$aLang.user_login_bad}</p>
		{/if}

		<form action="{router page='ulogin/bind'}" method="POST">
			<h3>{$aLang.ulogin_already_a_member}</h3>

			<p>
				<label for="login-input">{$aLang.user_login}</label><br/>
				<input type="text" class="input-text input-wide" name="bind_login" value="{$_aRequest.bind_login}" tabindex="1" id="login-input"/>
			</p>

			<p>
				<label for="password-input">{$aLang.user_password}</label><br/>
				<input type="password" name="bind_password" class="input-text input-wide value="" tabindex="2" id="password-input"/>
			</p>
				
			{hook run='form_login_end'}
	
			<p>
				<input type="submit" class="button" value="{$aLang.user_login_submit}" />
				<label for="" class="input-checkbox"><input type="checkbox" name="bind_remember" {if $_aRequest.bind_remember}checked{/if} tabindex="3" >{$aLang.user_login_remember}</label>
			<p>

			<input type="hidden" name="submit_bind">
		</form>

		<p>
			<a href="{router page='registration'}">{$aLang.user_registration}</a><br/>
			<a href="{router page='login'}reminder/" tabindex="-1">{$aLang.user_password_reminder}</a>
		</p>
		
	</div>

	<div class="center register">
		<form action="{router page='ulogin/registration'}" method="POST">
			<h3>{$aLang.ulogin_new_user}</h3>
			
			<p>
				<label for="login">{$aLang.registration_login}:</label><br />
				<input type="text" class="input-text input-wide" name="login" id="login" value="{if $_aRequest.login}{$_aRequest.login}{else}{$user_info.nickname}{/if}"/><br />
				<span class="note">{$aLang.registration_login_notice}</span>
			</p>
			
			<p>
				<label for="email">{$aLang.registration_mail}:</label><br />
				<input type="text" class="input-text input-wide" id="email" name="mail" value="{if $_aRequest.mail}{$_aRequest.mail}{else}{$user_info.email}{/if}"/><br />
				<span class="note">{$aLang.registration_mail_notice}</span>
			</p>
			
			<p>
				<label for="pass">{$aLang.registration_password}:</label><br />
				<input type="password" class="input-text input-wide" id="pass" value="" name="password"/><br />
				<span class="note">{$aLang.registration_password_notice}</span>
			</p>
			
			<p>
				<label for="repass">{$aLang.registration_password_retry}:</label><br />
				<input type="password" class="input-text input-wide"  value="" id="repass" name="password_confirm"/>
			</p>
			
			{hook run='form_registration_end'}

				<input type="submit" name="submit_register" class="button" style="float: none;" value="{$aLang.registration_submit}" />
			</div>		
		</form>
	</div>
	<br><br><br>
</div>
{include file='footer.light.tpl'}
