<template>
	<tr class="planetRow">
		<th width="30">{{ i }}</th>
		<th class="img">
			<a v-if="item && !item.destruyed" class="tooltip_sticky">
				<div class="tooltip-content">
					<table width="240">
						<tr>
							<td class="c" colspan="2">Планета {{ item.name }} [{{ $parent.page.galaxy }}:{{ $parent.page.system }}:{{ i }}]</td>
						</tr>
						<tr>
							<th width="80">
								<img v-bind:src="$root.getUrl('assets/images/planeten/small/s_'+item.image+'.jpg')" height="75" width="75">
							</th>
							<th align="left">
								<a v-if="$parent.page.user.phalanx > 0" v-bind:onclick="$root.getUrlPath('phalanx/index/galaxy/'+$parent.page.galaxy+'/system/'+$parent.page.system+'/planet/'+i+'/')" target="_blank">Фаланга</a>

								<a v-if="item.user_id !== $root.user.id" v-bind:href="$root.getUrl('fleet/g'+$parent.page.galaxy+'/s'+$parent.page.system+'/p'+i+'/t'+item.planet_type+'/m1/')">Атаковать</a>
								<a v-if="item.user_id !== $root.user.id" v-bind:href="$root.getUrl('fleet/g'+$parent.page.galaxy+'/s'+$parent.page.system+'/p'+i+'/t'+item.planet_type+'/m5/')">Удерживать</a>

								<a v-if="item.user_id === $root.user.id" v-bind:href="$root.getUrl('fleet/g'+$parent.page.galaxy+'/s'+$parent.page.system+'/p'+i+'/t'+item.planet_type+'/m4/')">Оставить</a>

								<a v-bind:href="$root.getUrl('fleet/g'+$parent.page.galaxy+'/s'+$parent.page.system+'/p'+i+'/t'+item.planet_type+'/m3/')">Транспорт</a>
							</th>
						</tr>
					</table>
				</div>
				<img v-bind:src="$root.getUrl('assets/images/planeten/small/s_'+item.image+'.jpg')" height=30 width="30">
			</a>
		</th>
		<th width="130">
			<div style="overflow:hidden;width:130px">
				<div v-if="item && !destruyed">
					<span v-if="item.last_active <= 10" class="star">(*)</span>
					<span v-else-if="item.last_active < 60" class="star">({{ Math.floor(item.last_active) }})</span>
					<span v-bind:class="{negative: item.user_id === $root.user.id}">{{ item.name }}</span>
				</div>
				<div v-else-if="item && destruyed">
					Планета уничтожена
				</div>
			</div>
		</th>
		<th v-bind:class="[getDebrisClass]" style="white-space: nowrap;" width="30">
			<a v-if="item && !item.luna_destruyed && !item.luna_id" style="cursor: pointer;" class="tooltip_sticky">
				<div class="tooltip-content">
					<table width="240">
						<tr>
							<td class="c" colspan="2">
								Луна: {{ item.luna_name }} [{{ $parent.page.galaxy }}:{{ $parent.page.system }}:{{ i }}]
							</td>
						</tr>
						<tr>
							<th width="80">
								<img v-bind:src="$root.getUrl('assets/images/planeten/mond.jpg')" height=75 width=75 />
							</th>
							<th>
								<table class="table">
									<tr>
										<td class="c" colspan="2">Характеристики</td>
									</tr>
									<tr>
										<th>Диаметр</th>
										<th>{{ Format.number(item.luna_diameter) }}</th>
									</tr>
									<tr>
										<th>Температура</th>
										<th>{{ item.luna_temp }}</th>
									</tr>
									<tr>
										<td class="c" colspan="2">Действия</td>
									</tr>
									<tr>
										<th colspan="2" align="center">
											<a v-if="item.user_id !== $root.user.id" v-bind:href="$root.getUrl('fleet/g'+$parent.page.galaxy+'/s'+$parent.page.system+'/p'+i+'/t3/m1/')">Атаковать</a>
											<a v-if="item.user_id !== $root.user.id" v-bind:href="$root.getUrl('fleet/g'+$parent.page.galaxy+'/s'+$parent.page.system+'/p'+i+'/t3/m5/')">Удерживать</a>
									
											<a v-if="item.user_id !== $root.user.id && $parent.page.user.destroy > 0" v-bind:href="$root.getUrl('fleet/g'+$parent.page.galaxy+'/s'+$parent.page.system+'/p'+i+'/t3/m9/')">Уничтожить</a>
									
											<a v-if="item.user_id === $root.user.id" v-bind:href="$root.getUrl('fleet/g'+$parent.page.galaxy+'/s'+$parent.page.system+'/p'+i+'/t3/m4/')">Оставить</a>
									
											<a v-bind:href="$root.getUrl('fleet/g'+$parent.page.galaxy+'/s'+$parent.page.system+'/p'+i+'/t3/m3/')">Транспорт</a>
										</th>
									</tr>
								</table>
							</th>
						</tr>
					</table>	
				</div>
				<img v-bind:src="$root.getUrl('assets/images/planeten/small/s_mond.jpg')" height="30" width="30">
			</a>
			<span v-if="item && item.luna_destruyed && item.luna_id">~</span>
		</th>
		<th width="30">
			<a v-if="item && (item.metal > 0 || item.crystal > 0)" style="cursor: pointer;" class="tooltip_sticky">
				<div class="tooltip-content">
					<table width="240">
						<tr>
							<td class="c" colspan="2">
								Обломки: [{{ $parent.page.galaxy }}:{{ $parent.page.system }}:{{ i }}]
							</td>
						</tr>
						<tr>
							<th width="80">
								<img v-bind:src="$root.getUrl('assets/images/planeten/debris.jpg')" height="75" width="75">
							</th>
							<th>
								<table class="table">
									<tr>
										<td class="c" colspan="2">Ресурсы</td>
									</tr>
									<tr>
										<th>Металл</th>
										<th>{{ item.metal }}</th>
									</tr>
									<tr>
										<th>Кристалл</th>
										<th>{{ item.crystal }}</th>
									</tr>
									<tr v-if="$parent.page.user.recycler > 0">
										<th colspan="2" align="left">
											<a  v-bind:onclick=QuickFleet(8,$parent.page.galaxy,$parent.page.system,i,2,0)>Собрать</a>
										</th>
									</tr>
									<tr>
										<th colspan="2" align="left">
											<a v-bind:href="$root.getUrl('fleet/g'+$parent.page.galaxy+'/s'+$parent.page.system+'/p'+i+'/t2/m8/')">Отправить флот</a>
										</th>
									</tr>
								</table>
							</th>
						</tr>
					</table>	
				</div>
				<img v-bind:src="$root.getUrl('assets/images/planeten/debris.jpg')" height="22" width="22">
			</a>	
		</th>
		<th width=150>
			<a style="cursor: pointer;" class="tooltip_sticky">
				<div class="tooltip-content">
					<table width="280">
						<tr>
							<td class="c" colspan="2">Игрок {{ item.username }}, место {{ item.total_rank }}</td>
						</tr>
						<tr>
							<td v-if="getUserAvatar !== ''" width="122" height="126" rowspan="3" valign="middle" class="c" v-bind:style="'background:url('+getUserAvatar+') 50% 50% no-repeat;'"></td>
							<td v-else width="122" height="126" rowspan="3" valign="middle" class="c">нет<br>аватара</td>
							
							<th v-if="item.user_id !== $root.user.id">
								<a v-bind:href="$root.getUrl('messages/write/'+item.user_id+'/')">Послать сообщение</a>
							</th>
						</tr>
						<tr v-if="item.user_id !== $root.user.id">
							<th>
								<a v-bind:href="$root.getUrl('buddy/new/'+item.user_id+'/')">Добавить в друзья</a>
							</th>
						</tr>
						<tr>
							<th valign="top">
								<a v-bind:href="$root.getUrl('stat/players/range/'+getStatsPage+'/pid/'+item.user_id+'/')">Статистика</a>
							</th>
						</tr>
					</table>
				</div>

				<span v-bind:class="[getUserStatusClass]">
					{{ item.username }}
					<span v-if="user_status">
						<font color="white">(</font>{{ user_status }}<font color="white">)</font>
					</span>
					<font v-if="item.authlevel === 3" color="red">A</font>
					<font v-if="item.authlevel === 2" color="orange">SGo</font>
					<font v-if="item.authlevel === 1" color="green">Go</font>
				</span>
			</a>
		</th>
		<th width="16">
			<a v-if="item.race" v-bind:href="$root.getUrl('info/70'+item.race+'/')">
				<img v-bind:src="$root.getUrl('assets/images/skin/race'+item.race+'.gif')" width="16" height="16" v-bind:alt='races[item.race]' v-bind:title='races[item.race]'>
			</a>
		</th>
		<th width="80">
			<a v-if="item && item.ally_id" style="cursor: pointer;" class="tooltip_sticky">
				<div class="tooltip-content">
					<table width="240">
						<tr>
							<td class="c">
								Альянс {{ item.ally_name }} с {{ item.ally_members }} членами
							</td>
						</tr>
						<tr>
							<th>
								<a v-bind:href="$root.getUrl('alliance/info/'+item.ally_id+'/')">Информация</a>
							</th>
						</tr>
						<tr>
							<th>
								<a v-bind:href="$root.getUrl('stat/alliance/start/0/')">Статистика</a>
							</th>
						</tr>
						<tr v-if="item.ally_web.length">
							<th>
								<a v-bind:href="$root.getUrl(item.ally_web)" target='_blank'>Сайт альянса</a>
							</th>
						</tr>
					</table>
				</div>

				<span v-bind:class="{allymember: $root.user.alliance.id === item.ally_id}">{{ item.ally_tag }}</span>
			</a>

			<div v-if="$root.user.alliance.id !== item.ally_id">
				<small v-if="item.type === 0">[нейтральное]</small>
				<small v-if="item.type === 1"><font color="orange">[перемирие]</font></small>
				<small v-if="item.type === 2"><font color="green">[мир]</font></small>
				<small v-if="item.type === 3"><font color="red">[война]</font></small>
			</div>
		</th>

		<th style="white-space: nowrap;" width="125">
			<div v-if="item && item.user_id !== $root.user.id && !item.destruyed">
				<a title="Отправить сообщение" v-bind:onclick="sendMessage">
					<span class="sprite skin_m"></span>
				</a>
				<a v-bind:href="$root.getUrl('buddy/new/'+item.user_id+'/')" title="Добавить в друзья">
					<span class="sprite skin_b"></span>
				</a>

				<a v-if="$parent.page.user.missile" v-bind:href="$root.getUrl('galaxy/'+$parent.page.galaxy+'/'+$parent.page.system+'/planet/'+i+'/r/2/user/'+$root.user.planet+'/')" title="Ракетная атака">
					<span class="sprite skin_r"></span>
				</a>

				<a v-if="$parent.page.user.spy_sonde && !item.vacation" title="Шпионаж" class="tooltip_sticky">
					<div class="tooltip-content">
						<center>
							<input type=text v-bind:name="'spy'+i+''" v-bind:id="'spy'+i+''" v-bind:value="$parent.page.user.spy">
							<br>
							<input type=button class=spyButton v-bind:data-planet="i" v-bind:data-type="item.planet_type" value="Отправить на планету">
							<br>

							<input v-if="!item.luna_destruyed && item.luna_id" type=button class=spyButton v-bind:data-planet="i" data-type="3" value="Отправить на луну">
						</center>
					</div>
					<span class="sprite skin_e"></span>
				</a>

				<a v-bind:href="$root.getUrl('players/'+item.user_id+'/')" title="Информация об игроке">
					<span class="sprite skin_s"></span>
				</a>
				<a v-bind:href="$root.getUrl('fleet/shortcut/add/new/g/'+$parent.page.galaxy+'/s/'+$parent.page.system+'/p/'+i+'/t/'+item.planet_type+'/')" title="Добавить в закладки">
					<span class="sprite skin_z"></span>
				</a>
			</div>

			<a v-if="!item && $parent.page.user.colonizer" v-bind:href="$root.getUrl('fleet/g'+$parent.page.galaxy+'/s'+$parent.page.system+'/p'+i+'/t0/m7/')" title="Колонизация">
				<span class="sprite skin_e"></span>
			</a>
		</th>
	</tr>
