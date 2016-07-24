<? if ($this->config->view->get('socialIframeView', 0) && $_SERVER['SERVER_NAME'] == 'ok1.xnova.su'): ?>
	<script type="text/javascript">
		var title = [];
		title[0] = "Звездная империя – космическая стратегия в реальном времени.";
		title[1] = "Звездная империя - отличная космическая войнушка.";
		title[2] = "Звездная империя - интересная игра, приглашаю стать моим союзником.";
		title[3] = "Отстраивай планету и повышай свой рейтинг";
		title[4] = "Ищу союзников, для серьезных дел.";
		title[5] = "Игра затягивает! Советую!";
		title[6] = "Друзья, помогите мне с победой. Спасибо!";
		title[7] = "Пришло время воевать!";

		function showInviteBox ()
		{
			var text = title[Math.round(Math.random()*7)];

			FAPI.UI.showInvite(text, 'userId=<?=$this->user->getId() ?>');
		}
	</script>
<? endif; ?>
<a href="#" class="menu-toggle hidden-sm-up">
    <span>
        <span class="first"></span>
        <span class="second"></span>
        <span class="third"></span>
    </span>
</a>
<div class="menu-sidebar hidden-sm-up">
	<ul class="nav">
		<? foreach(_getText('main_menu') AS $id => $menu): if ($menu[2] > $adminlevel) continue; ?>
			<li><a <? if (isset($menu[3])): ?>data-link="Y"<? endif; ?> href="<?=$menu[1] ?>" <?=($controller == $id ? 'class="check"' : '') ?>><?=$menu[0] ?></a></li>
		<? endforeach; ?>
	</ul>
</div>

<header class="game_menu">
	<div class="hidden-sm-up text-xs-center bar">
		<? if (isset($tutorial) && $tutorial < 10): ?>
			<a class="m1 tooltip" href="{{ url("tutorial/") }}" data-content="Квесты"><span class="sprite ico_tutorial"></span></a>
		<? endif; ?>
		<a class="m1 tooltip" href="{{ url("chat/") }}" data-content="Чат"><span class="sprite ico_chat"></span></a>
		<a class="m1 tooltip" href="{{ url("messages/") }}" data-content="Сообщения"><span class="sprite ico_mail"></span> <b><?=$messages ?></b></a>
		<? if ($messages_ally != ''): ?>
			<a class="m1 tooltip" href="{{ url("alliance/chat/") }}" data-content="Альянс"><span class="sprite ico_alliance"></span> <b><?=$messages_ally ?></b></a>
		<? endif; ?>
	</div>
	<div class="bar hidden-xs-down">
		<div class="message_list">
			<? if (isset($tutorial) && $tutorial < 10): ?>
				<a class="m1 tooltip" href="{{ url("tutorial/") }}" data-content="Квесты"><span class="sprite ico_tutorial"></span></a>
			<? endif; ?>
			<a class="m1 tooltip" href="{{ url("chat/") }}" data-content="Чат"><span class="sprite ico_chat"></span></a>
			<a class="m1 tooltip" href="{{ url("messages/") }}" data-content="Сообщения"><span class="sprite ico_mail"></span> <b><?=$messages ?></b></a>
			<? if ($messages_ally != ''): ?>
				<a class="m1 tooltip" href="{{ url('alliance/chat/') }}" data-content="Альянс"><span class="sprite ico_alliance"></span> <b><?=$messages_ally ?></b></a>
			<? endif; ?>
		</div>
		<div class="top_menu">
			<? if ($this->config->view->get('socialIframeView', 0)): ?>
				<a href="?fullscreen=Y" target="_blank" class="tooltip m1" data-content="Развернуть"><span class="sprite ico_fullscreen"></span></a>
			<? endif; ?>
			<? if (!$this->config->view->get('socialIframeView', 0)): ?>
				<a href="http://xnova.su/" target="_blank" class="tooltip m1" data-content="Вселенные"><span class="sprite ico_space"></span></a>
			<? endif; ?>
			<a href="{{ url("stat/") }}" class="tooltip m1" data-content="Статистика"><span class="sprite ico_stats"></span></a>
			<a href="{{ url("tech/") }}" class="tooltip m1" data-content="Технологии"><span class="sprite ico_tech"></span></a>
			<a href="{{ url("sim/") }}" class="tooltip m1" data-content="Симулятор"><span class="sprite ico_sim"></span></a>
			<a href="{{ url("search/") }}" class="tooltip m1" data-content="Поиск"><span class="sprite ico_search"></span></a>
			<? if ($this->config->view->get('socialIframeView', 0) == 0): ?>
				<a href="{{ url("support/") }}" class="tooltip m1" data-content="Техподдержка"><span class="sprite ico_support"></span></a>
			<? endif; ?>
			<? if ($this->config->view->get('socialIframeView', 0) == 0): ?>
				<a href="<?=$this->config->game->get('forum_url', '') ?>" target="_blank" class="tooltip m1" data-content="Форум"><span class="sprite ico_forum"></span></a>
			<? endif; ?>
			<a href="{{ url("options/") }}" class="tooltip m1" data-content="Настройки"><span class="sprite ico_settings"></span></a>
			<? if ($this->config->view->get('socialIframeView', 0) == 0): ?>
				<a href="{{ url("logout/") }}" class="tooltip m1" data-link="Y" data-content="Выход"><span class="sprite ico_exit"></span></a>
			<? endif; ?>
			<? if ($this->config->view->get('socialIframeView', 0) && $_SERVER['SERVER_NAME'] == 'ok1.xnova.su'): ?>
				<? if (!$this->cookies->has($this->config->cookie->prefix.'_full') || $this->cookies->get($this->config->cookie->prefix.'_full') == 'N'): ?>
					<a class="m1" style="color:#38CA38" href="javascript:;" onclick="showInviteBox()">Пригласить друзей</a>
				<? endif; ?>
			<? endif; ?>
		</div>
	</div>
</header>
<div class="icon-panel hidden-sm-up">
	<a href="{{ url("stat/") }}" class="sprite ico_stats"></a>
	<a href="{{ url("tech/") }}" class="sprite ico_tech"></a>
	<a href="{{ url("sim/") }}" class="sprite ico_sim"></a>
	<a href="{{ url("search/") }}" class="sprite ico_search"></a>
	<a href="{{ url("support/") }}" class="sprite ico_support"></a>
	<a href="<?=$this->config->game->get('forum_url', '') ?>" target="_blank" class="sprite ico_forum"></a>
	<a href="{{ url("options/") }}" class="sprite ico_settings"></a>
	<a href="{{ url("logout/") }}" class="sprite ico_exit"  data-link="Y"></a>
</div>