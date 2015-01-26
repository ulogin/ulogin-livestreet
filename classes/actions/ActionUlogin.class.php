<?php
/*---------------------------------------------------------------------------------------
 *	author: uLogin Team, [team@ulogin.ru]
 *	plugin: uLogin
 *	author site: https://ulogin.ru/
 *	license: GNU General Public License, version 2
 * --------------------------------------------------------------------------------------*/

class PluginUlogin_ActionUlogin extends ActionPlugin {
	protected $u_data;
	protected $currentUserId;
	protected $isUserLogined;
	protected $doRedirect;
	protected $token;
	protected $redirect;
	protected $oUserCurrent;


	public function Init() {
		$this->SetDefaultEvent('login');
	}

	protected function RegisterEvent() {
		$this->AddEvent('login','EventLogin');
		$this->AddEvent('deleteaccount','EventDeleteAccount');
		$this->AddEvent('admin','EventAdmin');
	}

	public function EventLogin() {
		$title = '';
		$msg = '';

		if (isAjaxRequest()) {
			$this->doRedirect = false;
		} else {
			$this->doRedirect = true;
		}

		$this->isUserLogined = $this->User_IsAuthorization();
		if ($this->isUserLogined) {
			$this->currentUserId = $this->User_GetUserCurrent()->getId();
		} else {
			$this->currentUserId = 0;
		}

		if ($this->isUserLogined){
			$msg = 'plugin.ulogin.ulogin_add_account_success';//'Аккаунт успешно добавлен';
		}

		$this->uloginLogin($title, $msg);

		if (isAjaxRequest()) {
			exit;
		}

		$this->SetTemplate(false);
	}

	public function EventDeleteAccount() {
		$this->deleteAccount();
		$this->SetTemplate(false);
	}

	public function EventAdmin() {

		$this->oUserCurrent = $this->User_GetUserCurrent();
		if (!$this->oUserCurrent or !$this->oUserCurrent->isAdministrator()) {
			return $this->EventNotFound();
		}

		if(isPost('admin_submit')) {
			$settings = array(
				'uloginid1'         => mysql_real_escape_string(getRequest('uloginid1', '', 'post')),
				'uloginid2'         => mysql_real_escape_string(getRequest('uloginid2', '', 'post')),
				'uloginid_profile'  => mysql_real_escape_string(getRequest('uloginid_profile', '', 'post')),
			);

			$this->PluginUlogin_Ulogin_setSettings($settings);
		} else {
			$settings = $this->PluginUlogin_Ulogin_getSettings();
		}

		$this->Viewer_Assign('uloginid1', $settings['uloginid1']);
		$this->Viewer_Assign('uloginid2', $settings['uloginid2']);
		$this->Viewer_Assign('uloginid_profile', $settings['uloginid_profile']);

		$this->Viewer_SetHtmlTitle($this->Lang_Get('plugin.ulogin.admin_ulogin_title') . ' / ' . Config::Get('view.name'));

		$this->SetTemplateAction('admin');
		return true;
	}

//============================================================


	protected function uloginLogin ($title = '', $msg = '') {

		$this->u_data = $this->uloginParseRequest();
		if ( !$this->u_data ) {
			return;
		}

		try {
			$u_user_db = $this->PluginUlogin_Ulogin_getUloginUserItem(array('identity' => $this->u_data['identity']));;
			$user_id = 0;

			if ( $u_user_db ) {

				if ($this->PluginUlogin_Ulogin_checkUserId($u_user_db['user_id'])) {
					$user_id = $u_user_db['user_id'];
				}

				if ( intval( $user_id ) > 0 ) {
					if ( !$this->checkCurrentUserId( $user_id ) ) {
						// если $user_id != ID текущего пользователя
						return;
					}
				} else {
					// данные о пользователе есть в ulogin_table, но отсутствуют в users. Необходимо переписать запись в ulogin_table и в базе users.
					$user_id = $this->newUloginAccount( $u_user_db );
				}

			} else {
				// пользователь НЕ обнаружен в ulogin_table. Необходимо добавить запись в ulogin_table и в базе users.
				$user_id = $this->newUloginAccount();
			}

			// обновление данных и Вход
			if ( $user_id > 0 ) {
				$this->loginUser( $user_id );

				$networks = $this->PluginUlogin_Ulogin_getUloginUserNetworks( $user_id );
				$this->sendMessage(array(
					'title' => $title,
					'msg' => $msg,
					'networks' => $networks,
					'type' => 'success',
				));
				return;
			}

			$this->sendMessage (array(
				'title' => '',
				'msg' => 'plugin.ulogin.ulogin_login_error',
				'type' => 'error'
			));
			return;
		}

		catch (Exception $e){
			$this->sendMessage (array(
				'title' => 'plugin.ulogin.ulogin_db_error',//"Ошибка при работе с БД.",
				'msg' => "Exception: " . $e->getMessage(),
				'type' => 'error'
			));
			return;
		}
	}


