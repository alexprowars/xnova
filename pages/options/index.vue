<template>
	<div>
		<router-form v-if="page['vacation']" action="/options/change/">
			<table class="table">
				<tbody>
					<tr>
						<td class="c" colspan="2">Режим отпуска</td>
					</tr>
					<tr>
					</tr>
					<tr>
						<th colspan="2">Режим отпуска включён до: <br/>{{ page['um_end_date'] }}</th>
					</tr>
					<tr>
						<th>Имя</th>
						<th><input name="username" size="20" :value="page['opt_usern_data']" type="hidden">{{ page['opt_usern_data'] }}</th>
					</tr>
					<tr>
						<th><a title="Режим отпуска нужен для защиты планет во время вашего отсутствия.">Включить режим отпуска</a></th>
						<th><input name="vacation" v-model="page['opt_modev_data']" type="checkbox" title=""></th>
					</tr>
					<tr>
						<th><a title="Профиль будет удалён через 7 дней.">Удалить профиль</a></th>
						<th><input name="delete" v-model="page['opt_delac_data']" type="checkbox" title=""></th>
					</tr>
					<tr>
						<th colspan="2"><input type="submit" value="Сохранить изменения"/></th>
					</tr>
				</tbody>
			</table>
		</router-form>

		<router-form v-else action="/options/change/">
			<tabs>
				<tab name="Информация">
					<table class="table">
						<tbody>
							<tr>
								<th width="50%">
									Ник
									<br>
									<span class="negative">Можно менять не чаще раза в сутки</span>
								</th>
								<th>
									<input v-if="page['opt_usern_datatime']" name="username" size="20" :value="page['opt_usern_data']" type="text" title="" autocomplete="username">
									<template v-else>{{ page['opt_usern_data'] }}</template>
								</th>
							</tr>
							<template v-if="!page['social']">
								<template v-if="page['opt_isemail']">
									<tr>
										<th>Старый пароль</th>
										<th><input name="password" size="20" value="" type="password" title="" autocomplete="current-password"></th>
									</tr>
									<tr>
										<th>Новый пароль (мин. 8 Знаков)</th>
										<th><input name="new_password" size="20" maxlength="40" type="password" title="" autocomplete="new-password"></th>
									</tr>
									<tr>
										<th>Новый пароль (повтор)</th>
										<th><input name="new_password_confirm" size="20" maxlength="40" type="password" title="" autocomplete="new-password"></th>
									</tr>
								</template>
								<tr>
									<th>Адрес e-mail (логин)</th>
									<th>
										<input v-if="!page['opt_isemail']" type="text" name="email" value="" title="">
										<template v-else>
											{{ page['opt_mail_data'] }} <nuxt-link to="/options/email/" class="button">сменить</nuxt-link>
										</template>
									</th>
								</tr>
							</template>
							<tr>
								<th>Пол</th>
								<th><select name="sex" title="">
									<option value="M">мужской</option>
									<option value="F" :selected="page['sex'] === 2" >женский</option>
								</select></th>
							</tr>
							<tr>
								<th colspan="2"><input value="Сохранить изменения" type="submit"></th>
							</tr>
						</tbody>
					</table>
				</tab>
				<tab name="Интерфейс">
					<template v-if="page['social']">
						<div style="display: none">
							<input name="chatbox" v-model="page['opt_chatbox_data']" type="checkbox" title="">
							<input name="planetlistselect" v-model="page['opt_planetlistselect_data']" type="checkbox" title="">
						</div>
					</template>
					<table class="table">
						<tbody>
							<tr>
								<th rowspan="2" width="50%">Упорядочить планеты по:</th>
								<th>
									<select name="settings_sort" style='width:170px' title="" v-model="page['settings']['planet_sort']">
										<option value="0">Времени колонизации</option>
										<option value="1">Координатам</option>
										<option value="2">Алфавитному порядку</option>
										<option value="3">Типу</option>
									</select>
								</th>
							</tr>
							<tr>
								<th>
									<select name="settings_order" style='width:170px' title="" v-model="page['settings']['planet_sort_order']">
										<option value="0">Возрастанию</option>
										<option value="1">Убыванию</option>
									</select>
								</th>
							</tr>
							<tr>
								<th>Кол-во отправляемых шпионских зондов в меню "Космос"</th>
								<th><input name="spy" :value="page['spy']" type="text" title=""></th>
							</tr>
							<tr>
								<th>Участвовать в рекордах</th>
								<th><input name="records" v-model="page['opt_record_data']" type="checkbox" title=""></th>
							</tr>
							<tr>
								<th>Использовать BB коды в сообщениях</th>
								<th><input name="bbcode" v-model="page['opt_bbcode_data']" type="checkbox" title=""></th>
							</tr>
							<tr>
								<th>Показывать только доступные постройки</th>
								<th><input name="available" v-model="page['opt_available_data']" type="checkbox" title=""></th>
							</tr>
							<tr v-if="!page['social']">
								<th>Показывать панель чата</th>
								<th><input name="chatbox" v-model="page['opt_chatbox_data']" type="checkbox" title=""></th>
							</tr>
							<tr>
								<th>Цвет ваших сообщений в чате</th>
								<th>
									<select name='color' style='width:170px' title="" v-model="page['color']">
										<option v-for="(color, id) in $t('colors')" v-if="color[1] !== ''" :value="id" :style="'color:'+color[0]">{{ color[1] }}</option>
									</select>
								</th>
							</tr>
							<tr>
								<th>Часовой пояс</th>
								<th><select name='timezone' style='width:170px' title="" v-model="page['timezone']">
									<option value="-30">-12</option>
									<option value="-28">-11</option>
									<option value="-26">-10</option>
									<option value="-24">-9</option>
									<option value="-22">-8</option>
									<option value="-20">-7</option>
									<option value="-18">-6</option>
									<option value="-16">-5</option>
									<option value="-14">-4</option>
									<option value="-12">-3</option>
									<option value="-10">-2</option>
									<option value="-8">-1</option>
									<option value="-6">0</option>
									<option value="-4">+1</option>
									<option value="-2">+2</option>
									<option value="0">+3 (Московское время)</option>
									<option value="2">+4</option>
									<option value="4">+5</option>
									<option value="6">+6</option>
									<option value="8">+7</option>
									<option value="10">+8</option>
									<option value="12">+9</option>
									<option value="14">+10</option>
									<option value="16">+11</option>
									<option value="18">+12</option>
								</select></th>
							</tr>
							<tr>
								<th>Аватар</th>
								<th>
									<template v-if="page['avatar'] !== ''">
										<img :src="page['avatar']" height="100" alt=""><br>
										<label>
											<input type="checkbox" name="image_delete" value="Y">
											Удалить
										</label>
										<br><br>
									</template>

									<input type="file" name="image" value=""><br>
									<small>Картинки уменьшаются до размера в 300x300 пикселей</small>
								</th>
							</tr>
							<tr>
								<th colspan="2"><input value="Сохранить изменения" type="submit"></th>
							</tr>
						</tbody>
					</table>
				</tab>
				<tab name="Описание">
					<table class="table">
						<tbody>
							<tr>
								<th colspan="2" class="p-a-0">
									<text-editor :text="page['about']"></text-editor>
								</th>
							</tr>
							<tr>
								<th colspan="2"><input value="Сохранить изменения" type="submit"></th>
							</tr>
						</tbody>
					</table>
				</tab>
				<tab name="Отпуск / Удаление">
					<table class="table">
						<tbody>
							<tr>
								<th width="50%"><a title="Режим отпуска нужен для защиты планет во время вашего отсутствия">Включить режим отпуска</a></th>
								<th><input name="vacation" v-model="page['opt_modev_data']" type="checkbox" title=""></th>
							</tr>
							<tr>
								<th colspan="2">
									<span class="negative">Режим отпуска включается минимум на 2 суток!<br>Пока это время не прошло, выключить режим отпуска НЕВОЗМОЖНО!</span>
								</th>
							</tr>
							<tr>
								<th><a title="Профиль будет удалён через 7 дней">Удалить профиль</a></th>
								<th><input name="delete" v-model="page['opt_delac_data']" type="checkbox" title=""></th>
							</tr>
							<tr>
								<th colspan="2">
									<span class="negative">Ваш профиль будет удален спустя несколько дней, в течение которых вы можете отменить данную процедуру.</span>
								</th>
							</tr>
							<tr>
								<th colspan="2"><input value="Сохранить изменения" type="submit"></th>
							</tr>
						</tbody>
					</table>
				</tab>
				<tab name="Личное дело">
					<table class="table">
						<tbody>
							<tr>
								<td class="c">Добавить запись в личное дело</td>
							</tr>
							<tr>
								<th><textarea name="ld" cols="" rows="5" title=""></textarea></th>
							</tr>
							<tr>
								<th><input value="Записать" type="submit"></th>
							</tr>
						</tbody>
					</table>
				</tab>
				<tab v-if="!page['social']" name="Точки входа">
					<table v-if="page['auth'].length" class="table">
						<tbody>
							<tr>
								<td class="c">Аккаунт</td>
								<td class="c">Дата регистрации</td>
								<td class="c">Последняя авторизация</td>
							</tr>
							<tr v-for="auth in page['auth']">
								<th>{{ auth['external_id'] }}</th>
								<th>{{ auth['create_time'] | date('d.m.Y H:i:s') }}</th>
								<th>
									<template v-if="auth['enter_time'] > 0">
										{{ auth['enter_time'] | date('d.m.Y H:i:s') }}
									</template>
									<template>
										-
									</template>
								</th>
							</tr>
						</tbody>
					</table>
					<table class="table">
						<tbody>
							<tr>
								<td class="c">Привязать аккаунт к социальным сетям</td>
							</tr>
							<tr>
								<th>
									<br>
									<div id="uLogin" data-uloginid="e4860195" :x-ulogin-params="'display=panel;fields=first_name,last_name,photo;providers=vkontakte,odnoklassniki,facebook,twitter,yandex,googleplus,mailru;redirect_uri=http%3A%2F%2F'+$store.state['host']+'%2Foptions%2Fexternal%2F'"></div>
									<br>
								</th>
							</tr>
						</tbody>
					</table>
				</tab>
			</tabs>
		</router-form>

		<template v-if="typeof page['bot_auth'] === 'object'">
			<br><br>
			<div class="table">
				<div class="row">
					<div class="col-12 th">
						Ваш код для привязки Telegram-бота:<br><br><b>{{ page['bot_auth']['code'] }}</b>
					</div>
				</div>
			</div>
		</template>
	</div>
</template>

<script>
	import { addScript } from '~/utils/helpers'

	export default {
		name: 'options',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		mounted () {
			addScript('https://ulogin.ru/js/ulogin.js');
		}
	}
</script>