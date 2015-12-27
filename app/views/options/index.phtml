<form action="?set=options&mode=change" method="post">
	<div id="tabs" class="ui-tabs ui-widget ui-widget-content">
		<div class="head">
			<ul class="ui-tabs-nav ui-widget-header">
				<li><a href="#tabs-0">Информация</a></li>
				<li><a href="#tabs-1">Интерфейс</a></li>
				<li><a href="#tabs-2">Описание</a></li>
				<li><a href="#tabs-3">Отпуск / Удаление</a></li>
				<li><a href="#tabs-4">Личное дело</a></li>
				<? if (\Xcms\Core::getConfig('socialIframeView', 0) == 0): ?>
					<li><a href="#tabs-5">Точки входа</a></li>
				<? endif; ?>
			</ul>
		</div>
		<div id="tabs-0" class="ui-tabs-panel ui-widget-content">
			<table class="table">
				<tr>
					<th width="50%">
						Ник
						<br>
						<span class="negative">Можно менять не чаще раза в сутки</span>
					</th>
					<th>
						<? if ($parse['opt_usern_datatime'] < (time() - 86400)): ?><input name="db_character" size="20" value="<? endif; ?><?=$parse['opt_usern_data'] ?><? if ($parse['opt_usern_datatime'] < (time() - 86400)): ?>" type="text"><? endif; ?>
					</th>
				</tr>
				<? if (\Xcms\Core::getConfig('socialIframeView', 0) == 0): ?>
					<? if (is_email($parse['opt_mail_data'])): ?>
						<tr>
							<th>Старый пароль</th>
							<th><input name="db_password" size="20" value="" type="password"></th>
						</tr>
						<tr>
							<th>Новый пароль (мин. 8 Знаков)</th>
							<th><input name="newpass1" size="20" maxlength="40" type="password"></th>
						</tr>
						<tr>
							<th>Новый пароль (повтор)</th>
							<th><input name="newpass2" size="20" maxlength="40" type="password"></th>
						</tr>
					<? endif; ?>
					<tr>
						<th>Адрес e-mail (логин)</th>
						<th>
							<? if (!is_email($parse['opt_mail_data'])): ?>
								<input type="text" name="email" value="">
							<? else: ?>
								<?=$parse['opt_mail_data'] ?> <a href="?set=options&mode=changeemail" class="button">сменить</a>
							<? endif; ?>
						</th>
					</tr>
				<? endif; ?>
				<tr>
					<th>Пол</th>
					<th><select name="sex">
						<option value="M">мужской</option>
						<option value="F" <?=(($parse['sex'] == 2) ? ' selected' : '')?>>женский</option>
					</select></th>
				</tr>
				<tr>
					<th colspan="2"><input value="Сохранить изменения" type="submit"></th>
				</tr>
			</table>
		</div>
		<div id="tabs-1" class="ui-tabs-panel ui-widget-content"  style="display: none">
			<table class="table">
				<tr>
					<th>Упорядочить планеты по:</th>
					<th>
						<select name="settings_sort" style='width:170px'>
							<?=$parse['opt_lst_ord_data'] ?>
						</select>
					</th>
				</tr>
				<tr>
					<th>Упорядочить по:</th>
					<th>
						<select name="settings_order" style='width:170px'>
							<?=$parse['opt_lst_cla_data'] ?>
						</select>
					</th>
				</tr>
				<tr>
					<th>Кол-во по умолчанию отправляемых<br> шпионских зондов в меню "Космос"</th>
					<th><input name="spy" value="<?=$parse['spy'] ?>" type="text"></th>
				</tr>
				<tr>
					<th>Участвовать в рекордах</th>
					<th><input name="records"<?=$parse['opt_record_data'] ?> type="checkbox"></th>
				</tr>
				<tr>
					<th>Использовать BB коды в сообщениях</th>
					<th><input name="bbcode"<?=$parse['opt_bbcode_data'] ?> type="checkbox"></th>
				</tr>
				<tr>
					<th>Показывать только доступные постройки</th>
					<th><input name="available"<?=$parse['opt_available_data'] ?> type="checkbox"></th>
				</tr>
				<? if (\Xcms\Core::getConfig('socialIframeView', 0) == 0): ?>
					<tr>
						<th>Включить просмотр игровой активности</th>
						<th><input name="gameactivity"<?=$parse['opt_gameactivity_data'] ?> type="checkbox"></th>
					</tr>
					<tr>
						<th>Выпадающий список планет</th>
						<th><input name="planetlistselect"<?=$parse['opt_planetlistselect_data'] ?> type="checkbox"></th>
					</tr>
					<tr>
						<th>Повышенная безопасность входа</th>
						<th><input name="security"<?=$parse['opt_sec_data'] ?> type="checkbox"></th>
					</tr>
					<tr>
						<th>
							Включить ускорение интерфейса игры
							<br>
							<span class="negative">Работает некорректно на IE ниже 9 версии<br>и на телефонах с Android OS ниже 4 версии</span>
						</th>
						<th><input name="ajaxnav"<?=$parse['opt_ajax_data'] ?> type="checkbox"></th>
					</tr>
				<? endif; ?>
				<tr>
					<th>Цвет чата</th>
					<th>
						<select name='color' style='width:170px'>
							<? foreach(_getText('colors') AS $id => $color): if (!$color[1]) continue; ?>
								<option value="<?=$id ?>" <?=($parse['color'] == $id ? 'selected' : '') ?> style="color:<?=$color[0] ?>"><?=$color[1] ?></option>
							<? endforeach; ?>
						</select>
					</th>
				</tr>
				<tr>
					<th>Часовой пояс</th>
					<th><select name='timezone' style='width:170px'>
						<option value="-30"<?=(($parse['timezone'] == (-30)) ? 'selected' : '')?>>-12</option>
						<option value="-28"<?=(($parse['timezone'] == (-28)) ? 'selected' : '')?>>-11</option>
						<option value="-26"<?=(($parse['timezone'] == (-26)) ? 'selected' : '')?>>-10</option>
						<option value="-24"<?=(($parse['timezone'] == (-24)) ? 'selected' : '')?>>-9</option>
						<option value="-22"<?=(($parse['timezone'] == (-22)) ? 'selected' : '')?>>-8</option>
						<option value="-20"<?=(($parse['timezone'] == (-20)) ? 'selected' : '')?>>-7</option>
						<option value="-18"<?=(($parse['timezone'] == (-18)) ? 'selected' : '')?>>-6</option>
						<option value="-16"<?=(($parse['timezone'] == (-16)) ? 'selected' : '')?>>-5</option>
						<option value="-14"<?=(($parse['timezone'] == (-14)) ? 'selected' : '')?>>-4</option>
						<option value="-12"<?=(($parse['timezone'] == (-12)) ? 'selected' : '')?>>-3</option>
						<option value="-10"<?=(($parse['timezone'] == (-10)) ? 'selected' : '')?>>-2</option>
						<option value="-8"<?=(($parse['timezone'] == (-8)) ? 'selected' : '')?>>-1</option>
						<option value="-6"<?=(($parse['timezone'] == (-6)) ? 'selected' : '')?>>0</option>
						<option value="-4"<?=(($parse['timezone'] == (-4)) ? 'selected' : '')?>>+1</option>
						<option value="-2"<?=(($parse['timezone'] == (-2)) ? 'selected' : '')?>>+2</option>
						<option value="0"<?=(($parse['timezone'] == 0) ? 'selected' : '')?>>+3 (Московское время)</option>
						<option value="2"<?=(($parse['timezone'] == 2) ? 'selected' : '')?>>+4</option>
						<option value="4"<?=(($parse['timezone'] == 4) ? 'selected' : '')?>>+5</option>
						<option value="6"<?=(($parse['timezone'] == 6) ? 'selected' : '')?>>+6</option>
						<option value="8"<?=(($parse['timezone'] == 8) ? 'selected' : '')?>>+7</option>
						<option value="10"<?=(($parse['timezone'] == 10) ? 'selected' : '')?>>+8</option>
						<option value="12"<?=(($parse['timezone'] == 12) ? 'selected' : '')?>>+9</option>
						<option value="14"<?=(($parse['timezone'] == 14) ? 'selected' : '')?>>+10</option>
						<option value="16"<?=(($parse['timezone'] == 16) ? 'selected' : '')?>>+11</option>
						<option value="18"<?=(($parse['timezone'] == 18) ? 'selected' : '')?>>+12</option>
					</select></th>
				</tr>
				<? if (\Xcms\Core::getConfig('socialIframeView', 0) == 0): ?>
					<tr>
						<th>Аватар</th>
						<th><?=$parse['avatar'] ?> <a href="?set=avatar" class="button">Выбрать аватар</a></th>
					</tr>
				<? endif; ?>
				<tr>
					<th colspan="2"><input value="Сохранить изменения" type="submit"></th>
				</tr>
			</table>
		</div>
		<div id="tabs-2" class="ui-tabs-panel ui-widget-content"  style="display: none">
			<table class="table">
				<tr>
					<th colspan="2" class="nopadding">
						<div id="editor"></div>
						<textarea name="text" id="text" cols="" rows="10"><?=preg_replace('!<br.*>!iU', "\n", $parse['about']) ?></textarea>

						<div id="showpanel" style="display:none">
							<table class="table">
								<tr>
									<td class="c"><b>Предварительный просмотр</b></td>
								</tr>
								<tr>
									<td class="b"><span id="showbox"></span></td>
								</tr>
							</table>
						</div>
						<script type="text/javascript">edToolbar('text');</script>
					</th>
				</tr>
				<tr>
					<th colspan="2"><input value="Сохранить изменения" type="submit"></th>
				</tr>
			</table>
		</div>
		<div id="tabs-3" class="ui-tabs-panel ui-widget-content"  style="display: none">
			<table class="table">
				<tr>
					<th width="50%"><a title="Режим отпуска нужен для защиты планет во время вашего отсутствия">Включить режим отпуска</a></th>
					<th><input name="urlaubs_modus"<?=$parse['opt_modev_data'] ?> type="checkbox"/></th>
				</tr>
				<tr>
					<th colspan="2">
						<span class="negative">Режим отпуска включается минимум на 2 суток!<br>Пока это время не прошло, выключить режим отпуска НЕВОЗМОЖНО!</span>
					</th>
				</tr>
				<tr>
					<th><a title="Профиль будет удалён через 7 дней">Удалить профиль</a></th>
					<th><input name="db_deaktjava"<?=$parse['opt_delac_data'] ?> type="checkbox"/></th>
				</tr>
				<tr>
					<th colspan="2">
						<span class="negative">Ваш профиль будет удален спустя несколько дней, в течение которых вы можете отменить данную процедуру.</span>
					</th>
				</tr>
				<tr>
					<th colspan="2"><input value="Сохранить изменения" type="submit"></th>
				</tr>
			</table>
		</div>
		<div id="tabs-4" class="ui-tabs-panel ui-widget-content"  style="display: none">
			<table class="table">
				<tr>
					<td class="c">Добавить запись в личное дело</td>
				</tr>
				<tr>
					<th><textarea name="ld" cols="" rows="5"></textarea></th>
				</tr>
				<tr>
					<th><input value="Записать" type="submit"></th>
				</tr>
			</table>
		</div>
		<? if (\Xcms\Core::getConfig('socialIframeView', 0) == 0): ?>
			<div id="tabs-5" class="ui-tabs-panel ui-widget-content"  style="display: none">
				<? if (count($parse['auth'])): ?>
					<table class="table">
						<tr>
							<td class="c">Аккаунт</td>
							<td class="c">Дата регистрации</td>
							<td class="c">Последняя авторизация</td>
						</tr>
						<? foreach ($parse['auth'] AS $auth): ?>
							<tr>
								<th><?=$auth['external_id'] ?></th>
								<th><?=datezone("d.m.Y H:i:s", $auth['register_time']) ?></th>
								<th><?=($auth['enter_time'] > 0 ? datezone("d.m.Y H:i:s", $auth['enter_time']) : '-') ?></th>
							</tr>
						<? endforeach; ?>
					</table>
				<? endif; ?>
				<table class="table">
					<tr>
						<td class="c">Привязать аккаунт к социальным сетям</td>
					</tr>
					<tr>
						<th>
							<br>
							<script type="text/javascript" src="http://u-login.com/js/ulogin.js"></script>
							<div id="uLogin" x-ulogin-params="display=panel;fields=first_name,last_name,photo;providers=vkontakte,odnoklassniki,facebook,twitter,yandex,googleplus,mailru;redirect_uri=<?=urlencode("http://".$_SERVER['SERVER_NAME']."/?set=options&mode=external") ?>"></div>
							<br>
						</th>
					</tr>
				</table>
			</div>
		<? endif; ?>
	</div>
</form>
<script type="text/javascript">
	$(document).ready(function()
	{
		$( "#tabs" ).tabs();
	});
</script>