	/**
	 * Отправляет данные как ответ на ajax запрос, если код выполняется в результате вызова callback функции,
	 * либо добавляет сообщение в сессию для вывода в режиме redirect
	 * @param array $params
	 */
	protected function sendMessage ($params = array()) {
		$params = array(
			'title' => !empty($params['title']) ? $this->Lang_Get($params['title']) : '',
			'msg' => !empty($params['msg'])
				? (!is_array($params['msg'])
					? $this->Lang_Get($params['msg']) 
					: $this->Lang_Get($params['msg'][0], array('s'=>$params['msg'][1])))
				: '',
			'type' => isset($params['type']) ? $params['type'] : '',
			'script' => isset($params['script']) ? $params['script'] : '',
			'networks' => isset($params['networks']) ? $params['networks'] : '',
		);

		if ($this->doRedirect){
			$message = $params['msg'];

			if (!empty($params['script'])) {
				$token = !empty($params['script']['token']) ? $params['script']['token'] : '';
				$identity = !empty($params['script']['identity']) ? $params['script']['identity'] : '';
				$s = '';

				if  ($token && $identity) {
					$s = "uLogin.mergeAccounts('$token', '$identity');";
				} else if ($token) {
					$s = "uLogin.mergeAccounts('$token');";
				}

				if ($s) {
					$message .= "<script type=\"text/javascript\">$s</script>";
				}
			}

			if (!empty($message) || !empty($params['title'])){
				if ($params['type'] == 'error') {
					$this->Message_AddError($message, $params['title'], true);
				} else {
					$this->Message_AddNotice($message, $params['title'], true);
				}
			}

			$redirect = urldecode(getRequest('backurl', ''));
			if (empty($redirect)) {
				$redirect = Config::Get('path.root.web').'/';
			}
			Router::Location($redirect);
		} else {
			echo json_encode($params);
			exit;
		}
	}


	/**
	 * Добавление в таблицу uLogin
	 * @param $u_user_db - при непустом значении необходимо переписать данные в таблице uLogin
	 */
	protected function newUloginAccount($u_user_db = ''){
		$u_data = $this->u_data;

		if ($u_user_db) {
			// данные о пользователе есть в ulogin_user, но отсутствуют в users => удалить их
			$this->PluginUlogin_Ulogin_deleteUloginAccount(array('id' => $u_user_db['id']));
		}

		$CMSuserId = $this->PluginUlogin_Ulogin_getUserIdByEmail($u_data['email']);

		// $emailExists == true -> есть пользователь с таким email
		$user_id = 0;
		$emailExists = false;
		if ($CMSuserId) {
			$user_id = $CMSuserId; // id юзера с тем же email
			$emailExists = true;
		}

		// $isUserLogined == true -> пользователь онлайн
		$currentUserId = $this->currentUserId;
		$isUserLogined = $this->isUserLogined;

		if (!$emailExists && !$isUserLogined) {
			// отсутствует пользователь с таким email в базе -> регистрация в БД
			$user_id = $this->regUser();
			$this->addUloginAccount($user_id);
		} else {
			// существует пользователь с таким email или это текущий пользователь
			if (intval($u_data["verified_email"]) != 1){
				// Верификация аккаунта

				$this->sendMessage(
					array(
						'title' => 'plugin.ulogin.ulogin_verify',//'Подтверждение аккаунта.',
						'msg' => 'plugin.ulogin.ulogin_verify_text',
						'script' => array('token' => $this->token),
					)
				);
				return false;
			}

			$user_id = $isUserLogined ? $currentUserId : $user_id;

			$other_u = $this->PluginUlogin_Ulogin_getUloginUserItem(array(
				'user_id' => $user_id,
			));

			if ($other_u) {
				// Синхронизация аккаунтов
				if(!$isUserLogined && !isset($u_data['merge_account'])){
					$this->sendMessage(
						array(
							'title' => 'plugin.ulogin.ulogin_synch',//'Синхронизация аккаунтов.',
							'msg' => 'plugin.ulogin.ulogin_synch_text',
							'script' => array('token' => $this->token, 'identity' => $other_u['identity']),
						)
					);
					return false;
				}
			}

			$this->addUloginAccount($user_id);
		}

		return $user_id;
	}



