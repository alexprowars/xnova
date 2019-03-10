<template>
	<div class="page-search">
		<div class="block">
			<div class="title">Поиск по игре</div>
			<div class="content border-0">
				<router-form action="/search/">
					<div class="table middle">
						<div class="row">
							<div class="col th">
								<select name="type" title="" v-model="page['type']">
									<option value="playername">Логин игрока</option>
									<option value="planetname">Название планеты</option>
									<option value="allytag">Аббревиатура альянса</option>
									<option value="allyname">Название альянса</option>
								</select>
								&nbsp;&nbsp;
								<input type="text" name="searchtext" :value="page['searchtext']" title="">
								&nbsp;&nbsp;
								<input type="submit" value="Поиск">
							</div>
						</div>
					</div>
				</router-form>
			</div>
		</div>
		<div class="separator"></div>
		<template v-if="page['searchtext'] !== ''">
			<template v-if="page['type'] === 'playername' || page['type'] === 'planetname'">
				<table class="table">
					<tr>
						<td class="c" width="120">Имя</td>
						<td class="c" width="40">&nbsp;</td>
						<td class="c" width="20">&nbsp;</td>
						<td class="c">Альянс</td>
						<td class="c">Планета</td>
						<td class="c" width="80">Координаты</td>
						<td class="c" width="40">Место</td>
					</tr>
					<tr v-for="result in page['result']">
						<th>{{ result['username'] }}</th>
						<th nowrap>
							<popup-link :to="'/messages/write/'+result['id']+'/'" :title="result['username']+': отправить сообщение'" :width="680">
								<span class='sprite skin_m'></span>
							</popup-link>
							<router-link :to="'/buddy/new/'+result['id']+'/'" title="Предложение подружиться">
								<span class='sprite skin_b'></span>
							</router-link>
						</th>
						<th>
							<img v-if="result['race'] !== 0" :src="'/images/skin/race'+result['race']+'.gif'" width="16" height="16" alt="">
						</th>
						<th>{{ result['ally_name'] }}</th>
						<th>{{ result['planet_name'] }}</th>
						<th><router-link :to="'/galaxy/'+result['g']+'/'+result['s']+'/'">{{ result['g'] }}:{{ result['s'] }}:{{ result['p'] }}</router-link></th>
						<th><router-link :to="'/stat/players/range/'+result['total_rank']+'/'">{{ result['total_rank'] }}</router-link></th>
					</tr>
					<tr v-if="page['result'].length === 0">
						<th colspan="7">Поиск не дал результатов</th>
					</tr>
				</table>
			</template>
			<template v-else>
				<table class="table">
					<tr>
						<td class="c">Аббревиатура</td>
						<td class="c">Имя</td>
						<td class="c">Члены</td>
						<td class="c">Очки</td>
					</tr>
					<tr v-for="result in page['result']">
						<th>
							<router-link :to="'/alliance/info/'+result['id']+'/'">
								{{ result['tag'] }}
							</router-link>
						</th>
						<th>{{ result['name'] }}</th>
						<th>{{ result['members'] }}</th>
						<th>{{ result['total_points'] }}</th>
					</tr>
					<tr v-if="page['result'].length === 0">
						<th colspan="6">Поиск не дал результатов</th>
					</tr>
				</table>
			</template>
		</template>
	</div>
</template>

<script>
	export default {
		name: "search",
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
		watchQuery: true,
		middleware: ['auth'],
	}
</script>