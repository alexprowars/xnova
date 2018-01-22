<div class="left">
	<div class="middle">
		<div class="loginform">
			<div class="login">Вход в игру:</div>

			<div class="login-inputs">
				<div class="error" id="authError"></div>
				<form action="{{ url('login/') }}" method="post" id="authForm">
					<div>
						<input class="input-text" name="email" placeholder="Email" value="" type="text" />
						<input class="input-text" name="password" placeholder="Пароль" value="" type="password" />
						<input class="input-submit" type="submit" value="Вход" />
						<div class="remember">
							<input name="rememberme" id="rememberme" type="checkbox"><label for="rememberme">Запомнить?</label>
						</div>
					</div>
				</form>
			</div>
			<div class="lost-pass"><a id="lost-pass-link" href="javascript:;" onclick="showWindow('Восстановление пароля', '{{ url('remind/') }}', (window.orientation !== undefined ? 300 : 400), 200);" title="Восстановление пароля">Забыли?</a></div>
			<div class="sm">
				Войти с помощью:<br><br>
				<script type="text/javascript" src="//ulogin.ru/js/ulogin.js"></script>
				<div id="uLogin" data-uloginid="e4860195" x-ulogin-params="display=panel;fields=first_name,last_name,photo;providers=vkontakte,odnoklassniki,facebook,twitter,yandex,googleplus,mailru;redirect_uri=http%3A%2F%2F{{ request.getServer('SERVER_NAME') }}%2F"></div>
			</div>
		</div>
	</div>
</div>
<div class="right">
	<div class="middle">
		<div class="text">
			<h1>{{ option('site_title') }} - это браузерная игра в жанре космическая стратегия</h1>

			<p>Захватывающие битвы, множество альянсов, нескончаемый игровой мир, тысячи противников,
			-&nbsp;это неполный список того, что вам предстоит испытать на себе в космической стратегии XNova.</p>

			<p>Завоёвывайте планеты, покоряйте галактики, создайте нерушимый альянс сильнейших игроков!
			Сойдитесь в неравной космической битве со своими противниками, окунувшись в зрелищный и захватывающий мир XNova!</p>

			<div id="reg_button"><a href="javascript:;" onclick="showWindow('Регистрация', '/registration/', (window.orientation !== undefined ? 300 : 500), 400);">Регистрация</a></div>
			<div class="clear"></div>
		</div>
	</div>
</div>
<div style="position: absolute;bottom: 30px;right: 10px;">
	<a href="//www.free-kassa.ru/"><img src="//www.free-kassa.ru/img/fk_btn/17.png"></a>
</div>
<div class="bottom">
	<div class="desk">{{ option('site_title') }} - онлайн-игра</div>
	<div class="nav">
		<a href="http://forum.xnova.su" title="Официальный форум" target="_blank">Форум</a>  |
		<a href="{{ url('xnsim/') }}">Симулятор</a>  |  <a href="{{ url('stat/') }}">Статистика</a>  |
		<a href="//vkontakte.ru/xnova_game" title="Официальная группа ВКонтакте" target="_blank">ВКонтакте</a>  |
		<a href="{{ url('content/agb/') }}">Правила</a>  |
		<a href="{{ url('banned/') }}">Блокировки</a>  |
		<a href="{{ url('contact/') }}">Администрация</a>
	</div>
	<div class="copy">{{ option('users_online') }} / {{ option('users_total') }}&nbsp;&nbsp;&nbsp;&copy; {{ date("Y") }} XNOVA.SU</div>
</div>
<div id="mask"></div>

<script type="text/javascript">
	$(document).ready(function()
	{
		$('#authForm').on('submit', function(e)
		{
			e.preventDefault();

			$.ajax({
				url: '{{ url('login/') }}',
				type: 'post',
				data: $(this).serialize(),
				success: function(result)
				{
					if (result.status && data.result.redirect !== undefined)
						window.location.href = result.data.redirect;
					else
					{
						result.data.messages.forEach(function(item)
						{
							$('#authError').html(item.text);
						});
					}
				}
			});
		});
	});
</script>
<div id="windowDialog" class="hidden"></div>