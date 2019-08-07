<template>
	<div class="page-stat">
		<div class="block">
			<div class="title text-center">
				Статистика: {{ page['update'] }}
			</div>
			<div class="content border-0">
				<div class="table">
					<div class="row">
						<div class="th col-2 middle">Статистика</div>
						<div class="th col-4 col-sm-2">
							<select v-model="form.list" @change="loadItems">
								<option value="players">Игроков</option>
								<option value="alliances">Альянсов</option>
								<option value="races">Фракций</option>
							</select>
						</div>
						<div class="th col-2 col-sm-1 middle">по</div>
						<div class="th col-4 col-sm-3">
							<select v-model="form.type" @change="loadItems">
								<option :value="1">Очкам</option>
								<option :value="2">Флоту</option>
								<option :value="5">Постройкам</option>
								<option :value="3">Исследованиям</option>
								<option :value="4">Обороне</option>
								<option v-if="form.list !== 'races'" :value="6">Мирному уровню</option>
								<option v-if="form.list !== 'races'" :value="7">Боевому уровню</option>
							</select>
						</div>
						<div v-if="form.list !== 'races'" class="th col-2 middle">место</div>
						<div v-if="form.list !== 'races'" class="th col-10 col-sm-2">
							<select v-model="form.page" @change="loadItems">
								<option v-for="i in form.pages" :value="i">{{ (i - 1) * 100 + 1 }} - {{ i * 100 }}</option>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>

		<StatPlayers v-if="form.list === 'players'" :items="items"></StatPlayers>
		<StatAlliances v-if="form.list === 'alliances'" :items="items"></StatAlliances>
		<StatRaces v-if="form.list === 'races'" :items="items"></StatRaces>
	</div>
</template>

<script>
	import StatPlayers from '~/components/page/stat/players.vue'
	import StatAlliances from '~/components/page/stat/alliances.vue'
	import StatRaces from '~/components/page/stat/races.vue'

	export default {
		name: 'stat',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		components: {
			StatPlayers,
			StatAlliances,
			StatRaces
		},
		data () {
			return {
				form: {
					list: '',
					type: '',
					page: 1,
					pages: 0
				},
				items: []
			}
		},
		watch: {
			'form.list'() {
				this.form.type = 1;
				this.form.page = 1;
			},
			'form.type'() {
				this.form.page = 1;
			}
		},
		methods: {
			loadItems ()
			{
				this.$nextTick(() =>
				{
					this.$post('/stat/', {
						view: this.form.list,
						type: this.form.type,
						range: this.form.page
					})
					.then((result) => {
						this.items = result.page.items
					})
				})
			},
		},
		created ()
		{
			this.form.list = this.page['list'];
			this.form.type = this.page['type'];
			this.form.pages = Math.ceil(this.page['elements'] / 100)
			this.items = this.page['items']
		}
	}
</script>