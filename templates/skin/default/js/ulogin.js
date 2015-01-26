if ( (typeof jQuery === 'undefined') && !window.jQuery ) {
    document.write(unescape("%3Cscript type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js'%3E%3C/script%3E%3Cscript type='text/javascript'%3EjQuery.noConflict();%3C/script%3E"));
} else {
    if((typeof jQuery === 'undefined') && window.jQuery) {
        jQuery = window.jQuery;
    } else if((typeof jQuery !== 'undefined') && !window.jQuery) {
        window.jQuery = jQuery;
    }
}

function uloginCallback(token){
    jQuery.ajax({
        url: '/ulogin/login',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {token: token},
        success: function (data) {
            switch (data.type) {
                case 'error':
                    uloginMessage(data.title, data.msg, data.type);
                    break;
                case 'success':
                    if (jQuery('.ulogin_profile').length > 0){
                        adduLoginNetworkBlock(data.networks, data.title, data.msg);
                    } else {
                        location.reload();
                    }
                    break;
            }

            if (data.script) {
                var token = data.script['token'],
                    identity = data.script['identity'];
                if  (token && identity) {
                    uLogin.mergeAccounts(token, identity);
                } else if (token) {
                    uLogin.mergeAccounts(token);
                }
            }
        }
    });
}

function uloginMessage(title, msg, type) {
    if (title == '' && msg == '') { return; }
    var ulogin_messages_box = jQuery('#ulogin-message-box');
    if (ulogin_messages_box.length == 0) { return; }

    var mess = (title != '') ? '<strong>' + title + '</strong><br>' : '';
    mess += (msg != '') ? msg : '';
    mess = '<li>' + mess + '</li>';

    var class_msg = 'system-message-';
    if (type == 'error') {
        class_msg += 'error';
    } else {
        class_msg += 'notice';
    }

    ulogin_messages_box.removeClass('system-message-error system-message-notice').addClass(class_msg).html(mess).show();

    setTimeout(function () {
        ulogin_messages_box.hide();
    }, 5000);
}

function uloginDeleteAccount(network){
    jQuery.ajax({
        url: '/ulogin/deleteaccount',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {network: network},
        error: function (data, textStatus, errorThrown) {
            alert('Не удалось выполнить запрос');
        },
        success: function (data) {
            switch (data.type) {
                case 'error':
                    uloginMessage(data.title, data.msg, 'error');
                    break;
                case 'success':
                    var accounts = jQuery('.ulogin_accounts'),
                        nw = accounts.find('[data-ulogin-network='+network+']');
                    if (nw.length > 0) nw.hide();

                    if (accounts.find('.ulogin_provider:visible').length == 0) {
                        var delete_accounts = jQuery('.ulogin_profile').find('.delete_accounts');
                        if (delete_accounts.length > 0) delete_accounts.hide();
                    }

                    uloginMessage(data.title, data.msg, 'success');
                    break;
            }
        }
    });
}


function adduLoginNetworkBlock(networks, title, msg) {
    var uAccounts = jQuery('.ulogin_accounts');

    uAccounts.each(function(){
        for (var uid in networks) {
            var network = networks[uid],
                uNetwork = jQuery(this).find('[data-ulogin-network='+network+']');

            if (uNetwork.length == 0) {
                var onclick = '';
                if (jQuery(this).hasClass('can_delete')) {
                    onclick = ' onclick="uloginDeleteAccount(\'' + network + '\')"';
                }
                jQuery(this).append(
                    '<div data-ulogin-network="' + network + '" class="ulogin_provider big_provider ' + network + '_big"' + onclick + '></div>'
                );
                uloginMessage(title, msg, 'success');
            } else {
                if (uNetwork.is(':hidden')) {
                    uloginMessage(title, msg, 'success');
                }
                uNetwork.show();
            }
        }

        var delete_accounts = jQuery('.ulogin_profile').find('.delete_accounts');
        if (delete_accounts.length > 0) delete_accounts.show();

    });
}