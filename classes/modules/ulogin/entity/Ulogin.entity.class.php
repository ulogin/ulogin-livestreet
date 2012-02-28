<?php
/*---------------------------------------------------------------------------------------
 *	author: helper2424@gmail.com
 *	plugin: uLogin
 *	author site: http://x01d.com/
 *	license: CC BY-SA 3.0, http://creativecommons.org/licenses/by-sa/3.0/
 *--------------------------------------------------------------------------------------*/

class PluginUlogin_ModuleUlogin_EntityUlogin extends EntityORM {
	protected $aRelations = array(
		'user_id'=>array('belongs_to','ModuleUser_EntityUser','user_id')
	);
}
?>
