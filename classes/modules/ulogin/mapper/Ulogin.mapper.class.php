<?php
/*---------------------------------------------------------------------------------------
 *	author: uLogin Team, [team@ulogin.ru]
 *	plugin: uLogin
 *	author site: https://ulogin.ru/
 *	license: GNU General Public License, version 2
 * --------------------------------------------------------------------------------------*/

class PluginUlogin_ModuleUlogin_MapperUlogin extends Mapper {

//--------------------
	/**
	 * Проверка, есть ли пользователь с указанным id в базе
	 * @param $u_id
	 * @return bool
	 */
	public function checkUserId ($u_id) {
		$sql = "SELECT ulogin_userid
				FROM " . Config::Get('plugin.ulogin.table.ulogin') . "
				WHERE ulogin_userid = ?d";

		if ($aRow=$this->oDb->selectRow($sql, $u_id)) {
			return true;
		}
		return false;
	}

//--------------------
	/**
	 * Проверка, есть ли пользователь с указанным username в базе
	 * @param string $username
	 * @return bool
	 */
	public function checkUserName ($username = '') {
		$sql = "SELECT user_id
				FROM " . Config::Get('db.table.user') . "
				WHERE user_login LIKE ?";

		if ($aRow=$this->oDb->selectRow($sql, $username)) {
			return true;
		}
		return false;
	}


//--------------------
	/**
	 * Получение id пользователя по email
	 * @param string $email
	 * @return int|bool
	 */
	public function getUserIdByEmail ($email = '') {
		$sql = "SELECT user_id
				FROM " . Config::Get('db.table.user') . "
				WHERE user_mail LIKE ?";

		if ($aRow=$this->oDb->selectRow($sql, $email)) {
			return $aRow['user_id'];
		}
		return false;
	}


//--------------------
	/**
	 * Получение данных о пользователе из таблицы ulogin_users по identity или user_id
	 * @param $data
	 * @return bool|mixed
	 */
	public function getUloginUserItem ($data = array()) {
		$sql = "SELECT * FROM ". Config::Get('plugin.ulogin.table.ulogin') . " ";

		if (isset($data['identity'])) {
			$sql .= "WHERE ulogin_identity = ?";
			if ($aRow=$this->oDb->selectRow($sql, $data['identity'])) {
				return $aRow;
			}
		} else if (isset($data['user_id'])) {
			$sql .= "WHERE ulogin_userid = ?d";
			if ($aRow=$this->oDb->selectRow($sql, $data['user_id'])) {
				return $aRow;
			}
		}

		return false;
	}


//--------------------
	/**
	 * Получение массива соцсетей пользователя по значению поля $user_id
	 * @param int $user_id
	 * @return array|bool
	 */
	public function getUloginUserNetworks ($user_id = 0) {
		$sql = "SELECT ulogin_network
				FROM " . Config::Get('plugin.ulogin.table.ulogin') . "
				WHERE ulogin_userid = ?d";

		if (!$aRows=$this->oDb->select($sql, $user_id)) {
			return false;
		}

		foreach ($aRows as $row)
		{
			$networks[] = $row["ulogin_network"];
		}

		return $networks;
	}

	
//--------------------
	/**
	 * Удаление данных о пользователе из таблицы ulogin_user
	 * @param int $user_id
	 * @return bool
	 */
	public function deleteUloginAccount ($data = array()) {
		$sql = "DELETE FROM " . Config::Get("plugin.ulogin.table.ulogin") . " ";

		if (isset($data['id'])) {
			$sql .= "WHERE ulogin_id = ?d";
			return $this->oDb->query($sql, $data['id']);
		} else if (isset($data['user_id']) && isset($data['network'])) {
			$sql .= "WHERE ulogin_userid = ?d AND ulogin_network = ?";
			return $this->oDb->query($sql, $data['user_id'], $data['network']);
		}

		return false;
	}


//--------------------
	/**
	 * Добавление данных о пользователе в таблицы ulogin_user
	 * @param array $data
	 * @return bool
	 */
	public function addUloginAccount ($data = array()) {
		$sql = "SELECT ulogin_id FROM " . Config::Get("plugin.ulogin.table.ulogin") . "
		        WHERE ulogin_identity = ?";

		if ($this->oDb->query($sql, $data['identity'])) {
			return false;
		}

		$sql = "INSERT INTO " . Config::Get("plugin.ulogin.table.ulogin") . "
		        SET ulogin_userid = ?d,
		         ulogin_identity = ?,
		         ulogin_network = ?";

		if ($this->oDb->query($sql, $data['user_id'], $data['identity'], $data['network'])) {
			return true;
		}
		return false;
	}

//--------------------------------------------------
//--------------------------------------------------
	/**
	 * Добавление колонки 'ulogin_network' в таблицу ulogin
	 */
	public function addUloginNetworkColumn() {
		$sql = "ALTER TABLE `prefix_ulogin` ADD `ulogin_network` varchar(50) DEFAULT NULL;";

		if ($this->oDb->query($sql)) {
			return true;
		}
		return false;
	}


	/**
	 * Получение пустых строк из таблицы ulogin с пустым полем ulogin_network
	 */
	public function getEmptyUloginNetworks () {
		$sql = "SELECT ulogin_id, ulogin_identity, ulogin_network
				FROM prefix_ulogin
				WHERE ulogin_network IS NULL
				OR ulogin_network = ''";
		if ($aRows=$this->oDb->select($sql)) {
			return $aRows;
		}
		return false;
	}

	/**
	 * Заполнение значений ulogin_network
	 */
	public function fillUloginNetworks($data = array()) {
		if (empty($data)) return true;

		foreach ($data as $key=>$row) {
			$sql = "UPDATE prefix_ulogin SET ulogin_network = ? " .
			       "WHERE ulogin_id = ?d AND (ulogin_network IS NULL OR ulogin_network = '')" ;
			$this->oDb->query($sql, $row['ulogin_network'], $row['ulogin_id']);
		}

		return true;
	}


	public function getSettings () {
		$sql = "SELECT *
				FROM prefix_ulogin_settings";

		if (!$aRows=$this->oDb->select($sql)) {
			return false;
		}

		foreach ($aRows as $row) {
			$settings[$row['ulogin_id']] = $row['ulogin_value'];
		}

		return $settings;
	}


	public function setSettings ($settings) {
		if (empty($settings) || !is_array($settings)) return false;
		foreach ($settings as $key=>$s) {
			$sql = "UPDATE prefix_ulogin_settings SET ulogin_value = ? " .
			       "WHERE ulogin_id = ? " ;
			$this->oDb->query($sql, $s, $key);
		}
		return true;
	}
}
?>