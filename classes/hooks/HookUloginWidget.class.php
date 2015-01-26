<?
/*---------------------------------------------------------------------------------------
 *	author: uLogin Team, [team@ulogin.ru]
 *	plugin: uLogin
 *	author site: https://ulogin.ru/
 *	license: GNU General Public License, version 2
 * --------------------------------------------------------------------------------------*/

class PluginUlogin_HookUloginWidget extends Hook {
    private $settings;

    public function RegisterHook() {
        $this->AddHook( 'template_form_login_popup_begin', 'UloginWidget', __CLASS__, 1 );
        $this->AddHook( 'template_form_login_begin', 'UloginWidget', __CLASS__, 1 );
        $this->AddHook( 'template_form_registration_begin', 'UloginWidget', __CLASS__, 1 );

        $this->AddHook( 'template_userbar_item', 'UloginWidgetHeader', __CLASS__, 1 );

//        $this->AddHook( 'template_form_settings_profile_end', 'UloginUserProfile', __CLASS__, 1 );
        $this->AddHook( 'template_form_settings_account_begin', 'UloginUserProfile', __CLASS__, 1 );

        $this->AddHook( 'template_content_begin','UloginMessageBox',__CLASS__, 1 );
    }


    public function UloginWidget($aVars) {
        if (!$this->User_IsAuthorization()) {
            $this->GetUloginParams();
            echo $this->Viewer_Fetch( Plugin::GetTemplatePath( __CLASS__ ) . "ulogin_widget.tpl" );
        }
    }

    public function UloginWidgetHeader($aVars) {
        if (!$this->User_IsAuthorization()) {
            $uloginid2 = $this->settings['uloginid2'];
            $this->Viewer_Assign( 'sUloginid2', $uloginid2 );
            $this->GetUloginParams();
            echo $this->Viewer_Fetch( Plugin::GetTemplatePath( __CLASS__ ) . "ulogin_widget_header.tpl" );
        }
    }

    public function UloginUserProfile($aVars) {
        if (!$this->User_IsAuthorization()) {
            return;
        }
        $user_id = $this->User_GetUserCurrent()->getId();

        $this->GetUloginParams();
        $uloginid_profile = $this->settings['uloginid_profile'];

        $networks = $this->PluginUlogin_Ulogin_getUloginUserNetworks($user_id);

        $this->Viewer_Assign('sUloginidProfile', $uloginid_profile);
        $this->Viewer_Assign('aNetworks', $networks);
        echo $this->Viewer_Fetch(Plugin::GetTemplatePath(__CLASS__)."ulogin_widget_profile.tpl");
    }


    public function UloginMessageBox($aVars) {
        echo $this->Viewer_Fetch(Plugin::GetTemplatePath(__CLASS__)."ulogin_message_box.tpl");
    }


    private function GetUloginParams() {
        $this->settings = $this->PluginUlogin_Ulogin_getSettings();

        $redirect_uri = Config::Get('path.root.web') . '/ulogin/login';
        $redirect_uri .= '?backurl=' . urlencode($this->getUrl());
        $callback = 'uloginCallback';
        $uloginid = $this->settings['uloginid1'];

        $data_ulogin = "redirect_uri=". urlencode($redirect_uri) .";callback=$callback;";
        $data_ulogin_def = 'display=small;fields=first_name,last_name,email;providers=vkontakte,odnoklassniki,mailru,facebook;hidden=other;' . $data_ulogin;

        $this->Viewer_Assign('sUloginid', $uloginid);
        $this->Viewer_Assign('sDataUlogin', $data_ulogin);
        $this->Viewer_Assign('sDataUloginDef', $data_ulogin_def);
    }


    private function getUrl() {
        $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
        $url .= ( $_SERVER["SERVER_PORT"] != 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
        $url .= $_SERVER["REQUEST_URI"];
        return $url;
    }
}
