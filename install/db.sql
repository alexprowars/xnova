SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `bot_callback_query` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique identifier for this query.',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'Sender',
  `message` text COMMENT 'Message',
  `inline_message_id` char(255) DEFAULT NULL COMMENT 'Identifier of the message sent via the bot in inline mode, that originated the query',
  `data` char(255) NOT NULL DEFAULT '' COMMENT 'Data associated with the callback button.',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `bot_chat` (
  `id` bigint(20) NOT NULL COMMENT 'Unique user or chat identifier',
  `type` enum('private','group','supergroup','channel') NOT NULL COMMENT 'chat type private, group, supergroup or channel',
  `title` char(255) DEFAULT '' COMMENT 'chat title null if case of single chat with the bot',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date update',
  `old_id` bigint(20) DEFAULT NULL COMMENT 'Unique chat identifieri this is filled when a chat is converted to a superchat'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `bot_chosen_inline_query` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique identifier for chosen query.',
  `result_id` char(255) NOT NULL DEFAULT '' COMMENT 'Id of the chosen result',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'Sender',
  `location` char(255) DEFAULT NULL COMMENT 'Location object, senders''s location.',
  `inline_message_id` char(255) DEFAULT NULL COMMENT 'Identifier of the message sent via the bot in inline mode, that originated the query',
  `query` char(255) NOT NULL DEFAULT '' COMMENT 'Text of the query',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `bot_conversation` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Row unique id',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'User id',
  `chat_id` bigint(20) DEFAULT NULL COMMENT 'Telegram chat_id can be a the user id or the chat id ',
  `status` enum('active','cancelled','stopped') NOT NULL DEFAULT 'active' COMMENT 'active conversation is active, cancelled conversation has been truncated before end, stopped conversation has end',
  `command` varchar(160) DEFAULT '' COMMENT 'Default Command to execute',
  `notes` varchar(1000) DEFAULT 'NULL' COMMENT 'Data stored from command',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date update'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `bot_inline_query` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique identifier for this query.',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'Sender',
  `location` char(255) DEFAULT NULL COMMENT 'Location of the sender',
  `query` char(255) NOT NULL DEFAULT '' COMMENT 'Text of the query',
  `offset` char(255) NOT NULL DEFAULT '' COMMENT 'Offset of the result',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `bot_message` (
  `chat_id` bigint(20) NOT NULL COMMENT 'Chat identifier.',
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique message identifier',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'User identifier',
  `date` timestamp NULL DEFAULT NULL COMMENT 'Date the message was sent in timestamp format',
  `forward_from` bigint(20) DEFAULT NULL COMMENT 'User id. For forwarded messages, sender of the original message',
  `forward_from_chat` bigint(20) DEFAULT NULL COMMENT 'Chat id. For forwarded messages from channel',
  `forward_date` timestamp NULL DEFAULT NULL COMMENT 'For forwarded messages, date the original message was sent in Unix time',
  `reply_to_chat` bigint(20) DEFAULT NULL COMMENT 'Chat identifier.',
  `reply_to_message` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Message is a reply to another message.',
  `text` text COMMENT 'For text messages, the actual UTF-8 text of the message max message length 4096 char utf8',
  `entities` text COMMENT 'For text messages, special entities like usernames, URLs, bot commands, etc. that appear in the text',
  `audio` text COMMENT 'Audio object. Message is an audio file, information about the file',
  `document` text COMMENT 'Document object. Message is a general file, information about the file',
  `photo` text COMMENT 'Array of PhotoSize objects. Message is a photo, available sizes of the photo',
  `sticker` text COMMENT 'Sticker object. Message is a sticker, information about the sticker',
  `video` text COMMENT 'Video object. Message is a video, information about the video',
  `voice` text COMMENT 'Voice Object. Message is a Voice, information about the Voice',
  `caption` text COMMENT 'For message with caption, the actual UTF-8 text of the caption',
  `contact` text COMMENT 'Contact object. Message is a shared contact, information about the contact',
  `location` text COMMENT 'Location object. Message is a shared location, information about the location',
  `venue` text COMMENT 'Venue object. Message is a Venue, information about the Venue',
  `new_chat_member` bigint(20) DEFAULT NULL COMMENT 'User id. A new member was added to the group, information about them (this member may be bot itself)',
  `left_chat_member` bigint(20) DEFAULT NULL COMMENT 'User id. A member was removed from the group, information about them (this member may be bot itself)',
  `new_chat_title` char(255) DEFAULT NULL COMMENT 'A group title was changed to this value',
  `new_chat_photo` text COMMENT 'Array of PhotoSize objects. A group photo was change to this value',
  `delete_chat_photo` tinyint(1) DEFAULT '0' COMMENT 'Informs that the group photo was deleted',
  `group_chat_created` tinyint(1) DEFAULT '0' COMMENT 'Informs that the group has been created',
  `supergroup_chat_created` tinyint(1) DEFAULT '0' COMMENT 'Informs that the supergroup has been created',
  `channel_chat_created` tinyint(1) DEFAULT '0' COMMENT 'Informs that the channel chat has been created',
  `migrate_from_chat_id` bigint(20) DEFAULT NULL COMMENT 'Migrate from chat identifier.',
  `migrate_to_chat_id` bigint(20) DEFAULT NULL COMMENT 'Migrate to chat identifier.',
  `pinned_message` text COMMENT 'Pinned message, Message object.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `bot_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `code` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `bot_telegram_update` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'The update''s unique identifier.',
  `chat_id` bigint(20) DEFAULT NULL COMMENT 'Chat identifier.',
  `message_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Unique message identifier',
  `inline_query_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'The inline query unique identifier.',
  `chosen_inline_query_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'The chosen query unique identifier.',
  `callback_query_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'The callback query unique identifier.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `bot_user` (
  `id` bigint(20) NOT NULL COMMENT 'Unique user identifier',
  `first_name` char(255) NOT NULL DEFAULT '' COMMENT 'User first name',
  `last_name` char(255) DEFAULT NULL COMMENT 'User last name',
  `username` char(255) DEFAULT NULL COMMENT 'User username',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date update'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `bot_user_chat` (
  `user_id` bigint(20) NOT NULL COMMENT 'Unique user identifier',
  `chat_id` bigint(20) NOT NULL COMMENT 'Unique user or chat identifier'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_access` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL DEFAULT '',
  `module` varchar(50) NOT NULL DEFAULT 'core'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `game_access` (`id`, `code`, `module`) VALUES
(1, 'access', 'admin'),
(2, 'access', 'xnova'),
(3, 'controller_read::modules', 'admin'),
(4, 'controller_write::modules', 'admin'),
(5, 'controller_write::groups', 'admin'),
(6, 'controller_read::groups', 'admin'),
(7, 'controller_read::users', 'admin'),
(8, 'controller_write::users', 'admin'),
(9, 'controller_write::messages', 'admin'),
(10, 'controller_read::messages', 'admin'),
(11, 'controller_read::content', 'admin'),
(12, 'controller_write::content', 'admin'),
(13, 'controller_read::support', 'admin'),
(14, 'controller_write::support', 'admin'),
(15, 'controller_read::server', 'admin'),
(16, 'controller_write::server', 'admin'),
(17, 'controller_read::money', 'admin'),
(18, 'controller_write::money', 'admin'),
(23, 'controller_read::manager', 'admin'),
(24, 'controller_write::manager', 'admin'),
(25, 'controller_read::planets', 'admin'),
(26, 'controller_write::planets', 'admin'),
(27, 'controller_read::activeplanet', 'admin'),
(28, 'controller_write::activeplanet', 'admin'),
(29, 'controller_read::moons', 'admin'),
(30, 'controller_write::moons', 'admin'),
(31, 'controller_read::flyfleets', 'admin'),
(32, 'controller_write::flyfleets', 'admin'),
(33, 'controller_read::alliances', 'admin'),
(34, 'controller_write::alliances', 'admin'),
(41, 'controller_read::email', 'admin'),
(42, 'controller_write::email', 'admin'),
(43, 'controller_read::messages', 'admin'),
(44, 'controller_write::messages', 'admin'),
(45, 'controller_read::messageall', 'admin'),
(46, 'controller_write::messageall', 'admin');

CREATE TABLE `game_aks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `fleet_id` int(32) DEFAULT NULL,
  `galaxy` int(2) DEFAULT NULL,
  `system` int(4) DEFAULT NULL,
  `planet` int(2) DEFAULT NULL,
  `planet_type` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_aks_user` (
  `id` int(11) NOT NULL,
  `aks_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_alliance` (
  `id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `tag` varchar(8) NOT NULL,
  `owner` int(11) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `description` text,
  `web` varchar(255) DEFAULT NULL,
  `text` text,
  `image` int(11) NOT NULL DEFAULT '0',
  `request` text,
  `request_notallow` tinyint(1) NOT NULL DEFAULT '0',
  `owner_range` varchar(32) DEFAULT NULL,
  `ranks` text,
  `members` tinyint(3) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_alliance_chat` (
  `id` int(11) UNSIGNED NOT NULL,
  `ally_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `user` varchar(50) NOT NULL DEFAULT '',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  `timestamp` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_alliance_diplomacy` (
  `id` int(11) NOT NULL,
  `a_id` int(11) NOT NULL DEFAULT '0',
  `d_id` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `primary` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_alliance_members` (
  `a_id` int(11) NOT NULL DEFAULT '0',
  `u_id` int(11) NOT NULL DEFAULT '0',
  `rank` tinyint(2) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_alliance_requests` (
  `a_id` int(11) NOT NULL DEFAULT '0',
  `u_id` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `request` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_artifacts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `element_id` smallint(6) NOT NULL DEFAULT '0',
  `level` tinyint(1) NOT NULL DEFAULT '1',
  `expired` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_banned` (
  `id` int(11) NOT NULL,
  `who` int(11) NOT NULL DEFAULT '0',
  `theme` text NOT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  `longer` int(11) NOT NULL DEFAULT '0',
  `author` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_bots_users` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `last_update` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_buddy` (
  `id` bigint(11) NOT NULL,
  `sender` int(11) NOT NULL DEFAULT '0',
  `owner` int(11) NOT NULL DEFAULT '0',
  `ignor` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(3) NOT NULL DEFAULT '0',
  `text` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_content` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL DEFAULT '',
  `alias` varchar(100) NOT NULL DEFAULT '',
  `html` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `game_content` (`id`, `title`, `alias`, `html`) VALUES
(1, 'Законы игры', 'agb', '<table class=\"table\"><tr><td class=\"c\" style=\"text-align:left;\">Правила игры</td></tr>	<tr><th style=\"text-align:left;\">Эти правила действительны и являются обязательными к исполнению в игре.<br>Правила предназначены для того, чтобы обеспечить всем игрокам честную игру и доставить им удовольствие от игры.<br>За соблюдением правил следит администрация.<br><br>Основные термины правил:<br>«Пользователь» (далее игрок) - любое физическое лицо, зарегистрированное на Проекте и использующее его в некоммерческих личных целях, а также иное лицо получившее доступ к проекту.<br>«Соглашение» - Правила использования Проекта \"XNova\" и определение ответственности между Администрацией Проекта и Пользователем.<br>«Администрация» - группа лиц, наделенных необходимыми для развития и поддержки проекта правами.<br>«Проект» - бесплатная космическая онлайн стратегия XNova.	</th></tr><tr><td class=\"c\" style=\"text-align:left;\">1. Аккаунты</td></tr><tr><th style=\"text-align:left;\">1.1 Владение аккаунтом<br>Одним игровым аккаунтом разрешено играть только одному человеку. Общая игра нескольких людей одним аккаунтом и управление чужим аккаунтом - запрещены. См. также п. 1.2. Запрещено играть несколькими аккаунтами в одной Вселенной. В одной Вселенной разрешено иметь лишь один аккаунт. Регистрация и создание новых аккаунтов в рамках одной Вселенной запрещено.<br>Владельцем аккаунта считается обладатель постоянного электронного адреса, указанного в профиле. В сомнительных случаях оператор вправе требовать от игрока вести переписку только с этого адреса.<br><br>1.2 Уход за чужим аккаунтом (ситтинг)<br>Разрешён недолгосрочный уход за чужим аккаунтом один раз в неделю на протяжении 24 часов (от первой до последней активности на данном аккаунте), при котором запрещено использовать сенсорную фалангу, межпланетные ракеты, посылать флоты с заданием «атаковать», «объединить» и «шпионаж». C заданием «переработать» можно посылать флот только в сейв, миссия «транспорт» разрешена только для перевозки ресурсов с колоний, разрешены любые запуски и остановки строительств и исследований.<br>Одновременно уход разрешён только за одним аккаунтом помимо своего собственного.<br>По истечению срока ухода за аккаунтом, Хозяин аккаунта не может отдавать аккаунт на повторный уход в течение 7 дней. Также игрок, который присматривал за аккаунтом, не может присматривать в течение 7 дней за этим либо другим аккаунтом.<br>При нарушении: бессрочная блокировка (минимум 35 дней) аккаунтов с их последующим удалением.<br><br>1.3 Обмен аккаунтами<br>Обмен аккаунтами считается действительным только в том случае, когда оба игрока дали письменное соглашение на форуме. Возврат или восстановление аккаунтов, обменянных без переписки на форуме, невозможен.<br><br>Продажа, либо попытка продажи аккаунта в проекте наказывается блокированием продаваемого аккаунта.</th></tr><tr><td class=\"c\" style=\"text-align:left;\">2. Игра с одного компьютера / IP </td></tr><tr><th style=\"text-align:left;\">Играть нескольким игрокам через одно интернет-соединение, например из локальной сети, интернет-кафе, компьютерного клуба, одного компьютера - разрешено.<br><br>При игре с одного компьютера / IP игроки обязаны немедленно сообщить об этом оператору. Оператор, в свою очередь, обязуется внести в профиле игроков соответствующую заметку. Также игроки обязаны соблюдать правило о наблюдении за аккаунтом (ситинг). <br>Такие игроки не смогут обмениваться между собой ресурсами и посылать друг другу флоты. Внимание со стороны администрации к ним удвоенное. При постоянной игре с одного IP в одной вселенной запрещено любое пересечение между аккаунтами.<br>При нарушении: бан за мультоводство</th></tr><tr><td class=\"c\" style=\"text-align:left;\">3. Прокачка</td></tr><tr><th style=\"text-align:left;\">Под прокачкой подразумеваются все односторонние пересылки ресурсов от более слабого по очкам игрока к более сильному (даже внутри альянса), а также торговля по курсу меньше официально разрешённого минимума и больше официально разрешенного максимума. Это также относится к атакам по договорённости, в том числе для возникновения полей обломков, исключение - атаки по договорённости для создания луны.<br>Не разрешается сохранять или использовать ресурсы, полученные от игрока с более низким уровнем аккаунта, если они попали к Вам случайно. Немедленно сообщите об этом игровому администратору и отошлите ресурсы назад на одну из планет этого игрока.<br>При первом нарушении - блокировка аккаунтов с РО на 3 дня.<br>При повторном нарушении – блокировка аккаунтов с РО на 7 дней.<br>Третье нарушение – бессрочная блокировка аккаунтов с РО с их последующим удалением.<br><br>3.1 Создание луны по договорённости<br><br>Допускаются атаки по договоренности для создания луны.<br>При попытках образования луны по договорённости, вышестоящий в рейтинге игрок не должен получить прибыль. Компенсация затрат дейтерия запрещена. На пересылку ресурсов после попыток луноделия даётся 3 дня.<br>Нарушение приравнивается к прокачке.<br><br>3.2 Помощь в восстановлении потерь флота<br><br>Игрок, потерявший свой флот, имеет право на помощь в его частичном восстановлении от игроков любого уровня.<br>Срок помощи ограничивается 7-ю днями с момента обращения (но не позже 5 дней после боя), размер помощи - не более 50% от стоимости потерянного флота.<br>При достижении предела 50%, нижестоящие в рейтинге игроки не могут помогать в восстановлении флота.<br>Нарушение приравнивается к прокачке.<br><br>3.3 Помощь члену альянса в сборе обломков переработчиками<br>Внутри альянса разрешено одалживать переработчики для сбора поля обломков, получившегося в результате атаки. В этом случае игрок, который атаковал, отсылает оператору лог боя, имена игрока(ов) (кто помогал собирать обломки), количество собранных им(и) ресурсов, не позднее чем 3 суток с момента атаки, и обязательно до отправки ему этих обломков. В свою очередь, игрок(и) собирающие обломки в праве взять часть ресурсов себе (для восполнения расходов на отправку переработчиков и транспортников в результате этой операции, по одному из официальных курсов).<br><br>3.4 Вымогательство ресурсов<br>Вымогать ресурсы за соглашения о ненападении запрещено. В случае вымогательства сообщите вымогающему игроку о том, что это запрещено и сошлитесь на этот пункт правил. При отсутствии реакции и продолжении вымогательства оповестите соответствующего оператора. Вы можете отослать ресурсы вымогающему, если Вашему аккаунту грозит опасность с его стороны, и тогда вымогающий будет заблокирован за прокачку, но только при условии, что Вы предупредили оператора. <br><br>3.5 Заём<br>Заем ресурсов под проценты запрещён.<br><br>3.6 Прощальные бои.<br>Поле лома образовавшееся в результате договорного боя, независимо от того, кто собрал лом, должно остаться на участвовавших в бое аккаунтах, в соответствии с потерями.<br><br>3.7. Особый случай.<br>В спорных случаях решение о факте прокачке принимается оператором. В случае, если оператор решает, что имела место прокачка, ресурсы должны быть возвращены на аккаунты ниже в рейтинге чем те, с которых производилась прокачка.</th></tr><tr><td class=\"c\" style=\"text-align:left;\">4. Торговля</td></tr><tr><th style=\"text-align:left;\">Официальный курс для торговли (металл:кристалл:дейтерий) - 4:2:1 (за 1 ед. дейтерия может даваться либо 2 ед. кристалла, либо 4 ед. металла, за 1 ед. кристалла может даваться 2 ед. металла). <br>Максимальный срок возврата ресурсов – 3 дня. <br><br>Торговля ресурсами и вообще любые сделки за реальные деньги запрещены, поэтому прежде, чем тратить свои деньги, подумайте, готовы ли Вы их потерять в случае блокировки. <br><br>Вести торговлю и заёмы по курсу, нарушающему настоящие правила, запрещено. <br>Нарушения приравниваются к нарушению правила о прокачке.</th></tr><tr><td class=\"c\" style=\"text-align:left;\">5. Башинг</td></tr><tr><th style=\"text-align:left;\">5.1 Определение<br>Под башингом понимается более трёх атак одного игрока на одну планету за промежуток времени равный 24 часам, Луна также попадает под правило о башинге.<br>Время между атаками роли не играет (т.е. все три атаки можно послать одну за другой).<br>Атаки на неактивных игроков учитываются (т.к. они в любой момент могут потом стать активными и подать жалобу).<br>Ракетные атаки не учитываются.<br>Отозванная атака считается совершённой.<br><br>5.1 Исключением в правиле о башинге является война.<br>Правило о башинге не распространяется на игроков из альянсов, ведущих войну.<br>Для объявления войны необходимо отправить и подтвердить заявку о войне в разделе дипломатии альянса. Война может быть объявлена только альянсом альянсу.<br>Правило о башинге перестаёт действовать только при подтверждении этой заявки другим альянсом.<br><br>Правило о башинге записано в программый код игры и поэтому не контролируется операторами. <br>Но так как многие обходят правило башинга поэтому теперь по жалобе игрока на которого будет применяться этот обход те кто его обходят будут забанены при первом разе 1 день с Ро при повторном нарушении до 3 дней с РО</th></tr><tr><td class=\"c\" style=\"text-align:left;\">6. Использование багов</td></tr><tr><th style=\"text-align:left;\">Баги и/или ошибки в программировании игры использовать запрещено.<br>Об обнаруженных ошибках необходимо как можно быстрее сообщить на форуме в разделе ошибок или через e-mail оператору вселенной.<br>При нарушении: бессрочный блок (минимум 35 дней) с последующим удалением аккаунта.</th></tr><tr><td class=\"c\" style=\"text-align:left;\">7. Вмешательство в игровую технику</td></tr><tr><th style=\"text-align:left;\">Под вмешательством в игровую технику понимается использование любых сторонних программ и механизмов, дающих или могущих дать преимущество в игре или ведущих завышенному трафику.<br>В частности запрещены автоматические и полуавтоматические скрипты, которые выполняют запросы в базы данных или приводят в действие игровые механизмы.<br>При обнаружении фактов вмешательства в игровую технику у более, чем одного члена одного альянса, оператор вправе заблокировать весь альянс до выяснения обстоятельств. Поэтому подумайте о своих коллегах, прежде чем использовать боты и скрипты.<br>При нарушении: исключение из XNova.</th></tr><tr><td class=\"c\" style=\"text-align:left;\">8. Общение между игроками</td></tr><tr><th style=\"text-align:left;\">8.1 Угрозы и вымогательство в реальной жизни<br>Под угрозой в реальной жизни расценивается сообщения, однозначно сигнализирующие об угрозе жизни и/или здоровью игрока или его близких.<br>Также категорически запрещено вымогательство, относящееся к реальной жизни.<br>При нарушении: исключение из одной или всех вселенных XNova.<br><br>8.2 Оскорбления<br>Под оскорблениями понимается прямое грубое оскорбление игрока либо его близких, а также мат в игровых сообщениях.<br>Если оскорбление сделано не на русском языке, то за оскорбление оно принимается если как минимум один из членов команды может подтвердить его оскорбительный характер. Относительно сообщений не на русском языке см. также п. 8.3.<br>Примечание: ввиду разнообразия способов написания и лингвистических приёмов мы не приводим конкретный список того, что можно говорить, а что нельзя. Право последнего голоса при решении спорных моментов остаётся за оператором, поэтому крайне рекомендуется не рисковать и общаться друг с другом культурно.<br>При нарушении: в зависимости от тяжести оскорбления на усмотрение оператора, но минимум 1 день без РО. При рецидиве срок блока увеличивается – 3 дня, 7 дней, 14 дней, пожизненно.<br><br>8.3 Некорректные названия<br>У игроков есть возможность давать некоторым игровым единицам (название аккаунта, планет, альянса) собственные названия.<br>Названия, содержащие оскорбительные, матерные слова, а также научные термины, могущие быть интерпретированные как оскорбления, наказываются 3 днями без РО за каждое название.<br>См. также п. 8.6.<br><br>8.4 Язык общения<br>Официальный язык общения в XNova – русский.<br>Для личного общения игроки могут использовать любой другой язык, но если сообщение не на русском языке послано любому другому игроку, то он вправе пожаловаться на него оператору. Исключение с оставляют сообщения, в которых спрашивается, владеет ли игрок тем или иным языком.<br>При нарушении: простое предупреждение, при злоупотреблении может быть бан на 1 день с РО.<br>Если сообщение имело оскорбительный характер, что может подтвердить как минимум один член команды, то к этому сроку прибавляется соответствующий срок за оскорбление.<br>Нерусскоязычным игрокам разрешено создавать свои альянсы, текст альянса также может быть на любом языке при условии, что он содержит точный и полный русский перевод.<br>При нарушении: бан основателя альянса на 3 дня с РО и удаление альянса.<br>Письма операторам на других языках будут принимаются к рассмотрению только в том случае, если оператор согласен вести переписку на этом языке.<br><br>8.5 Спам и флуд<br>Под спамом понимаются сообщения, носящие рекламный характер и не имеющие отношения к проекту, отправленные игрокам, не выразившим желание её получать. Приглашения в альянс спамом не считаются.<br>Под флудом понимаются часто повторяющиеся сообщения, не относящиеся по смыслу к содержанию предыдущего общения.<br>При нарушении:<br>1-й случай – 1 день без РО<br>2-й случай – 3 дня без РО<br>3-й случай – бессрочная блокировка (минимум 35 дней) с последующим удалением аккаунта.<br><br>8.6 Запрещённые материалы<br>Использование любых материалов (текст, графика, ссылки, название аккаунтов, планет, альянсов), содержащих порнографию, носящие дискриминирующий характер по национальном, расовым, религиозным или половым признакам, пропагандирующие насилие наказывается исключением из XNova.<br>К таким материалам также относятся названия игровых единиц, содержащие имена людей или названия организаций, пропагандирующих либо причастных к вышеуказанным темам (например, имена деятелей Третьего Рейха, лиц, причастных к массовым репрессиям в СССР, названия экстремистских и террористических организаций и т.п.).<br>Использование в качестве логотипов альянсов графических изображений, подпадающих под вышеуказанные категории, в первоначальном либо изменённом виде, также запрещается. Если Вы не уверены в том, как будет расценен Ваш логотип, то согласуйте своё решение с оператором соответствующей вселенной.<br><br>8.7 Прочее<br>Крайне нежелательны любые материалы, связанные с политикой, в частности касающиеся отношений между странами. Не забывайте, что у нас играют представители многих государств.<br>Также крайне нежелательно использование транслита, в интернете достаточно конверторов.<br>Для начала игрок получает простое предупреждение, но в случае рецидива игрок может быть заблокирован на усмотрение оператора минимум на 1 день без РО.</th></tr><tr><td class=\"c\" style=\"text-align:left;\">9. Общение с членами команды</td></tr><tr><th style=\"text-align:left;\">К членам команды относятся игровые операторы, модераторы форума, администратор игры и форума, а также руководство проекта<br><br>9.1 Отмена принятых мер<br>Меры, принятые одним оператором по отношению к игроку, могут быть отменены только им самим. Другой оператор может отменить их только после согласования с первым оператором, либо в одностороннем порядке если имело место доказанное превышение полномочий или халатность со стороны первого оператора или если имело место недоразумение и соответствующий оператор покинул проект либо отсутствует более 3-х суток.<br><br>9.2 Блокировка<br>Мы прилагаем все усилия к тому, чтобы случаи нарушения прав перед блокировкой были идентифицированы на 100%, но не исключены случаи ошибок со стороны оператора либо недоразумений.<br>Перед блокировкой игроки не предупреждаются, если обратное не предусмотрено соответствующим правилом.<br>При попытке войти в аккаунт после блокировки игроки получают ссылку на столб позора, где можно увидеть кем, когда, за что и на сколько произведена блокировка. <br>Отдельно обращается внимание на то, что там указано имя и адрес заблокировавшего оператора, и все сообщения должны направляться именно на этот адрес. <br>Жалобы, отправленные не по адресу к рассмотрению не принимаются. Операторы могут, но не обязаны, сами перенаправлять такие жалобы по адресу.<br>Владельцы платных аккаунтов предупреждаются отдельно, что оплата предоставляет только заранее оговоренные услуги и не освобождает от соблюдения правил и основных положений игры.<br><br>9.3 Содержание сообщений<br>В тех случаях, если игрок желает каких-либо действий со стороны оператора, то сообщение должно содержать соответствующие данные (напр., по вопросам касательно своего аккаунта – название аккаунта и номер вселенной, при пропаже флота – координаты планет и состав флота, при башинге – имя нападающего и т.п.).<br>Сообщение должно иметь заголовок, однозначно определяющий содержание сообщения. В тех случаях, когда заголовок выставляется автоматически, то менять его нельзя.<br>Все сообщения должны содержать всю предыдущую переписку, т.к. оператор не может держать в голове содержание всех приходящих ему писем.<br>Сообщения, не соответствующие этим требованиям, будут рассматриваться второстепенно либо не будут рассматриваться вообще.<br>К немедленному исключению из игры без обсуждения ведут сообщения членам команды, которые содержат:<br>- оскорбления и угрозы;<br>- попытки обмана или шантажа;<br>- попытки обхода обманным путём решений, принятых другим членом команды.<br><br>9.4 Восстановление потерь<br>Потери, понесённые игроком в результате ошибок программирования, ошибок операторов и т.п. не восстанавливаются ввиду бесплатного характера игры.<br>В исключительных случаях восстановление может быть произведено, решение об этом принимается руководством проекта.</th></tr><tr><td class=\"c\" style=\"text-align:left;\">10. Игровые взносы</td></tr><tr><th style=\"text-align:left;\">Игрок, перечисляющий игровые взносы, совершает данный акт на добровольной основе и ничем не обязывает команду игрового проекта. Каждый игровой взнос переводится в игровую валюту и даёт игроку дополнительные возможности на определённый период времени. Такого рода перевод не даёт игроку особого статуса перед другими игроками и права нарушать установленные правила игры.</th></tr><tr><td class=\"c\" style=\"text-align:left;\">11. Основные Положения</td></tr><tr><th style=\"text-align:left;\">Регистрируясь в игре XNova игроки соглашаются с Основными Положениями и правилами соответствующей игры. </th></tr><tr><td class=\"c\" style=\"text-align:left;\">12. Изменения</td></tr><tr><th style=\"text-align:left;\">Руководство проекта оставляет за собой право изменять данные правила.<br>Все изменения будут опубликованы на форуме минимум за 2 недели до вступления их в силу. В особых случаях (напр., при угрозе ходу игры или при мелких дополнениях) этот срок может быть сокращён и указан при предварительной публикации.</th></tr></table>'),
(2, 'Помощь', 'help', '<br>\r\n<center><b>Руководство для новичков космической стратегии ЗВЕЗДНАЯ ИМПЕРИЯ</b></center>\r\n<br>\r\n<table border=\"0\" cellpadding=\"2\" cellspacing=\"1\" width=95% align=center>\r\n<tr><td class=\"c\" style=\"text-align:left;\">Начало</td></tr>\r\n<tr><th style=\"text-align:left;\">\r\nВ Звездной империи начало - понятие относительное. В зависимости от стиля игры Вы можете выбрать начальный путь развития своей империи, используя одну из тактик. <br>\r\n<br>\r\n<u>1.\'Налетчик\'</u><br>\r\nИспользуя эту тактику, Вы можете входить в топ 100 игроков в своей Вселенной, хотя это требует больших затрат времени и сил. Налетчику необходимо как можно быстрее обзавестись малыми транспортами для нападения на соседей. Их ресурсы станут хорошим дополнением к производству собственных шахт. Для постройки малого транспорта необходимо провести исследования, поэтому важно усиливать собственное производство. Стройте свои шахты. <br>\r\n<br>\r\nПорядок застройки: S1, M1, M2, S2, M3, K1, S3, M4, k2; S4, D1, S5, M5, K3... (здесь S-солнечная электростанция, М-рудник металла, К-рудник кристаллов, D-синтезатор дейтерия; цифры-уровень производства строения). <br>\r\n<br>\r\n<u>2.\'Шахтер\'</u><br>\r\nЦелью шахтера является увеличение производства металла, кристаллов и дейтерия насколько это возможно. Шахтер не придает большого значения научным исследованиям и строительству кораблей, а вкладывает все свои ресурсы в усиление производства. Намного опережая налетчиков по производству, они строят все более и более продвинутые шахты. Большое внимание уделяется также созданию мощной системы планетарной защиты. Данная тактика требует намного меньше времени он-лайн. <br>\r\n<br>\r\nПорядок застройки: S1, M1, M2, S2, M3, K1, S3, M4, K2, S4, D1, S5, M5, K3... <br>\r\n<br>\r\n<u>3.Смешанная тактика</u><br>\r\nСистема защиты начинающих игроков (newbie protection) заставляет использовать эту тактику. Тактика налетчика в чистом виде не может существовать слишком долго, т.к. большинство доноров очень быстро подпадает под действие этой системы и выходят из-под нее , имея хорошо защищенную планету. Поэтому необходимо использовать комбинированную тактику. С одной стороны, строим флот и получаем очки в рейтинг, а с другой - проводим исследования для строительства Колонизатора и как можно быстрее основываем колонии. При строительстве колонии Колонизатор разрушается и из рейтинга очки вычитаются. Таким образом, Вы не залезаете сильно вверх, что дает Вам гораздо больший выбор целей для налетов, находящихся в системе защиты начинающих игроков. Одновременно, Вы обладаете боеспособным флотом. Ну и наконец, Вы получаете больше ресурсов от своих колоний.<br>\r\n</th></tr>\r\n<tr><td class=\"c\" style=\"text-align:left;\">Ресурсы</td></tr>\r\n<tr><th style=\"text-align:left;\">\r\nВ Звездной империи ресурсы добываются шахтами. Каждая шахта производит определенное количество ресурса в час, и это количество зависит от уровня шахты. Шахты добывают ресурсы ВСЕГДА, даже когда Вы офф-лайн. <br>\r\nЕсть только одно условие для работы шахты - наличие достаточного количества энергии. Выбрав пункт меню \"Сырье\" можно посмотреть детальную информацию о производстве на планете. <br>\r\n<br>\r\nСуществует три типа шахт, различающихся своими производственными параметрами, ценой и потреблением энергии. Например, рудник по добыче металла дешев, добывает много металла и потребляет наименьшее количество энергии. Рудник по добыче кристалла производит половину продукции по сравнению с рудником металла, но имеет такой же уровень энергопотребления. А синтезатору дейтерия необходимо очень много энергии, он очень дорог и имеет очень низкий уровень производства. <br>\r\n<br>\r\nВ самом начале наиболее востребован металл. Рудник по его добыче необходимо совершенствовать как можно быстрее. Рудник по добыче кристалла может отставать на два - три уровня. В дальнейшем же желательно поддерживать их на одинаковом уровне. <br>\r\n</th></tr>\r\n<tr><td class=\"c\" style=\"text-align:left;\">Исследования</td></tr>\r\n<tr><th style=\"text-align:left;\">\r\nВ самом начале игры у Вас очень ограниченный выбор зданий для постройки. Можно строить три типа зданий, производящих ресурсы, солнечную электростанцию, фабрику роботов и хранилища. На данном этапе этого достаточно. <br>\r\n<br>\r\nКаждое строение, юнит или защитное сооружение имеет свои требования для создания. <br>\r\nНапример, для строительства верфи необходима фабрика роботов второго уровня. Полный же список строений/юнитов с необходимыми требованиями можно найти в пункте меню \"Технология\". Уже выполненные условия выделены в списке зеленым цветом, остальные красным. <br>\r\n<br>\r\nНаучные исследования проводятся одновременно для всех планет, принадлежащих игроку. Поэтому уже однажды исследованную технологию можно сразу использовать на всех планетах. В исследовании принимают участие все лаборатории, а не только лаборатория-инициатор. <br>\r\n<br>\r\nЧем выше уровень лаборатории, тем быстрее проводятся исследования. Поначалу все исследования занимают немного времени. Стоимость последующего уровня развития технологии удваивается по сравнению с предыдущим, время для исследования растет экспоненциально. Ограничивающим фактором в области исследований на поздних этапах игры является как раз не стоимость, а время ! Исследование оружейной техники 15-го уровня занимает недели... На время исследования влияет только уровень лаборатории на планете, где эти исследования проводятся. <br>\r\n<br>\r\nНо какие же исследования наиболее необходимы в начале развития ? <br>\r\nКонечно же нужно побыстрее иметь как можно больше ресурсов. Поэтому начинайте нападать на других игроков. <br>\r\n<br>\r\nА для этого нужны космические корабли, способные вывезти ресурсы с других планет. Наиболее подходящим для этой цели кораблем в начале развития является малый транспорт. Он очень дешев в исследовании и строительстве. Нужно иметь только верфь второго уровня и провести исследование двигателя внутреннего сгорания также второго уровня. <br>\r\n<br>\r\nОднако, вооружение и защита малого транспорта очень малы, поэтому ему необходимо сопровождение. Легкий истребитель относительно дешев и два истребителя уничтожают реактивный гранатомет. К моменту, когда становится доступной постройка малого транспорта, можно строить и легкие истребители. <br>\r\n<br>\r\nНу а теперь то ? Может хватит исследований ? <br>\r\nНет ! До этого все атаки проводились вслепую. Было неизвестно, что ожидает атакующий флот на вражеских планетах. Теперь необходимо обзавестись шпионскими зондами. Постройте их пару штук и начинайте зондировать окружающие планеты. Теперь Вы можете выбирать себе цель, не рискуя большими потерями при атаке. Для одновременной отправки более чем одного флота нужна компьютерная технология. Атакуя большее количество планет за промежуток времени, получаем большее количество ресурсов. <br>\r\n<br>\r\nТеперь, в зависимости от выбранной тактики, можно исследовать или большой транспорт, или колонизатор. Соответственно, сделать налеты более быстрыми и безопасными или расширять свою империю. <br>\r\n<br>\r\nДальнейшие исследования зависят от Вашего вкуса. Кто-то проводит исследования, направленные на получение линкоров, кто-то сначала исследует крейсер... Ну а кто-то направляет все силы на исследование переработчиков. <br>\r\n<br>\r\nНеобходимо помнить одно: исследования делают Ваши юниты быстрее, выносливее и сильнее в атаке. Это сокращает боевые потери и делает Вас более трудной целью для остальных. Проводите исследования, даже если это занимает несколько дней. Исследования увеличивают очки рейтинга, и никто не сможет эти очки отобрать. <br>\r\n<br>\r\nИ последнее: <br>\r\nНе забывайте, что некоторые технологии очень дороги на высоких уровнях, поэтому необходимо их согласовывать со своими дальнейшими планами. Какой смысл исследовать плазменную технику до пятого уровня, если в дальнейшем Вы не собираетесь улучшать ее до седьмого и строить плазмометы ? Всегда обращайте внимание на то, что Вы исследуете, и вместе с тем не опасайтесь потратить лишних ресурсов на исследование нужной Вам технологии. Они неограничены !  <br>\r\n</th></tr>\r\n<tr><td class=\"c\" style=\"text-align:left;\">Колонии</td></tr>\r\n<tr><th style=\"text-align:left;\">\r\nДля построения звездной империи необходимо осваивать новые колонии. Всего таких колоний одновременно может быть восемь. <br>\r\n<br>\r\nДля колонизации необходим специальный корабль - колонизатор, который отправляют на необитаемую планету. По прибытии возникает колония. Колонизировать можно только свободную планету, захватить чужую нельзя. <br>\r\n<br>\r\nВ колонии можно делать все то, что и на основной планете. В зависимости от предназначения колонии порядок застройки разный. Если она создана для увеличения поступления ресурсов, то застройка такая же, как и на основной планете. Если колония используется как промежуточная база для флота, то на первое место по значению выходит синтезатор дейтерия.  <br>\r\n</th></tr>\r\n<tr><td class=\"c\" style=\"text-align:left;\">Переработка</td></tr>\r\n<tr><th style=\"text-align:left;\">\r\nНа карте просмотра Вселенной иногда рядом с координатами планеты написаны буквы. Буква \'Т\' означает наличие около планеты поля обломков. Если к этой букве подвести курсор, можно посмотреть количество ресурсов в поле. <br>\r\n<br>\r\nВсякий раз, когда космический корабль или солнечный спутник уничтожается в битве, на месте сражения возникает поле обломков. Оно содержит 30% ресурсов, потраченных при строительстве юнита. Дейтерий никогда не остается в поле обломков.<br>\r\n<br>\r\nНапример, при уничтожении шпионского зонда возникает поле обломков, состоящее из 300 единиц кристалла. 10 уничтоженных линкоров добавят в поле 120k металла и 60k кристалла. <br>\r\n<br>\r\nХотя поле обломков находится около чьей-то планеты, на самом деле оно никому не принадлежит. Каждый, у кого есть переработчик, может послать его, и тот кто будет первым получит все ресурсы. Можно сражаться за поле обломков или защищать его. Собственник планеты, около которой находится поле обломков, даже не получает никаких сообщений о присутствии чужого переработчика. Его единственное преимущество заключается в том, что он находится ближе всех к полю и может успеть собрать ресурсы раньше других. <br>\r\n<br>\r\nВместимость переработчика 20k. Металл и кристаллы занимают одинаковый объем при погрузке. Те ресурсы, которые не поместились в переработчик, останутся на поле обломков. <br>\r\n<br>\r\nОтправка переработчика к полю обломков осуществляется из меню \"Флот\". Там задаются координаты поля, тип задания - \"Переработка\". <br>\r\n<br>\r\nВместе с переработчиком можно отправить и другие корабли, например для его защиты, но в процессе переработки участия они не принимают. <br>\r\n<br>\r\nТактики: <br>\r\nПереработчик намного медленне боевых юнитов. Поэтому, если Вы хотите атаковать планету и собрать поле обломков, возникшее в результате боя, необходимо отправлять переработчика ДО отправки нападающего флота. Если же переработчик прибудет через несколько часов после сражения, то он рискует просто не найти поля обломков, потому что его соберет кто-нибудь более шустрый. А владельцу атакованной планеты до поля лететь вообще около семи минут... Может оказаться, что около планеты, на которую Вы хотите напасть, вовсе нет обломков и, соответственно, нельзя дать задание переработчику. В этом случае можно послать на планету шпионский зонд с заданием атаковать. Зонд будет уничтожен и возникнет поле обломков, которое позволит направить к нему переработчик. <br>\r\n<br>\r\nНу а если есть уверенность, что владелец планеты не залогинится за время приближения атакующего флота, можно послать переработчик прямо вместе с ним. Скорость всего флота будет установлена на максимум скорости переработчика, поэтому переработчик прибудет на несколько секунд позже основного флота и сразу же приступит к сбору свежевыпавших (хе-хе) обломков. <br>\r\n</th></tr>\r\n<tr><td class=\"c\" style=\"text-align:left;\">Очки рейтинга</td></tr>\r\n<tr><th style=\"text-align:left;\">\r\nКоличество очков - наиболее важный показатель успеха. Каждый по мере сил старается попасть в топ 10 или хотя бы в топ 50. <br>\r\n<br>\r\nТут все просто: за каждую 1000 потраченных ресурсов дается одно очко. Не имеет значения что было построено или исследовано. Ну а за потерянные ресурсы (например, за сбитые корабли или исчезнувший во время основания новой колонии колонизатор) очки вычитаются. <br>\r\n<br>\r\nЭто означает, что если Вы хотите быть первым, то необходимо строить и исследовать больше и быстрее своих конкурентов... <br>\r\n<br>\r\nКроме общего рейтинга существуют еще два: для флота и для исследований. Тут есть небольшое отличие. За каждый новый уровень исследованной технологии/новый построенный корабль дается одно очко. Стоимость исследования/строительства не учитывается. <br>\r\n<br>\r\nИ наконец, существует рейтинг альянсов. Тут тоже все прозрачно: очки всех членов альянса суммируютя, после чего делятся на 1000. Полученное число и представляет собой рейтинг альянса. <br>\r\n</th></tr>\r\n<tr><td class=\"c\" style=\"text-align:left;\">Защита новичков</td></tr>\r\n<tr><th style=\"text-align:left;\">\r\nСистема зашиты начинающих (newbie protection) была введена, чтобы дать новым игрокам возможность освоится в игре, без риска стать донором на начальном этапе. Однако, это не означает защиту всех слабых или менее активных игроков от атак. \r\n<br>\r\n<br>Действие системы распространяется только на игроков, имеющих менее 5k очков. \r\n<br>\r\n<br>Они не могут нападать на противника, имеющего на 500% больше или на 20% меньше очков. Соответственно, противник также не может этого сделать. \r\n<br>\r\n<br>Начиная с 5k очков, Вы можете атаковать всех, имеющих очков больше Вас, и можете быть атакованы ими. Но игроки, у которых менее 20% от количества Ваших очков, не могут атаковать Вас/быть атакованы Вами. \r\n<br>\r\n<br>Имея 25k, игрок может нападать на всех, у кого больше 5k очков, и может подвергаться нападению с их стороны. \r\n<br>\r\n<br>Поэтому, даже если Вы подпадаете под условия системы защиты начинающих, то к Вам все равно могут прилететь за ресурсами игроки, у которых количество очков примерно одинаково с Вами. И разумеется Вы также можете напасть на них. \r\n<br>\r\n<br>Данная система дает каждому шанс играть и учиться. \r\n</th></tr>\r\n<tr><td class=\"c\" style=\"text-align:left;\">Оборона</td></tr>\r\n<tr><th style=\"text-align:left;\">\r\nВ этой статье обсужлается такой вопрос: что значит иметь защиту ? \r\n<br>\r\n<br>Прежде всего: сооружение защиты совершенно необходимо. Если враг не понес потерь при атаке, то он почти наверняка вернется снова. Другими словами - если Вы \"отличная мишень\", то защита используется очень часто. \r\n<br>\r\n<br>Конечно, необходимо учитывать уровень своего противника. Против игрока, занимающего первую строчку в рейтинге флотов, даже с хорошей защитой мало шансов на успех. Но против небольших сил этого достаточно. \r\n<br>\r\n<br>Как же выглядит \"хорошая\" защита ? \r\n<br>\r\n<br>Первый рял обороны: множество легких орудий (легких лазеров или реактивных гранатометов, в зависимости от количества Ваших ресурсов). \r\n<br>\r\n<br>Ударный ряд: несколько тяжелых орудий (пушки Гаусса или плазмометы). Сооружение этих орудий предотвращает большинство налетов, т.к. против плазмометов любой атакующий флот понесет потери. Даже звезда смерти более или менее уязвима для плазменного огня. \r\n</th></tr>\r\n<tr><td class=\"c\" style=\"text-align:left;\">Флот</td></tr>\r\n<tr><th style=\"text-align:left;\">\r\nЗдесь представлен полный перечень всех типов космических кораблей с небольшим описанием преимуществ и недостатков каждого типа. Технологии/строения, необходимые для создания конкретного типа, здесь не приводятся. Их можно посмотреть в разделе \"Техника\" игрового меню. \r\n<br>\r\n<br><u>Малый транспорт: </u>\r\n<br>В начале игры эти юниты представляют основу грузоперевозок. Не имеют брони, уязвимы и медленны, но способны принять на борт до 5000 ресурсов. Малый транспорт - первый корабль, используемый для нападения на другие планеты. Не отправляйте его без боевого сопровождения, т.к. даже один реактивный гранатомет может его сбить, а единственная цель, которую способен уничтожить малый транспорт - это солнечный спутник. \r\n<br>Ресурсы: 2000, 2000, 0 \r\n<br>\r\n<br><u>Большой транспорт: </u>\r\n<br>Большой брат малого транспорта, но скорость выше и вместимость 25000. Потребление топлива немного больше, но в целом этот корабль более рентабелен. Один из наиболее важных юнитов в OGame для налетов. Тоже нуждается в боевом эскорте. \r\n<br>Ресурсы: 6000, 6000, 0 \r\n<br>\r\n<br><u>Легкий истребитель: </u>\r\n<br>Первый недорогой боевой юнит. Не является непобедимой боевой машиной, но против транспортов всегда побеждает. Используется для налетов на слабозащищенные планеты. Легкий истребитель вместе с транспортом способен уничтожить два реактивных гранатомета. \r\n<br>Ресурсы: 3000, 1000, 0 \r\n<br>\r\n<br><u>Тяжелый истребитель: </u>\r\n<br>Тяжелый истребитель имеет значительно более высокую огневую мощь и выдерживает большее количество повреждений. К тому же, благодаря новой технологии двигателя, он быстрее. Но и потребление топлива выше, и стоит он дороже более, чем в два раза. Однако, провести исследования для тяжелого истребителя совершенно необходимо, ибо он превосходит легкий в бою, к тому же импульсный двигатель в любом случае нужен для колонизатора. Тяжелый истребитель - это превосходный боевой юнит против малых защитных сооружений (реактивных гранатометов, легких лазеров и, при численном превосходстве, даже против ионных орудий). Если соотношение 1:1 (против ионных орудий 1:2), то Вы не понесете никаких потерь. \r\n<br>Ресурсы: 6000, 4000, 0 \r\n<br>\r\n<br><u>Крейсер: </u>\r\n<br>Крейсер представляет собой боевой корабль средней тяжести. Он значительно мощнее обоих типов истребителей, имеет примерно такую же скорость и выдерживает намного большие повреждения. Но у него есть серьезные недостатки: его производство недешево и требует специального исследования двух новых технодогий. Соотношение цена/качество не самое лучшее, а ионная технология впоследствии необходима только для сооружения ионных орудий. В случае сомнений лучше построить больше тяжелых истребителей. \r\n<br>Ресурсы: 20.000, 7.000, 2.000 \r\n<br>\r\n<br><u>Линкор: </u>\r\n<br>Линкоры - наиболее используемые всеми боевые юниты. У них хорошее соотношение цена/качество, они быстрее, чем все ранее рассмотренные корабли. Исследование линкоров совершенно необходимо. Однако, было бы ошибкой исследовать их слишком рано. Это пустая трата ресурсов, которые могут быть потрачены более эффективно. Позднее будет значительно легче собрать столь значительные ресурсы. Линкоры лучше всего использовать против тяжелой защиты. Для разрушения легких защитных сооружений они не выгодны из-за большого потребления топлива. С такой задачей вполне справятся и тяжелые истребители. \r\n<br>Ресурсы: 40.000, 20.000 \r\n<br>\r\n<br><u>Уничтожитель: </u>\r\n<br>Разрушитель превосходит все корабли по стоимости. Затраты на него огромны. В принципе, наличие разрушителя не является необходимым условием для создания боеспособного флота. Но он будет превосходным дополнением к нему из-за своей огневой мощи. Стоит однако учитывать, что разрушитель значительно снижает скорость флота (вообще скорость флота зависит от скорости самого медленного корабля, входящего в его состав). Основное же применение разрушителя - уничтожение звезд смерти и разрушение планетарной защиты. \r\n<br>Ресурсы: 60.000, 50.000, 15.000 \r\n<br>\r\n<br><u>Звезда смерти: </u>\r\n<br>Было бы ошибкой называть звезду смерти кораблем. Скорее это перемешаемая луна, обладающая колоссальной огневой мощью. Но и цена на строительство соответствующая. Имеет очень низкую скорость, но вместе с тем превосходную защиту. \r\n<br>Ресурсы: 5.000.000, 4.000.000, 1.000.000 \r\n<br>\r\n<br><u>Бомбардировщик: </u>\r\n<br>Бомбардировщик был разработан специально для того, чтобы уничтожать планетарную защиту. С помощью лазерного прицела он точно сбрасывает плазменые бомбы на поверхность планеты и таким образом наносит огромные повреждения защитным сооружениям. Скорострел против реактивного гранатомёта(10), лёгких (10) и тяжёлых лазерных орудий (5), а также против ионных орудий (5). \r\n<br>\r\n<br><u>Переработчик: </u>\r\n<br>Переработчик может выполнять специальное задание - перерабатывать поле обломков. Его вместимость такая же, как и у большого транспорта (20000). Скорость самая низкая, за исключением звезды смерти. Переработчик можно послать в одиночку без сопровождения, владелец планеты, около которой находится поле обломков, не получит никакого сообщения. Игроки, построившие переработчик раньше других, получают большое преимущество, т.к. могут собирать ресурсы, без необходимости вести военные действия. \r\n<br>Ресурсы: 10.000, 6.000, 2.000 \r\n<br>\r\n<br><u>Шпионский зонд: </u>\r\n<br>Задачей шпионского зонда является сбор сведений о планетах. Он имеет огромную скорость. Шпионаж связан с риском обнаружения и немедленного уничтожения зонда. Эффективность зонда может быть увеличена путем непрерывного исследования технологии шпионажа. Это гарантирует отсутствие неприятных сюрпризов для атакующего флота, когда он прибудет на планету. \r\n<br>Ресурсы: 0, 1.000, 0 \r\n<br>\r\n<br><u>Солнечный спутник: </u>\r\n<br>Солнечные спутники выводятся на планетарную орбиту и остаются там, перерабатывае солнечное излучение в энергию. Их легко сбить при атаке на планету. Эффективность работы спутников зависит от удаленности планеты от солнца. Преимуществом спутников является то, что им не нужны строительные слоты на планете. При наличии же места для застройки более выгодны солнечная и термоядерная станции. \r\n<br>Ресурсы: 0, 2.000, 500 \r\n<br>\r\n<br><u>Колонизатор: </u>\r\n<br>Колонизатор необходим для освоения новых планет. Он дорог и к тому же уничтожается при удачной колонизации, но это единственный способ заселения незанятой планеты. \r\n<br>Ресурсы: 10.000, 20.000, 10.000 \r\n</th></tr>\r\n<tr><td class=\"c\" style=\"text-align:left;\">Луна</td></tr>\r\n<tr><th style=\"text-align:left;\">\r\nЛуна как и в реальной жизни это спутник планеты. Она расположена в том же месте, где и поле обломков (на орбите планеты), и может быть выбрана в качестве цели аналогичным образом. \r\n<br>\r\n<br>Луна создается из обломков уничтоженных кораблей. За каждые 100k ресурсов в поле обломков вероятность создания луны увеличивается на 1%. Максимальная вероятность появления луны 20%. При возникновении луны поле обломков исчезает. Однако, если обломков было более двух биллионов, то \"лишние\" ресурсы остаются в поле. \r\n<br>\r\n<br>Чтобы заселить луну, необходимо построить лунную базу. После этого появятся несколько полей (за каждый уровень лунной базы даются три поля) для застройки. Существуют два специальных строения, которые могут быть построены ТОЛЬКО на луне. Это сенсорная фаланга, ведущая шпионаж за флотами других игроков, и ворота, перемещающие флот за нулевое время. \r\n<br>\r\n<br>Важно: луна может быть атакована, как и обычная планета.  \r\n</th></tr>\r\n<tr><td class=\"c\" style=\"text-align:left;\">Сенсорная фаланга</td></tr>\r\n<tr><th style=\"text-align:left;\">\r\nСенсорная фаланга отслеживает перемещения вражеского флота. Её можно построить ТОЛЬКО на луне, причем она имеет ограниченный радиус обзора. Формула для вычисления радиуса действия фаланги в системах следующая: (уровень фаланги)? - 1. \r\n<br>Фаланга первого уровня может сканировать только свою систему. \r\n<br>\r\n<br>Например: \r\n<br>уровень 1: только своя система \r\n<br>уровень 2: 3 системы \r\n<br>уровень 3: 8 систем \r\n<br>уровень 4: 15 систем \r\n<br>и т.д... \r\n<br>\r\n<br>При использовании фаланги показываются все флоты, совершающие перелет. Совершенно не важно входят планеты назначения этих флотов или нет в радиус обзора. В отличие от шпионских зондов, показывающих только \"стационарные\" вещи на планете (строения, ресурсы, исследования), сенсорная фаланга демонстрирует еще и передвигающиеся флоты и время их прибытия в пункт назначения. \r\n<br>\r\n<br>Для начала сканирования просто выберите свою луну и кликните на карте Вселенной по имени планеты, которую надо просканировать. Выпадет окошко с результатами сканирования. Владелец просмотренной планеты ничего не узнает. \r\n<br>\r\n<br>Сенсорная фаланга с одной стороны повышает безопасность Ваших налетов, а с другой может дать такую полезную информацию как координаты вражеских колоний (искать вручную по карте Вселенной долго и нудно и требует затрат дейтерия), с возможностью создать \"профиль перемещений\" игрока. \r\n</th></tr></table>');
INSERT INTO `game_content` (`id`, `title`, `alias`, `html`) VALUES
(3, 'Пользовательское соглашение', 'agreement', '<table width=\"95%\" align=\"center\">\r\n<tr><td class=\"c\">Пользовательское соглашение</td></tr>\r\n<tr><th style=\"text-align:left;\"><br />\r\n Администрация онлайн игры «XNova», именуемая в дальнейшем \"Администрация\", с одной стороны и физическое лицо, именуемое в дальнейшем «Пользователь», с другой стороны заключили настоящее Соглашение о нижеследующем:\r\n<br /><br />\r\n Согласие Пользователя с положениями настоящего Соглашения является обязательным условием для использования  Игры \"XNova\" (далее \"Игра\"), размещенной в сети Интернет по адресу: www.xnova.su, а также на относящиеся к указанной Игре сопутствующие информационные и развлекательные ресурсы (в совокупности Игра и информационные и развлекательные ресурсы, далее «Проект»).\r\n<br /><br />\r\n Проект составляют любые материалы, размещенные на доменном имени xnova.su.\r\n<br /><br />\r\n Все права собственности и права на интеллектуальную собственность в отношении  Игры, включая, но, не ограничиваясь, любыми графическими изображениями,  фотографиями,  анимацией, видеозаписями, звукозаписями, музыкой, текстом, сопровождающие  Игру материалы и любые ее части или копии принадлежат Администрации или соответствующим правообладателям.\r\n<br /><br />\r\n Настоящее Соглашение может быть изменено Администрацией в любое время без предварительного уведомления Пользователя. Действующая версия Соглашения расположена по адресу: http://xnova.su/?set=sogl . Пользователь, принимая условия настоящего Соглашения, подтверждает, что он достиг 18-летнего возраста, правоспособен и дееспособен и вправе заключать настоящее Соглашение, лица, не достигшие 18-летнего возраста, обязаны согласовать использование Проекта со своими законными представителями (родителями, опекунами и т.д.).\r\n<br /><br />\r\n Пользователь подтверждает, что в соответствии с законами его государства, он не имеет запретов и ограничений в получении игровых услуг он-лайн.\r\n<br /><br />\r\n В случае если Вам, в соответствии с законами Вашего государства, запрещено получать игровые on-line услуги, или существуют иные законодательные ограничения, включая ограничения допуска к таким услугам по возрасту, Вы не вправе использовать Игру или отдельные услуги в Игре. Вы обязуетесь немедленно прекратить использование Игры или таких услуг в Игре.\r\n<br /><br />\r\n Пользователь настоящим уведомлен, что регулярное длительное (непрерывное) нахождение у персонального компьютера может вызывать различные осложнения физического состояния, в том числе ослабление зрения, сколиоз, различные формы неврозов и прочие негативные воздействия на организм. Пользователь гарантирует, что он будет использовать Проект исключительно на протяжении разумного времени, с перерывами на отдых или иные мероприятия по профилактике физического состояния, если таковые Пользователю рекомендованы или предписаны. Настоящим Пользователь уведомлен, что Игра может предусматривать различные звуковые и/или видео эффекты, которые, при определенных обстоятельствах, могут вызывать обострение состояний у лиц, склонных к эпилептическим или иным расстройствам нервного характера. Пользователь гарантирует, что указанными расстройствами он не страдает, или же обязуется не использовать Игру.\r\n<br /><br />\r\n Пользователь гарантирует, что используемое им оборудование и средства связи, в том числе используемые Пользователем услуги оператора связи, являются достаточными и исправными для участия в Проекте.\r\n<br /><br />\r\n Администрация предоставляет бесплатный доступ Пользователя к Игре. Администрация не обеспечивает Пользователю доступ в всемирную сеть Интернет. Получение дополнительных сервисов Игры, предоставляется безвоздмездно, на усмотрение Администрации, отдельным Пользователям и не освобождает Пользователей от соблюдения настоящего Соглашения или Законов Игры.\r\nПользователем является любое лицо, получившее доступ к Игре в соответствии с настоящим Соглашением, использующее материалы, размещенные на доменном имени xnova.su, в частном порядке, только в личных некоммерческих целях.\r\n<br /><br />\r\n Администрация обеспечивает Пользователю возможность использования Игры круглосуточно, за исключением форс-мажорных и иных обстоятельств, возникших не по вине Администрации, а также профилактических и технических перерывов.\r\nАдминистрация игры имеет право отказать Пользователю в предоставлению доступа в игру, полностью или частично. Пользователь также соглашается с тем, что Администрация не несет какой-либо ответственности за любое изменение, ограничение или прекращение доступа Пользователя к Проекту.\r\n<br /><br />\r\n Пользователь имеет право пользоваться игрой исключительно в некоммерческих целях. Пользователь имеет право на изменение своего пароля. При этом Пользователь обязан самостоятельно обеспечивать неразглашение (тайну) своего пароля и иных необходимых данных и соответственно несет ответственность за сохранение и неразглашение своего пароля, а так же все риски (убытки), связанные с этим. Пользователь вправе зарегистрировать 1 (одного) персонажа. Пользователь не имеет права передавать свои регистрационные данные (логин и пароль) третьему лицу, а также не имеет права получать его от третьего лица.\r\n<br /><br />\r\n По требованию Администрации Пользователь обязан сообщить достоверную, полную и актуальную информацию о себе. Если после регистрации эта информация изменится, Пользователь обязан её актуализировать. Любая информация Пользователя персонального характера может быть запрошена Администрацией исключительно для целей исполнения Соглашения, и ни при каких обстоятельствах не передается третьим лицам не иначе как для целей исполнения Соглашения или в соответствии с требованиями законодательства.\r\n<br /><br />\r\n Пользователь не имеет права:\r\n<br /><br />\r\n - использовать ошибки (баги) программного обеспечения и обязан незамедлительно сообщать о них по адресу Администрации.\r\n<br /><br />\r\n - вмешиваться в программный код,\r\n<br /><br />\r\n - несанкционированно получать доступ к компьютерной системе, доступ к базе данных пользователей Игры.\r\n<br /><br />\r\n Пользователю запрещено использование и распространение любых скриптов, клиентских программ и сторонних программных средств, не предусмотренных игрой и вызывающих:\r\n<br /><br />\r\n - неконтролируемые игровые последствия;\r\n<br /><br />\r\n - облегчение игрового процесса;\r\n<br /><br />\r\n - сбои в работе сервера;\r\n<br /><br />\r\n Пользователь обязуется уважительно и корректно относиться к другим Пользователям Игры, а также не использовать Игру для:\r\n<br /><br />\r\n - загрузки, посылки, передачи или любого другого способа размещения информации, который является незаконным, вредоносным, угрожающим, клеветническим, оскорбляет нравственность, нарушает авторские права, пропагандирует ненависть и/или дискриминацию людей по расовому, этническому, половому, религиозному, социальному признакам, содержит оскорбления в адрес конкретных лиц или организаций;\r\n<br /><br />\r\n - ущемления прав меньшинств;\r\n<br /><br />\r\n - умышленного, преднамеренного искажения, либо сокрытия информации, вводящих в заблуждение других пользователей относительно его личности и принадлежности к Администрации игры;\r\n<br /><br />\r\n - загрузки, посылки, передачи или любого другого способа размещения информации, которая затрагивает какой-либо патент, торговую марку, коммерческую тайну, копирайт или прочие права собственности и/или авторские и смежные с ним права третьей стороны;\r\n<br /><br />\r\n - загрузки, посылки, передачи или любого другого способа размещения каких-либо материалов, содержащих вирусы или другие компьютерные коды, файлы или программы, предназначенные для нарушения, уничтожения либо ограничения функциональности любого компьютерного или телекоммуникационного оборудования или программ, для осуществления несанкционированного доступа, а также серийные номера к коммерческим программным продуктам и программы для их генерации, логины, пароли и прочие средства для получения несанкционированного доступа к платным ресурсам в Интернете, а также размещения ссылок на вышеуказанную информацию;\r\n<br /><br />\r\n - сбора и хранения персональных данных других лиц;\r\n<br /><br />\r\n - размещение ссылок на ресурсы сети Интернет;\r\n<br /><br />\r\n - содействия действиям, направленным на нарушение ограничений и запретов, налагаемых Соглашением.\r\n<br /><br />\r\n Пользователю запрещается копировать, воспроизводить, распространять, переводить на другие языки любой компонент Игры, осуществлять вскрытие технологии, извлекать исходный код, модифицировать, разбирать, декомпилировать, или создавать производные объекты, основанные на Проекте, включать любой из материалов Проекта в основу своих разработок.\r\n<br /><br />\r\n Пользователь предупрежден и соглашается, что Администрация вправе в любой момент прекратить, либо ограничить доступ к Игре, вплоть до удаления из базы Игры.\r\n<br /><br />\r\n Администрация Игры не дает никаких явных или подразумеваемых гарантий того, что в процессе разработки Игры были обнаружены все недочеты и выявлены все программные ошибки. Администрация прилагает все разумные усилия для того, чтобы указанные ошибки были выявлены и в разумные сроки исправлены. При этом Игрок, заметивший такой недочет или ошибку, но не сообщивший о ней Администрации, и использовавший ее для получения непредусмотренных условиями соглашения возможностей Игры (публично обнародовавший факт ее существования или характер проявления), лишается прав, предусмотренных Соглашением, непосредственно в момент такого неправомерного использования или обнародования.\r\n<br /><br />\r\n Администрация управляет игрой и игровыми процессами исключительно по своему усмотрению. Для сбора статистических данных и идентификации посетителей Проекта Администрация может отслеживать и сохранять информацию об IP-адресах доступа Пользователя к игре, использовать файлы технической информации (cookies), размещаемые на локальном терминале Пользователя.\r\nПользователь или посетитель игры согласен с тем, что Администрация, может и вправе запросить, собирать и хранить персональную информацию Пользователя, включая информацию о фамилии, имени, отчестве, поле, возрасте, адресе зарегистрированного или фактического пребывания, контактных телефонах или адресах электронной почты или иных средств электронной коммуникации.\r\n<br /><br />\r\n Администрация имеет право удалить персонажа Пользователя или блокировать (приостановить к нему доступ), если возникли основания подозревать, что предоставленная Пользователем информация не является полной, достоверной или актуальной.\r\n<br /><br />\r\n Администрация не несет ответственности за временные технические сбои и перерывы в работе Проекта, за временные сбои и перерывы в работе линий связи, иные аналогичные сбои, а также за неполадки компьютера, с которого Пользователь осуществляет выход в сеть Интернет.\r\n<br /><br />\r\n Администрация не несет ответственности перед Пользователем за действия других Пользователей.\r\nАдминистрация имеет право на удаление или изменение любой информации, размещенной Пользователем на сервере игры без предварительного согласования с Пользователем.\r\n<br /><br />\r\n Администрация вправе закрывать и/или ограничивать функциональность Игры в любое время без какого-либо предварительного уведомления Пользователя.\r\n<br /><br />\r\n Администрация вправе в одностороннем порядке и в любое время изменить количество и качество предоставляемых услуг, без предварительного уведомления Пользователя. Администрация вправе в одностороннем порядке и в любое время изменять условия настоящего Соглашения, и соответственно, внести изменения в информацию на этой странице. Пользователь соглашается, что к нему применимы все изменения Соглашения с момента их публикации на этой странице. Пользователь должен периодически посещать эту страницу для ознакомления с актуальными положениями Соглашения.\r\n<br /><br />\r\n Споры, возникающие из настоящего Соглашения, подлежат разрешению путем переговоров.\r\n<br /><br />\r\n Настоящее Соглашение вступает в силу при акцептации его Пользователем путем выбора и нажатия опции \"согласен\" (или иной аналогичной опции). Пользователь соглашается, что выбор им вышеуказанной опции означает, что он предварительно и в полном объеме ознакомился с условиями настоящего Соглашения, и принимает их без каких-либо исключений. Соглашение заключается на неопределенный срок. Пользователь может прекратить действие Соглашения в любое время, прекратив использование Проекта. Администрация может в любое время прекратить действие Соглашения по любой причине или без причины. При прекращении настоящего Соглашения по любой причине или без причины, все права, предоставленные Пользователю по настоящему Соглашению, прекращаются.\r\nАдминистрация вправе в одностороннем порядке отказаться от настоящего Соглашения и прекратить предоставление услуг по пользованию Проектом без возмещения убытков. Все изменения, дополнения, приложения к настоящему Соглашению или иная информация являются неотъемлемой его частью и являются обязательными для исполнения с момента опубликования их на сайте игры.\r\n<br /><br />\r\n Начало использования проекта означает безусловное согласие и принятие условий пользователем, в отношении положений настоящего соглашения.\r\n<br /><br />\r\n</th></tr></table>'),
(4, 'Ссылки на игровые ресурсы', 'links', '<br>\r\n<table width=600>\r\n<tr>\r\n  <td class=c colspan=2>Ссылки на игровые ресурсы</td>\r\n</tr>\r\n<tr><th>Законы игры</th><th><a href=\"http://uni2.xnova.su/?set=agb\">Ссылка</a></th></tr>\r\n<tr><th>Пользовательское соглашение</th><th><a href=\"http://uni2.xnova.su/?set=sogl\">Ссылка</a></th></tr>\r\n<tr><th>Помощь новичкам</th><th><a href=\"http://uni2.xnova.su/?set=help\">Ссылка</a></th></tr>\r\n<tr><th>Симулятор боев</th><th><a href=\"http://uni2.xnova.su/xnsim/\">Ссылка</a></th></tr>\r\n<tr><th>Администрация игры</th><th><a href=\"http://uni2.xnova.su/?set=contact\">Ссылка</a></th></tr>\r\n<tr><th>Симулятор уничтожения лун</th><th><a href=\"http://uni2.xnova.su/xnsim/moon.php\">Ссылка</a></th></tr>\r\n<tr><th>Приложение для Вконтакте.Ру</th><th><a href=\"http://vkontakte.ru/app1798249\">Ссылка</a></th></tr>\r\n<table>\r\n	<br><br><br>\r\n<table width=600><tr><th>Если вы хотите поместить ссылку на ресурс, относящийся к игре, напишите запрос в техподдержку</th></tr></table>'),
(5, 'Добро пожаловать в игру', 'welcome', 'Добро пожаловать в космическую онлайн стратегию \"Звездная Империя\".<br><br>\r\nВы начинаете на одной малоразвитой планете и превращаете её в могущественную империю, способную защитить все Ваши колонии, развитые непосильным трудом. <br><br>\r\nСоздайте экономическую и военную инфраструктуру для будущего \r\nразвития новейших технологий. Ведите войны против других империй, \r\nведь только в бою Вы сможете одержать победу в войне за ресурсы. <br><br>\r\nВедите переговоры с другими императорами и создавайте альянсы \r\nили обменивайтесь ресурсами. Постройте непобедимый флот, чтобы\r\n установить господство над всей вселенной. Прячьте ресурсы под \r\nнеуязвимой планетарной обороной. \r\n\r\n<br><br>В игре представлено четыре расы, \r\nкаждая из которых имеет уникальные особенности и корабли.\r\n<br>\r\n<br>\r\n<center><img src=\"/skins/default/images/race1.gif\">\r\n<img src=\"/skins/default/images/race2.gif\">\r\n<img src=\"/skins/default/images/race3.gif\">\r\n<img src=\"/skins/default/images/race4.gif\">\r\n<br><br>\r\nДля начала игры вам нужно выбрать одну из представленных рас.<br><br><a href=\"javascript:;\" id=\"startLink\" onclick=\"closeWindow()\">Поехали!</a></center>'),
(6, 'Оплата выполнена успешно', 'success', '<div class=\"separator\"></div><table class=\"table\"><tr><th><br>Вы успешно пополнили ваш игровой счет!<br><br></th></tr></table>'),
(7, 'Оплата не произведена', 'fail', '<div class=\"separator\"></div><table class=\"table\"><tr><th><br>Произошла ошибка при попытке пополнения игрового счета!<br><br></th></tr></table>');

CREATE TABLE `game_errors` (
  `error_id` bigint(11) NOT NULL,
  `error_sender` varchar(32) NOT NULL DEFAULT '0',
  `error_time` int(11) NOT NULL DEFAULT '0',
  `error_type` varchar(32) NOT NULL DEFAULT 'unknown',
  `error_text` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_files` (
  `id` int(11) NOT NULL,
  `src` varchar(200) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `size` int(11) NOT NULL DEFAULT '0',
  `mime` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_fleets` (
  `id` bigint(11) NOT NULL,
  `owner` int(11) NOT NULL DEFAULT '0',
  `owner_name` varchar(35) NOT NULL DEFAULT '',
  `mission` int(11) NOT NULL DEFAULT '0',
  `amount` bigint(11) NOT NULL DEFAULT '0',
  `fleet_array` json DEFAULT NULL,
  `start_time` int(11) NOT NULL DEFAULT '0',
  `start_galaxy` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `start_system` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `start_planet` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `start_type` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `end_time` int(11) NOT NULL DEFAULT '0',
  `end_stay` int(11) NOT NULL DEFAULT '0',
  `end_galaxy` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `end_system` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `end_planet` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `end_type` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `resource_metal` bigint(11) UNSIGNED NOT NULL DEFAULT '0',
  `resource_crystal` bigint(11) UNSIGNED NOT NULL DEFAULT '0',
  `resource_deuterium` bigint(11) UNSIGNED NOT NULL DEFAULT '0',
  `target_owner` int(11) NOT NULL DEFAULT '0',
  `target_owner_name` varchar(35) NOT NULL DEFAULT '',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `mess` int(11) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `raunds` tinyint(1) NOT NULL DEFAULT '6',
  `won` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_groups` (
  `id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL DEFAULT '',
  `locked` enum('Y','N') NOT NULL DEFAULT 'N',
  `metadata` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `game_groups` (`id`, `title`, `locked`, `metadata`) VALUES
(1, 'Администраторы', 'N', NULL),
(2, 'Аноним', 'N', NULL),
(3, 'Пользователь', 'N', NULL),
(4, 'Оператор', 'N', NULL),
(5, 'Супер-оператор', 'N', NULL);

CREATE TABLE `game_groups_access` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `access_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_hall` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `debris` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `won` tinyint(1) NOT NULL,
  `sab` tinyint(1) NOT NULL DEFAULT '0',
  `log` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_logs` (
  `mission` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `time` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `kolvo` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `s_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `s_galaxy` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `s_system` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `s_planet` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `e_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `e_galaxy` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `e_system` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `e_planet` tinyint(2) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_log_attack` (
  `uid` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `time` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `planet_start` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `planet_end` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `fleet` varchar(255) NOT NULL DEFAULT '',
  `battle_log` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_log_chat` (
  `id` int(11) UNSIGNED NOT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  `user` int(11) NOT NULL DEFAULT '0',
  `user_name` varchar(100) NOT NULL DEFAULT '',
  `text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_log_credits` (
  `uid` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `credits` smallint(6) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_log_email` (
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `email` varchar(35) NOT NULL,
  `ok` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_log_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `planet` int(11) NOT NULL,
  `operation` int(11) NOT NULL,
  `from_metal` int(11) NOT NULL,
  `from_crystal` int(11) NOT NULL,
  `from_deuterium` int(11) NOT NULL,
  `to_metal` int(11) NOT NULL,
  `to_crystal` int(11) NOT NULL,
  `to_deuterium` int(11) NOT NULL,
  `build_id` int(11) NOT NULL,
  `tech_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_log_ip` (
  `id` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `ip` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_log_load` (
  `time` int(11) NOT NULL DEFAULT '0',
  `value` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_log_refers` (
  `time` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `refers` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_log_sim` (
  `id` int(11) NOT NULL,
  `sid` varchar(50) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT '0',
  `data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_log_stats` (
  `id` int(11) NOT NULL,
  `object_id` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(2) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `tech_rank` int(11) NOT NULL DEFAULT '0',
  `tech_points` int(11) NOT NULL DEFAULT '0',
  `build_rank` int(11) NOT NULL DEFAULT '0',
  `build_points` int(11) NOT NULL DEFAULT '0',
  `defs_rank` int(11) NOT NULL DEFAULT '0',
  `defs_points` int(11) NOT NULL DEFAULT '0',
  `fleet_rank` int(11) NOT NULL DEFAULT '0',
  `fleet_points` int(11) NOT NULL DEFAULT '0',
  `total_rank` int(11) NOT NULL DEFAULT '0',
  `total_points` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_log_transfers` (
  `id` int(11) NOT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  `target_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_log_username` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `username` varchar(35) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_lostpasswords` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `keystring` varchar(50) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(30) NOT NULL DEFAULT '',
  `active` enum('0','1') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_messages` (
  `id` bigint(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `from_id` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  `theme` varchar(100) DEFAULT NULL,
  `text` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_modules` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL DEFAULT '',
  `sort` smallint(6) NOT NULL DEFAULT '0',
  `namespace` varchar(100) NOT NULL DEFAULT '',
  `active` enum('N','Y') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `game_modules` (`id`, `code`, `sort`, `namespace`, `active`) VALUES
(1, 'core', 10, 'Friday\\Core', 'Y'),
(2, 'xnova', 20, '', 'Y'),
(3, 'admin', 30, '', 'Y'),
(4, 'bot', 40, '', 'Y');

CREATE TABLE `game_moneys` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip` varchar(50) NOT NULL DEFAULT '',
  `time` bigint(20) NOT NULL DEFAULT '0',
  `referer` varchar(250) NOT NULL DEFAULT '',
  `user_agent` varchar(250) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_notes` (
  `id` bigint(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `priority` tinyint(1) DEFAULT NULL,
  `title` varchar(60) DEFAULT NULL,
  `text` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_options` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL DEFAULT '',
  `title` varchar(250) NOT NULL DEFAULT '',
  `value` text,
  `group_id` enum('general','xnova') NOT NULL DEFAULT 'general',
  `type` enum('string','integer','text','checkbox') NOT NULL DEFAULT 'string',
  `def` varchar(250) NOT NULL,
  `description` varchar(250) NOT NULL,
  `cached` enum('N','Y') NOT NULL DEFAULT 'Y'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `game_options` (`id`, `name`, `title`, `value`, `group_id`, `type`, `def`, `description`, `cached`) VALUES
(1, 'site_title', 'Название сайта', 'Звездная Империя', 'general', 'string', '', '', 'Y'),
(2, 'assets_join_js', 'Объединять JS файлы', 'N', 'general', 'checkbox', 'N', '', 'Y'),
(3, 'assets_join_css', 'Объединять CSS файлы', 'N', 'general', 'checkbox', 'N', '', 'Y'),
(4, 'assets_minify_js', 'Сжимать JS файлы', 'N', 'general', 'checkbox', 'N', '', 'Y'),
(5, 'assets_minify_css', 'Сжимать CSS файлы', 'N', 'general', 'checkbox', 'N', '', 'Y'),
(6, 'users_online', 'Кол-во игроков онлайн', '0', 'xnova', 'integer', '', '', 'Y'),
(7, 'users_total', 'Кол-во игроков', '1', 'xnova', 'integer', '', '', 'Y'),
(8, 'active_alliance', 'Активные альянсы', '0', 'xnova', 'integer', '', '', 'Y'),
(9, 'active_users', 'Активные игроки', '0', 'xnova', 'integer', '', '', 'Y'),
(10, 'disableAttacks', 'Отключить атаки', 'N', 'xnova', 'checkbox', 'N', '', 'Y'),
(11, 'LastSettedGalaxyPos', 'Галактика', '1', 'xnova', 'integer', '1', '', 'N'),
(12, 'LastSettedPlanetPos', 'Планета', '2', 'xnova', 'integer', '1', '', 'N'),
(13, 'LastSettedSystemPos', 'Система', '1', 'xnova', 'integer', '1', '', 'N'),
(14, 'newsMessage', 'Информационное сообщение', '', 'xnova', 'text', '', '', 'Y'),
(15, 'stat_update', 'Последнее обновление статистики', '0', 'xnova', 'integer', '', '', 'Y'),
(16, 'email_notify', 'Email для оповещений', 'info@xnova.su', 'general', 'string', '', '', 'Y'),
(17, 'assets_use_minify', 'Подключать сжатые js/css файлы', 'Y', 'general', 'checkbox', 'N', '', 'Y');

CREATE TABLE `game_planets` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `id_owner` int(11) UNSIGNED DEFAULT NULL,
  `id_ally` int(11) NOT NULL DEFAULT '0',
  `id_level` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `galaxy` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `system` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `planet` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `last_update` int(11) DEFAULT NULL,
  `last_active` int(11) NOT NULL DEFAULT '0',
  `planet_type` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `destruyed` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `image` varchar(32) NOT NULL DEFAULT 'normaltempplanet01',
  `diameter` smallint(6) UNSIGNED NOT NULL DEFAULT '12800',
  `field_current` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `field_max` smallint(6) UNSIGNED NOT NULL DEFAULT '163',
  `temp_min` smallint(3) NOT NULL DEFAULT '-17',
  `temp_max` smallint(3) NOT NULL DEFAULT '23',
  `metal` double(32,4) NOT NULL DEFAULT '500.0000',
  `crystal` double(32,4) NOT NULL DEFAULT '500.0000',
  `deuterium` double(32,4) NOT NULL DEFAULT '0.0000',
  `energy_ak` double(11,2) NOT NULL DEFAULT '0.00',
  `last_jump_time` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `parent_planet` int(11) NOT NULL DEFAULT '0',
  `debris_metal` int(11) NOT NULL DEFAULT '0',
  `debris_crystal` int(11) NOT NULL DEFAULT '0',
  `merchand` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `game_planets` (`id`, `name`, `id_owner`, `id_ally`, `id_level`, `galaxy`, `system`, `planet`, `last_update`, `last_active`, `planet_type`, `destruyed`, `image`, `diameter`, `field_current`, `field_max`, `temp_min`, `temp_max`, `metal`, `crystal`, `deuterium`, `energy_ak`, `last_jump_time`, `parent_planet`, `debris_metal`, `debris_crystal`, `merchand`) VALUES
(1, 'Главная планета', 1, 0, 0, 1, 1, 1, 1526921749, 1526921749, 1, 0, 'wasserplanet07', 13038, 0, 170, -19, 21, 6368.4388, 5684.2195, 5000.0000, 0.00, 0, 0, 0, 0, 0);

CREATE TABLE `game_planets_buildings` (
  `id` int(11) NOT NULL,
  `planet_id` int(11) DEFAULT NULL,
  `build_id` int(11) DEFAULT NULL,
  `level` smallint(6) NOT NULL DEFAULT '0',
  `power` tinyint(2) NOT NULL DEFAULT '10'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_planets_units` (
  `id` int(11) NOT NULL,
  `planet_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `amount` int(11) NOT NULL DEFAULT '0',
  `power` tinyint(2) NOT NULL DEFAULT '10'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_private` (
  `id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL DEFAULT '0',
  `a_id` int(11) NOT NULL DEFAULT '0',
  `text` varchar(255) NOT NULL,
  `time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_queue` (
  `id` int(11) NOT NULL,
  `type` enum('build','tech','unit') NOT NULL,
  `user_id` int(11) NOT NULL,
  `planet_id` int(11) NOT NULL,
  `object_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `operation` enum('build','destroy') NOT NULL,
  `time` int(11) NOT NULL,
  `time_end` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_refs` (
  `r_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `u_id` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_rw` (
  `id` int(11) UNSIGNED NOT NULL,
  `id_users` varchar(255) NOT NULL DEFAULT '',
  `raport` mediumtext NOT NULL,
  `no_contact` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `time` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_savelog` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `log` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_sessions` (
  `id` int(11) NOT NULL,
  `token` varchar(100) DEFAULT NULL,
  `object_type` enum('client','user') DEFAULT NULL,
  `object_id` int(11) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  `lifetime` int(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_statpoints` (
  `id` int(11) NOT NULL,
  `id_owner` int(11) NOT NULL DEFAULT '0',
  `username` varchar(35) NOT NULL DEFAULT '',
  `race` tinyint(1) NOT NULL DEFAULT '0',
  `id_ally` int(11) NOT NULL DEFAULT '0',
  `ally_name` varchar(50) NOT NULL DEFAULT '',
  `stat_type` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `stat_code` int(11) NOT NULL DEFAULT '0',
  `tech_rank` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `tech_old_rank` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `tech_points` bigint(20) NOT NULL DEFAULT '0',
  `tech_count` int(11) NOT NULL DEFAULT '0',
  `build_rank` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `build_old_rank` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `build_points` bigint(20) NOT NULL DEFAULT '0',
  `build_count` int(11) NOT NULL DEFAULT '0',
  `defs_rank` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `defs_old_rank` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `defs_points` bigint(20) NOT NULL DEFAULT '0',
  `defs_count` int(11) NOT NULL DEFAULT '0',
  `fleet_rank` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `fleet_old_rank` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `fleet_points` bigint(20) NOT NULL DEFAULT '0',
  `fleet_count` int(11) NOT NULL DEFAULT '0',
  `total_rank` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `total_old_rank` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `total_points` bigint(20) NOT NULL DEFAULT '0',
  `total_count` int(11) NOT NULL DEFAULT '0',
  `stat_hide` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_support` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '9',
  `time` int(11) NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_users` (
  `id` int(11) UNSIGNED NOT NULL,
  `username` varchar(30) NOT NULL,
  `authlevel` tinyint(1) NOT NULL DEFAULT '0',
  `group_id` smallint(6) NOT NULL DEFAULT '0',
  `banned` int(11) NOT NULL DEFAULT '0',
  `onlinetime` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `ip` bigint(20) NOT NULL DEFAULT '0',
  `sex` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `race` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `planet_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `planet_current` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `bonus` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `ally_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `ally_name` varchar(50) NOT NULL DEFAULT '',
  `lvl_minier` smallint(6) UNSIGNED NOT NULL DEFAULT '1',
  `lvl_raid` smallint(6) UNSIGNED NOT NULL DEFAULT '1',
  `xpminier` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `xpraid` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `credits` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `messages` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `messages_ally` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `avatar` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `galaxy` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `system` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `planet` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `vacation` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `deltime` int(11) NOT NULL DEFAULT '0',
  `rpg_geologue` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `rpg_admiral` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `rpg_ingenieur` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `rpg_technocrate` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `rpg_constructeur` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `rpg_meta` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `rpg_komandir` int(11) NOT NULL DEFAULT '0',
  `raids_win` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `raids_lose` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `raids` int(11) NOT NULL DEFAULT '0',
  `bonus_multi` tinyint(2) NOT NULL DEFAULT '0',
  `refers` int(11) NOT NULL DEFAULT '0',
  `message_block` int(11) NOT NULL DEFAULT '0',
  `links` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `chat` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `game_users` (`id`, `username`, `authlevel`, `group_id`, `banned`, `onlinetime`, `ip`, `options`, `sex`, `race`, `planet_id`, `planet_current`, `bonus`, `ally_id`, `ally_name`, `lvl_minier`, `lvl_raid`, `xpminier`, `xpraid`, `credits`, `messages`, `messages_ally`, `avatar`, `galaxy`, `system`, `planet`, `vacation`, `deltime`, `rpg_geologue`, `rpg_admiral`, `rpg_ingenieur`, `rpg_technocrate`, `rpg_constructeur`, `rpg_meta`, `rpg_komandir`, `raids_win`, `raids_lose`, `raids`, `bonus_multi`, `refers`, `message_block`, `links`, `chat`) VALUES
(1, 'admin', 3, 0, 0, 1526921725, 0, 0, 1, 1, 1, 1, 0, 0, '', 1, 1, 0, 0, 0, 0, 0, 8, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

CREATE TABLE `game_users_auth` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `external_id` varchar(255) NOT NULL DEFAULT '',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `enter_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_users_groups` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `metadata` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_users_info` (
  `id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `password` char(32) NOT NULL DEFAULT '',
  `email` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `second_name` varchar(100) NOT NULL DEFAULT '',
  `last_name` varchar(100) NOT NULL DEFAULT '',
  `gender` enum('M','F') DEFAULT NULL,
  `photo` varchar(150) DEFAULT NULL,
  `birthday` varchar(20) NOT NULL DEFAULT '',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `fleet_shortcut` text NOT NULL,
  `free_race_change` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `image` int(11) NOT NULL DEFAULT '0',
  `about` text NOT NULL,
  `username_last` varchar(150) NOT NULL DEFAULT '',
  `settings` json NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `game_users_info` (`id`, `password`, `email`, `name`, `second_name`, `last_name`, `gender`, `photo`, `birthday`, `create_time`, `fleet_shortcut`, `free_race_change`, `image`, `about`, `username_last`, `settings`) VALUES
(1, 'e10adc3949ba59abbe56e057f20f883e', 'admin@xnova.su', '', '', '', NULL, NULL, '', 0, '', 1, 0, '', '', '{\"spy\": 1, \"color\": 1, \"chatbox\": true, \"records\": true, \"timezone\": 0, \"bb_parser\": false, \"planetlist\": false, \"planet_sort\": 0, \"only_available\": false, \"planetlistselect\": false, \"planet_sort_order\": 0}');

CREATE TABLE `game_users_payments` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL DEFAULT '0',
  `product_code` varchar(100) NOT NULL DEFAULT '',
  `call_id` int(11) NOT NULL DEFAULT '0',
  `method` varchar(100) NOT NULL DEFAULT '',
  `transaction_id` bigint(20) NOT NULL DEFAULT '0',
  `transaction_time` datetime NOT NULL,
  `uid` bigint(20) NOT NULL DEFAULT '0',
  `amount` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_users_quests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `quest_id` int(11) NOT NULL DEFAULT '0',
  `finish` enum('0','1') NOT NULL DEFAULT '0',
  `stage` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_users_tech` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tech_id` int(11) DEFAULT NULL,
  `level` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `bot_callback_query`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `bot_chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `old_id` (`old_id`);

ALTER TABLE `bot_chosen_inline_query`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `bot_conversation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `chat_id` (`chat_id`),
  ADD KEY `status` (`status`);

ALTER TABLE `bot_inline_query`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `bot_message`
  ADD PRIMARY KEY (`chat_id`,`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `forward_from` (`forward_from`),
  ADD KEY `forward_from_chat` (`forward_from_chat`),
  ADD KEY `reply_to_chat` (`reply_to_chat`),
  ADD KEY `reply_to_message` (`reply_to_message`),
  ADD KEY `new_chat_member` (`new_chat_member`),
  ADD KEY `left_chat_member` (`left_chat_member`),
  ADD KEY `migrate_from_chat_id` (`migrate_from_chat_id`),
  ADD KEY `migrate_to_chat_id` (`migrate_to_chat_id`),
  ADD KEY `reply_to_chat_2` (`reply_to_chat`,`reply_to_message`);

ALTER TABLE `bot_requests`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `bot_telegram_update`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`chat_id`,`message_id`),
  ADD KEY `inline_query_id` (`inline_query_id`),
  ADD KEY `chosen_inline_query_id` (`chosen_inline_query_id`),
  ADD KEY `callback_query_id` (`callback_query_id`);

ALTER TABLE `bot_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

ALTER TABLE `bot_user_chat`
  ADD PRIMARY KEY (`user_id`,`chat_id`),
  ADD KEY `chat_id` (`chat_id`);

ALTER TABLE `game_access`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_aks`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_aks_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aks_id` (`aks_id`);

ALTER TABLE `game_alliance`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_alliance_chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ally_id` (`ally_id`);

ALTER TABLE `game_alliance_diplomacy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `a_id` (`a_id`,`d_id`);

ALTER TABLE `game_alliance_members`
  ADD UNIQUE KEY `u_id` (`u_id`),
  ADD KEY `a_id` (`a_id`);

ALTER TABLE `game_alliance_requests`
  ADD UNIQUE KEY `a_id` (`a_id`,`u_id`);

ALTER TABLE `game_artifacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `game_banned`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_bots_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

ALTER TABLE `game_buddy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender` (`sender`),
  ADD KEY `owner` (`owner`);

ALTER TABLE `game_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `alias` (`alias`);

ALTER TABLE `game_errors`
  ADD PRIMARY KEY (`error_id`);

ALTER TABLE `game_files`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_fleets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fleet_owner` (`owner`),
  ADD KEY `fleet_target_owner` (`target_owner`),
  ADD KEY `fleet_time` (`update_time`),
  ADD KEY `fleet_end_system` (`end_system`),
  ADD KEY `fleet_start_time` (`start_time`),
  ADD KEY `fleet_end_stay` (`end_stay`),
  ADD KEY `fleet_end_time` (`end_time`),
  ADD KEY `fleet_group` (`group_id`);

ALTER TABLE `game_groups`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_groups_access`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `access_id` (`access_id`);

ALTER TABLE `game_hall`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sab` (`sab`),
  ADD KEY `time` (`time`);

ALTER TABLE `game_logs`
  ADD KEY `time` (`time`),
  ADD KEY `s_id` (`s_id`);

ALTER TABLE `game_log_attack`
  ADD KEY `uid` (`uid`,`time`);

ALTER TABLE `game_log_chat`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_log_email`
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `game_log_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `game_log_load`
  ADD KEY `time` (`time`);

ALTER TABLE `game_log_sim`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sid` (`sid`);

ALTER TABLE `game_log_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`object_id`,`type`);

ALTER TABLE `game_log_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `game_log_username`
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `game_lostpasswords`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_owner` (`user_id`),
  ADD KEY `message_time` (`time`),
  ADD KEY `message_sender` (`from_id`),
  ADD KEY `message_owner_2` (`user_id`,`deleted`);

ALTER TABLE `game_modules`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_moneys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ip` (`ip`),
  ADD KEY `time` (`time`);

ALTER TABLE `game_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner` (`user_id`);

ALTER TABLE `game_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `game_planets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_owner` (`id_owner`),
  ADD KEY `galaxy` (`galaxy`),
  ADD KEY `system` (`system`),
  ADD KEY `id_ally` (`id_ally`);

ALTER TABLE `game_planets_buildings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `planet_id` (`planet_id`,`build_id`);

ALTER TABLE `game_planets_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `planet_id` (`planet_id`,`unit_id`);

ALTER TABLE `game_private`
  ADD PRIMARY KEY (`id`),
  ADD KEY `u_id` (`u_id`);

ALTER TABLE `game_queue`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_refs`
  ADD PRIMARY KEY (`r_id`);

ALTER TABLE `game_rw`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_savelog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user_id`);

ALTER TABLE `game_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

ALTER TABLE `game_statpoints`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `stat_type` (`stat_type`),
  ADD KEY `id_owner` (`id_owner`),
  ADD KEY `total_rank` (`total_rank`),
  ADD KEY `tech_rank` (`tech_rank`),
  ADD KEY `defs_rank` (`defs_rank`),
  ADD KEY `fleet_rank` (`fleet_rank`),
  ADD KEY `stat_type_2` (`stat_type`,`stat_code`,`stat_hide`);

ALTER TABLE `game_support`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player_id` (`user_id`);

ALTER TABLE `game_users`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_users_auth`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `external_id` (`external_id`);

ALTER TABLE `game_users_groups`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_users_info`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_users_payments`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `game_users_quests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `game_users_tech`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`tech_id`);


ALTER TABLE `bot_chosen_inline_query`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for chosen query.';

ALTER TABLE `bot_conversation`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Row unique id';

ALTER TABLE `bot_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

ALTER TABLE `game_aks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_aks_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_alliance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_alliance_chat`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_alliance_diplomacy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_artifacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_banned`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_bots_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_buddy`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `game_errors`
  MODIFY `error_id` bigint(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_fleets`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `game_groups_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_hall`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_log_chat`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_log_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_log_sim`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_log_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_log_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_lostpasswords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_messages`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `game_moneys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_notes`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

ALTER TABLE `game_planets`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `game_planets_buildings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_planets_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_private`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_rw`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_savelog`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `game_statpoints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_support`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `game_users_auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_users_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_users_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_users_quests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `game_users_tech`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
