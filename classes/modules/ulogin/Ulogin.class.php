<?php
/*---------------------------------------------------------------------------------------
 *	author: uLogin Team, [team@ulogin.ru]
 *	plugin: uLogin
 *	author site: https://ulogin.ru/
 *	license: GNU General Public License, version 2
 * --------------------------------------------------------------------------------------*/

class PluginUlogin_ModuleUlogin extends Module {
	protected $oMapper;

	/**
	 * Инициализация
	 */
	public function Init() {
		$this->oMapper=Engine::GetMapper(__CLASS__);
	}

	/**
	 * Проверка, есть ли пользователь с указанным id в базе
	 * @param $u_id
	 * @return bool
	 */
	public function checkUserId($u_id) {
		return $this->oMapper->checkUserId($u_id);
	}

	/**
	 * Проверка, есть ли пользователь с указанным username в базе
	 * @param string $username
	 * @return bool
	 */
	public function checkUserName ($username = '') {
		return $this->oMapper->checkUserName($username);
	}

	/**
	 * Получение id пользователя по email
	 * @param string $email
	 * @return int|bool
	 */
	public function getUserIdByEmail ($email = '') {
		return $this->oMapper->getUserIdByEmail($email);
	}

	/**
	 * Получение данных о пользователе из таблицы ulogin_users по identity или user_id
	 * @param $data
	 * @return bool|mixed
	 */
	public function getUloginUserItem ($data) {
		$result = $this->oMapper->getUloginUserItem($data);
		if ($result) {
			$result['id']       = $result['ulogin_id'];
			$result['user_id']  = $result['ulogin_userid'];
			$result['identity'] = $result['ulogin_identity'];
		}
		return $result;
	}

	/**
	 * Получение массива соцсетей пользователя по значению поля $user_id
	 * @param int $user_id
	 * @return bool
	 */
	public function getUloginUserNetworks ($user_id = 0) {
		if ($user_id > 0)
			return $this->oMapper->getUloginUserNetworks($user_id);
		else return false;
	}

	/**
	 * Удаление данных о пользователе из таблицы ulogin_user
	 * @param int $user_id
	 * @return bool
	 */
	public function deleteUloginAccount ($data = array()) {
		return $this->oMapper->deleteUloginAccount($data);
	}

	/**
	 * Добавление данных о пользователе в таблицы ulogin_user
	 * @param array $data
	 * @return bool
	 */
	public function addUloginAccount ($data = array()) {
		return $this->oMapper->addUloginAccount($data);
	}


//----------------------------------------------
	/**
	 * Добавление колонки 'ulogin_network' в таблицу ulogin
	 */
	public function addUloginNetworkColumn () {
		return $this->oMapper->addUloginNetworkColumn();
	}

	/**
	 * Заполнение данных network по identity
	 */
	public function fillUloginNetworkData () {
		$result = $this->oMapper->getEmptyUloginNetworks();

		if ($result) {
			foreach ($result as $key=>$row) {
				if (preg_match("/^https?:\/\/vk\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'vkontakte';
				} else if (preg_match("/^https?:\/\/odnoklassniki\.ru/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'odnoklassniki';
				} else if (preg_match("/^https?:\/\/login\.yandex\.ru/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'yandex';
				} else if (preg_match("/^https?:\/\/plus\.google\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'google';
				} else if (preg_match("/^https?:\/\/steamcommunity\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'steam';
				} else if (preg_match("/^https?:\/\/soundcloud\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'soundcloud';
				} else if (preg_match("/^https?:\/\/(www\.)?last\.fm/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'lastfm';
				} else if (preg_match("/^https?:\/\/(www\.)?linkedin\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'linkedin';
				} else if (preg_match("/^https?:\/\/(www\.)?facebook\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'facebook';
				} else if (preg_match("/^https?:\/\/my\.mail\.ru/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'mailru';
				} else if (preg_match("/^https?:\/\/twitter\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'twitter';
				} else if (preg_match("/^https?:\/\/profile\.live\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'liveid';
				} else if (preg_match("/^https?:\/\/(www\.)?flickr\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'flickr';
				} else if (preg_match("/^https?:\/\/vimeo\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'vimeo';
				} else if (preg_match("/^https?:\/\/(.*?)\.livejournal\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'livejournal';
				} else if (preg_match("/^https?:\/\/openid\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'openid';
				} else if (preg_match("/^https?:\/\/(.*?)\.wmkeeper\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'webmoney';
				} else if (preg_match("/^https?:\/\/gdata\.youtube\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'youtube';
				} else if (preg_match("/^https?:\/\/foursquare\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'foursquare';
				} else if (preg_match("/^https?:\/\/(www\.)?tumblr\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'tumblr';
				} else if (preg_match("/^https?:\/\/plus\.google\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'googleplus';
				} else if (preg_match("/^https?:\/\/dudu\.com/", $row['ulogin_identity'])) {
					$result[$key]['ulogin_network'] = 'dudu';
				} else {
					unset($result[$key]);
				}
			}

			$this->oMapper->fillUloginNetworks($result);
		}

		return true;
	}

	public function getSettings () {
		$s = $this->oMapper->getSettings();
		if (!$s) {
			$s = array(
				'uloginid1'         => '',
				'uloginid2'         => '',
				'uloginid_profile'  => '',
			);
		}
		return $s;
	}

	public function setSettings ($settings) {
		if (is_array($settings) &&
		    (
		        array_key_exists('uloginid1',$settings) ||
		        array_key_exists('uloginid2',$settings) ||
		        array_key_exists('uloginid_profile',$settings)
		    )
		) {
			return $this->oMapper->setSettings($settings);
		}
		return false;
	}

}
?>