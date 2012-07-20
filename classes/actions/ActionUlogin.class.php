<?php
/*---------------------------------------------------------------------------------------
 *	author: helper [helper2424@gmail.com, x01d.com]
 *	plugin: uLogin
 *	author site: http://x01d.com/
 *	license: CC BY-SA 3.0, http://creativecommons.org/licenses/by-sa/3.0/ 
 * --------------------------------------------------------------------------------------*/

class PluginUlogin_ActionUlogin extends ActionPlugin {
	/**
	 * Текущий юзер
	 *
	 * @var ModuleUser_EntityUser
	 */
	protected $oUserCurrent=null;
	protected $oUserAdmin=false;

	/**
	 * Инициализация экшена
	 */
	public function Init() {
		/**
		 * Получаем текущего пользователя
		 */
		$this->oUserCurrent=$this->User_GetUserCurrent();
		if ($this->oUserCurrent && $this->oUserCurrent->isAdministrator()) {
			$this->oUserAdmin=true;
		}

		/**
		 * Устанавливаем дефолтный эвент
		 */
		$this->SetDefaultEvent('index');
		/**
		 * Устанавливаем дефолтный шаблон
		 */
		$this->SetTemplateAction('index');
	}
        
        // Получение полного пути шаблона
	protected function GetTemplateActionPath($sTemplate) {
		return Plugin::GetTemplatePath(__CLASS__).'actions/ActionUlogin/'.$sTemplate.'.tpl';
	} 
	    
	// Назначаем шаблон текущему экшену
	protected function SetTemplateAction($sTemplate) {
		$this->SetTemplate($this->GetTemplateActionPath($sTemplate));
	}
        
        /**
         * Регистрация эвентов
         */
        protected function RegisterEvent() {
                $this->AddEvent('index','EventIndex');   
		//$this->AddEvent('registration', 'EventRegistration');    
		$this->AddEvent('admin','EventAdmin');
		//$this->AddEvent('bind','EventBind');
		//$this->AddEvent('one_click_registration','EventOneClickRegistration');
        }

	/**
	 * Основная страница плагина
	 *
	 */
	public function EventIndex() {
		//Получаем token от uLogin
		$token = getRequest('token', false);

		if(!$token)
		{
			$this->Message_AddErrorSingle($this->Lang_Get('ulogin_token_error'));
			return Router::Action('error');
		}
	
		try
		{
			//Получаем пользователские данные от uLogin
			$s = file_get_contents('http://ulogin.ru/token.php?token=' . trim(substr($token, 0, 100)) . '&host=' . $_SERVER['HTTP_HOST']);
			$user = json_decode($s, true);
				
			//Защита от mysql инъекуий
			foreach($user as $key => $value)
				$user[$key] = mysql_real_escape_string(substr(trim((string)$value), 0, 100));
	
			//Проверка наличия у полученных данных уникального польховательского идентификатора
			if(!isset($user['identity']))
				throw new Exception(); 
		}
		catch(Exception $excp)
		{
			$this->Message_AddErrorSingle($this->Lang_Get('ulogin_token_error'));
			return Router::Action('error');
		}

		//Выбор дальнейших действий
		$oUloginUser=$this->PluginUlogin_ModuleUlogin_GetUloginByIdentity($user['identity']);
		
		if($oUloginUser)
		{
			//Если пользователь уже привязал данный аккаунт 
			if($this->oUserCurrent)
				// и зарегистрирован и уже авторизован, то выдаем ошибку
				return parent::EventNotFound();	
			else
			{
				// и не авторизован, то авторизуем его
				$oUser = $this->ModuleUser_GetUserById($oUloginUser->getUserid());
				$this->User_Authorization($oUser, true);
				
				if (isset($_SERVER['HTTP_REFERER'])) {
					$sBackUrl=$_SERVER['HTTP_REFERER'];
					if (strpos($sBackUrl,Router::GetPath('ulogin'))===false) {
						Router::Location($sBackUrl);
					}
				}

				Router::Location(Config::Get('path.root.web').'/');	
			}
		}
		else
		{
			//Если данные, полученные от uLogin, не зарегистрированы в базе, то
			if($this->oUserCurrent)
			{
				//если пользователь уже зарегистрирован и авторизован, то просто привязываем к его аккаунту новые данные
				if($this->bind($this->oUserCurrent, $user['identity']))
				{
					if (isset($_SERVER['HTTP_REFERER'])) {
						$sBackUrl=$_SERVER['HTTP_REFERER'];
						if (strpos($sBackUrl,Router::GetPath('ulogin'))===false) {
							Router::Location($sBackUrl);
						}
					}
						
					$this->Message_AddNotice($this->Lang_Get('ulogin_success_binded'));

					Router::Location(Config::Get('path.root.web').'/');
				}
				else
				{
					$this->Message_AddErrorSingle($this->Lang_Get('ulogin_this_else_registered'));
					return Router::Action('error');
				}
			}
			else
			{
				//пользователь не зарегистрирован, перенаправляем его на страницу регистрации

			/*
				данные от uLogin сохраняем в сессию
				$this->Session_Set('ulogin_user_data', $user);
	
				$oSettings = $this->PluginUlogin_ModuleUlogin_GetSettingsById('one_click_registration');
			
				if(is_null($oSettings))
					$oneClick_registrationFlag = Config::Get('plugin.ulogin.one_click_registration');
				else
					$oneClick_registrationFlag = $oSettings->getValue();

				if($oneClick_registrationFlag == FALSE)
					return Router::Action('ulogin','registration');
				
				return Router::Action('ulogin', 'one_click_registration');
			 */ 
				return $this->registration($user);
			}
		}

		$this->SetTemplateAction('index');
	}