	/**
	 * Регистрация пользователя в БД users
	 * @return mixed
	 */
	protected function regUser(){
		$u_data = $this->u_data;

		$login = $this->generateNickname(
			isset($u_data['first_name']) ? $u_data['first_name'] : '',
			isset($u_data['last_name']) ? $u_data['last_name'] : '',
			isset($u_data['nickname']) ? $u_data['nickname'] : '',
			isset($u_data['bdate']) ? $u_data['bdate'] : ''
		);

		$password = md5($u_data['identity'].time().rand());
		$password = substr($password, 0, 12);

		$oUser=Engine::GetEntity('User');
		$oUser->setLogin($login);
		$oUser->setMail($u_data['email']);
		$oUser->setPassword(md5($password));
		$oUser->setDateRegister(date("Y-m-d H:i:s"));
		$oUser->setIpRegister(func_getIp());

		// Если используется активация, то генерим код активации
		if ($activation_flag = (Config::Get('general.reg.activation') && $u_data["verified_email"] == -1)) {
			$oUser->setActivate(0);
			$oUser->setActivateKey(md5(func_generator().time()));
		} else {
			$oUser->setActivate(1);
			$oUser->setActivateKey(null);
		}

		if ($oUser = $this->User_Add($oUser)) {

			$this->Hook_Run('registration_after', array('oUser' => $oUser));

			// Подписываем пользователя на дефолтные события в ленте активности
			$this->Stream_switchUserEventDefaultTypes($oUser->getId());

			$oUser->setCountVote(0);
			$oUser->setSettingsNoticeNewTopic(1);
			$oUser->setSettingsNoticeNewComment(1);
			$oUser->setSettingsNoticeNewTalk(1);
			$oUser->setSettingsNoticeReplyComment(1);
			$oUser->setSettingsNoticeNewFriend(1);

			// Если стоит регистрация с активацией то проводим её
			if ($activation_flag) {
				// Отправляем на мыло письмо о подтверждении регистрации
				$this->Notify_SendRegistrationActivate($oUser, $password);

				$this->sendMessage (array(
					'title' => "",
					'msg' => array('plugin.ulogin.ulogin_account_inactive', $u_data['email']),
					'type' => 'success'
				));
				return false;
			}

			$this->Notify_SendRegistration($oUser, $password);
			$oUser = $this->User_GetUserById($oUser->getId());

			return $oUser->getId();
		}

		$this->sendMessage (array(
			'title' => "plugin.ulogin.ulogin_reg_error",
			'msg' => 'plugin.ulogin.ulogin_reg_error_text',
			'type' => 'error'
		));
		return false;
	}



	/**
	 * Добавление записи в таблицу ulogin_user
	 * @param $user_id
	 * @return bool
	 */
	protected function addUloginAccount($user_id){
		$res = $this->PluginUlogin_Ulogin_addUloginAccount(array(
			'user_id' => $user_id,
			'identity' => strval($this->u_data['identity']),
			'network' => $this->u_data['network'],
		));

		if (!$res) {
			$this->sendMessage (array(
				'title' => 'plugin.ulogin.ulogin_auth_error',//"Произошла ошибка при авторизации.",
				'msg' => 'plugin.ulogin.ulogin_add_account_error',//"Не удалось записать данные об аккаунте.",
				'type' => 'error'
			));
			return false;
		}

		return true;
	}



