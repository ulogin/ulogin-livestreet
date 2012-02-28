{include file='header.light.tpl'}

	<div class="center" width=100%>

		<form action="{router page='ulogin/one_click_registration'}" method="POST">
			
				<p>
					<label for="login">{$aLang.registration_login}:</label><br />
					<input type="text" class="input-text input-wide" name="login" id="login" value="{if $_aRequest.login}{$_aRequest.login}{else}{$user_info.nickname}{/if}"/><br/>
					<span class="note">{$aLang.registration_login_notice}</span>
				</p>
				
				<p>
					<label for="email">{$aLang.registration_mail}:</label><br />
					<input type="text" class="input-text input-wide" id="email" name="mail" value="{if $_aRequest.mail}{$_aRequest.mail}{else}{$user_info.email}{/if}"/><br/>
					<span class="note">{$aLang.registration_mail_notice}</span>
				</p>

				<input type="hidden" name="one_click_registration_submit" value="1">

				<input type="submit" class="button" value="{$aLang.ulogin_next}" />
		</form>

	</div>

{include file='footer.light.tpl'}
