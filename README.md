=== uLogin - виджет авторизации через социальные сети ===
=== uLogin is widget for user's authorization using social networks ===
Contributors: uLogin
Donate link: http://ulogin.ru/donate.html
Tags: ulogin, login, social, authorization
Requires at least: 0.5
Tested up to: 0.5.1
Stable tag: 1.7
License: GPL V3

== Description ==
This is ulogin plugin for LiveStreet CMF.
uLogin a service authorization that allows your users to authenticate your site with services such as Google, Twitter, Facebook etc.

uLogin — это инструмент, который позволяет пользователям получить единый доступ к различным Интернет-сервисам без необходимости повторной регистрации,
а владельцам сайтов — получить дополнительный приток клиентов из социальных сетей и популярных порталов (Google, Яндекс, Mail.ru, ВКонтакте, Facebook и др.)

== Installation ==

1. Распакуйте архив с плагином в папку `/livestreet/plugins/`
2. Для активации плагина необходимо зайти в 
   "Настройка сайта" -> "Админка" (внизу экрана) -> "Управление плагинами" и в списке плагинов выбрать "Активировать" напротив uLogin
3. Если при использовании плагина появляются ошибки, то следует исправить следующую строчку в /index.php :
    ini_set('display_errors', 0); на ini_set('display_errors', 1);
   
