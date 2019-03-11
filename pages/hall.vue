<template>
	<div>
	<table class="table">
		<tr>
			<td class="c" width="100">TOP50</td>
			<td class="c"><router-link to="/hall/">Зал Славы</router-link></td>
			<td class="c" width="137">
				<router-form action="/hall/">
					<select name="visible" title="" v-model="page['type']">
						<option value="1">Бои</option>
						<option value="2">САБ</option>
					</select>
				</router-form>
			</td>
		</tr>
	</table>
	<div class="separator"></div>
	<table class="table">
		<tr>
			<td class="c" width="35">Место</td>
			<td class="c"><font color=#CDB5CD>{{ page['type'] <= 1 ? 'Самые разрушительные бои' : 'Самые разрушительные групповые бои' }}</font></td>
			<td class="c" width="45">Итог</td>
			<td class="c" width="125">Дата</td>
		</tr>
		<tr v-for="(log, i) in page['hall']">
			<th>{{ i + 1 }}</th>
			<th>
				<a :href="'/log/'+log['log']+'/'" target="_blank">{{ log['title'] }}</a>
			</th>
			<th>
				<template v-if="log['won'] === 0">
					Н
				</template>
				<template v-if="log['won'] === 1">
					А
				</template>
				<template v-else>
					О
				</template>
			</th>
			<th nowrap :class="{positive: page['time'] === log['time']}">
				{{ log['time'] | date('d.m.y H:i') }}
			</th>
		</tr>
		<tr v-if="page['hall'].length === 0">
			<th colspan="4">В этой вселенной еще не было крупных боев</th>
		</tr>
	</table>
	</div>
</template>

<script>
	export default {
		name: "hall",
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
		watchQuery: true,
		middleware: ['auth'],
	}
</script>