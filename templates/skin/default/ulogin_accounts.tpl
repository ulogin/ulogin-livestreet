<div class="ulogin_accounts can_delete">
    {if $aNetworks}
        {foreach from=$aNetworks item=network}
            <div data-ulogin-network='{$network}'
                 class="ulogin_provider big_provider {$network}_big"
                 onclick="uloginDeleteAccount('{$network}')"></div>
        {/foreach}
    {/if}
</div><div style="clear:both"></div>