	protected function registration($userData)
	{
		if(isset($userData['nickname']))
			$userData['nickname'] = trim(strtolower($userData['nickname']));
		
		if(isset($userData['email']))
			$userData['email'] = strtolower($userData['email']);
			
		if(!isset($userData['nickname']) || !$userData['nickname'] || $this->User_GetUserByLogin($userData['nickname']))
			$userData['nickname'] .= '_'.$userData['network'].'_'.$userData['uid'];
		
		if(!isset($userData['email']) || !$userData['email'])
			$userData['email'] = $userData['uid'].'_'.$userData['network'].'@'.str_replace(array("http:","/") ,"" ,Config::Get('path.root.web'));
			
		while($this->User_GetUserByMail($userData['email']))
			$userData['email'] = str_replace("@" ,"+@" ,$userData['email']);
		
		if(!isset($userData['sex']) || !in_array($userData['sex'], array(1,2)))
			$userData['sex'] = 'other';
		elseif($userData['sex'] == 2)
			$userData['sex'] = 'man';
		else 
			$userData['sex'] = 'women';
		
		if(isset($userData['bdate']) and !empty($userData['bdate'])){
                    $bdate = explode('.', $userData['bdate']);
                    $userData['bdate'] = date('Y-m-d H:i:s', mktime(0,0,0,$bdate[1],$bdate[0], $bdate[2]));
                }
		
		$oUser=Engine::GetEntity('User');
		$oUser->setLogin($userData['nickname']);
		$oUser->setProfileSex($userData['sex']);
		$oUser->setProfileName($userData['first_name'].' '.$userData['last_name']);
		$oUser->setMail($userData['email']);
		$oUser->setPassword(func_encrypt($this->GeneratePassword()));
		$oUser->setDateRegister(date("Y-m-d H:i:s"));
		$oUser->setIpRegister(func_getIp());
		$oUser->setProfileBirthday($userData['bdate']);
		
		if(isset($userData['country']) && $userData['country'])
			$oUser->setProfileCountry($userData['country']);
		
		if(isset($userData['city']) && $userData['city'])		
			$oUser->setProfileCity($userData['city']);

		if (Config::Get('general.reg.activation')) {
			$oUser->setActivate(0);
			$oUser->setActivateKey(md5(func_generator().time()));
		} else {
			$oUser->setActivate(1);
			$oUser->setActivateKey(null);
		}
		
		return $this->UserRegistration($oUser, $userData);
	}
	