	/**
	 * Выполнение входа пользователя в систему по $user_id
	 * @param $u_user
	 * @param int $user_id
	 */
	protected function loginUser($user_id = 0) {
		// обновление данных
		$change_flag = false;
		$u_data = $this->u_data;
		$oUser = $this->ModuleUser_GetUserById($user_id);

		// проверка имени профиля
		if (!$oUser->getProfileName()) {
			if (!empty($u_data['first_name']) && !empty($u_data['last_name'])) {
				$profileName = $u_data['first_name'] . ' ' . $u_data['last_name'];
			} else {
				$profileName = $this->generateNickname(
					isset($u_data['first_name']) ? $u_data['first_name'] : '',
					isset($u_data['last_name']) ? $u_data['last_name'] : '',
					isset($u_data['nickname']) ? $u_data['nickname'] : '',
					isset($u_data['bdate']) ? $u_data['bdate'] : ''
				);
			}

			$oUser->setProfileName($profileName);
			$change_flag = true;
		}

		// проверка пола
		if ($oUser->getProfileSex() == 'other') {
			$profileSex = 'other';
			if (isset($u_data['sex']) && $u_data['sex'] == 2)
				$profileSex = 'man';
			else if (isset($u_data['sex']) && $u_data['sex'] == 1)
				$profileSex = 'women';

			if ($profileSex != 'other') {
				$oUser->setProfileSex($profileSex);
				$change_flag = true;
			}
		}

		// проверка даты рождения
		if (!$oUser->getProfileBirthday()) {
			if(!empty($u_data['bdate']))
				$oUser->setProfileBirthday(date("Y-m-d H:i:s", strtotime($u_data['bdate'])));
			$change_flag = true;
		}

		// проверка адреса
		$aGeoTargets=$this->Geo_GetTargetsByTargetArray('user',$user_id);
		if (empty($aGeoTargets)) {
			$oGeoObject=null;
			if (!empty($u_data['city'])) {
				$aRes = $this->Geo_GetCities(array('name_ru'=>$u_data['city']),array('sort'=>'asc'),1,1);
				if (!empty($aRes['collection'][0])) {
					$oGeoObject = $aRes['collection'][0];
				}
			}
			if (!$oGeoObject && !empty($u_data['country'])) {
				$aRes = $this->Geo_GetCountries(array('name_ru'=>$u_data['country']),array('sort'=>'asc'),1,1);
				if (!empty($aRes['collection'][0])) {
					$oGeoObject = $aRes['collection'][0];
				}
			}

			if ($oGeoObject) {
				$this->Geo_CreateTarget($oGeoObject, 'user', $oUser->getId());
				if ($oCountry=$oGeoObject->getCountry()) {
					$oUser->setProfileCountry($oCountry->getName());
				}
				if ($oRegion=$oGeoObject->getRegion()) {
					$oUser->setProfileRegion($oRegion->getName());
				}
				if ($oCity=$oGeoObject->getCity()) {
					$oUser->setProfileCity($oCity->getName());
				}
				$change_flag = true;
			}
		}

		// проверка аватара и фото
		if (!$oUser->getProfileAvatar() || !$oUser->getProfileFoto()) {
			$file_url = (!empty($u_data['photo_big']))
				? $u_data['photo_big']
				: (!empty( $u_data['photo'] ) ? $u_data['photo'] : '');

			// загрузка аватара
			if($file_url && $aFile = $this->loadFile($file_url)) {
				$aFile2 = $aFile . "_copy";
				@copy($aFile, $aFile2);

				if (!$oUser->getProfileAvatar()) {
					if ($sFileAvatar=$this->User_UploadAvatar($aFile,$oUser))
						$oUser->setProfileAvatar( $sFileAvatar );
					$change_flag = true;
				}

				if (!$oUser->getProfileFoto()) {
					if ($sFileFoto=$this->User_UploadFoto($aFile2,$oUser))
						$oUser->setProfileFoto( $sFileFoto );
					$change_flag = true;
				}
			}
		}

		if ($change_flag) {
			$this->User_Update( $oUser );
		}

		$result = $this->User_Authorization($oUser, true);

		if (!$result) {
			$this->sendMessage (
				array(
					'title' => '',
					'msg' => 'plugin.ulogin.ulogin_auth_error', // "Произошла ошибка при авторизации."
					'type' => 'error',
				)
			);
		}

		return true;
	}



