{assign var="noSidebar" value=true}
{include file='header.tpl'}

<div class="ulogin-admin">

	<h2 class="page-header">{$aLang.plugin.ulogin.admin_ulogin_title}</h2>

	{$aLang.plugin.ulogin.admin_ulogin_title_explain}

	<form action="{router page='ulogin/admin'}" method="POST" class="wrapper-content">
		<p>
			<label for="uloginid1"><b>{$aLang.plugin.ulogin.admin_uloginid1}:</b></label>
			<input type="text" name="uloginid1" id="uloginid1" value="{$uloginid1}" maxlength="8" />
			<span class="note">{$aLang.plugin.ulogin.admin_uloginid1_explain}</span>
		</p>
		<p>
			<label for="uloginid2"><b>{$aLang.plugin.ulogin.admin_uloginid2}:</b></label>
			<input type="text" name="uloginid2" id="uloginid2" value="{$uloginid2}" maxlength="8" />
			<span class="note">{$aLang.plugin.ulogin.admin_uloginid2_explain}</span>
		</p>
		<p>
			<label for="uloginid_profile"><b>{$aLang.plugin.ulogin.admin_uloginid_profile}:</b></label>
			<input type="text" name="uloginid_profile" id="uloginid_profile" value="{$uloginid_profile}"  maxlength="8"/>
			<span class="note">{$aLang.plugin.ulogin.admin_uloginid_profile_explain}</span>
		</p>

		<input type="hidden" name="admin_submit" value="1">
		<input type="submit" class="button button-primary" value="{$aLang.plugin.ulogin.ulogin_save}" />
	</form>

</div>
{include file='footer.tpl'}
