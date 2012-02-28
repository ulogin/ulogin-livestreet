<?php
/*---------------------------------------------------------------------------------------
 *	author: helper2424@gmail.com
 *	plugin: uLogin
 *	author site: http://x01d.com/
 *	license: CC BY-SA 3.0, http://creativecommons.org/licenses/by-sa/3.0/
 *--------------------------------------------------------------------------------------*/

if (!class_exists('Plugin')) {
	die('Hacking attemp!');
}

class PluginUlogin extends Plugin {
	/**
	 * Активация плагина
	 */
	public function Activate() {
		$result = true;

		$data = $this->ExportSQL(dirname(__FILE__).'/sql/install.sql');

		if (!$data['result']) {
		    foreach ($data['errors'] as $err) {
			if ($err>'') $result = false;
		    }
		}

		if (!$result) $this->Message_AddErrorSingle('Cannot update database for this plugin', $this->Lang_Get('error'),true);

		return $result;
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
		return true;
	}

}

?>
