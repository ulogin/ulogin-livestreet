<?php
/*---------------------------------------------------------------------------------------
 *	author: uLogin Team, [team@ulogin.ru]
 *	plugin: uLogin
 *	author site: https://ulogin.ru/
 *	license: GNU General Public License, version 2
 * --------------------------------------------------------------------------------------*/

if (!class_exists('Plugin')) {
	die('Hacking attemp!');
}

class PluginUlogin extends Plugin {
	/**
	 * Активация плагина
	 */
	public function Activate() {
		if (!$this->isTableExists('prefix_ulogin') || !$this->isTableExists('prefix_ulogin_settings')) {
			$this->ExportSQL(dirname(__FILE__).'/sql/install.sql');
		}

		if (!$this->isFieldExists('prefix_ulogin', 'ulogin_network')) {
			$this->PluginUlogin_Ulogin_addUloginNetworkColumn();
//			$this->ExportSQL(dirname(__FILE__).'/sql/add_ulogin_network_column.sql');
		}

		$this->PluginUlogin_Ulogin_fillUloginNetworkData();

		return true;
	}

	/**
	 * Деактивация плагина
	 */
	public function Deactivate() {
		$this->ExportSQL(dirname(__FILE__).'/sql/deinstall.sql');
		return true;
	}

	/**
	 * Инициализация плагина
	 */
	public function Init() {
		$this->Viewer_AppendStyle(Plugin::GetTemplateWebPath(__CLASS__) . 'css/ulogin.css');
		$this->Viewer_AppendStyle('//ulogin.ru/css/providers.css');
		$this->Viewer_AppendScript('//ulogin.ru/js/ulogin.js');
		$this->Viewer_AppendScript(Plugin::GetTemplateWebPath(__CLASS__) . 'js/ulogin.js');
		$this->Viewer_Assign("sUloginWidgetPath", Plugin::GetTemplatePath(__CLASS__) . 'ulogin_widget.tpl');
		$this->Viewer_Assign("sUloginAccountsPath", Plugin::GetTemplatePath(__CLASS__) . 'ulogin_accounts.tpl');
		return true;
	}

}

?>
