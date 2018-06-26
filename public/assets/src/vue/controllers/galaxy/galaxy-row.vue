<template>
	<tr class="planetRow">
		<th width="30">{{ i + 1 }}</th>
		<th class="img">
			<a v-if="item && !item['p_delete']" class="tooltip sticky">
				<div class="tooltip-content">
					<table width="240">
						<tr>
							<td class="c" colspan="2">Планета {{ item['p_name'] }} [{{ galaxy }}:{{ system }}:{{ item['planet'] }}]</td>
						</tr>
						<tr>
							<th width="80">
								<img :src="$root.getUrl('assets/images/planeten/small/s_'+item['p_image']+'.jpg')" height="75" width="75">
							</th>
							<th align="left">
								<div v-if="$parent['page']['user']['phalanx'] > 0">
									<a :href="$root.getUrl('phalanx/index/galaxy/'+galaxy+'/system/'+system+'/planet/'+item['planet']+'/')" target="_blank">Фаланга</a>
								</div>

								<div v-if="item['u_id'] !== $store.state['user']['id']">
									<router-link :to="'/fleet/g'+galaxy+'/s'+system+'/p'+item['planet']+'/t'+item['p_type']+'/m1/'">Атаковать</router-link>
									<br>
									<router-link :to="'/fleet/g'+galaxy+'/s'+system+'/p'+item['planet']+'/t'+item['p_type']+'/m5/'">Удерживать</router-link>
								</div>
								<div v-else>
									<router-link v-if="item['u_id'] === $store.state['user']['id']" :to="'/fleet/g'+galaxy+'/s'+system+'/p'+item['planet']+'/t'+item['p_type']+'/m4/'">Оставить</router-link>
								</div>
								<router-link :to="'/fleet/g'+galaxy+'/s'+system+'/p'+item['planet']+'/t'+item['p_type']+'/m3/'">Транспорт</router-link>
							</th>
						</tr>
					</table>
				</div>
				<img :src="$root.getUrl('assets/images/planeten/small/s_'+item['p_image']+'.jpg')" height=30 width="30">
			</a>
		</th>
		<th width="130">
			<div v-if="item && !item['p_delete']">
				<span v-if="item['p_active'] <= 10" class="star">(*)</span>
				<span v-else-if="item['p_active'] < 60" class="star">({{ Math.floor(item['p_active']) }})</span>
				<span :class="{negative: item['u_id'] === $store.state['user']['id']}">{{ item['p_name'] }}</span>
			</div>
			<div v-else-if="item && item['p_delete']">
				Планета уничтожена
			</div>
		</th>
		<th style="white-space: nowrap;" width="30">
			<a v-if="item && !item['l_delete'] && item['l_id']" class="tooltip sticky">
				<div class="tooltip-content">
					<table width="240">
						<tr>
							<td class="c" colspan="2">
								Луна: {{ item['l_name'] }} [{{ galaxy }}:{{ system }}:{{ item['planet'] }}]
							</td>
						</tr>
						<tr>
							<th width="80">
								<img :src="$root.getUrl('assets/images/planeten/mond.jpg')" height=75 width=75 />
							</th>
							<th>
								<table class="table">
									<tr>
										<td class="c" colspan="2">Характеристики</td>
									</tr>
									<tr>
										<th>Диаметр</th>
										<th>{{ item['l_diameter']|number }}</th>
									</tr>
									<tr>
										<th>Температура</th>
										<th>{{ item['l_temp'] }}</th>
									</tr>
									<tr>
										<td class="c" colspan="2">Действия</td>
									</tr>
									<tr>
										<th colspan="2" align="center">
											<div v-if="item['u_id'] !== $store.state['user']['id']">
												<router-link :to="'/fleet/g'+galaxy+'/s'+system+'/p'+item['planet']+'/t3/m1/'">Атаковать</router-link>
												<br>
												<router-link :to="'/fleet/g'+galaxy+'/s'+system+'/p'+item['planet']+'/t3/m5/'">Удерживать</router-link>

												<div v-if="$parent['page']['user'].destroy > 0">
													<router-link :to="'/fleet/g'+galaxy+'/s'+system+'/p'+item['planet']+'/t3/m9/'">Уничтожить</router-link>
												</div>
											</div>
											<div v-else>
												<router-link :to="'/fleet/g'+galaxy+'/s'+system+'/p'+item['planet']+'/t3/m4/'">Оставить</router-link>
											</div>
											<router-link :to="'/fleet/g'+galaxy+'/s'+system+'/p'+item['planet']+'/t3/m3/'">Транспорт</router-link>
										</th>
									</tr>
								</table>
							</th>
						</tr>
					</table>	
				</div>
				<img :src="$root.getUrl('assets/images/planeten/small/s_mond.jpg')" height="30" width="30">
			</a>
			<span v-if="item && item['l_delete'] && item['l_id']">~</span>
		</th>
		<th :class="[debris_class]" width="30">
			<a v-if="item && (item['p_metal'] > 0 || item['p_crystal'] > 0)" class="tooltip sticky">
				<div class="tooltip-content">
					<table width="240">
						<tr>
							<td class="c" colspan="2">
								Обломки: [{{ galaxy }}:{{ system }}:{{ item['planet'] }}]
							</td>
						</tr>
						<tr>
							<th width="80">
								<img :src="$root.getUrl('assets/images/planeten/debris.jpg')" height="75" width="75">
							</th>
							<th>
								<table class="table">
									<tr>
										<td class="c" colspan="2">Ресурсы</td>
									</tr>
									<tr>
										<th>Металл</th>
										<th>{{ item['p_metal'] }}</th>
									</tr>
									<tr>
										<th>Кристалл</th>
										<th>{{ item['p_crystal'] }}</th>
									</tr>
									<tr v-if="$parent['page']['user']['recycler'] > 0">
										<th colspan="2" align="left">
											<a @click.prevent="$parent.fleet.sendMission(8, galaxy, system, item['planet'], 2, 0)">Собрать</a>
										</th>
									</tr>
									<tr>
										<th colspan="2" align="left">
											<router-link :to="'/fleet/g'+galaxy+'/s'+system+'/p'+item['planet']+'/t2/m8/'">Отправить флот</router-link>
										</th>
									</tr>
								</table>
							</th>
						</tr>
					</table>	
				</div>
				<img :src="$root.getUrl('assets/images/planeten/debris.jpg')" height="22" width="22">
			</a>	
		</th>
		<th width="150">
			<div v-if="item && !item['p_delete']">
				<a class="tooltip sticky">
					<div class="tooltip-content">
						<table width="280">
							<tr>
								<td class="c" colspan="2">Игрок {{ item['u_name'] }}, место {{ item['s_rank'] }}</td>
							</tr>
							<tr>
								<td v-if="user_avatar !== ''" width="122" height="126" rowspan="3" valign="middle" class="c" :style="'background:url('+user_avatar+') 50% 50% no-repeat;background-size:cover;'"></td>
								<td v-else width="122" height="126" rowspan="3" valign="middle" class="c">нет<br>аватара</td>

								<th v-if="item['u_id'] !== $store.state['user']['id']">
									<router-link :to="'/messages/write/'+item['u_id']+'/'">Послать сообщение</router-link>
								</th>
							</tr>
							<tr v-if="item['u_id'] !== $store.state['user']['id']">
								<th>
									<router-link :to="'/buddy/new/'+item['u_id']+'/'">Добавить в друзья</router-link>
								</th>
							</tr>
							<tr>
								<th valign="top">
									<router-link :to="'/stat/players/range/'+stat_page+'/pid/'+item['u_id']+'/'">Статистика</router-link>
								</th>
							</tr>
						</table>
					</div>

					<span :class="[user_status_class]">{{ item['u_name'] }}</span>
				</a>

				<span v-if="user_status" :class="[user_status_class]">
					<font color="white">(</font><span v-if="user_status === 'UG' || user_status === 'G'"><router-link to="/banned/" :class="[user_status_class]">{{ user_status }}</router-link></span><span v-else="">{{ user_status }}</span><font color="white">)</font>
				</span>

				<font v-if="item['u_admin'] === 3" color="red">A</font>
				<font v-if="item['u_admin'] === 2" color="orange">SGo</font>
				<font v-if="item['u_admin'] === 1" color="green">Go</font>
			</div>
		</th>
		<th width="16">
			<router-link v-if="item && !item.delete && item['u_race']" :to="'/info/70'+item['u_race']+'/'">
				<img :src="$root.getUrl('assets/images/skin/race'+item['u_race']+'.gif')" width="16" height="16" :alt="races[item['u_race']]" :title="races[item['u_race']]">
			</router-link>
		</th>
		<th width="80">
			<a v-if="item && !item.delete && item['a_id']" class="tooltip sticky">
				<div class="tooltip-content">
					<table width="240">
						<tr>
							<td class="c">
								Альянс {{ item['a_name'] }} с {{ item['a_members'] }} членами
							</td>
						</tr>
						<tr>
							<th>
								<router-link :to="'alliance/info/'+item['a_id']+'/'">Информация</router-link>
							</th>
						</tr>
						<tr>
							<th>
								<router-link to="/stat/alliance/start/0/">Статистика</router-link>
							</th>
						</tr>
						<tr v-if="item['a_web'] && item['a_web'].length">
							<th>
								<a :href="item['a_web']" target="_blank">Сайт альянса</a>
							</th>
						</tr>
					</table>
				</div>

				<span :class="{allymember: $store.state['user']['alliance']['id'] === item['a_id']}">{{ item['a_tag'] }}</span>
			</a>

			<div v-if="$store.state['user']['alliance']['id'] !== item['a_id']">
				<small v-if="item['d_type'] === 0">[нейтральное]</small>
				<small v-if="item['d_type'] === 1"><font color="orange">[перемирие]</font></small>
				<small v-if="item['d_type'] === 2"><font color="green">[мир]</font></small>
				<small v-if="item['d_type'] === 3"><font color="red">[война]</font></small>
			</div>
		</th>

		<th style="white-space: nowrap;" width="125">
			<div v-if="item && item['u_id'] !== $store.state['user']['id'] && !item['p_delete']">
				<popup-link :title="item['u_name']+': отправить сообщение'" :to="'/messages/write/'+item['u_id']+'/'" :width="680">
					<span class="sprite skin_m"></span>
				</popup-link>
				<router-link :to="'/buddy/new/'+item['u_id']+'/'" title="Добавить в друзья">
					<span class="sprite skin_b"></span>
				</router-link>

				<a v-if="$parent['page']['user']['missile']" @click.prevent="$parent.sendMissile(item['planet'])" title="Ракетная атака">
					<span class="sprite skin_r"></span>
				</a>

				<a v-if="$parent['page']['user']['spy_sonde'] && !item['u_vacation']" title="Шпионаж" class="tooltip sticky">
					<div class="tooltip-content">
						<center>
							<input type="text" :value="$parent['page']['user'].spy">
							<br>
							<input type="button" @click.prevent="spy(item['p_type'], $event)" value="Отправить на планету">
							<br>
							<input v-if="!item['l_delete'] && item['l_id']" type="button" @click.prevent="spy(3, $event)" value="Отправить на луну">
						</center>
					</div>
					<span class="sprite skin_e"></span>
				</a>

				<router-link :to="'/players/'+item['u_id']+'/'" title="Информация об игроке">
					<span class="sprite skin_s"></span>
				</router-link>
				<router-link :to="'/fleet/shortcut/add/new/g/'+galaxy+'/s/'+system+'/p/'+item['planet']+'/t/'+item['p_type']+'/'" title="Добавить в закладки">
					<span class="sprite skin_z"></span>
				</router-link>
			</div>

			<router-link v-if="!item && $parent['page']['user']['colonizer']" :to="'fleet/g'+galaxy+'/s'+system+'/p'+(i + 1)+'/t0/m7/'" title="Колонизация">
				<span class="sprite skin_e"></span>
			</router-link>
		</th>
	</tr>
