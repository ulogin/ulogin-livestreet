<?php
/*---------------------------------------------------------------------------------------
 *	author: uLogin Team, [team@ulogin.ru]
 *	plugin: uLogin
 *	author site: https://ulogin.ru/
 *	license: GNU General Public License, version 2
 * --------------------------------------------------------------------------------------*/

$config['table']['ulogin']            = '___db.table.prefix___ulogin';
$config['table']['ulogin_settings']   = '___db.table.prefix___ulogin_settings';

Config::Set('router.page.ulogin', 'PluginUlogin_ActionUlogin');

return $config;
?>