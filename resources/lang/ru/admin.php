<?php

$lang['page_title'] = [
	'users_index'			=> 'Пользователи',
	'users_edit'			=> 'Редактирование пользователя',
	'users_add'				=> 'Добавление нового пользователя',
	'dashboard_index'		=> 'Стартовый экран',

	'index_index'			=>  'Dashboard',
	'support_index'			=>  'Техподдержка',
	'support_detail'		=>  'Просмотр тикета',
	'server_index'			=>  'Переменные сервера',
	'money_index'			=>  'Денежные транзакции',
	'money_add'				=>  'Начислить кредиты',
	'planets_index'			=>  'Список планет',
	'moons_index'			=>  'Список лун',
	'fleets_index'			=>  'Флоты в полёте',
	'alliances_index'		=>  'Список альянсов',
	'messages_index'		=>  'Список сообщений',
	'messageall_index'		=>  'Рассылка сообщений',
	'content_index'			=>  'Список контента',

	'manager_data' 			=> 'Просмотр игрового профиля',
	'manager_ip' 			=> 'Поиск по IP',
];

$lang['category'] = array
(
	'overview' 		=> Array('Обзор', 'home', 1),
	'support' 		=> Array('Техподдержка', 'bolt', 1),
	'server' 		=> Array('Информация', 'dashboard', 3),
	'load' 			=> Array('Загрузка', 'signal', 3),
	'money' 		=> Array('Финансы', 'rub', 3, Array
	(
			'add' => Array('Начислить кредиты'),
			'transactions' => Array('Транзакции')
	)),
	'settings' 		=> Array('Настройки', 'cogs', 3),
	'userlist' 		=> Array('Список игроков', 'user', 3),
	'manager' 		=> Array('Редактор', 'edit', 1, Array
	(
			'ip_search' => Array('Поиск по IP'),
			'usr_data' => Array('Статистика'),
			'usr_level' => Array('Смена прав')
	)),
	'planetlist' 	=> Array('Список планет', 'globe', 3),
	'activeplanet' 	=> Array('Активные планеты', 'clock-o', 2),
	'moonlist' 		=> Array('Список лун', 'star', 2),
	'flyfleettable' => Array('Флоты в полёте', 'plane', 3),
	'alliancelist' 	=> Array('Список альянсов', 'group', 2),
	'banned' 		=> Array('Заблокировать', 'ban', 1),
	'unbanned' 		=> Array('Разблокировать', 'legal', 2),
	'md5changepass' => Array('Сменить пароль', 'key', 3),
	'email' 		=> Array('Сменить Email', 'envelope', 3),
	'messagelist' 	=> Array('Сообщения', 'inbox', 3),
	'messall' 		=> Array('Рассылка', 'bullhorn', 1),
	'errors' 		=> Array('Список ошибок', 'bug', 2)
);

$lang['adm_done']               = "Обновлено";
$lang['adm_stat_title']         = "Статистика";
$lang['adm_cleaner_title']      = "Обновление очереди построек";
$lang['adm_cleaned']            = "Anzahl der der gestrichenen: ";
$lang['Fix']                    = "Aktualisieren";
$lang['Welcome_to_Fix_section'] = "Willkommen bei der Abteilung Update";
$lang['There_is_not_need_fix']  = "Das Update wurde durchgef&uuml;rt!";
$lang['Fix_welldone']           = "Die Aktualisierung konte nicht durchgef&uuml;rt werden.";

$lang['adm_ov_title'] = "Обоз игроков";
$lang['adm_ov_infos'] = "Информация";
$lang['adm_ov_yourv'] = "Версия игры";
$lang['adm_ov_lastv'] = "Последняя версия";
$lang['adm_ov_here']  = "здесь";
$lang['adm_ov_onlin'] = "Online";
$lang['adm_ov_ally']  = "Альянс";
$lang['adm_ov_point'] = "Очки";
$lang['adm_ov_activ'] = "В сети";
$lang['adm_ov_count'] = "Игроков в сети";
$lang['adm_ov_wrtpm'] = "Nachricht senden";
$lang['adm_ov_altpm'] = "PN";