	/*
	 * Регистрация новго пользвателя и автоматическое привязываение ему новых данных (используется стандартная форма регистрации + возможность привязывать новые аккаунты)
	
	public function EventRegistration()
	{	
		$this->SetTemplateAction('registration');

		
		 // Если нажали кнопку "Зарегистрироваться"
		
		if ($user_data = $this->Session_Get('ulogin_user_data') and isPost('submit_register')) {
			//Проверяем  входные данные
			$bError=false;
			
			 // Проверка логина
			 
			if (!func_check(getRequest('login'),'login',3,30)) {
				$this->Message_AddError($this->Lang_Get('registration_login_error'),$this->Lang_Get('error'));
				$bError=true;
			}
			//  Проверка мыла
			 
			if (!func_check(getRequest('mail'),'mail')) {
				$this->Message_AddError($this->Lang_Get('registration_mail_error'),$this->Lang_Get('error'));
				$bError=true;
			}
			
	 		// 	Проверка пароля
			 
			if (!func_check(getRequest('password'),'password',5)) {
				$this->Message_AddError($this->Lang_Get('registration_password_error'),$this->Lang_Get('error'));
				$bError=true;
			} elseif (getRequest('password')!=getRequest('password_confirm')) {
				$this->Message_AddError($this->Lang_Get('registration_password_error_different'),$this->Lang_Get('error'));
				$bError=true;
			}

			
			 // А не занят ли логин?
			 
			if ($this->User_GetUserByLogin(getRequest('login'))) {
				$this->Message_AddError($this->Lang_Get('registration_login_error_used'),$this->Lang_Get('error'));
				$bError=true;
			}
			
			 // А не занято ли мыло?
			 
			if ($this->User_GetUserByMail(getRequest('mail'))) {
				$this->Message_AddError($this->Lang_Get('registration_mail_error_used'),$this->Lang_Get('error'));
				$bError=true;
			}
			
			 // Если всё то пробуем зарегить
			 
			if (!$bError) {
				
				 // Создаем юзера
				 

				srand();

				$oUser=Engine::GetEntity('User');
				$oUser->setLogin(getRequest('login'));
				$oUser->setMail(getRequest('mail'));
				$oUser->setPassword(func_encrypt(getRequest('password')));
				$oUser->setDateRegister(date("Y-m-d H:i:s"));
				$oUser->setIpRegister(func_getIp());
				
				 // Если используется активация, то генерим код активации
				 
				if (Config::Get('general.reg.activation')) {
					$oUser->setActivate(0);
					$oUser->setActivateKey(md5(func_generator().time()));
				} else {
					$oUser->setActivate(1);
					$oUser->setActivateKey(null);
				}

				//Убираем из сессии данные из uLogin
				$this->Session_Drop('ulogin_user_data');					
				
				 // Регистрируем
				 
				return $this->UserRegistration($oUser, $user_data);
			}
		}
		elseif(!$user_data)
			return parent::EventNotFound();	
	}
	*/
	
