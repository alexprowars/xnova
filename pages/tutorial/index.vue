<template>
	<table class="table">
		<tbody>
		<tr>
			<td class="c" colspan="3">Текущие задания</td>
		</tr>
		<tr v-for="quest in page['list']">
			<th width="30">{{ quest['ID'] }}</th>
			<th width="30">
				<img :src="'/images/'+(quest['FINISH'] ? 'check' : 'none')+'.gif'" height="11" width="12" alt="">
			</th>
			<th class="text-left">
				<nuxt-link v-if="quest['AVAILABLE']" :to="'/tutorial/'+quest['ID']+'/'"><span class="positive">{{ quest['TITLE'] }}</span></nuxt-link>
				<span v-else class="positive">{{ quest['TITLE'] }}</span>
				<template v-if="quest['AVAILABLE'] === false && Object.keys(quest['REQUIRED']).length > 0">
					<br><br>Требования:
						<div v-for="(req, key) in quest['REQUIRED']">
							<span v-if="key === 'QUEST'" :class="[(page['quests'][req] === undefined || page['quests'][req]['finish'] === 0) ? 'negative' : 'positive']">Выполнение задания №{{ req }}</span>
							<span v-else-if="key === 'LEVEL_MINIER'" :class="[user.lvl_minier < req ? 'negative' : 'positive']">Промышленный уровень {{ req }}</span>
							<span v-else-if="key === 'LEVEL_RAID'" :class="[user.lvl_raid < req ? 'negative' : 'positive']">Военный уровень {{ req }}</span>
						</div>
				</template>
			</th>
		</tr>
		</tbody>
	</table>
</template>

<script>
	export default {
		name: "tutorial",
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
		watchQuery: true,
		middleware: ['auth'],
	}
</script>