</template>

<script>
	export default {
		name: "row",
		props: ['item', 'i'],
		data: function ()
		{
			return {
				'races': ['', 'Конфедерация', 'Бионики', 'Сайлоны', 'Древние']
			}
		},
		computed: {
			user_status: function ()
			{
				var CurrentPoints 	= this.$parent.page.user.total_points;
				var RowUserPoints 	= this.item.total_points;

				if (!RowUserPoints)
	                RowUserPoints = 0;

				var CurrentLevel  	= CurrentPoints * 5;
				var RowUserLevel  	= RowUserPoints * 5;

	            var time = Math.floor(Date.now() / 1000);

				if (this.item.banned > time && this.item.vacation > 0)
					return "UG";
				else if (this.item.banned > time)
					return "G";
				else if (this.item.vacation > 0)
					return "U";
				else if (this.item.online === 1)
					return "i";
				else if (this.item.online === 2)
					return "iI";
				else if (RowUserLevel < CurrentPoints && RowUserPoints < 50000)
					return "N";
				else if (RowUserPoints > CurrentLevel && CurrentPoints < 50000)
					return "S";
			}
		},
		methods:
		{
			getDebrisClass: function ()
			{
				if (!this.item)
					return '';

				if ((parseInt(this.item.metal) + parseInt(this.item.crystal)) >= 10000000)
					return 'debris_100'
				else if ((parseInt(this.item.metal) + parseInt(this.item.crystal)) >= 1000000)
					return 'debris_50'
				else if ((parseInt(this.item.metal) + parseInt(this.item.crystal)) >= 100000)
					return 'debris_0'

				return '';
			},
			getUserAvatar: function ()
			{
				if (this.item.user_image > 0)
					return this.$root.getUrl('assets/avatars/'+this.item.user_image);
				else if (this.item.avatar > 0)
				{
					if (this.item.avatar !== 99)
						return this.$root.getUrl('assets/images/faces/'+this.item.sex+'/'+this.item.avatar+'s.png');
					else
						return this.$root.getUrl('assets/avatars/upload_'+this.item.user_id+'.jpg');
				}
				else if (this.item.photo.length)
					return this.item.photo;

				return '';
			},
			getStatsPage: function ()
			{
				if (this.item.total_rank < 100)
					return 1;

				return (Math.floor(this.item.total_rank / 100 ) * 100) + 1;
			},
			getUserStatusClass: function ()
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
			sendMessage: function()
			{
				showWindow(this.item.username+': отправить сообщение', this.$root.getUrl('messages/write/'+this.item.user_id+'/'), 680)
			}
		}
	}
</script>