	/*
	 * Регистрация новго пользвателя и автоматическое привязываение ему новых данных (используется упрощенная форма регистрации и отсутствует возможность привязывать новые аккаунты)
	
	public function EventOneClickRegistration()
	{
		$this->SetTemplateAction('one_click_registration');

		if ($user_info = $this->Session_Get('ulogin_user_data') and isPost('one_click_registration_submit')) 
		{
			$bError=false;
			
			 // Проверка логина
			 
			if (!func_check(getRequest('login'),'login',3,30)) {
				$this->Message_AddError($this->Lang_Get('registration_login_error'),$this->Lang_Get('error'));
				$bError=true;
			}
	
			// Проверка мыла
			 
			if (!func_check(getRequest('mail'),'mail')) {
				$this->Message_AddError($this->Lang_Get('registration_mail_error'),$this->Lang_Get('error'));
				$bError=true;
			}
			
			// А не занят ли логин?
			
			if ($this->User_GetUserByLogin(getRequest('login'))) {
				$this->Message_AddError($this->Lang_Get('registration_login_error_used'),$this->Lang_Get('error'));
				$bError=true;
			}

			//	А не занято ли мыло?			
			if ($this->User_GetUserByMail(getRequest('mail'))) {
				$this->Message_AddError($this->Lang_Get('registration_mail_error_used'),$this->Lang_Get('error'));
				$bError=true;
			}

			if(!$bError)
			{
				$oUser=Engine::GetEntity('User');
				$oUser->setLogin(getRequest('login'));
				if(isset($user_info['email']) and !$this->User_GetUserByMail($user_info['email']))
					$oUser->setMail($user_info['email']);
				else
					$oUser->setMail(getRequest('mail'));
				$oUser->setPassword(func_encrypt($this->GeneratePassword()));
				$oUser->setDateRegister(date("Y-m-d H:i:s"));
				$oUser->setIpRegister(func_getIp());

				if (Config::Get('general.reg.activation')) {
					$oUser->setActivate(0);
					$oUser->setActivateKey(md5(func_generator().time()));
				} else {
					$oUser->setActivate(1);
					$oUser->setActivateKey(null);
				}
				
				$this->Session_Drop('ulogin_user_data');	
				return $this->UserRegistration($oUser, $user_info);
			}
		
			$this->Viewer_Assign('user_info', $user_info);
		}
		elseif(!$user_info)
			return parent::EventNotFound();
	}
	*/
	
	/*
	 * Метод для привязки новых аккаунтов(если пользователь не авторизован изначально. доступнотолько с расширенной формой регистрации)
	 * 
	public function EventBind()
	{
		$this->SetTemplateAction('registration');
		
	 	// Если нажали кнопку "Войти"
		if ($user_info = $this->Session_Get('ulogin_user_data') and isPost('submit_bind') and is_string(getRequest('bind_login')) and is_string(getRequest('bind_password'))) {
			
			// Проверяем есть ли такой юзер по логину
			
			if ((func_check(getRequest('bind_login'),'mail') and $oUser=$this->User_GetUserByMail(getRequest('bind_login')))  or  $oUser=$this->User_GetUserByLogin(getRequest('bind_login'))) {	
				
				 // Сверяем хеши паролей и проверяем активен ли юзер
				 
				if ($oUser->getPassword()==func_encrypt(getRequest('bind_password')) and $oUser->getActivate() ) {
					$bRemember=getRequest('bind_remember',false) ? true : false;
					
					 // Авторизуем 
		
					$this->Session_Drop('ulogin_user_data');
				
					if(!$this->bind($oUser, $user_info['identity']))
					{
						$this->Message_AddErrorSingle($this->Lang_Get('ulogin_this_else_registered'));
						return Router::Action('error');
					}

					$this->User_Authorization($oUser,$bRemember);

					Router::Location(Config::Get('path.root.web').'/');
				}
			}
			$this->Viewer_Assign('bLoginError',true);
		}
		elseif(!$user_info)
			return parent::EventNotFound();
	}
	 */

