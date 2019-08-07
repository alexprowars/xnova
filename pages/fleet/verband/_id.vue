<template>
	<div v-if="page" class="page-fleet-verband">
		<div class="block">
			<div class="title">Флоты в совместной атаке</div>
			<div class="content border-0">
				<table class="table">
					<tr>
						<th>Задание</th>
						<th>Кол-во</th>
						<th>Отправлен</th>
						<th>Прибытие</th>
						<th>Цель</th>
						<th>Возврат</th>
					</tr>
					<tr v-for="item in page['list']">
						<th>
							<a>{{ $t('FLEET_MISSION.'+item.mission) }}</a>
							<a v-if="(item['start']['time'] + 1) === item['target']['time']">(F)</a>
						</th>
						<th>
							<Popper>
								<div v-for="data in item['ships']">{{ $t('TECH.'+data['id']) }}: {{ data['count'] }}</div>
								<template slot="reference">
									{{ item['ships_total'] | number }}
								</template>
							</Popper>
						</th>
						<th>
							<planet-link :galaxy="item['start']['galaxy']" :system="item['start']['system']" :planet="item['start']['planet']"></planet-link>
							<div>{{ item['start']['name'] }}</div>
						</th>
						<th>
							{{ item['start']['time']|date('d.m H:i:s') }}
							<timer :value="item['start']['time'] + 1" class="positive"></timer>
						</th>
						<th>
							<planet-link :galaxy="item['target']['galaxy']" :system="item['target']['system']" :planet="item['target']['planet']"></planet-link>
							<div>{{ item['target']['name'] }}</div>
						</th>
						<th>{{ item['target']['time']|date('d.m H:i:s') }}</th>
					</tr>
					<tr v-if="page['list'].length === 0"><th colspan="9">-</th></tr>
				</table>
			</div>
		</div>
	
		<div v-if="page['group'] === 0" class="block">
			<div class="title">Создание ассоциации флота</div>
			<div class="content border-0">
				<router-form :action="'/fleet/verband/'+page['fleetid']+'/'">
					<input type="hidden" name="action" value="add">
					<div class="table">
						<div class="row">
							<div class="col th">
								<input type="text" name="name" :value="'AKS'+rand(100000, 999999999)" size="50">
								<br>
								<input type="submit" value="Создать">
							</div>
						</div>
					</div>
				</router-form>
			</div>
		</div>

		<div v-else-if="page['aks'] && page['fleetid'] === page['aks']['fleet_id']" class="block">
			<div class="title">Ассоциация флота {{ page['aks']['name'] }}</div>
			<div class="content border-0">
				<div class="table">
					<div class="row">
						<div class="col th">
							<router-form :action="'/fleet/verband/'+page['fleetid']+'/'">
								<input type="hidden" name="action" value="changename">
								<input type="text" name="name" :value="page['aks']['name']" size="50">
								<br>
								<input type="submit" value="Изменить">
							</router-form>
						</div>
					</div>
					<div class="row">
						<div class="col th">
							<table class="table">
								<tr>
									<td class="c">Приглашенные участники</td>
									<td class="c">Пригласить участников</td>
								</tr>
								<tr>
									<th width="50%" valign="top">
										<select size="10" style="width:75%;">
											<option v-for="user in page['users']">{{ user }}</option>
											<option v-if="page['users'].length === 0">нет участников</option>
										</select>
									</th>
									<th>
										<router-form :action="'/fleet/verband/'+page['fleetid']+'/'">
											<input type="hidden" name="action" value="adduser">
											<div v-if="page['friends'].length > 0 || page['alliance'].length > 0">
												<select name="user_id" size="10" style="width:75%;">
													<option value="">-не выбрано-</option>
													<optgroup v-if="page['friends'].length > 0" label="Список друзей">
														<option v-for="user in page['friends']" :value="user['id']">{{ user['username'] }}</option>
													</optgroup>
													<optgroup v-if="page['alliance'].length > 0" label="Члены альянса">
														<option v-for="user in page['alliance']" :value="user['id']">{{ user['username'] }}</option>
													</optgroup>
												</select>
												<div class="separator"></div>
											</div>
											<input type="text" name="user_name" size="40" placeholder="Введите игровой ник">
											<br>
											<input type="submit" value="OK">
										</router-form>
									</th>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'fleet-verband',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		methods: {
			rand (min, max) {
				return Math.floor(Math.random() * (max - min + 1)) + min
			}
		}
	}
</script>