	/**
	 * Проверка текущего пользователя
	 * @param $user_id
	 */
	protected function checkCurrentUserId($user_id){
		$currentUserId = $this->currentUserId;
		if($this->isUserLogined) {
			if ($currentUserId == $user_id) {
				return true;
			}
			$this->sendMessage (
				array(
					'title' => '',
					'msg' => 'plugin.ulogin.ulogin_account_not_available',
					'type' => 'error',
				)
			);
			return false;
		}
		return true;
	}



	/**
	 * Обработка ответа сервера авторизации
	 */
	protected function uloginParseRequest(){

		$this->token = getRequest('token', '', 'post');

		if (!$this->token) {
			$this->sendMessage (array(
				'title' => 'plugin.ulogin.ulogin_auth_error', //"Произошла ошибка при авторизации.",
				'msg' => 'plugin.ulogin.ulogin_no_token_error', //"Не был получен токен uLogin.",
				'type' => 'error'
			));
			return false;
		}

		$s = $this->getUserFromToken();

		if (!$s){
			$this->sendMessage (array(
				'title' => 'plugin.ulogin.ulogin_auth_error', //"Произошла ошибка при авторизации.",
				'msg' => 'plugin.ulogin.ulogin_no_user_data_error', //"Не удалось получить данные о пользователе с помощью токена.",
				'type' => 'error'
			));
			return false;
		}

		$this->u_data = json_decode($s, true);

		if (!$this->checkTokenError()){
			return false;
		}

		return $this->u_data;
	}


	/**
	 * "Обменивает" токен на пользовательские данные
	 */
	protected function getUserFromToken() {
		$response = false;
		if ($this->token){
			$host = Config::Get('path.root.web');
			$request = 'http://ulogin.ru/token.php?token=' . $this->token . '&host=' . $host;
			$response = $this->getResponse($request);
		}
		return $response;
	}

	/**
	 * Получение данных с помощью curl или file_get_contents
	 * @param string $url
	 * @return bool|mixed|string
	 */
	private function getResponse($url="", $do_abbort=true) {
		$result = false;

		if (in_array('curl', get_loaded_extensions())) {
			$request = curl_init($url);
			curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($request, CURLOPT_BINARYTRANSFER, 1);
			$result = curl_exec($request);
		}elseif (function_exists('file_get_contents') && ini_get('allow_url_fopen')){
			$result = file_get_contents($url);
		}

		if (!$result) {
			if ($do_abbort) {
				$this->sendMessage(array(
					'title' => 'plugin.ulogin.ulogin_read_response_error',
					'msg' => 'plugin.ulogin.ulogin_read_response_error_text',
					'type' => 'error'
				));
			}
			return false;
		}

		return $result;
	}

	/**
	 * Загрузка файла аватарки
	 * @param $fileUrl
	 * @return array|bool
	 */
	protected function loadFile($fileUrl)
	{
//		$file = $this->getResponse($fileUrl, false);
		$file = file_get_contents($fileUrl);

		if(!$file) {
			return false;
		}

		$tmp_name = tempnam(Config::Get('sys.cache.dir'), "php");

		$handle = fopen($tmp_name, "w");
		$fileSize = fwrite($handle, $file);
		fclose($handle);

		if(!$fileSize)
		{
			@unlink($tmp_name);
			return false;
		}

		return $tmp_name;
	}


