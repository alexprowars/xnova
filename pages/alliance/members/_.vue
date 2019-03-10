<template>
	<table class="table">
		<tr>
			<td class="c" colspan="10">Список членов альянса (количество: {{ page['i'] }})</td>
		</tr>
		<tr>
			<th>№</th>
			<th>
				<router-link :to="'/alliance/'+(page['admin'] ? 'admin/edit/members' : 'members')+'/sort1/1/sort2/'+page['s']+'/'">Ник</router-link>
			</th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
			<th>
				<router-link :to="'/alliance/'+(page['admin'] ? 'admin/edit/members' : 'members')+'/sort1/2/sort2/'+page['s']+'/'">Ранг</router-link>
			</th>
			<th>
				<router-link :to="'/alliance/'+(page['admin'] ? 'admin/edit/members' : 'members')+'/sort1/3/sort2/'+page['s']+'/'">Очки</router-link>
			</th>
			<th>Координаты</th>
			<th>
				<router-link :to="'/alliance/'+(page['admin'] ? 'admin/edit/members' : 'members')+'/sort1/4/sort2/'+page['s']+'/'">Дата вступления</router-link>
			</th>
			<th v-if="page['status']">
				<router-link :to="'/alliance/'+(page['admin'] ? 'admin/edit/members' : 'members')+'/sort1/5/sort2/'+page['s']+'/'">Активность</router-link>
			</th>
			<th v-if="page['admin']">Управление</th>
		</tr>
		<template v-for="m in page['memberslist']">
			<tr v-if="m['Rank_for'] === undefined || page['admin'] === false">
				<th>
					{{ m['i'] }}
				</th>
				<th>
					{{ m['username'] }}
				</th>
				<th>
					<popup-link :to="'/messages/write/'+m['id']+'/'" :title="m['username']+': отправить сообщение'" :width="680"><span class='sprite skin_m'></span></popup-link>
				</th>
				<th>
					<img :src="'/images/skin/race'+m['race']+'.gif'" width="16" height="16" alt="">
				</th>
				<th>
					{{ m['range'] }}
				</th>
				<th>
					{{ m['points'] }}
				</th>
				<th>
					<router-link :to="'/galaxy/'+m['galaxy']+'/'+m['system']+'/'">{{ m['galaxy'] }}:{{ m['system'] }}:{{ m['planet'] }}</router-link>
				</th>
				<th>
					{{ m['time'] }}
				</th>
				<th v-if="page['status']" v-html="m['onlinetime']"></th>
				<th v-if="page['admin']">
					<a :href="'/alliance/admin/edit/members/kick/'+m['id']+'/'" onclick="return confirm('Вы действительно хотите исключить данного игрока из альянса?');">
						<img src="/images/abort.gif" alt="">
					</a>
					&nbsp;
					<router-link :to="'/alliance/admin/edit/members/rank/'+m['id']+'/'">
						<img src="/images/key.gif" alt="">
					</router-link>
				</th>
			</tr>
			<tr v-else>
				<td colspan="10">
					<router-form :action="'/alliance/admin/edit/members/id/'+m['id']+'/'">
						<table class="table">
							<tr>
								<th colspan="7">{{ m['Rank_for'] }}</th>
								<th><select name="newrang" title="">{{ m['options'] }}</select></th>
								<th colspan="2"><input type="submit" value="Сохранить"></th>
							</tr>
						</table>
					</router-form>
				</td>
			</tr>
		</template>
		<tr>
			<td class="c" colspan="10">
				<router-link :to="'/alliance'+(page['admin'] ? '/admin/edit/ally' : '')+'/'">вернутся к обзору</router-link>
			</td>
		</tr>
	</table>
</template>

<script>
	export default {
		name: "alliance-members",
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
		watchQuery: true,
		middleware: ['auth'],
	}
</script>