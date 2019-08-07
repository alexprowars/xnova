<template>
	<div>
		<table class="table">
			<tr>
				<td class="c" colspan="3">Ваши запросы</td>
			</tr>
			<tr v-for="diplo in page['DMyQuery']">
				<th>{{ diplo['name'] }}</th>
				<th>{{ $t('alliance.diplomacy_status.'+diplo['type']) }}</th>
				<th>
					<nuxt-link :to="'/alliance/diplomacy/edit/del/id/'+diplo['id']+'/'"><img src="/images/abort.gif" alt="Удалить заявку"></nuxt-link>
				</th>
			</tr>
			<tr v-if="page['DMyQuery'].length === 0">
				<th colspan="3">нет</th>
			</tr>
		</table>
		<div class="separator"></div>
		<table class="table">
			<tr>
				<td class="c" colspan="3">Запросы вашему альянсу</td>
			</tr>
			<tr v-for="diplo in page['DQuery']">
				<th>{{ diplo['name'] }}</th>
				<th>{{ $t('alliance.diplomacy_status.'+diplo['type']) }}</th>
				<th>
					<nuxt-link :to="'/alliance/diplomacy/edit/suc/id/'+diplo['id']+'/'"><img src="/images/appwiz.gif" alt="Подтвердить"></nuxt-link>
					<nuxt-link :to="'/alliance/diplomacy/edit/del/id/'+diplo['id']+'/'"><img src="/images/abort.gif" alt="Удалить заявку"></nuxt-link>
				</th>
			</tr>
			<tr v-if="page['DQuery'].length === 0">
				<th colspan="3">нет</th>
			</tr>
		</table>
		<div class="separator"></div>
		<table class="table">
			<tr>
				<td class="c" colspan="4">Отношения между альянсами</td>
			</tr>
			<tr v-for="diplo in page['DText']">
				<th>{{ diplo['name'] }}</th>
				<th>{{ $t('alliance.diplomacy_status.'+diplo['type']) }}</th>
				<th>
					<nuxt-link :to="'/alliance/diplomacy/edit/del/id/'+diplo['id']+'/'"><img src="/images/abort.gif" alt="Удалить заявку"></nuxt-link>
				</th>
			</tr>
			<tr v-if="page['DText'].length === 0">
				<th colspan="4">нет</th>
			</tr>
		</table>
		<div class="separator"></div>
		<router-form action="/alliance/diplomacy/edit/add/">
			<table class="table">
				<tr>
					<td class="c" colspan="2">Добавить альянс в список</td>
				</tr>
				<tr>
					<th>
						<select name="ally" title="">
							<option value="0">список альянсов</option>
							<option v-for="item in page['a_list']" :value="item['id']">{{ item['name'] }} [{{ item['tag'] }}]</option>
						</select>
					</th>
					<th>
						<select name="status" title="">
							<option value="1">Перемирие</option>
							<option value="2">Мир</option>
							<option value="3">Война</option>
						</select>
					</th>
				</tr>

				<tr>
					<td class="c"><nuxt-link to="/alliance/">назад</nuxt-link></td>
					<td class="c">
						<input type="submit" value="Добавить">
					</td>
				</tr>
			</table>
		</router-form>
	</div>
</template>

<script>
	export default {
		name: 'alliance-diplomacy',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
	}
</script>