<?php
/*---------------------------------------------------------------------------------------
 *	author: helper [helper2424@gmail.com]
 *	plugin: Forum
 *	author site: http://x01d.com/
 *	license: CC BY-SA 3.0, http://creativecommons.org/licenses/by-sa/3.0/ *--------------------------------------------------------------------------------------*/
 
$config=array();

$config['one_click_registration'] = true;

/**
 * Настройки роутера
 */
Config::Set('router.page.ulogin', 'PluginUlogin_ActionUlogin');

return $config;
?>