	/**
	 * Проверка пользовательских данных, полученных по токену
	 */
	protected function checkTokenError(){
		if (!is_array($this->u_data)){
			$this->sendMessage (array(
				'title' => 'plugin.ulogin.ulogin_auth_error', //"Произошла ошибка при авторизации.",
				'msg' => 'plugin.ulogin.ulogin_wrong_user_data_error', //"Данные о пользователе содержат неверный формат.",
				'type' => 'error'
			));
			return false;
		}

		if (isset($this->u_data['error'])){
			$strpos = strpos($this->u_data['error'],'host is not');
			if ($strpos){
				$this->sendMessage (array(
					'title' => 'plugin.ulogin.ulogin_auth_error', //"Произошла ошибка при авторизации.",
					'msg' => array('plugin.ulogin.ulogin_host_address_error', sub($this->u_data['error'],intval($strpos)+12)),//"<i>ERROR</i>: адрес хоста не совпадает с оригиналом " . sub($this->u_data['error'],intval($strpos)+12),
					'type' => 'error'
				));
				return false;
			}
			switch ($this->u_data['error']){
				case 'token expired':
					$this->sendMessage (array(
						'title' => 'plugin.ulogin.ulogin_auth_error', //"Произошла ошибка при авторизации.",
						'msg' => 'plugin.ulogin.ulogin_token_expired_error', //"<i>ERROR</i>: время жизни токена истекло",
						'type' => 'error'
					));
					break;
				case 'invalid token':
					$this->sendMessage (array(
						'title' => 'plugin.ulogin.ulogin_auth_error', //"Произошла ошибка при авторизации.",
						'msg' => 'plugin.ulogin.ulogin_invalid_token_error', //"<i>ERROR</i>: неверный токен",
						'type' => 'error'
					));
					break;
				default:
					$this->sendMessage (array(
						'title' => 'plugin.ulogin.ulogin_auth_error', //"Произошла ошибка при авторизации.",
						'msg' => "<i>ERROR</i>: " . $this->u_data['error'],
						'type' => 'error'
					));
			}
			return false;
		}
		if (!isset($this->u_data['identity'])){
			$this->sendMessage (array(
				'title' => 'plugin.ulogin.ulogin_auth_error', //"Произошла ошибка при авторизации.",
				'msg' => array('plugin.ulogin.ulogin_no_variable_error', 'identity'), //"В возвращаемых данных отсутствует переменная <b>identity</b>.",
				'type' => 'error'
			));
			return false;
		}
		if (!isset($this->u_data['email'])){
			$this->sendMessage (array(
				'title' => 'plugin.ulogin.ulogin_auth_error', //"Произошла ошибка при авторизации.",
				'msg' => array('plugin.ulogin.ulogin_no_variable_error', 'email'), //"В возвращаемых данных отсутствует переменная <b>email</b>",
				'type' => 'error'
			));
			return false;
		}
		return true;
	}