	private function UserRegistration($oUser, $uLoginUserData)
	{
		if ($oUser = $this->User_Add($oUser) and $this->bind($oUser, $uLoginUserData['identity'])) {
			/**
			 * Создаем персональный блог
			 */

			if(isset($uLoginUserData['photo']) && $uLoginUserData['photo'] && $photo = $this->loadFile($uLoginUserData['photo']))
			{
				if($sPath=$this->UploadAvatar($photo,$oUser))
					$oUser->setProfileAvatar($sPath);
			}

			if(isset($uLoginUserData['photo_big']) && $uLoginUserData['photo_big'] && $photo_big = $this->loadFile($uLoginUserData['photo_big']))
			{
				if ($sFileFoto=$this->UploadFoto($photo_big,$oUser))
					$oUser->setProfileFoto($sFileFoto);
			}

			if ($oUser->getProfileCountry()) {
				/*if (!($oCountry=$this->Geo_GetCountryByName($oUser->getProfileCountry()))) {
					$oCountry=Engine::GetEntity('Geo_Country');
     				$oCountry->setName($oUser->getProfileCountry());
					$this->User_AddCountry($oCountry);
				}
				$this->User_SetCountryUser($oCountry->getId(),$oUser->getId());*/
			}

			/**
			 * Добавляем город
			 */
    		if ($oUser->getProfileCity()) {
				/*if (!($oCity=$this->User_GetCityByName($oUser->getProfileCity()))) {
					$oCity=Engine::GetEntity('User_City');
					$oCity->setName($oUser->getProfileCity());
					$this->User_AddCity($oCity);
				}
				$this->User_SetCityUser($oCity->getId(),$oUser->getId()); */
			}

			$this->User_Update($oUser);
			
			$this->Blog_CreatePersonalBlog($oUser);				

			$oUser=$this->User_GetUserById($oUser->getId());
			$this->User_Authorization($oUser,true);

			$this->Message_AddNotice($this->Lang_Get('ulogin_success_register'));
			Router::Location(Config::Get('path.root.web').'/');	
			return true;			
		}
		
		return parent::EventNotFound(); 		
	}

	/*
	 * Генераия случайного пароля 
	 */
	protected function GeneratePassword()	
	{
		return md5(uniqid(rand(), true));
	}

	/**
	 * Привязка дданных о uLogin к пользовательским данным livestreet
	 */
	private function bind($oUser, $identity) 
	{
		$oUloginNewEntity=LS::Ent('PluginUlogin_ModuleUlogin_EntityUlogin');
		$oUloginNewEntity->setIdentity($identity);
		$oUloginNewEntity->setUserid($oUser->getId());
		return $oUloginNewEntity->save();
	}
	/**
	 * Админпанель для плагина
	 */
	public function EventAdmin() {
		if (!$this->oUserAdmin) {
			return parent::EventNotFound();
		}

		$this->SetTemplateAction('admin');
	
		$oSettings = $this->PluginUlogin_ModuleUlogin_GetSettingsById('one_click_registration');

		if(is_null($oSettings))
			$oneClick_registrationFlag = Config::Get('plugin.ulogin.one_click_registration');
		else
			$oneClick_registrationFlag = $oSettings->getValue();
	
		if(isPost('admin_submit'))
		{
			$one_click_registration = getRequest('registration_type');
	
			if($one_click_registration == 'full_registration')
				$oneClick_registrationFlag = false;
			else
				$oneClick_registrationFlag = true;
	
			$oSettings = $this->PluginUlogin_ModuleUlogin_GetSettingsById('one_click_registration');
		
			if(is_null($oSettings))
			{
				$oSettings = LS::Ent('PluginUlogin_ModuleUlogin_EntitySettings');
				$oSettings->setId('one_click_registration');
			}
	
			$oSettings->setValue((string)(int)$oneClick_registrationFlag);			
			$oSettings->Save();

			$this->Message_AddNotice($this->Lang_Get('ulogin_settings_saved'));
		}
	
		$this->Viewer_Assign('one_click_registration', $oneClick_registrationFlag);
	}