</template>

<script>
	import { sendMission } from "../../js/fleet"

	export default {
		name: "row",
		props: ['item', 'i'],
		data ()
		{
			return {
				'races': ['', 'Конфедерация', 'Бионики', 'Сайлоны', 'Древние']
			}
		},
		computed: {
			galaxy () {
				return this.$parent['page']['galaxy'];
			},
			system () {
				return this.$parent['page']['system'];
			},
			user_status ()
			{
				let CurrentPoints 	= this.$parent['page']['user']['stat_points'];
				let RowUserPoints 	= this.item['s_points'];

				if (!RowUserPoints)
	                RowUserPoints = 0;

				if (this.item['u_ban'] > this.$root.serverTime() && this.item['u_vacation'] > 0)
					return "UG";
				else if (this.item['u_ban'] > this.$root.serverTime())
					return "G";
				else if (this.item['u_vacation'] > 0)
					return "U";
				else if (this.item['u_online'] === 1)
					return "i";
				else if (this.item['u_online'] === 2)
					return "iI";
				else if (RowUserPoints * 5 < CurrentPoints || RowUserPoints <= 5000)
					return "N";
				else if (RowUserPoints > CurrentPoints * 5)
					return "S";
				else
					return '';
			},
			user_status_class ()
			{
				if (this.user_status === 'UG')
					return "vacation";
				else if (this.user_status === 'G')
					return "banned";
				else if (this.user_status === 'U')
					return "vacation";
				else if (this.user_status === 'i')
					return "inactive";
				else if (this.user_status === 'iI')
					return "longinactive";
				else if (this.user_status === 'N')
					return "noob";
				else if (this.user_status === 'S')
					return "strong";

				return '';
			},
			debris_class ()
			{
				if (!this.item)
					return '';

				let debris = parseInt(this.item['p_metal']) + parseInt(this.item['p_crystal']);

				if (debris >= 10000000)
					return 'debris_100'
				else if (debris >= 1000000)
					return 'debris_50'
				else if (debris >= 100000)
					return 'debris_0'

				return '';
			},
			user_avatar ()
			{
				if (!this.item)
					return '';

				if (this.item['u_image'].length > 0)
					return this.$root.getUrl(this.item['u_image']);
				else if (this.item['u_avatar'] > 0)
				{
					if (this.item['u_avatar'] !== 99)
						return this.$root.getUrl('assets/images/faces/'+this.item['u_sex']+'/'+this.item['u_avatar']+'s.png');
					else
						return this.$root.getUrl('assets/avatars/upload_'+this.item['u_id']+'.jpg');
				}

				return '';
			},
			stat_page ()
			{
				if (!this.item || this.item['s_rank'] < 100)
					return 1;

				return (Math.floor(this.item['s_rank'] / 100 ) * 100) + 1;
			},
		},
		methods:
		{
			spy (planet_type, event)
			{
				let obj = $(event.target);

				obj.prop('disabled', true);

				let spyNum = obj.parent().find('input[type=text]').val();

				sendMission(6, this.galaxy, this.system, this.item['planet'], planet_type, spyNum).then(() => {
					obj.prop('disabled', false);
				});
			}
		}
	}
</script>