	/**
	 * Гнерация логина пользователя
	 * в случае успешного выполнения возвращает уникальный логин пользователя
	 * @param $first_name
	 * @param string $last_name
	 * @param string $nickname
	 * @param string $bdate
	 * @param array $delimiters
	 * @return string
	 */
	protected function generateNickname($first_name, $last_name="", $nickname="", $bdate="", $delimiters=array('.', '_')) {
		$delim = array_shift($delimiters);

		$first_name = $this->translitIt($first_name);
		$first_name_s = substr($first_name, 0, 1);

		$variants = array();
		if (!empty($nickname))
			$variants[] = $nickname;
		$variants[] = $first_name;
		if (!empty($last_name)) {
			$last_name = $this->translitIt($last_name);
			$variants[] = $first_name.$delim.$last_name;
			$variants[] = $last_name.$delim.$first_name;
			$variants[] = $first_name_s.$delim.$last_name;
			$variants[] = $first_name_s.$last_name;
			$variants[] = $last_name.$delim.$first_name_s;
			$variants[] = $last_name.$first_name_s;
		}
		if (!empty($bdate)) {
			$date = explode('.', $bdate);
			$variants[] = $first_name.$date[2];
			$variants[] = $first_name.$delim.$date[2];
			$variants[] = $first_name.$date[0].$date[1];
			$variants[] = $first_name.$delim.$date[0].$date[1];
			$variants[] = $first_name.$delim.$last_name.$date[2];
			$variants[] = $first_name.$delim.$last_name.$delim.$date[2];
			$variants[] = $first_name.$delim.$last_name.$date[0].$date[1];
			$variants[] = $first_name.$delim.$last_name.$delim.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name.$date[2];
			$variants[] = $last_name.$delim.$first_name.$delim.$date[2];
			$variants[] = $last_name.$delim.$first_name.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name.$delim.$date[0].$date[1];
			$variants[] = $first_name_s.$delim.$last_name.$date[2];
			$variants[] = $first_name_s.$delim.$last_name.$delim.$date[2];
			$variants[] = $first_name_s.$delim.$last_name.$date[0].$date[1];
			$variants[] = $first_name_s.$delim.$last_name.$delim.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name_s.$date[2];
			$variants[] = $last_name.$delim.$first_name_s.$delim.$date[2];
			$variants[] = $last_name.$delim.$first_name_s.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name_s.$delim.$date[0].$date[1];
			$variants[] = $first_name_s.$last_name.$date[2];
			$variants[] = $first_name_s.$last_name.$delim.$date[2];
			$variants[] = $first_name_s.$last_name.$date[0].$date[1];
			$variants[] = $first_name_s.$last_name.$delim.$date[0].$date[1];
			$variants[] = $last_name.$first_name_s.$date[2];
			$variants[] = $last_name.$first_name_s.$delim.$date[2];
			$variants[] = $last_name.$first_name_s.$date[0].$date[1];
			$variants[] = $last_name.$first_name_s.$delim.$date[0].$date[1];
		}
		$i=0;

		$exist = true;
		while (true) {
			if ($exist = $this->userExist($variants[$i])) {
				foreach ($delimiters as $del) {
					$replaced = str_replace($delim, $del, $variants[$i]);
					if($replaced !== $variants[$i]){
						$variants[$i] = $replaced;
						if (!$exist = $this->userExist($variants[$i]))
							break;
					}
				}
			}
			if ($i >= count($variants)-1 || !$exist)
				break;
			$i++;
		}

		if ($exist) {
			while ($exist) {
				$nickname = $first_name.mt_rand(1, 100000);
				$exist = $this->userExist($nickname);
			}
			return $nickname;
		} else
			return $variants[$i];
	}


	/**
	 * Проверка существует ли пользователь с заданным логином
	 */
	protected function userExist($login){
		if (!$this->PluginUlogin_Ulogin_checkUserName(strtolower($login))){
			return false;
		}
		return true;
	}


	/**
	 * Транслит
	 */
	protected function translitIt($str) {
		$tr = array(
			"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
			"Д"=>"d","Е"=>"e","Ж"=>"j","З"=>"z","И"=>"i",
			"Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
			"О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
			"У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch",
			"Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"",
			"Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
			"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
			"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
			"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
			"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
			"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
			"ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
		);
		if (preg_match('/[^A-Za-z0-9\_\-]/', $str)) {
			$str = strtr($str,$tr);
			$str = preg_replace('/[^A-Za-z0-9\_\-\.]/', '', $str);
		}
		return $str;
	}



	/**
	 * Удаление привязки к аккаунту соцсети в таблице ulogin_user для текущего пользователя
	 */
	protected function deleteAccount() {
		if (!isAjaxRequest()) {
			return parent::EventNotFound();
		}

		$this->isUserLogined = $this->User_IsAuthorization();
		if ($this->isUserLogined) {
			$this->currentUserId = $this->User_GetUserCurrent()->getId();
		} else {
			$this->currentUserId = 0;
		}

		if(!$this->isUserLogined) {exit;}

		$user_id = $this->currentUserId;

		$network = getRequest('network', '', 'post');

		if ($user_id > 0 && $network != '') {
			try {
				$this->PluginUlogin_Ulogin_deleteUloginAccount(array('user_id' => $user_id, 'network' => $network));
				echo json_encode(array(
					'title' => '',
					'msg' => $this->Lang_Get('plugin.ulogin.ulogin_delete_account_success', array('s'=>$network)), //"Удаление аккаунта $network успешно выполнено",
					'type' => 'success'
				));
				exit;
			} catch (Exception $e) {
				echo json_encode(array(
					'title' => $this->Lang_Get('plugin.ulogin.ulogin_delete_account_error'), //"Ошибка при удалении аккаунта",
					'msg' => "Exception: " . $e->getMessage(),
					'type' => 'error'
				));
				exit;
			}
		}
		exit;
	}

}