	protected function loadFile($fileUrl)
	{
		$file = file_get_contents($fileUrl);
		
		if($file === False)
			return False;
		
		$fileArray = array('size' => 0, 'name' => 'some_name', 'type' => '', 'tmp_name' => '', 'error' => 0);
		
		$tmp_name = tempnam(Config::Get('sys.cache.dir'), "php");
		
		$handle = fopen($tmp_name, "w");
		$fileSize = fwrite($handle, $file);
		fclose($handle);
		
		if(!$fileSize)
		{
			@unlink($tmp_name);
			return False;
		}
		
		$fileArray['tmp_name'] = $tmp_name;
		$fileArray['size'] = $fileSize;
		
		//$finfo = finfo_open(FILEINFO_MIME_TYPE);
		
		//$fileArray['type'] = finfo_file($finfo, $fileArray['tmp_name']);
		
		//finfo_close($finfo);
		
		return $fileArray;
	}
	
	public function UploadAvatar($aFile,$oUser) {
		if(!is_array($aFile) || !isset($aFile['tmp_name'])) {
			return false;
		}

		$sFileTmp=Config::Get('sys.cache.dir').func_generator();
		if (!rename($aFile['tmp_name'],$sFileTmp)) {
			return false;
		}
		
		$sPath = $this->Image_GetIdDir($oUser->getId());
		$aParams=$this->Image_BuildParams('avatar');

		/**
		 * Срезаем квадрат
		 */
		$oImage = new LiveImage($sFileTmp);
		/**
		 * Если объект изображения не создан,
		 * возвращаем ошибку
		 */
		if($sError=$oImage->get_last_error()) {
			// Вывод сообщения об ошибки, произошедшей при создании объекта изображения
			// $this->Message_AddError($sError,$this->Lang_Get('error'));
			@unlink($sFileTmp);
			return false;
		}

		$oImage = $this->Image_CropSquare($oImage);
		$oImage->set_jpg_quality($aParams['jpg_quality']);
		$oImage->output(null,$sFileTmp);

		if ($sFileAvatar=$this->Image_Resize($sFileTmp,$sPath,'avatar_100x100',Config::Get('view.img_max_width'),Config::Get('view.img_max_height'),100,100,false,$aParams)) {
			$aSize=Config::Get('module.user.avatar_size');
			foreach ($aSize as $iSize) {
				if ($iSize==0) {
					$this->Image_Resize($sFileTmp,$sPath,'avatar',Config::Get('view.img_max_width'),Config::Get('view.img_max_height'),null,null,false,$aParams);
				} else {
					$this->Image_Resize($sFileTmp,$sPath,"avatar_{$iSize}x{$iSize}",Config::Get('view.img_max_width'),Config::Get('view.img_max_height'),$iSize,$iSize,false,$aParams);
				}
			}
			@unlink($sFileTmp);
			/**
			 * Если все нормально, возвращаем расширение загруженного аватара
			 */
			return $this->Image_GetWebPath($sFileAvatar);
		}
		@unlink($sFileTmp);
		/**
		 * В случае ошибки, возвращаем false
		 */
		return false;
	}

	/**
	 * Upload user foto
	 *
	 * @param  array           $aFile
	 * @param  ModuleUser_EntityUser $oUser
	 * @return string
	 */
	public function UploadFoto($aFile,$oUser) {
		if(!is_array($aFile) || !isset($aFile['tmp_name'])) {
			return false;
		}

		$sFileTmp=Config::Get('sys.cache.dir').func_generator();
		if (!rename($aFile['tmp_name'],$sFileTmp)) {
			return false;
		}
		$sDirUpload=$this->Image_GetIdDir($oUser->getId());
		$aParams=$this->Image_BuildParams('foto');

		if ($sFileFoto=$this->Image_Resize($sFileTmp,$sDirUpload,func_generator(6),Config::Get('view.img_max_width'),Config::Get('view.img_max_height'),250,null,true,$aParams)) {
			@unlink($sFileTmp);
			/**
			 * удаляем старое фото
			 */
			$this->User_DeleteFoto($oUser);
			return $this->Image_GetWebPath($sFileFoto);
		}
		@unlink($sFileTmp);
		return false;
	}
}
?>