$lang['adm_ul_title'] = "Список игроков";
$lang['adm_ul_ttle2'] = "Зарегистрированные игроки";
$lang['adm_ul_id']    = "ID";
$lang['adm_ul_name']  = "Логин игрока";
$lang['adm_ul_mail']  = "E-Mail";
$lang['adm_ul_adip']  = "IP";
$lang['adm_ul_regd']  = "Регистрация";
$lang['adm_ul_lconn'] = "Последний вход";
$lang['adm_ul_bana']  = "Блок";
$lang['adm_ul_detai'] = "Инфо";
$lang['adm_ul_actio'] = "Действия";
$lang['adm_ul_playe'] = " игрока";
$lang['adm_ul_yes']   = "Да";
$lang['adm_ul_no']    = "Нет";

$lang['adm_pl_title'] = "Активные планеты";
$lang['adm_pl_activ'] = "Активные планеты";
$lang['adm_pl_name']  = "Название планеты";
$lang['adm_pl_posit'] = "Координаты";
$lang['adm_pl_point'] = "Очки";
$lang['adm_pl_since'] = "В сети";
$lang['adm_pl_they']  = "В игре";
$lang['adm_pl_apla']  = "активные планеты";

$lang['adm_am_plid']  = "ID планеты";
$lang['adm_am_done']  = "Hinzuf&uuml;gen OK";
$lang['adm_am_ttle']  = "Добавить ресурсов";
$lang['adm_am_add']   = "Добавить";
$lang['adm_am_form']  = "Форма ввода данных";

$lang['adm_bn_ttle']  = "Банилка";
$lang['adm_bn_plto']  = "Игрок";
$lang['adm_bn_name']  = "Имя";
$lang['adm_bn_reas']  = "Причина";
$lang['adm_bn_time']  = "Время бана";
$lang['adm_bn_days']  = "Дни";
$lang['adm_bn_hour']  = "Часы";
$lang['adm_bn_mins']  = "Минуты";
$lang['adm_bn_secs']  = "Секунды";
$lang['adm_bn_bnbt']  = "Забанить";
$lang['adm_bn_thpl']  = "Игрок";
$lang['adm_bn_isbn']  = "был заблокирован!";

$lang['adm_rz_ttle']  = "Обнуление Вселенной";
$lang['adm_rz_done']  = "User &uuml;bertragen";
$lang['adm_rz_conf']  = "Подтверждение";
$lang['adm_rz_text']  = "Обнуление обнуляет всё нах!!!!!!!!!!!!!!!!";
$lang['adm_rz_doit']  = "Обнулить";

$lang['adm_ch_ttle']  = "Администрирование чата";
$lang['adm_ch_list']  = "Список сообщений";
$lang['adm_ch_clear'] = "Alles L&uuml;schen";
$lang['adm_ch_idmsg'] = "ID";
$lang['adm_ch_delet'] = "Сообщение";
$lang['adm_ch_play']  = "Игрок";
$lang['adm_ch_time']  = "Дата";
$lang['adm_ch_nbs']   = "сообщений.";

$lang['adm_er_ttle']  = "Ошибки игры";
$lang['adm_er_list']  = "Список ошибок скрипта";
$lang['adm_er_clear'] = "Очистить списк";
$lang['adm_er_idmsg'] = "ID";
$lang['adm_er_type']  = "Тип";
$lang['adm_er_play']  = "ID игрока";
$lang['adm_er_time']  = "Дата";
$lang['adm_er_nbs']   = "ошибок...";

$lang['Id'] = "ID планеты";
$lang['cle'] = "Легкий истребитель";
$lang['clourd'] = "Тяжелый истребитель";
$lang['pt'] = "Малый транспорт";
$lang['gt'] = "Большой транспорт";
$lang['cruise'] = "Крейсер";
$lang['vb'] = "Линкор";
$lang['colo'] = "Колонизатор";
$lang['rc'] = "Переработчик";
$lang['spy'] = "Шпионский зонд";
$lang['bomb'] = "Бомбардировщик";
$lang['solar'] = "Солнечный спутник";
$lang['des'] = "Уничтожитель";
$lang['rip'] = "Звезда смерти";
$lang['traq'] = "Линейный крейсер";

