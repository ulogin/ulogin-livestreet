{include file='header.light.tpl'}

	<div class="lite-center" width=100%>

		<form action="{router page='loginza/one_click_registration'}" method="POST">
				<label for="login">{$aLang.registration_login}:</label><br />
				<p><input type="text" class="input-text" name="login" id="login" value="{if $_aRequest.login}{$_aRequest.login}{else}{$user_info.nickname}{/if}"/>
				<span class="input-note">{$aLang.registration_login_notice}</span></p>
				
				<label for="email">{$aLang.registration_mail}:</label><br />
				<p><input type="text" class="input-text" id="email" name="mail" value="{if $_aRequest.mail}{$_aRequest.mail}{else}{$user_info.email}{/if}"/>
				<span class="input-note">{$aLang.registration_mail_notice}</span></p>

				<div class="lite-note">
					<button type="submit" class="button"><span><em>{$aLang.ulogin_next}</em></span></button>
				</div>
				<input type="hidden" name="submit_login">
		</form>

	</div>

{include file='footer.light.tpl'}
