<? 
class PluginUlogin_HookUloginWidget extends Hook {   
        public function RegisterHook() {
                $this->AddHook('template_form_login_popup_begin','UloginWidget',__CLASS__,1);
		$this->AddHook('template_form_login_begin','UloginWidget',__CLASS__,1);
		$this->AddHook('template_form_registration_begin','UloginWidget',__CLASS__,1);
        }
        
        public function UloginWidget($aVars) {
		$return_url = Config::Get('path.root.web') . '/ulogin/'; 		
		
		$this->Viewer_Assign('return_url',$return_url);
		echo $this->Viewer_Fetch(Plugin::GetTemplatePath(__CLASS__)."ulogin_widget.tpl");
        }
}
?>
