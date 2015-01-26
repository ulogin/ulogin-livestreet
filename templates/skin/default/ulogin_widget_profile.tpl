<div class="ulogin_profile">

    <h3>{$aLang.plugin.ulogin.ulogin_profile_title}</h3>

    <dl class="form-item">
        <dt><label>{$aLang.plugin.ulogin.add_account}:</label></dt>
        <dd>
            {include file="$sUloginWidgetPath" sUloginid=$sUloginidProfile}
            <small class="note">{$aLang.plugin.ulogin.add_account_explain}</small>
        </dd>

        <div class="delete_accounts" {if !$aNetworks}style="display: none;"{/if}>
            <dt><label>{$aLang.plugin.ulogin.delete_account}:</label></dt>
            <dd>
                {include file="$sUloginAccountsPath"}
                <small class="note">{$aLang.plugin.ulogin.delete_account_explain}</small>
            </dd>
        </div>
    </dl>

</div>