<template>
	<div>
		<table class="table">
			<tr>
				<td class="c" colspan="3">Привлечённые игроки</td>
			<tr>
				<tr v-if="page['ref'].length > 0">
					<td class="c">Ник</td>
					<td class="c">Дата регистрации</td>
					<td class="c">Уровень развития</td>
				</tr>
				<tr v-for="list in page['ref']">
					<th>
						{% if game.datezone("d", list['create_time']) >= 15 %}
							+
						{% endif %}
						<router-link :to="'/players/'+list['id']+'/'">{{ list['username'] }}</router-link>
					</th>
					<th>{{ list['create_time'] | date('d.m.Y H:i') }}</th>
					<th>П:{{ list['lvl_minier'] }}, В:{{ list['lvl_raid'] }}</th>
				</tr>
				<tr v-if="page['ref'].length === 0">
					<th colspan="3">Нет привлеченных игроков</th>
				</tr>
		</table>

		<template v-if="page['you'] !== undefined">
			<br><br>
			<table class="table">
				<tr>
					<th>Вы были привлечены игроком:</th>
					<th><router-link :to="'/players/'+page['you']['id']+'/'">{{ page['you']['username'] }}</router-link></th>
				</tr>
			</table>
		</template>

		<template v-if="!$store.state.isSocial">
			<br><br>
			<table class="table">
				<tr>
					<th colspan="2" style="padding:15px;">
						Помоги проекту, поделись им с друзьями!<br><br>

						<div class="yashare-auto-init"
							data-yashareL10n="ru"
							data-yashareTheme="counter"
							data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,moimir,gplus"
							:data-yashareLink="host+'/?'+$store.state.user.id"
							data-yashareTitle=""
						></div>
					</th>
				</tr>
			</table>

			<div class="separator"></div>
			<table class="table">
				<tr>
					<td class="c">Юзербар</td>
				</tr>
				<tr>
					<th>
						<br>
						<img :src="'/userbar'+$store.state.user.id+'.jpg'" alt="">

						<br><br>
						HTML код:
						<br>
						<input style="width:100%" type="text" :value="html" title="">
						<div class="separator"></div>
						BB код:
						<input style="width:100%" type="text" :value="'[url='+host+'/?'+$store.state.user.id+'][img]'+host+'/userbar'+$store.state.user.id+'.jpg[/img][/url]'" title="">
					</th>
				</tr>
			</table>
		</template>
	</div>
</template>

<script>
	import { addScript } from '~/utils/helpers'

	export default {
		name: "refers",
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
		watchQuery: true,
		middleware: ['auth'],
		computed: {
			host () {
				return process.server ? '' : window.location.origin
			},
			html () {
				return '<a href="'+this.host+'/?'+this.$store.state.user.id+'"><img src="'+this.host+'/userbar'+this.$store.state.user.id+'.jpg"></a>'
			}
		},
		mounted () {
			addScript('https://yandex.st/share/share.js')
		},
	}
</script>