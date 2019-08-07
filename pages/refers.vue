<template>
	<div class="page-refers">
		<div v-if="page['ref'].length > 0" class="block">
			<div class="title">Привлечённые игроки</div>
			<div class="content border-0">
				<div class="table">
					<div class="row">
						<div class="col c">Ник</div>
						<div class="col c">Дата регистрации</div>
						<div class="col c">Уровень развития</div>
					</div>
					<div class="row" v-for="list in page['ref']">
						<div class="col th">
							<router-link :to="'/players/'+list['id']+'/'">{{ list['username'] }}</router-link>
						</div>
						<div class="col th">{{ list['create_time'] | date('d.m.Y H:i') }}</div>
						<div class="col th">П:{{ list['lvl_minier'] }}, В:{{ list['lvl_raid'] }}</div>
					</div>
				</div>
			</div>
		</div>

		<template v-if="page['you'] !== undefined">
			<div class="table">
				<div class="row">
					<div class="col th">Вы были привлечены игроком:</div>
					<div class="col th">
						<router-link :to="'/players/'+page['you']['id']+'/'">{{ page['you']['username'] }}</router-link>
					</div>
				</div>
			</div>
		</template>

		<template v-if="!$store.state.isSocial">
			<div class="block">
				<div class="content border-0">
					<div class="table">
						<div class="row">
							<div class="col th" style="padding:15px;">
								Помоги проекту, поделись им с друзьями!<br><br>

								<div class="yashare-auto-init"
									data-yashareL10n="ru"
									data-yashareTheme="counter"
									data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,moimir,gplus"
									:data-yashareLink="host+'/?'+$store.state.user.id"
									data-yashareTitle=""
								></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="block">
				<div class="title">Юзербар</div>
				<div class="content border-0">
					<div class="table">
						<div class="row">
							<div class="col text-center">
								<br>
								<img :src="'/userbar'+$store.state.user.id+'.jpg'" alt="">

								<br><br>
								HTML код:
								<br>
								<input style="width:100%" type="text" :value="html" title="">
								<div class="separator"></div>
								BB код:
								<input style="width:100%" type="text" :value="'[url='+host+'/?'+$store.state.user.id+'][img]'+host+'/userbar'+$store.state.user.id+'.jpg[/img][/url]'" title="">
							</div>
						</div>
					</div>
				</div>
			</div>
		</template>
	</div>
</template>

<script>
	import { addScript } from '~/utils/helpers'

	export default {
		name: 'refers',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
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