$lang['add_ship_form']    = "Форма ввода данных";
$lang['add_ship_ttle']    = "Добавление флота";
$lang['ship_typ']         = "Тип корабля";
$lang['hinz']             = "Количество";
$lang['del']              = "L&ouml;schen";
$lang['nr']               = "№";
$lang['kt']               = "Малый транспорт";
$lang['gt']               = "Большой транспорт";
$lang['lj']               = "Лёгкий истребитель";
$lang['sj']               = "Тяжёлый истребитель";
$lang['kz']               = "Крейсер";
$lang['ss']               = "Линкор";
$lang['ks']               = "Колонизатор";
$lang['tf']               = "Переработчик";
$lang['sp']               = "Шпионский зонд";
$lang['bo']               = "Бомбардировщик";
$lang['so']               = "Солнечный спутник";
$lang['zt']               = "Уничтожитель";
$lang['rp']               = "Звезда смерти";
$lang['sk']               = "Линейный крейсер";

$lang['adm_delship1'] = "Schiffe L&ouml;schen";
$lang['adm_delship2'] = "Schiffe wurden erfolgreich gel&ouml;scht";
$lang['adm_addship1'] = "Schiffe hinzuf&uuml;gen";
$lang['adm_addship2'] = "Schiffe wurden erfolgreich hinzugef&uuml;gt";
$lang['adm_delbuilding1'] = "Geb&auml;ude verringern";
$lang['adm_delbuilding2'] = "Geb&auml;ude wurden erfolgreich verringert";
$lang['adm_addbuilding1'] = "Geb&auml;ude erweitern";
$lang['adm_addbuilding2'] = "Geb&auml;ude wurden erfolgreich erweitert";
$lang['adm_delresearch1'] = "Forschungen verringern";
$lang['adm_delresearch2'] = "Forschungen wurden erfolgreich verringert";
$lang['adm_addresearch1'] = "Forschungen erweitern";
$lang['adm_addresearch2'] = "Forschungen wurden erfolgreich erweitert";
$lang['adm_delmoney1'] = "Удаление ресурсов";
$lang['adm_delmoney2'] = "Rohstoffe erfolgreich abgezogen";
$lang['adm_deldef1'] = "Verteidigungen L&ouml;schen";
$lang['adm_deldef2'] = "Verteidigungen wurden erfolgreich gel&ouml;scht";
$lang['adm_adddef1'] = "Verteidigungen hinzuf&uuml;gen";
$lang['adm_adddef2'] = "Verteidigungen wurden erfolgreich hinzugef&uuml;gt";
$lang['adm_shipdel1'] = "Schiffe L&ouml;schen";
$lang['adm_shipdel2'] = "Formular f&uuml;r Schiffe L&ouml;schen";
$lang['adm_buildingadd1'] = "Geb&auml;ude hinzuf&uuml;gen";
$lang['adm_buildingadd2'] = "Форма ввода данных";
$lang['adm_moneydel1'] = "Удаление ресурсов";
$lang['adm_moneydel2'] = "Форма ввода данных";
$lang['adm_buildingdel1'] = "Удаление построек";
$lang['adm_buildingdel2'] = "Форма ввода данных";
$lang['adm_researchadd1'] = "Добавление исследований";
$lang['adm_researchadd2'] = "Форма ввода данных";
$lang['adm_researchdel1'] = "Удаление исследований";
$lang['adm_researchdel2'] = "Форма ввода данных";
$lang['adm_defadd1'] = "Добавление обороны";
$lang['adm_defadd2'] = "Форма добавления обороны";
$lang['adm_defdel1'] = "Удаление обороны";
$lang['adm_defdel2'] = "Форма для удаления обороны";

return $lang;